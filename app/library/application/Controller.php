<?php

namespace Application;

use \Models\User as User;
use \Models\Device as Device;
use \Phalcon\Http\Request;


class Controller extends \Phalcon\Mvc\Controller {
	public function getCurrentDevice() {
		$session = $this->request->getHeader('x-session');
		$device = Device::findDeviceBySession($session);
		return $device;
	}

	public function getCurrentUser() {
		$session = $this->request->getHeader('x-session');
		$user = User::getUserBySession($session);
		return $user;
	}

	public function getBody() {
		return $this->request->getJsonRawBody(true);
	}

	public function getRawBody() {
		return $this->request->getRawBody();
	}

	public function responseSuccess($data = array()) {
		$this->response->setStatusCode(200);
		return $this->getResponse($data);
	}

	public function responseNotFound($data = array()) {
		// $this->response->setStatusCode(404);
		$this->response->setStatusCode(200);
		return $this->getResponse($data);
	}

	public function responseServerError($data = array()) {
		// $this->response->setStatusCode(500);
		$this->response->setStatusCode(200);
		return $this->getResponse($data);
	}

	public function responseCreated($data = array()) {
		// $this->response->setStatusCode(201);
		$this->response->setStatusCode(200);
		return $this->getResponse($data);
	}

	public function responseClientError($data = array()) {
		// $this->response->setStatusCode(400);
		$this->response->setStatusCode(200);
		return $this->getResponse($data);
	}

	public function responseUnauthorized($data = array()) {
		// $this->response->setStatusCode(401);
		$this->response->setStatusCode(200);
		return $this->getResponse($data);
	}

	public function getResponse($data = array()) {
		
		$request = new Request();
		$body  = $this->request->getJsonRawBody(true);
        $logFile = "/var/www/giantcatalog.dev.tbvlabs.com/public/logs/giant.log";
        $routeAction = $_SERVER["REQUEST_URI"];
        $ip = \Helper\IpHelper::get_ip();
        $stdOut = date("Y-m-d H:i:s")."\t".$ip."\t[".$routeAction."]\t".json_encode($body)." ".json_encode($data);
        exec('echo "'.addslashes($stdOut).'" >> '.$logFile);
        exec('echo "" >> '.$logFile);
        
		$this->response->setContentType('application/json', 'UTF-8');
		$this->response->setContent(json_encode($data, JSON_UNESCAPED_SLASHES));
		return $this->response;
	}

}

?>
