<?php
namespace Models;

class Category extends \Phalcon\Mvc\Model
{
    public $id;
    public $title;
    public $status;

    public function getSource()
    {
        return "categories";
    }
    public function initialize()
    {
        $this->setSource("categories");
    }

}
