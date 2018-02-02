<?php

namespace Helper;

use Phalcon\DI;
use Helper\RedisHelper;
use Models\Notification;
use Models\Profile;

class NotificationHelper
{
    public static function profileIncomplete($profile = false)
    {

        $new_profile = Profile::findProfileByUuid($profile->uuid);
        
        if (!$new_profile->isComplete()) {
            if (!$profile->is_notified) {
                $new_profile->is_notified = true;

                $notif = new Notification();
                $notif->user_id = $profile->user_id;
                $notif->profile_id = $profile->id;
                $notif->category = "TRAVELERBUDDY";
                $notif->title = "Profile Incomplete";
                $notif->message = "Incomplete data for profile " . $profile->name . ".";
                $notif->trip_id = 0;
                $notif->trip_uuid = "";
                $notif->type = "missing_profile";
                $notif->url = "";
                $notif->data = json_encode([
                        "profile_id" => $profile->uuid
                    ]);

                if ($notif->save() != false) {
                    RedisHelper::pushNotification($notif->id, $profile->user_id);
                } else {

                }
            }
        } else {
            $new_profile->is_complete = true;
            $new_profile->is_notified = true;
        }
        $new_profile->save();

        // if (!$profile->isComplete()) {
        //     if (!$profile->is_notified) {
        //         $profile->is_notified = true;

        //         $notif = new Notification();
        //         $notif->user_id = $profile->user_id;
        //         $notif->profile_id = $profile->id;
        //         $notif->category = "TRAVELERBUDDY";
        //         $notif->title = "Profile Incomplete";
        //         $notif->message = "Incomplete data for profile " . $profile->name . ".";
        //         $notif->trip_id = 0;
        //         $notif->trip_uuid = "";
        //         $notif->type = "missing_profile";
        //         $notif->url = "";
        //         $notif->data = json_encode([
        //                 "profile_id" => $profile->uuid
        //             ]);

        //         if ($notif->save() != false) {
        //             RedisHelper::pushNotification($notif->id, $profile->user_id);
        //         } else {

        //         }
        //     }
        // } else {
        //     $profile->is_complete = true;
        //     $profile->is_notified = true;
        // }
        // $profile->save();
    }
}