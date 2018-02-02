<?php

/**
 * Small Micro application to run simple/rest based applications
 *
 * @package Application
 * @author Jete O'Keeffe
 * @version 1.0
 * @link http://docs.phalconphp.com/en/latest/reference/micro.html
 * @example
$app = new Micro();
$app->setConfig('/path/to/config.php');
$app->setAutoload('/path/to/autoload.php');
$app->get('/api/looks/1', function() { echo "Hi"; });
$app->finish(function() { echo "Finished"; });
$app->run();
 */

namespace Application;

use Abraham\TwitterOAuth\TwitterOAuth;
use Aws\S3\S3Client;
use Interfaces\IRun as IRun;
use League\Fractal\Manager;
use Phalcon\Annotations\Adapter\Files as MemoryAdapter;
use Phalcon\DI;
// use Phalcon\Annotations\Adapter\Memory as MemoryAdapter;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Logger\Formatter\Line as LineFormatter;

class Micro extends \Phalcon\Mvc\Micro implements IRun
{

    protected $loader;

    /**
     * Pages that doesn't require authentication
     * @var array
     */
    protected $_noAuthPages;

    /**
     * Constructor of the App
     */
    public function __construct()
    {
        $this->_noAuthPages = array();
    }

    /**
     * Set Dependency Injector with configuration variables
     *
     * @throws Exception        on bad database adapter
     * @param string $file        full path to configuration file
     */
    public function setConfig($file)
    {
        if (!file_exists($file)) {
            throw new \Exception('Unable to load configuration file');
        }

        $di = new \Phalcon\DI\FactoryDefault();
        $di->set('config', new \Phalcon\Config(require $file));

        $di->set('logger', function () use ($di) {
            $logger    = new FileAdapter($di->get('config')->log_location);
            $formatter = new LineFormatter("[%type%] %date% - %message%");
            $logger->setFormatter($formatter);
            return $logger;
        });
        $di->set('view', function () use ($di) {
            $view = new \Phalcon\Mvc\View\Simple();
            $view->setViewsDir('../app/views/');
            return $view;
        }, true);

        $di->set('db', function () use ($di) {
            $type  = strtolower($di->get('config')->database->adapter);
            $creds = array(
                'host'     => $di->get('config')->database->host,
                'port'     => $di->get('config')->database->port,
                'username' => $di->get('config')->database->username,
                'password' => $di->get('config')->database->password,
                'dbname'   => $di->get('config')->database->dbname,
            );

            if (isset($di->get('config')->database->sslmode)) {
                $creds['sslmode'] = $di->get('config')->database->sslmode;
            }

            if (isset($di->get('config')->database->sslmode)) {
                $creds['sslrootcert'] = $di->get('config')->database->sslrootcert;
            }

            if ($type == 'mysql') {
                $connection = new \Phalcon\Db\Adapter\Pdo\Mysql($creds);
            } else if ($type == 'postgres') {
                $connection = new \Phalcon\Db\Adapter\Pdo\Postgresql($creds);
            } else if ($type == 'sqlite') {
                $connection = new \Phalcon\Db\Adapter\Pdo\Sqlite($creds);
            } else {
                throw new Exception('Bad Database Adapter');
            }

            return $connection;
        });

        $di->set('S3Client', function () use ($di) {

            // Instantiate an Amazon S3 client.
            $s3Client = new S3Client([
                'version'     => $di->get('config')->aws->version,
                'region'      => $di->get('config')->aws->region,
                'signature'   => $di->get('config')->aws->signature,
                'credentials' => [
                    'key'    => $di->get('config')->aws->credentials->key,
                    'secret' => $di->get('config')->aws->credentials->secret,
                ],
            ]);
            return $s3Client;
        });

        $di->set('redis_client', function () use ($di) {
            $redis_client = new \Predis\Client([
                'scheme'   => $di->get('config')->redis->scheme,
                'host'     => $di->get('config')->redis->host,
                'port'     => $di->get('config')->redis->port,
                'database' => $di->get('config')->redis->database,
            ]);

            return $redis_client;
        });

        $di->set('fractal', function () use ($di) {
            $fractal = new Manager();
            return $fractal;
        });

        $di->set('fb_sdk', function () use ($di) {
            $fb = new \Facebook\Facebook([
                'app_id'                => $di->get('config')->facebook->app_id,
                'app_secret'            => $di->get('config')->facebook->app_secret,
                'default_graph_version' => $di->get('config')->facebook->graph_version,
            ]);
            return $fb;
        });

        $di->set('twitter_auth', function () use ($di) {
            $connection = new TwitterOAuth($di->get('config')->twitter->consumer_key, $di->get('config')->twitter->consumer_secret);
            return $connection;
        });

        $this->setDI($di);
    }

