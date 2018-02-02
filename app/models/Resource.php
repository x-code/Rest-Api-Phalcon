<?php
namespace Models;

class Resource extends \Models\BasePublic
{
    public $name;
    public $file_name;
    public $file_path;
    public $s3_data;
    public $type;
    public $parent_id;
    public function getSource()
    {
        return "resources";
    }
    public function initialize()
    {
        $this->setSource("resources");
    }
    public static function countSignatureByProfileId($profile_id = 0)
    {
        $profile = self::count(array(
            "is_deleted = false AND type = 'signature' AND parent_id = ?0",
            "bind" => [$profile_id],
        ));
        return $profile;
    }
    public static function countIndentificationByProfileId($profile_id = 0)
    {
        $profile = self::count(array(
            "is_deleted = false AND type = 'indentification' AND parent_id = ?0",
            "bind" => [$profile_id],
        ));
        return $profile;
    }
    public static function countPassportByProfileId($profile_id = 0)
    {
        $profile = self::count(array(
            "is_deleted = false AND type = 'passport' AND parent_id = ?0",
            "bind" => [$profile_id],
        ));
        return $profile;
    }
}
