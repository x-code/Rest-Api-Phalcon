<?php
namespace Controllers;

use \Models\Device as Device;

class DeviceController extends \Application\Controller
{
    public function provisioningAction()
    {

        $body = parent::getBody();
        if ($body === null) {
            return parent::responseClientError(['status' => '002001', "message" => "Body is required."]);
        }
        $device              = new Device();
        $device->imei        = $body['imei'];
        $device->device_name = $body['device_name'];
        $device->os          = $body['os'];
        $device->app_version = $body['app_version'];

        try {
            if ($device->save() == false) {
                $data['status'] = "002004";
                $data['token']  = $user->getUuid();

                foreach ($user->getMessages() as $message) {
                    $data['message'] .= $message . ".";
                }
            } else {
                $data = [
                    "status"  => "000000",
                    "message" => "Success.",
                ];
                $data['data']['session'] = $device->session;
            }
        } catch (Phalcon\Db\Exception $e) {
            return parent::responseServerError(['status' => '002005', "message" => $e->getMessage()]);
        }
        return parent::responseSuccess($data);
    }
}
