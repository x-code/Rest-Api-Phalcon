<?php
namespace Controllers;

use \Models\Logs;
use \Phalcon\Http\Request;

/**
 * Logs Controller
 *
 * @Route(route = "/v1/logs/")
 */
class LogsController extends \Application\Controller
{
    /**
     * Create Log
     * @Route(method = 'post', route = 'create', authentication = false)
     */
    public function createLogAction()
    {
        $request = new Request();

        $logs                = new Logs();
        $logs->user_id       = $request->getPost("user_id");
        $logs->type          = $request->getPost("type");
        $logs->request_uri   = $request->getPost("request_uri");
        $logs->request_data  = $request->getPost("request_data");
        $logs->response_data = $request->getPost("response_data");
        $logs->save();
    }
}
