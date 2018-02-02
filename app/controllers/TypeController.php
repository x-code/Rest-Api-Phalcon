<?php
namespace Controllers;

//use Helper\RedisHelper;
use \Models\Type as Type;

/**
 * Add Type controller
 *
 * @Route(route = "/v1/type/")
 */
class TypeController extends \Application\Controller
{

     /**
     * Type List.
     *
     * @return       Type List
     * @Route(method =      'get', route = 'list-type', authentication = false)
     */
    public function listMerchantAction()
    {
        $types = Type::find();
        $data['status']    = "000000";
        $data['message']   = "success";
        if (!empty($types)) {
            $data['data'] = array();
            foreach ($types as $key => $type) {
                // $img           = preg_replace('/^\s+|\n|\r|\s+$/m', '', $type->image);
                // $img_string    = str_replace("&nbsp;", " ", $img);
                
                
                $data['data'][$key] = array(
                    'id'            => $type->id,
                    'title'         => $type->title,
                    'status'         => $type->status,
                    
                );
                
            }
        } else {
            $data['message'] = "There is no available type";
        }
        return parent::responseSuccess($data);
    }


}
