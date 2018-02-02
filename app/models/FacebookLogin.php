<?php

namespace Models;

use Phalcon\Mvc\Model\Behavior\SoftDelete;

class FacebookLogin extends \Models\BasePublic
{
    
    public $user_id;

    public $access_token;

    public $social_id;

    public $social_data;

    public $email;

    const DELETED = true;

    const NOT_DELETED = false;

    public function initialize() 
    {
        $this->setSource("users");

        $this->addBehavior(new SoftDelete(
            array(
                'field' => 'is_deleted',
                'value' => FacebookLogin::DELETED
            )
        ));
    }

    public static function findByFacebookId($social_id = 0)
    {
        return FacebookLogin::findFirst([
                "is_deleted = false AND social_id = ?0",
                "bind" => [$social_id]
            ]);
    }

}