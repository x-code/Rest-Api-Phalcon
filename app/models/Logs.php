<?php
namespace Models;

class Logs extends \Phalcon\Mvc\Model
{
    public $id;
    public $user_id;
    public $request_uri;
    public $request_data;
    public $response_data;
    public $created_date;
    public function getSource()
    {
        return "logs";
    }
    public function initialize()
    {
        $this->setSource("logs");
    }

    public function beforeCreate()
    {
        $logs->ip_address = \Helper\IpHelper::get_ip();
    }
    public function beforeUpdate()
    {
        $logs->ip_address = \Helper\IpHelper::get_ip();
    }

}