    /**
     * Set namespaces to tranverse through in the autoloader
     *
     * @link http://docs.phalconphp.com/en/latest/reference/loader.html
     * @throws Exception
     * @param string $file        map of namespace to directories
     */
    public function setAutoload($file, $dir)
    {
        if (!file_exists($file)) {
            throw new \Exception('Unable to load autoloader file');
        }

        // Set dir to be used inside include file
        $namespaces = include $file;

        $this->loader = new \Phalcon\Loader();
        $this->loader->registerNamespaces($namespaces)->register();
    }

    /**
     * Set Routes\Handlers for the application
     *
     * @throws Exception
     * @param file            file thats array of routes to load
     */
    public function setRoutes($file)
    {

        if (!file_exists($file)) {
            throw new \Exception('Unable to load routes file');
        }

        $routes = include $file;

        if (!empty($routes)) {
            foreach ($routes as $obj) {

                // Which pages are allowed to skip authentication
                if (isset($obj['authentication']) && $obj['authentication'] === false) {

                    $method = strtolower($obj['method']);

                    if (!isset($this->_noAuthPages[$method])) {
                        $this->_noAuthPages[$method] = array();
                    }

                    $this->_noAuthPages[$method][] = $obj['route'];
                }

                $controllerName = class_exists($obj['handler'][0]) ? $obj['handler'][0] : false;
                // var_dump($controllerName);
                // die();
                if (!$controllerName) {
                    throw new \Exception("Wrong controller name in routes ({$obj['handler'][0]})");
                }

                $controller       = new $controllerName;
                $controllerAction = $obj['handler'][1];

                switch ($obj['method']) {
                    case 'get':
                        $this->get($obj['route'], array($controller, $controllerAction));
                        break;
                    case 'post':
                        $this->post($obj['route'], array($controller, $controllerAction));
                        break;
                    case 'delete':
                        $this->delete($obj['route'], array($controller, $controllerAction));
                        break;
                    case 'put':
                        $this->put($obj['route'], array($controller, $controllerAction));
                        break;
                    case 'head':
                        $this->head($obj['route'], array($controller, $controllerAction));
                        break;
                    case 'options':
                        $this->options($obj['route'], array($controller, $controllerAction));
                        break;
                    case 'patch':
                        $this->patch($obj['route'], array($controller, $controllerAction));
                        break;
                    default:
                        break;
                }

            }
        }
    }

