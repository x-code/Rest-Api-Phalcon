<?php
namespace Models;

use Models\Resource;

class Profile extends \Models\BasePublic
{
    public $name;
    public $first_name;
    public $last_name;
    public $photo;
    public $title;
    public $gender;
    public $marital;
    public $residence_country;
    public $date_of_birth;
    public $country_of_birth;
    public $nationality;
    public $occupation;

    public $user_id;
    public $is_complete;
    public $is_notified;
    public function getSource()
    {
        return "profiles";
    }
    public function initialize()
    {
        $this->setSource("profiles");
    }
    public function beforeCreate()
    {
        parent::beforeCreate();
        $this->is_complete = false;
        $this->is_notified = false;
    }
    public static function getAttribute()
    {
        $params = parent::getAttribute(new Profile());
        return $params;
    }
    public static function findProfile($uuid = "", $user_id = 0)
    {
        $profile = self::findFirst(array(
            "uuid = ?0 AND is_deleted = false AND user_id = ?1",
            "bind" => [$uuid, $user_id],
        ));
        return $profile;
    }
    public static function findProfileById($user_id = 0)
    {
        $profile = self::findFirst(array(
            "is_deleted = false AND user_id = ?0",
            "bind" => [$user_id],
        ));
        return $profile;
    }
    public static function findProfileByUuid($uuid = "")
    {
        $profile = self::findFirst(array(
            "uuid = ?0 AND is_deleted = false",
            "bind" => [$uuid],
        ));
        return $profile;
    }
    public function isComplete()
    {
        $address_count          = Address::countByProfileId($this->id);
        $driver_license_count   = DriverLicense::countByProfileId($this->id);
        $identificationById     = Identification::findByProfileId($this->id);
        $passport_count         = Passport::countByProfileId($this->id);
        $RewardProgramme_count  = RewardProgramme::countByProfileId($this->id);
        $ImportantContact_count = ImportantContact::countByProfileId($this->id);
        // $Signature_count = Signature::countByProfileId($this->id);
        $identification_count = Resource::countIndentificationByProfileId($identificationById->id);
        $Signature_count      = Resource::countSignatureByProfileId($this->id);
        $PassportById         = Passport::findFirst([
            "is_deleted = false AND profile_id = ?0",
            "bind" => [$this->id],
        ]);
        $passport_true = 0;
        if (!empty($PassportById->first_name) 
            && !empty($PassportById->last_name) 
            && !empty($PassportById->passport_no) 
            && !empty($PassportById->code)) {
            $passport_true = 1;
        }
        if (
            $address_count != 0 &&
            $driver_license_count != 0 &&
            $identification_count != 0 &&
            $passport_count != 0 &&
            $RewardProgramme_count != 0 &&
            $ImportantContact_count != 0 &&
            $Signature_count != 0 &&
            $passport_true != 0
        ) {
            return true;
        }
        return false;
    }
    public static function profileSetup($user_id)
    {
        $profile_count = Profile::count(array(
            "is_deleted = false AND user_id = ?0",
            "bind" => [$user_id],
        ));

        if ($profile_count > 0) {
            return false;
        } else {
            return true;
        }
    }
    public static function checkEmail($user_id, $email)
    {
        $profile_count = Profile::count(array(
            "is_deleted = false AND user_id = ?0 AND email = ?1",
            "bind" => [$user_id, $email],
        ));

        if ($profile_count > 0) {
            return false;
        } else {
            return true;
        }
    }
}
