<?php
namespace Models;

class User extends \Phalcon\Mvc\Model
{
    const DELETED     = true;
    const NOT_DELETED = false;
    public $firstname;
    public $lastname;
    public $email;
    public $password;
    public $verification_token;
    public $first_login;
    public $login_method;
    public $verified;
    public $country;
    public $contact_no;
    public $contact_code;
    public $is_change_password;
    public function getSource()
    {
        return "users";
    }
    public function initialize()
    {
        $this->setSource("users");
        // $this->addBehavior(new SoftDelete(
        //     array(
        //         'field' => 'is_deleted',
        //         'value' => User::DELETED
        //     )
        // ));
    }
    public function getId()
    {
        return $this->id;
    }
    public function getUuid()
    {
        return $this->uuid;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function setPassword($password)
    {
        $this->password = md5($this->di->get('config')->security->salt . $password);
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function isDuplicateEmail($email = "")
    {
        $email_count = User::count(array(
            "is_deleted = false AND email = ?0",
            "bind" => [$email],
        ));
        if ($email_count > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function isDuplicateEmailExceptUserId($email = "", $user_id = 0)
    {
        return User::count([
            "is_deleted = false AND email = ?0 AND id != ?1",
            "bind" => [$email, $user_id],
        ]);
    }
    public function getUserBySession($session = "")
    {
        try {
            $connection = $this->di->get('db');
            $sql        = "
            SELECT u.* FROM users u
            JOIN devices d ON d.user_id = u.id
            WHERE d.session = '{$session}' AND d.is_deleted = false AND u.is_deleted = false
            ";
            $result_set = $connection->query($sql);
            $result_set->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            return $result_set->fetch();
        } catch (Phalcon\Db\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
        }
        return false;
    }
    public function passwordIsValid($password = "")
    {
        $entry_password = md5($this->di->get('config')->security->salt.$password);
        if ($this->password === $entry_password) {
            return true;
        } else {
            return false;
        }
    }
    public static function getAttribute()
    {
        $params = parent::getAttribute(new User());
        return $params;
    }
    public static function isUnverified($email = "", $verification_token = "")
    {
        return User::findFirst([
            "is_deleted = false AND email = ?0 AND verification_token = ?1",
            "bind" => [$email, $verification_token],
        ]);
    }
    public static function findById($user_id = 0)
    {
        return User::findFirst([
            "is_deleted = false AND id = ?0",
            "bind" => [$user_id],
        ]);
    }
    public static function findByEmail($email = "")
    {
        return User::findFirst([
            "is_deleted = false AND email = lower(?0)",
            "bind" => [trim($email)],
        ]);
    }
    public static function generatePassword()
    {
        $factory   = new \RandomLib\Factory;
        $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
        $password  = $generator->generateString(8, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        return $password;
    }
    public static function valid_pass($candidate)
    {
        // $r1='/[A-Z]/';  //Uppercase
        $r2 = '/[a-z]/'; //lowercase
        $r3 = '/[!@#$%^&*()\-_=+{};:,<.>]/'; // whatever you mean by 'special char'
        $r4 = '/[0-9]/'; //numbers
        // if(preg_match_all($r1,$candidate, $o)<1) return FALSE;
        if (preg_match_all($r2, $candidate, $o) < 1) {
            return false;
        }
        if (preg_match_all($r3, $candidate, $o) < 1) {
            return false;
        }
        if (preg_match_all($r4, $candidate, $o) < 1) {
            return false;
        }
        if (strlen($candidate) < 8) {
            return false;
        }
        return true;
    }
    public function beforeCreate()
    {
        if (!empty($this->email)) {
            $this->email = strtolower($this->email);
        }
    }
    public function beforeUpdate()
    {
        if (!empty($this->email)) {
            $this->email = strtolower($this->email);
        }
    }
}
