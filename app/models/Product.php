<?php
namespace Models;

class Product extends \Phalcon\Mvc\Model
{
    public $id;
    public $category_id;
    public $title;
    public $image;
    public $flashdeal;
    public $flashdeal_start;
    public $flashdeal_end;
    public $address;
    public $description;
    public $views;
    public $likes;
    public $created_at;
    public $status;

    public function getSource()
    {
        return "products";
    }
    public function initialize()
    {
        $this->setSource("products");
    }
    public static function getNearProducts($lat, $long)
    {
        /*
        3959 = miles
        6371 = km
         */
        $sql = "SELECT *, ( 6371 * acos( cos( radians($lat) ) *
        cos( radians( h.lat ) ) *
        cos( radians( h.long ) - radians($long) ) +
        sin( radians($lat) ) * sin( radians( h.lat ) ) ) ) AS distance
                FROM products h HAVING distance <= 500 ORDER BY distance ASC LIMIT 10";
        $GameModel  = new self();
        $connection = $GameModel->getReadConnection();
        $result_set = $connection->query($sql);
        $result_set->setFetchMode(\Phalcon\Db::FETCH_OBJ);
        return $result_set->fetchAll($result_set);
    }

    public static function getNearMerchants($lat, $long)
    {
        /*
        3959 = miles
        6371 = km
         */
        $sql = "SELECT *, ( 6371 * acos( cos( radians($lat) ) *
        cos( radians( h.lat ) ) *
        cos( radians( h.long ) - radians($long) ) +
        sin( radians($lat) ) * sin( radians( h.lat ) ) ) ) AS distance
                FROM products h  GROUP BY merchant_id ASC LIMIT 10";
        $GameModel  = new self();
        $connection = $GameModel->getReadConnection();
        $result_set = $connection->query($sql);
        $result_set->setFetchMode(\Phalcon\Db::FETCH_OBJ);
        return $result_set->fetchAll($result_set);
    }


    public static function getProductKeyword($q)
    {
        
        $products = Product::find(array(
            "title LIKE '%".$q."%' ",
            "bind"=>array(
                'title'=>$q
            )
        ));

        return $products;
    }

    public static function findByUuid($uuid = "", $profile_id = 0)
    {
        $product = Product::findFirst([
            "id = ?0",
            "bind" => [$product->id],
        ]);
        return $product;   
    }


    public static function getPriceProducts($price){
       
        return $price;
    }


    public static function getCategoryProducts($category)
    {
        $product = Product::find([
            "category_id = ?0",
            "bind" => [$category],
        ]);
        return $product;   
    }
    

}
