<?php
namespace Controllers;

//use Helper\RedisHelper;
use \Models\Category as Category;

/**
 * Add Categories controller
 *
 * @Route(route = "/v1/category/")
 */
class CategoryController extends \Application\Controller
{

	 /**
     * Category List.
     *
     * @return       Category List
     * @Route(method =      'get', route = 'list-category', authentication = false)
     */
    public function listcategoryAction()
    {
        $categories = Category::find();
        $data['status']    = "000000";
        $data['message']   = "success";
        if (!empty($categories)) {
            $data['data'] = array();
            foreach ($categories as $key => $category) {
                // $img           = preg_replace('/^\s+|\n|\r|\s+$/m', '', $category->image);
                // $img_string    = str_replace("&nbsp;", " ", $img);
                
                
                $data['data'][$key] = array(
                    'id'            => $category->id,
                    'title'         => $category->title,
                    'status'         => $category->status,
                    
                );
                
            }
        } else {
            $data['message'] = "There is no available category near your location";
        }
        return parent::responseSuccess($data);
    }


}