    public function loadRoutes()
    {
        $controller_path = $this->loader->getNamespaces()['Controllers'] . "*Controller.php";
        $di              = new \Phalcon\DI\FactoryDefault();
        $di->set('config', new \Phalcon\Config(require dirname(__DIR__) . "/../../app/config/config.php"));
        // echo $_SERVER["DOCUMENT_ROOT"]. "/app/config/config.php";
        // die();
        foreach (glob($controller_path) as $controller) {
            $className = 'Controllers\\' . basename($controller, '.php');
            // $controllerName = 'Controllers\ExampleController';
            $controllerName = $className;
            $controller     = new $controllerName;
            $reader         = new MemoryAdapter([
                'annotationsDir' => $di->get('config')->annotation_location,
            ]);

            // For Development use Memory
            // $reader = new MemoryAdapter();

            $reflector = $reader->get($controllerName);

            $annotations = $reflector->getClassAnnotations();

            $method_annotations = $reflector->getMethodsAnnotations();

            if (is_array($method_annotations) && is_object($annotations)) {
                foreach ($annotations as $annotation) {
                    if ($annotation->getArguments()['route'] !== false) {
                        $root_url = $annotation->getArguments()['route'];
                    } else {
                        $root_url = "/";
                    }
                }

                foreach ($method_annotations as $key => $value) {
                    $controllerAction = $key;

                    foreach ($value->getAnnotations() as $key2 => $value2) {
                        $method         = $value2->getArguments()['method'];
                        $route          = $value2->getArguments()['route'];
                        $authentication = $value2->getArguments()['authentication'];

                        $abs_route = $root_url . $route;
                        if (isset($authentication) && $authentication === false) {

                            $method = strtolower($method);

                            if (!isset($this->_noAuthPages[$method])) {
                                $this->_noAuthPages[$method] = array();
                            }

                            $this->_noAuthPages[$method][] = $abs_route;
                        }

                        switch ($method) {
                            case 'get':
                                $this->get($abs_route, array($controller, $controllerAction));
                                break;
                            case 'post':
                                $this->post($abs_route, array($controller, $controllerAction));
                                break;
                            case 'delete':
                                $this->delete($abs_route, array($controller, $controllerAction));
                                break;
                            case 'put':
                                $this->put($abs_route, array($controller, $controllerAction));
                                break;
                            case 'head':
                                $this->head($abs_route, array($controller, $controllerAction));
                                break;
                            case 'options':
                                $this->options($abs_route, array($controller, $controllerAction));
                                break;
                            case 'patch':
                                $this->patch($abs_route, array($controller, $controllerAction));
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Set events to be triggered before/after certain stages in Micro App
     *
     * @param object $event        events to add
     */
    public function setEvents(\Phalcon\Events\Manager $events)
    {
        $this->setEventsManager($events);
    }

    /**
     *
     */
    public function getUnauthenticated()
    {
        return $this->_noAuthPages;
    }
    /**
     * Main run block that executes the micro application
     *
     */
    public function run()
    {

        $this->before(function () {
            DI::getDefault()->get('logger')->log(json_encode([
                "action" => "request",
                "method" => $this->request->getMethod(),
                "uri"    => $this->request->getURI(),
                "header" => $this->request->getHeaders(),
                "body"   => json_decode($this->request->getRawBody()),
            ]));
            return true;
        });

        $this->after(function () {
            $this->response->setHeader('x-session', $this->request->getHeaders()['X-Session']);
            DI::getDefault()->get('logger')->log(json_encode([
                "action" => "response",
                "method" => $this->request->getMethod(),
                "uri"    => $this->request->getURI(),
                "header" => $this->request->getHeaders(),
                "body"   => json_decode($this->response->getContent()),
            ]));
            return true;
        });

        // Handle any routes not found
        $this->notFound(function () {
            $response = new \Phalcon\Http\Response();
            $response->setHeader('x-session', $this->request->getHeaders()['X-Session']);
            $response
                ->setStatusCode(404, 'Not Found')
                ->setContentType('application/json', 'UTF-8')->sendHeaders();
            $response->setContent(json_encode(["status" => "011002", "message" => "API NOT FOUND."], JSON_UNESCAPED_SLASHES));

            DI::getDefault()->get('logger')->log(json_encode([
                "action" => "response",
                "method" => $this->request->getMethod(),
                "uri"    => $this->request->getURI(),
                "header" => $this->request->getHeaders(),
                "body"   => json_decode($response->getContent()),
            ]));

            $response->send();
        });

        $this->error(function ($e) {
            $response = new \Phalcon\Http\Response();
            $response->setHeader('x-session', $this->request->getHeaders()['X-Session']);
            $response
                ->setStatusCode(500, 'Server Error')
                ->setContentType('application/json', 'UTF-8')->sendHeaders();
            $response->setContent(json_encode(["status" => "011003", "message" => "Server Error." . json_encode($e->getMessage())], JSON_UNESCAPED_SLASHES));

            DI::getDefault()->get('logger')->log(json_encode([
                "action" => "response",
                "method" => $this->request->getMethod(),
                "uri"    => $this->request->getURI(),
                "header" => $this->request->getHeaders(),
                "body"   => json_decode($response->getContent()),
            ]));

            $response->send();
        });

        $this->handle();

    }

}
