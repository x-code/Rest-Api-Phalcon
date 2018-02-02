<?php

namespace Helper;

use Phalcon\DI;
use \Models\User as User;
use \Models\Device as Device;

class SessionHelper 
{
	
	public static function storeSession($session = "", $user_id = 0)
	{
		$client = DI::getDefault()->get('redis_client');
		$client->set("{session:" . $session . "}", $user_id);
	}

	public static function getSession($session = "")
	{
		$client = DI::getDefault()->get('redis_client');
		$value = $client->get("{session:" . $session . "}");
		if ($value != false) {
		    return $value;
		} else {
		    return false;
		}
	}

	public static function invalidateOtherSessionByGcmRegID($gcm_regid = "", $device_id = 0)
	{
		$excluded_keys = [
			'testtest',
			'xxxx',
			'-',
			'1234567890',
			'1234567891',
			'assss',
			'bbbbbebek',
			'bbbbbebek_lalalali',
		];
		if (in_array($gcm_regid, $excluded_keys)) {
			return true;
		}

		try {
			$manager = new \Phalcon\Mvc\Model\Transaction\Manager();
			$transaction = $manager->get();

			$client = DI::getDefault()->get('redis_client');
			$devices = Device::find([
					"is_deleted = false AND gcm_regid = ?0 AND id != ?1",
					"bind" => [$gcm_regid, $device_id]
				]);

			$tokens = [];
			foreach ($devices as $device) {
				$device->setTransaction($transaction);

				$session = sprintf("{session:%s}", $device->session);
				$tokens[] = $session;

				$device->delete();
			}
			
			$transaction->commit();
		} catch(\Exception $e) {
            echo 'Failed, reason: ', $e->getMessage();
            return false;
        }

		foreach ($tokens as $token) {
			$client->del($token);
		}
	}

	public static function invalidateSessionByUserId($user_id = 0)
	{
		try {
			$manager = new \Phalcon\Mvc\Model\Transaction\Manager();
			$transaction = $manager->get();

			$client = DI::getDefault()->get('redis_client');
			$devices = Device::find([
					"is_deleted = false AND user_id = ?0",
					"bind" => [$user_id]
				]);

			$tokens = [];
			foreach ($devices as $device) {
				$device->setTransaction($transaction);

				$session = sprintf("{session:%s}", $device->session);
				$tokens[] = $session;

				$device->delete();
			}
			
			$transaction->commit();
		} catch(\Exception $e) {
            echo 'Failed, reason: ', $e->getMessage();
            return false;
        }

		foreach ($tokens as $token) {
			$client->del($token);
		}

	}

}