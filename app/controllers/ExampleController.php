<?php
namespace Controllers;

/**
 * This is the class description
 *
 * @Route(route = "/v1/")
 */
class ExampleController extends \Phalcon\Mvc\Controller
{
    /**
     * This is a method
     *
     * @Route(method = "get", route = "ping", authentication = false)
     */
    public function getPingAction()
    {
        // $response = new Response();
        // $response->setStatusCode(401, "Unauthorized");
        // $response->setContent("Access is not authorized");
        // return $response;
        // print_r($this->di->get('arman'));
        //die('artar');
        echo "test";
    }
    /**
     * This is a method
     *
     * @Route(method = "post", route = "ping", authentication = true)
     */
    public function pingAction()
    {
        $this->response->setContentType('application/json', 'UTF-8');
        //Set the content of the response
        $this->response->setStatusCode(404);
        $this->response->setContent($this->request->getRawBody());
        return $this->response;
    }
    /**
     * This is a method
     *
     * @Route(method = "post", route = "test/{id}", authentication = true)
     */
    public function testAction($id)
    {
        echo "test (id: $id)";
    }

    /**
     * @Route(method =      'post', route = 'skip/{name}', authentication = false)
     * @param        [type]
     * @return       [type]
     */
    public function skipAction($name)
    {
        echo "auth skipped ($name)";
    }
}
