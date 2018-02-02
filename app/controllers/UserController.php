<?php
namespace Controllers;

use Underscore\Types\Arrays;
use Helper\SessionHelper as SessionHelper;
use Helper\FacebookHelper as FacebookHelper;
use Ramsey\Uuid\Uuid;
use \Models\Device as Device;
use \Models\Profile as Profile;
use \Models\Resource as Resource;
use \Models\User as User;
use \Phalcon\Http\Request;
use \Phalcon\Http\Request\File;
use \Phalcon\Http\Response;


/**
 * API Related To user Auth.
 *
 * @Route(route = "/v1/user/")
 */
class UserController extends \Application\Controller
{
    /**
     * Device Registration.
     *
     * @return       Device Session
     * @Route(method =      'post', route = 'regdevice', authentication = false)
     */
    public function regDeviceAction()
    {
        $body = parent::getBody();
        if ($body === null) {
            return parent::responseClientError(['status' => '001001', "message" => "Body is required."]);
        }
        // echo json_encode($body["primary_email"]);
        // $result = $this->db->query("SELECT UUID_SHORT() FROM devices");
        // $arr = $result->fetch();
        $device                     = new Device();
        $device->build_name         = $body["build_name"];
        $device->build_version      = $body["build_version"];
        $device->gcm_regid          = $body["gcm_regid"];
        $device->imei               = $body["imei"];
        $device->modified_date      = gmdate("Y-m-d", $body["modified_date"]);
        $device->operator_name      = $body["operator_name"];
        $device->phone_manufacturer = $body["phone_manufacturer"];
        $device->phone_name         = $body["phone_name"];
        $device->phone_model        = $body["phone_model"];
        $data                       = array();
        if ($device->save() == false) {
            $data['status'] = "001013|" . $device->session . "|";
            foreach ($device->getMessages() as $message) {
                $data['message'] .= $message . ".";
            }
            return parent::responseServerError($data);
        }
        $data['status']  = "000000";
        $data['message'] = "success";
        $data['data']    = [
            "id"      => $device->uuid,
            "session" => $device->session,
        ];
        //SessionHelper::storeSession($device->session, $device->id);

        return parent::responseCreated($data);
    }
    /**
     * User Registration.
     *
     * @return       User Session
     * @Route(method =      'post', route = 'register', authentication = false)
     */
    public function registrationAction()
    {
        $response = new Response();
        $response->setHeader("Content-Type", "application/json");
        $body = parent::getBody();
        if ($body === null) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001001', "message" => "Body is required.");
            $response->setContent(json_encode($message));
            return $response;
        }
        if ($body['email'] == "") {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001002', "message" => "Email is mandatory.");
            $response->setContent(json_encode($message));
            return $response;
        }
        if ($body['password'] == "") {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001003', "message" => "Password is mandatory.");
            $response->setContent(json_encode($message));
            return $response;
        }
        if (!User::valid_pass($body['password'])) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001033', "message" => "Password must be a minimum of 8 characters and include a combination of letters, " .
                "numbers and symbols.");
            $response->setContent(json_encode($message));
            return $response;
        }
        if (User::isDuplicateEmail($body['email'])) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001004', "message" => "Email already registered.");
            $response->setContent(json_encode($message));
            return $response;
        }
        // echo "data insert false";
        $current_device = parent::getCurrentDevice();
        $data           = array();
        try {
            $this->db->begin();
            $user = new User();
            $user->setEmail($body['email']);
            $user->setPassword($body['password']);
            $user->firstname          = $body['firstname'];
            $user->lastname           = $body['lastname'];
            $user->giant_id         = $body['giant_id'];
            $user->contact       = $body['contact'];
            $user->login_method       = 'email';
            $user->is_deleted         = "false";
            $user->first_login        = "true";
            $user->verified           = "true";
            $user->verification_token = hash('sha256', $body['email'], false);
            //$user->uuid = Uuid::uuid1();
            $user->uuid = md5(uniqid());
            if ($user->save() == false) {
                $this->db->rollback();
                $data['status'] = "001009";
                foreach ($user->getMessages() as $message) {
                    $data['message'] .= $message . ".";
                }
                return parent::responseServerError($data);
            }
            $this->db->commit();
            $data['status']  = "000000";
            $data['message'] = "success";
            $data['data']    = [
                "id"      => $user->uuid,
                "email"   => $user->getEmail(),
                "session" => $current_device->session,
            ];
            $content = $this->view->render('email/success-register', $body);
            \Helper\EmailHelper::send($user->getEmail(), "Congratulations!", $content);
        } catch (\Phalcon\Mvc\Model\Transaction\Failed $e) {
            //echo 'Failed, reason: ', $e->getMessage();
            $response->setStatusCode(404, "Not Found");
            $message = array('status' => '001011', "message" => "Registration failed.");
            $response->setContent(json_encode($message));
            return $response;
        }
        //RedisHelper::emailWelcomeQueue($user->getEmail(), $body['first_name'], $user->verification_token);
        return parent::responseCreated($data);
    }

    /**
     * Update Profile
     *
     * @return       Json
     * @Route(method =  'post', route = 'update-profile', authentication = false)
     */
    public function updateProfileAction()
    {

        $response = new Response();
        $response->setHeader("Content-Type", "application/json");
        $body = parent::getBody();
        if ($body === null) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001001', "message" => "Body is required.");
            $response->setContent(json_encode($message));
            return $response;
        }
        if ($body['email'] == "") {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001002', "message" => "Email is mandatory.");
            $response->setContent(json_encode($message));
            return $response;
        }
        $current_user = parent::getCurrentUser();
        $user_count   = User::isDuplicateEmailExceptUserId($body['email'], $current_user['id']);
        if ($user_count > 0) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001004', "message" => "Email already registered.");
            $response->setContent(json_encode($message));
            return $response;
        }
        $data = array();
        try {
            $this->db->begin();
            $user = User::findById($current_user['id']);
            $user->setEmail($body['email']);
            $user->firstname    = $body['first_name'];
            $user->lastname     = $body['last_name'];
            $user->giant_id = $body['giant_id'];
            $user->contact   = $body['contact'];
            $user->age   = $body['age'];
            $user->sex   = $body['sex'];
            $user->distance_type   = $body['distance_type'];
            $user->distance_long   = $body['distance_long'];
            if ($user->save() == false) {
                $this->db->rollback();
                $data['status'] = "001009";
                foreach ($user->getMessages() as $message) {
                    $data['message'] .= $message . ".";
                }
                return parent::responseServerError($data);
            }
            $this->db->commit();
            $data['status']  = "000000";
            $data['message'] = "success";
            $data['data']    = [
                "user_id"      => $user->id,
                "first_name"   => ($user->firstname != null) ? $user->firstname : "",
                "last_name"    => ($user->lastname != null) ? $user->lastname : "",
                "age"    => ($user->age != null) ? $user->age : "",
                "sex"    => ($user->sex != null) ? $user->sex : "",
                "distance_type"    => ($user->distance_type != null) ? $user->distance_type : "",
                "distance_long"    => ($user->distance_long != null) ? $user->distance_long : "",
                "giant_id" => ($user->giant_id != null) ? $user->giant_id : "",
                "contact"   => ($user->contact != null) ? $user->contact : "",
                "email"        => $user->getEmail(),
            ];
        } catch (\Phalcon\Mvc\Model\Transaction\Failed $e) {
            //echo 'Failed, reason: ', $e->getMessage();
            $response->setStatusCode(404, "Not Found");
            $message = array('status' => '001011', "message" => "Update Profile failed.");
            $response->setContent(json_encode($message));
            return $response;
        }
        return parent::responseSuccess($data);
    }


      /**
     * Upload image for profile.
     * 
     * @return       Upload Profile
     * @Route(method = 'post', route = 'upload-profile', authentication = false)
     */
    public function uploadProfileAction() 
    {
        $response = new Response();
        $response->setHeader("Content-Type", "application/json");
        if (!$this->request->hasFiles()) 
        {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '008015', "message" => "Photo file is required.");
            $response->setContent(json_encode($message));
            return $response;
        }

        $current_user = parent::getCurrentUser();
        // print_r($current_user);
        // die();
        $profile = User::findById($current_user['id']);
        if ($profile == false) {
            $response->setStatusCode(404, "Not Found");
            $message = array('status' => '002013', "message" => "User not found.");
            $response->setContent(json_encode($message));
            return $response;
        }

        $this->db->begin();
        try {
        foreach ($this->request->getUploadedFiles(true) as $file) {
            $length = 10;
            $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
            $path = $this->di->get('config')->assets->profile_picture_path;
            $url = $this->di->get('config')->assets->profile_url_path;
            $fileName =  $profile->id."_".$randomString."_".time()."_".$file->getName();
            $photo = $url.$fileName;
                if ($file->moveTo($path.$fileName)) {
                    $prof = User::findById($current_user['id']);
                    $prof->photo = $fileName;
                    $prof->user_id = $current_user['id'];
                    $prof->firstname = $current_user['firstname'];
                    $prof->lastname = $current_user['lastname'];
                    $prof->giant_id = $current_user['giant_id'];
                    $prof->contact = $current_user['contact'];
                    $prof->age = $current_user['age'];
                    $prof->sex = $current_user['sex'];
                    $prof->distance_type = $current_user['distance_type'];
                    $prof->distance_long = $current_user['distance_long'];
                     
                    if ($prof->save() == false) {
                        $data['status'] = "001013|" . $prof->id . "|";

                        foreach ($prof->getMessages() as $message) {
                            $data['message'] .= $message . ".";
                        }
                        $response->setStatusCode(404, "Not Found");
                        return parent::responseServerError($data);
                    }

                }

            }
        $this->db->commit();
        }catch(\Exception $e){
            $this->db->rollback();
            $response->setStatusCode(404, "Not Found");
            $message = array('status' => '001011', "message" => "Failed upload photo.");
            $response->setContent(json_encode($message));
            return $response;
        }

        $image = array('image' => $photo);
        $data['status']  = "000000";
        $data['message'] = "success";
        $data['data']  = $image;
        return parent::responseSuccess($data);


    }


    /**
     * Login with email.
     *
     * @return       Logged in user session
     * @Route(method =      'post', route = 'login', authentication = false)
     */
    public function emailLoginAction()
    {
        $body = parent::getBody();
        $response = new Response();
        $response->setHeader("Content-Type", "application/json");
        if ($body === null) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001001', "message" => "Body is required.");
            $response->setContent(json_encode($message));
            return $response;
        }
        if ($body['email'] == "") {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001002', "message" => "Email is mandatory.");
            $response->setContent(json_encode($message));
            return $response;
        }
        if ($body['password'] == "") {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001003', "message" => "Password is mandatory.");
            $response->setContent(json_encode($message));
            return $response;
            
        }
        $current_device = parent::getCurrentDevice();
        if ($current_device == false) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001014', "message" => "Session is expired.");
            $response->setContent(json_encode($message));
            return $response;
        }
        $data = array();
        try {
            $this->db->begin();
            $user = User::findByEmail($body['email']);
            if (!$user || !$user->passwordIsValid($body['password'])) {
                $response->setStatusCode(401, "Unauthorized");
                $message = array('status' => '001009', "message" => "Invalid username or password.");
                $response->setContent(json_encode($message));
                return $response;
            }
            if ($user->verified == false) {
                $response->setStatusCode(401, "Unauthorized");
                return parent::responseServerError([
                    'status'  => '001024',
                    "message" => "Hi Buddy, Please verify your email address"]);
            }
            $first_login = false;
            if ($user->first_login == true) {
                $first_login       = $user->first_login;
                $user->first_login = 'false';
            }
            $current_device->user_id = $user->id;
            if ($current_device->save() == false || $user->save() == false) {
                $this->db->rollback();
                $data['status'] = "001013";
                foreach ($current_device->getMessages() as $message) {
                    $data['message'] .= $message . ".";
                }
                foreach ($user->getMessages() as $message) {
                    $data['message'] .= $message . ".";
                }
                return parent::responseServerError($data);
            }

            $urlpath = $this->di->get('config')->assets->profile_url_path;
            if ($user->photo != '') {
                $photo = $urlpath.$user->photo;
            }else{
                $photo = '';
            }

            $this->db->commit();
            $data['status']  = "000000";
            $data['message'] = "success";
            $data['data']    = [
                // "id"            => $user->getUuid(),
                // "user_id"       => $user->id,
                "first_name"    => ($user->firstname != null) ? ucfirst($user->firstname) : "",
                "last_name"     => ($user->lastname != null) ? ucfirst($user->lastname) : "",
                "contact"    => ($user->contact != null) ? $user->contact : "",
                "age"    => ($user->age != null) ? $user->age : "",
                "sex"    => ($user->sex != null) ? $user->sex : "",
                "distance_type"    => ($user->distance_type != null) ? $user->distance_type : "",
                "distance_long"    => ($user->distance_long != null) ? $user->distance_long : "",
                "giant_id"       => ($user->giant_id != null) ? $user->giant_id : "",
                "photo"       => $photo,
                "email"         => $user->getEmail(),
                "session"       => $current_device->session,
                // "first_login"   => $first_login,
                // "is_verified"   => $user->verified,
            ];
        } catch (\Phalcon\Mvc\Model\Transaction\Failed $e) {
            $response->setStatusCode(401, "Not Found");
            $message = array('status' => '001010', "message" => "Login failed.");
            $response->setContent(json_encode($message));
            return $response;
            error_log('Failed, reason: ' . $e->getMessage());
        }
        //SessionHelper::invalidateOtherSessionByGcmRegID($current_device->gcm_regid, $current_device->id);
        //SessionHelper::storeSession($current_device->session, $user->id);
        $request     = new Request();
        $logFile     = "/var/www/giantcatalog.dev.tbvlabs.com/public/logs/giant.log";
        $routeAction = "LOGIN";
        $stdOut      = date("Y-m-d H:i:s") . "\t" . $request->getClientAddress() . "\t" . $routeAction . "\t" . json_encode($body) . " " . json_encode($data);
        exec('echo "' . $stdOut . '" >> ' . $logFile);

        return parent::responseSuccess($data);
    }

    /**
     * Facebook login function.
     * Receive code from facebook redirect and validate.
     *
     * @return       Login Status
     * @Route(method =      'get', route = 'fblogin', authentication = false)
     */
    public function facebookLoginAction()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $response = new Response();
        $response->setHeader("Content-Type", "application/json");
        $body = parent::getBody();
        $code = $this->request->get('code');
        if ($code == "") {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001018', "message" => "Facebook login failed. Code is mandatory.");
            $response->setContent(json_encode($message));
            return $response;
           
        }

        $current_device = parent::getCurrentDevice();
        if ($current_device == false) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001014', "message" => "Session is expired.");
            $response->setContent(json_encode($message));
            return $response;
        }

        $send_welcome_email = false;

        $this->db->begin();
        $response = FacebookHelper::getUserProfile($code);

        $graph_user = $response->getGraphUser();

        $user = User::findFirst([
            "social_id = ?0",
            "bind" => [$graph_user->getId()],
        ]);
        if ($user == false) {
            $user = new User();

            $send_welcome_email       = true;
            $user->social_id          = $graph_user->getId();
            $user->firstname          = $graph_user->getFirstName();
            $user->lastname           = $graph_user->getLastName();
            $user->email              = "-";
            $user->giant_id           = "-";
            $user->contact            = "-";
            $user->age                = "-";
            $user->sex                = "male";
            $user->distance_type      = "km";
            $user->distance_long      = "-";
            $user->photo              = "-";
            $user->is_deleted         = "false";
            $user->first_login        = "true";
            $user->verified           = "true";
            $user->password           = md5($this->di->get('config')->security->salt . 'fb');
            $user->login_method       = 'facebook';
            $user->uuid               = md5(uniqid());
            $user->verification_token = hash('sha256', md5(uniqid()), false);

            if ($user->save() == false) {
                $this->db->rollback();
                $data['status'] = "001009";
                foreach ($user->getMessages() as $message) {
                    $data['message'] .= $message . ".";
                }
                return parent::responseServerError($data);
            }
        }

        $this->db->commit();

        $data['status']  = "000000";
        $data['message'] = "success";
        $data['data']    = [
            "id"            => $user->getUuid(),
            "user_id"       => $user->id,
            "first_name"    => ($user->firstname != null) ? $user->firstname : "",
            "last_name"     => ($user->lastname != null) ? $user->lastname : "",
            "age"           => ($user->age != null) ? $user->age : "",
            "sex"           => ($user->sex != null) ? $user->sex : "",
            "distance_type"     => ($user->distance_type != null) ? $user->distance_type : "",
            "distance_long"     => ($user->distance_long != null) ? $user->distance_long : "",
            "giant_id"      => ($user->giant_id != null) ? $user->giant_id : "",
            "contact"       => ($user->contact != null) ? $user->contact : "",
            "email"         => $user->getEmail(),
            "session"       => $current_device->session,
            "first_login"   => $user->first_login,
            "is_verified"   => $user->verified,
            "setup_profile" => Profile::profileSetup($user->id),
        ];

        return parent::responseSuccess($data);



    }

    /**
     * API to verification user.
     *
     * @return       Status SUccess
     * @Route(method =      'get', route = 'verification', authentication = false)
     */
    public function emailVerificationAction()
    {
        $email = $this->request->get('email');
        $token = $this->request->get('token');
        if ($email == "") {
            return parent::responseClientError(['status' => '001002', "message" => "Email is mandatory."]);
        }
        if ($token == "") {
            return parent::responseClientError(['status' => '001014', "message" => "Token is mandatory."]);
        }
        $user = User::isUnverified($email, $token);
        if ($user == false) {
            return parent::responseServerError(['status' => '001015', 'message' => "User not found."]);
        }
        if ($user->verified == true) {
            return parent::responseServerError(['status' => '001016', "message" => "User already verified."]);
        }
        $user->verified           = true;
        $user->verification_token = "";
        if ($user->save() == false) {
            return parent::responseServerError(["status" => "001017", "message" => "Failed verified user."]);
        }
        $data["status"]  = "000000";
        $data["message"] = "success";
        $data["data"]    = [
            "id"       => $user->uuid,
            "email"    => $user->email,
            "email"    => $user->email,
            "verified" => $user->verified,
        ];
        return parent::responseSuccess($data);
    }
    /**
     * Update user email and password
     * @return       Status
     * @Route(method =      'post',   route = 'user_detail', authentication = true)
     */
    public function updateEmailPassword()
    {
        $body = parent::getBody();
        if ($body === null) {
            return parent::responseClientError(['status' => '001001', "message" => "Body is required."]);
        }
        if ($body['email'] == "") {
            return parent::responseClientError(['status' => '001002', "message" => "Email is mandatory."]);
        }
        if ($body['password'] == "") {
            return parent::responseClientError(['status' => '001003', "message" => "Password is mandatory."]);
        }
        if (!User::valid_pass($body['password'])) {
            return parent::responseClientError([
                'status'  => '001031',
                "message" => "Password must be a minimum of 8 characters and include a combination of letters, " .
                "numbers and symbols."]);
        }
        $current_user = parent::getCurrentUser();
        // $user_count = User::isDuplicateEmailExceptUserId($body['email'], $current_user['id']);
        // if ($user_count > 0) {
        //     return parent::responseClientError(['status' => '001004', "message" => "Email already registered."]);
        // }
        $data                    = array();
        $send_email_verification = false;
        try {
            $this->db->begin();
            $user = User::findById($current_user['id']);
            if ($user->login_method == 'email') {
                //check history password
                $history_password = array();
                $has_new_password = md5($this->di->get('config')->security->salt . $body['password']);
                if (!empty($user->password_history)) {
                    $history_password = json_decode($user->password_history, true);
                    if (in_array($has_new_password, $history_password)) {
                        return parent::responseNotFound(['status' => '001005', "message" => "Cant use the Password."]);
                    }
                }
                //set history
                $new_history_password = (array_merge(array($has_new_password), $history_password));
                if (count($new_history_password) > 3) {
                    $eliminate_last_password = array_pop($new_history_password);
                }
                $user->password_history = json_encode($new_history_password);
                $user->setPassword($body['password']);
            }
            if ($body['email'] != $user->email) {
                $user->email              = $body['email'];
                $user->verified           = false;
                $user->verification_token = hash('sha256', $body['email'], false);
                $send_email_verification  = true;
            }
            if ($user->save() == false) {
                $this->db->rollback();
                $data['status'] = "001023";
                foreach ($user->getMessages() as $message) {
                    $data['message'] .= $message . ".";
                }
                return parent::responseServerError($data);
            }
            $this->db->commit();
            $data['status']  = "000000";
            $data['message'] = "success";
            $data['data']    = [
                "id"    => $user->getUuid(),
                "email" => $user->getEmail(),
            ];
        } catch (\Phalcon\Mvc\Model\Transaction\Failed $e) {
            $this->db->rollback();
            echo 'Failed, reason: ', $e->getMessage();
            return parent::responseServerError([
                "status"  => "001022",
                "message" => "Failed to update email and password."]);
        }
        if ($send_email_verification) {
            //RedisHelper::emailVerificationQueue($user->getEmail(), $user->firstname, $user->verification_token);
            //SessionHelper::invalidateSessionByUserId($current_user['id']);
        }
        return parent::responseCreated($data);
    }
    /**
     * Forgot Password function.
     * @return       Status
     * @Route(method =      'post',   route = 'forgot_password', authentication = false)
     */
    public function forgotPasswordAction()
    {
        $response = new Response();
        $response->setHeader("Content-Type", "application/json");
        $body = parent::getBody();
        if ($body === null) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001001', "message" => "Body is required.");
            $response->setContent(json_encode($message));
            return $response;
        }
        if ($body['email'] == "") {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001002', "message" => "Email is mandatory.");
            $response->setContent(json_encode($message));
            return $response;
        }
        $user = User::findByEmail($body['email']);
        if ($user == false) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001027', "message" => "No user found with that email.");
            $response->setContent(json_encode($message));
            return $response;
        }

        /*Random String*/
        $length           = 5;
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $password         = $randomString;
        $password_encode  = md5($password);
        $body['password'] = $password;
        $body['name']     = $user->firstname;
        $connection       = $this->di->get('db');
        $sql              = "UPDATE  users SET  password =  '{$password_encode}' WHERE  email = '{$body['email']}';";
        if ($connection->query($sql)) {

            $content = $this->view->render('email/forgot-password', $body);
            \Helper\EmailHelper::send($body['email'], "Forgot Password", $content);
        }
        $success = json_decode('{}');
        $data['status']  = "000000";
        $data['message'] = "success";
        $data['data']  =  $success;
        return parent::responseCreated($data);
    }
    

    /**
     * Change user password.
     * @param        String $email      User email
     * @return       Status
     * @Route(method =      'post', route         = 'change_password', authentication = false)
     */
    public function changePassword()
    {
        $response = new Response();
        $response->setHeader("Content-Type", "application/json");
        $body = parent::getBody();
        if ($body === null) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001001', "message" => "Body is required.");
            $response->setContent(json_encode($message));
            return $response;
        }
        $current_user = parent::getCurrentUser();
        $user         = User::findById($current_user['id']);
        if ($user == false) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001027', "message" => "User not found.");
            $response->setContent(json_encode($message));
            return $response;
        }
        $old_pass = md5($this->di->get('config')->security->salt . $body['old_password']);
        if ($old_pass != $user->password) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001027', "message" => "The old password you entered was incorrect.");
            $response->setContent(json_encode($message));
            return $response;
            
        }
        if (!User::valid_pass($body['new_password'])) {
            $response->setStatusCode(401, "Unauthorized");
            $message = array('status' => '001029', "message" => "Password must be a minimum of 8 characters and include a combination of letters, " .
                "numbers and symbols.");
            $response->setContent(json_encode($message));
            return $response;
           
        }
        //check history password
        $history_password = array();
        $has_new_password = md5($this->di->get('config')->security->salt . $body['new_password']);
        if (!empty($user->password_history)) {
            $history_password = json_decode($user->password_history, true);
            if (in_array($has_new_password, $history_password)) {
                $response->setStatusCode(404, "Not Found");
                $message = array('status' => '001030', "message" => "Cant use the Password.");
                $response->setContent(json_encode($message));
                return $response;
            }
        }
        //set history
        $new_history_password = (array_merge(array($has_new_password), $history_password));
        if (count($new_history_password) > 3) {
            $eliminate_last_password = array_pop($new_history_password);
        }
        try {
            /*ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);*/
            $this->db->begin();
            $user->setPassword($body['new_password']);
            if ($user->save() == false) {
                $message = "";
                foreach ($user->getMessages() as $error_message) {
                    $message .= $error_message . ". ";
                }
                throw new \Phalcon\Exception($message);
            }
            //SessionHelper::invalidateSessionByUserId($user->id);
            $this->db->commit();
            //  $content = $this->view->render('email/update-password', $body);
            // \Helper\EmailHelper::send($user->getEmail(), "Update Password", $content);

        } catch (\Phalcon\Exception $e) {
            $this->db->rollback();
            // echo 'Failed, reason: ', $e->getMessage();
            // return parent::responseServerError(["status" => "001028", "message" => "Failed to forgot password."]);
             $response->setStatusCode(404, "Not Found");
                $message = array('status' => '001028', "message" => "Failed to forgot password.");
                $response->setContent(json_encode($message));
                return $response;
        }
        //success email
        //RedisHelper::emailChangePasswordSuccess($email, $user->firstname);
        $password = array('password' => $body['new_password']);
        $data['status']  = "000000";
        $data['message'] = "success";
        $data['data']    = $password;
        return parent::responseCreated($data);
    }
}
