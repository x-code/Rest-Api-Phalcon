<?php
namespace Models;

class Type extends \Phalcon\Mvc\Model
{
    public $id;
    public $title;
    public $status;

    public function getSource()
    {
        return "types";
    }
    public function initialize()
    {
        $this->setSource("types");
    }

}
