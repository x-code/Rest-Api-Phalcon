<?php

/**
 * Settings to be stored in dependency injector
 */

$settings = array(
'env' => 'production',
    'version'             => '0.0.1',
    'baseurl'             => 'http://giantcatalog.dev.tbvlabs.com/',
    'base_url'            => 'http://giantcatalog.dev.tbvlabs.com/',
    'verification_url'    => 'http://giantcatalog.dev.tbvlabs.com/',
    'forgot_password_url' => 'http://giantcatalog.dev.tbvlabs.com/',
    'log_location'        => '/var/www/giantcatalog.dev.tbvlabs.com/public/logs/' . date('Y-m-d') . '.log',
    'annotation_location' => "/var/www/giantcatalog.dev.tbvlabs.com/public/cache/",
    'database'            => array(
        'adapter'  => 'Mysql',
        'host'     => 'localhost',
        'username' => 'giant',
        'password' => 'giant.1234',
        'dbname'   => 'giant',
    ),
    'security'	=> array(
        "salt" => "",
    ),
    'assets'  => array(
        "profile_url_path" => "http://giantcatalog.dev.tbvlabs.com/public/images/",
        "profile_picture_path" => "/var/www/giantcatalog.dev.tbvlabs.com/public/images/",
    ),
    'redis'   => [
        'scheme'   => 'tcp',
        'host'     => '127.0.0.1',
        'port'     => 6379,
        'database' => 2,
        'ttl'      => "+1 month",
    ],
    'facebook'  => [
        'app_id'        => '236489873447778',
        'app_secret'    => '83f6a1032690821d95d0a457863e1977',
        'graph_version' => 'v2.4',
        'callback_uri'  => '',
    ],
      
);

return $settings;
