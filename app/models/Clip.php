<?php
namespace Models;

class Clip extends \Phalcon\Mvc\Model
{
    public $id;
    public $user_id;
    public $product_id;
    public $is_deleted;
    public $created_at;

    public function getSource()
    {
        return "clips";
    }
    public function initialize()
    {
        $this->setSource("clips");
    }

    public static function findById($id)
    {
        return Clip::findFirst([
                "id = ?0",
                "bind" => [$id],
                "order" => "id DESC"
            ]);   
    }

    public static function getUserClip($user_id, $product_id)
    {
        return self::find([
                "user_id = ?0 AND product_id = ?0",
                "bind" => [$user_id, $product_id]
            ]);
    }
}
