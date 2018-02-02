<?php

namespace Helper;

use Phalcon\DI;

class RedisHelper 
{

    public static function shareFullItineraryQueue($trip_uuid = '', $user_id = 0, $email = "")
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('share', 'ShareFullItinerary', [
                "trip_uuid" => $trip_uuid,
                "user_id" => $user_id,
                "destination_email" => $email,
            ]);

    }

    public static function emailVerificationQueue($email = "", $lastname = "", $token = "")
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('email_notification', 'EmailVerification', [
                "appname" => "Traveller Buddy",
                "email" => $email,
                "lastname" => $lastname,
                "token" => $token,
            ]);

    }

    public static function emailWelcomeQueue($email = "", $lastname = "", $token = "")
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('email_notification', 'EmailWelcome', [
                "appname" => "Traveller Buddy",
                "email" => $email,
                "lastname" => $lastname,
                "token" => $token,
            ]);

    }

    public static function emailForgotPassword($email = "", $lastname = "", $token = "")
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('email_notification', 'EmailForgotPassword', [
                "appname" => "Traveller Buddy",
                "email" => $email,
                "lastname" => $lastname,
                "token" => $token,
            ]);

    }

    public static function pushNotification($notification_id = 0, $user_id = 0)
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('notification', 'PushNotification', [
                "notification_id" => $notification_id,
                "user_id" => $user_id,
            ]);
    }

    public static function createFlightstatsAlert(
            $carrier,
            $flight_no,
            $departure_airport,
            $departure_date,
            $user_id
        )
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('flightstats', 'FlightstatsCreateAlert', [
            'carrier' => $carrier,
            'flight_no' => $flight_no,
            'departure_airport' => $departure_airport,
            'departure_date' => $departure_date,
            'user_id' => $user_id,
            ], true);
    }

    public static function worldmateTask($result)
    {
        $config = DI::getDefault()->get('config');
        
        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('worldmate', 'WorldmateResultTask', $result);
    }

    public static function emailChangePasswordSuccess($email = "", $lastname = "")
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('email_notification', 'EmailChangePasswordSuccess', [
                "appname" => "Traveller Buddy",
                "email" => $email,
                "lastname" => $lastname,
            ]);

    }

    public static function SuccessCreateTrip($trip_name = '', $lastname = "", $email = "")
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('email_notification', 'SuccessCreateTrip', [
                "tripname" => $trip_name,
                "lastname" => $lastname,
                "email" => $email,
            ]);

    }

}