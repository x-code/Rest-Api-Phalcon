<?php

namespace Helper;

use Phalcon\DI;

class GoogleHelper
{

    public static function getTokenFromCode($code = "")
    {
        $fb_sdk = DI::getDefault()->get('fb_sdk');
        $config = DI::getDefault()->get('config');
        
        $access_token = $fb_sdk->getOAuth2Client()->getAccessTokenFromCode(
                $code, 
                $config->facebook->callback_uri
            );

        return $access_token;
    }

    public static function getUserProfile($access_token = "")
    {
        $fb_sdk = DI::getDefault()->get('fb_sdk');
        $config = DI::getDefault()->get('config');

        return $fb_sdk->get(
                '/me?fields=name, first_name, last_name, gender, email, birthday',
                $access_token
            );
    }

}