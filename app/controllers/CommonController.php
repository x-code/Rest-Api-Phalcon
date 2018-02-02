<?php
namespace Controllers;

/**
 * Add common controller
 *
 * @Route(route = "/v1/common/")
 */
class CommonController extends \Application\Controller
{
    /**
     * Ping method.
     *
     * @param        profile_id
     * @return       Address List
     * @Route(method =      'post', route = 'ping', authentication = true)
     */
    public function pongAction()
    {
        return parent::responseSuccess(['status' => '000000', 'message' => "success.", 'data' => [
            'response' => 'pong',
            'version'  => $this->di->get('config')->version,
            'time'     => date('d/m/Y H:i:s', time()),
        ]]);
    }
}
