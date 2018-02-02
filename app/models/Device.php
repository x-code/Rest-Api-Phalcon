<?php
namespace Models;

use Phalcon\Mvc\Model\Behavior\SoftDelete;

class Device extends \Models\BasePublic
{
    const DELETED     = true;
    const NOT_DELETED = false;
    public $app_version;
    public $build_name;
    public $build_version;
    public $gcm_regid;
    public $imei;
    public $modified_date;
    public $msisdn;
    public $operator_name;
    public $phone_manufacturer;
    public $phone_model;
    public $primary_email;
    public $sim_number;
    public $session;
    public $user_id;
    public function getSource()
    {
        return "devices";
    }
    public function initialize()
    {
        $this->setSource("devices");
        $this->addBehavior(new SoftDelete(
            array(
                'field' => 'is_deleted',
                'value' => Device::DELETED,
            )
        ));
    }
    public function beforeSave()
    {
        $this->last_login = date('c');
        $data = $this->uuid . $this->phone_model . $this->primary_email . $this->app_version . $this->gcm_regid;
        $this->session    = hash('sha256', $data, false);
    }
    public function sessionActive($session = "")
    {
        try {
            $connection = $this->di->get('db');
            $result_set = $connection->query("SELECT * FROM devices WHERE session = '$session' AND is_deleted = false");
            error_log("SELECT * FROM devices WHERE session = '$session' AND is_deleted = false");
            if ($result_set->numRows($result_set) > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Phalcon\Db\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
        }
        return false;
    }
    public static function findDeviceBySession($session = "")
    {

        $device = Device::findFirst([
            "session = ?0",
            "bind" => [$session],
        ]);
        return $device;
    }
}
