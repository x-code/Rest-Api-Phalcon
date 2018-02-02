<?php
namespace Models;

use Rhumsaa\Uuid\Uuid;

class BasePublic extends \Phalcon\Mvc\Model
{
    public $id;
    public $uuid;
    public $created_at;
    public $updated_at;
    public $is_deleted;
    public $user_id;
    public function onConstruct()
    {
        $uuid4      = Uuid::uuid4();
        $this->uuid = $uuid4->toString();
    }
    public function beforeCreate()
    {
        $this->created_at = date('c');
        $this->updated_at = date('c');
        $this->is_deleted = false;
    }
    public function beforeUpdate()
    {
        $this->updated_at = date('c');
    }
    public static function getAttribute($class)
    {
        $params = get_class_vars(get_class($class));
        unset($params['id']);
        unset($params['user_id']);
        unset($params['profile_id']);
        unset($params['uuid']);
        unset($params['created_at']);
        unset($params['updated_at']);
        unset($params['is_deleted']);
        unset($params['_dependencyInjector']);
        unset($params['_modelsManager']);
        unset($params['_modelsMetaData']);
        unset($params['_errorMessages']);
        unset($params['_operationMade']);
        unset($params['_dirtyState']);
        unset($params['_transaction']);
        unset($params['_uniqueKey']);
        unset($params['_uniqueParams']);
        unset($params['_uniqueTypes']);
        unset($params['_skipped']);
        unset($params['_related']);
        unset($params['_snapshot']);
        unset($params['photo']);
        // $f = array_filter(array_keys($params), function ($k){
        //     return (substr( $k, 0, 1 ) !== "_");
        // });
        // $b = array_intersect_key($params, array_flip($f));
        // $response = [];
        // $response = Arrays::without($params, 'id', 'uuid', 'created_at', 'updated_at', 'is_deleted'); 
        // Returns array('bar', 'ter')
        return $params;
    }
}
