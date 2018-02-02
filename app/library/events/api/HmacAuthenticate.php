<?php

/**
 * Event that Authenticates the client message with HMac
 *
 * @package Events
 * @subpackage Api
 * @author Jete O'Keeffe
 * @version 1.0
 */

namespace Events\Api;

use Interfaces\IEvent as IEvent;
use \Models\Device as Device;
use Phalcon\DI;
use Helper\SessionHelper as SessionHelper;

class HmacAuthenticate extends \Phalcon\Events\Manager implements IEvent {

    /**
     * Constructor
     *
     * @param object
     * @param string
     */
    public function __construct() {
        // Add Event to validate message
        $this->handleEvent();
    }

    /**
     * Setup an Event
     *
     * Phalcon event to make sure client sends a valid message
     * @return FALSE|void
     */
    public function handleEvent() {

        $this->attach('micro', function ($event, $app) {
            if ($event->getType() == 'beforeExecuteRoute') {

                $session = $app->request->getHeader('x-session');
                
                // security checks, deny access by default
                $allowed = false;

                // last try - login without auth for open calls
                $method = strtolower($app->router->getMatchedRoute()->getHttpMethods());
                $unAuthenticated = $app->getUnauthenticated();

                if (isset($unAuthenticated[$method])) {
                        $unAuthenticated = array_flip($unAuthenticated[$method]);

                        if (isset($unAuthenticated[$app->router->getMatchedRoute()->getPattern()])) {
                                $allowed = true;
                        } 
                }
                
                if (!$allowed) { // already authorized skip this part
                    if (SessionHelper::getSession($session) != false) { // 1st security level - check hashes
                        return true; // gain access to open call
                    }
                    
                    
                    // still not authorized, get out of here
                    $app->response->setContentType('application/json', 'UTF-8');
                    $app->response->setStatusCode(401, "Unauthorized");
                    $app->response->setContent(json_encode(["status" => "009001", "message" => "Access denied."]));
                    $app->response->send();

                    return false;
                    
                }

            }
        });
    }
}
