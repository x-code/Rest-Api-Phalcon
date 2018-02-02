<?php
namespace Models;

class Promo extends \Phalcon\Mvc\Model
{
    public $id;
    public $hotel_id;
    public $url;
    public function getSource()
    {
        return "promos";
    }
    public function initialize()
    {
        $this->setSource("promos");
    }

}
