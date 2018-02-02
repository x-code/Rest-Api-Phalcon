<?php
namespace Models;

class Merchant extends \Phalcon\Mvc\Model
{
    public $id;
    public $title;
    public $status;

    public function getSource()
    {
        return "merchants";
    }
    public function initialize()
    {
        $this->setSource("merchants");
    }

    public static function getProducts($merchant_id)
    {
        
        $sql = "SELECT *, COUNT(merchant_id) AS total FROM products WHERE merchant_id='".$merchant_id."'";
        $GameModel  = new self();
        $connection = $GameModel->getReadConnection();
        $result_set = $connection->query($sql);
        $result_set->setFetchMode(\Phalcon\Db::FETCH_OBJ);
        return $result_set->fetch($result_set);
    }

}
