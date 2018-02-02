<?php

namespace Helper;

use Phalcon\DI;

class QueueHelper 
{

    public static function emailVerificationQueue($email = "", $lastname = "", $token = "")
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('notification', 'EmailVerification', [
                "appname" => "Traveller Buddy",
                "email" => $email,
                "lastname" => $lastname,
                "token" => $token,
            ]);

    }

    public static function traveldocQueue($traveldoc)
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('traveldoc', 'TraveldocTask', [
                "traveldoc_id" => $traveldoc->id,
                "issuing_country" => $traveldoc->passport_country_iso3,
                "nationality" => $traveldoc->passport_country_iso3,
                "departure_country" => $traveldoc->origin_country_iso3,
                "arrival_country" => $traveldoc->destination_country_iso3,
                "passport_expiry_date" => $traveldoc->passport_expiry_date,
                "arrival_date" => $traveldoc->arrival_date,
            ]);

    }

    public static function tripItemSequence($trip_id = "")
    {
        $config = DI::getDefault()->get('config');

        \Resque::setBackend($config->redis->host . ":" . $config->redis->port);
        \Resque::enqueue('system', 'TripItemReorderSequenceTask', [
                "trip_id" => $trip_id,
            ]);
    }

}