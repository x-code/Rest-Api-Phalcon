<?php

namespace Helper;

use Phalcon\DI;

class TwitterHelper
{

    public static function verifyCredential(
            $oauth_token = "", 
            $oauth_token_secret = ""
        )
    {
        $twitter_auth = DI::getDefault()->get('twitter_auth');
        $twitter_auth->setOauthToken($oauth_token, $oauth_token_secret);
        $credentials = $twitter_auth->get('account/verify_credentials', ['include_email' => true]);
        return $credentials;
    }
}