<?php
namespace Controllers;
use Ramsey\Uuid\Uuid;
use \Models\Device as Device;
use \Models\Product as Product;
use \Models\Clip as Clip;
use \Models\Category as Category;
use \Models\Type as Type;
use \Models\Merchant as Merchant;
use \Models\Logs;
use \Models\User as User;
use \Phalcon\Http\Request;

/**
 * Add Product controller
 *
 * @Route(route = "/v1/product/")
 */
class ProductController extends \Application\Controller
{

    /**
     * Product List.
     *
     * @return       Product List
     * @Route(method =      'get', route = 'list-product', authentication = false)
     */
    public function listProductAction()
    {
        $q = $this->request->get('q');
        if (!empty($q)) {
            $products = Product::getProductKeyword($q);
        }else{
            $products = Product::find();
        }
        $current_user = parent::getCurrentUser();
        $data['status']    = "000000";
        $data['message']   = "success";
        if (!empty($products)) {
            $data['data'] = array();
            foreach ($products as $key => $product) {
                $desc          = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->description);
                $desc_string   = str_replace("&nbsp;", " ", $desc);
                $img           = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->image);
                $img_string    = str_replace("&nbsp;", " ", $img);
                $address       = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->address);
                $address = htmlentities($address);
                

                $latitudeTo = $product->lat;
                $longitudeTo = $product->long;
                $theta = $longitudeFrom - $longitudeTo;
                $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $distance = ($miles * 1.609344);
                
                $getclip = Clip::findFirst([
                        "user_id = ?1 AND product_id = ?1",
                        "bind" => [$current_user['id'], $product->id]
                    ]);
            
                if (!empty($getclip)) {
                    $clip = TRUE;
                 }else{
                    $clip = FALSE;
                 }

                $getcategory = Category::findFirst($product->category_id);
                $gettype = Type::findFirst($product->type_id);
                $getmerchant = Merchant::findFirst($product->merchant_id);

                $flashdeal = array(
                        'isFlashDeal' => $product->flashdeal,
                        'startFlashDeal' => $product->flashdeal_start,
                        'endFlashDeal' => $product->flashdeal_end
                        );
                $location = array(
                        'address' => $address,
                        'lat'     => ($product->lat != null) ? $product->lat : "0",
                        'long'    => ($product->long != null) ? $product->long : "0",
                        );

                $data['data'][$key] = array(
                    'id'            => $product->id,
                    'category_id'   => $product->category_id,
                    'category_name' => $getcategory->title,
                    'type_id'       => $product->type_id,
                    'type_name'     => $gettype->title,
                    'merchant_id'   => $product->merchant_id,
                    'merchant_name' => $getmerchant->title,
                    'title'         => $product->title,
                    'price'         => $product->price,
                    'flashdeal'     => $flashdeal,
                    'description'   => strip_tags($product_desc_string),
                    'location'       => $location,
                    'clip'       => $clip,
                    'image'         => strip_tags($img_string),
                    'web_view'   => $product->web_view,
                );
                if (!empty($latitudeFrom) && !empty($longitudeFrom)) {
                    $data['data'][$key]['location']['distance'] = (double) number_format($distance, 3);
                }else{
                    $data['data'][$key]['location']['distance'] = 0;
                }
            }
        } else {
            $data['status']    = "000001";
            $data['message'] = "Product not found";
        }
        return parent::responseSuccess($data);
    }




    /**
     * Product Detail.
     *
     * @return       Product Detail
     * @Route(method =      'get', route = 'detail-product', authentication = false)
     */
    public function detailProductAction()
    {
        $id     = $this->request->get('id');
        if (!empty($id)) {
            $product = Product::findFirst($id);
        }
        $current_user = parent::getCurrentUser();
        $data['status']    = "000000";
        $data['message']   = "success";

        if (!empty($product)) {
            $data['data'] = array();
                $desc          = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->description);
                $desc_string   = str_replace("&nbsp;", " ", $desc);
                $img           = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->image);
                $img_string    = str_replace("&nbsp;", " ", $img);
                $address       = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->address);
                $address = htmlentities($address);
            

                $flashdeal = array(
                        'isFlashDeal' => $product->flashdeal,
                        'startFlashDeal' => $product->flashdeal_start,
                        'endFlashDeal' => $product->flashdeal_end
                        );
                $location = array(
                        'address' => $address,
                        'lat'     => ($product->lat != null) ? $product->lat : "0",
                        'long'    => ($product->long != null) ? $product->long : "0",
                        );
               	$getclip = Clip::findFirst([
		                "user_id = ?1 AND product_id = ?1",
		                "bind" => [$current_user['id'], $product->id]
		            ]);
        	
        		if (!empty($getclip)) {
                 	$clip = TRUE;
                 }else{
                 	$clip = FALSE;
                 }

                $getcategory = Category::findFirst($product->category_id);
                $gettype = Type::findFirst($product->type_id);
                $getmerchant = Merchant::findFirst($product->merchant_id);

                $data['data'] = array(
                    'id'            => $product->id,
                    'category_id'   => $product->category_id,
                    'category_name'	=> $getcategory->title,
                    'type_id'       => $product->type_id,
                    'type_name'     => $gettype->title,
                    'merchant_id'   => $product->merchant_id,
                    'merchant_name' => $getmerchant->title,
                    'title'         => $product->title,
                    'price'         => $product->price,
                    'flashdeal'     => $flashdeal,
                    'description'   => strip_tags($product_desc_string),
                    'location'       => $location,
                    'clip'       => $clip,
                    'image'         => strip_tags($img_string),
                    'web_view'   => $product->web_view,
                );
                if (!empty($latitudeFrom) && !empty($longitudeFrom)) {
                    $data['data']['location']['distance'] = (double) number_format($distance, 3);
                }else{
                    $data['data']['location']['distance'] = 0;
                }

        } else {
            $data['status']    = "000001";
            $data['message'] = "There is no available detail product";
        }
        return parent::responseSuccess($data);
    }


    /**
     * Product List.
     *
     * @return       Product List
     * @Route(method =      'get', route = 'merchant-product', authentication = false)
     */
    public function merchantProductAction()
    {
        $latitudeFrom     = $this->request->get('lat');
        $longitudeFrom     = $this->request->get('long');

        if (!empty($latitudeFrom) && !empty($longitudeFrom)) {
            $products = Product::getNearProducts($latitudeFrom, $longitudeFrom);

        } else {
            $products = Product::find();
        }
        $current_user = parent::getCurrentUser();

        
        $data['status']    = "000000";
        $data['message']   = "success";
        if (!empty($products)) {
            $data['data'] = array();
            
            foreach ($products as $key => $product) {
                $totals = Merchant::getProducts($product->id);
                // print_r($totals);
                // die();

                $desc          = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->description);
                $desc_string   = str_replace("&nbsp;", " ", $desc);
                $img           = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->image);
                $img_string    = str_replace("&nbsp;", " ", $img);
                $address       = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->address);
                $address = htmlentities($address);
                

                $latitudeTo = $product->lat;
                $longitudeTo = $product->long;
                $theta = $longitudeFrom - $longitudeTo;
                $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $distance = ($miles * 1.609344);
                
                $getclip = Clip::findFirst([
                        "user_id = ?1 AND product_id = ?1",
                        "bind" => [$current_user['id'], $product->id]
                    ]);
            
                if (!empty($getclip)) {
                    $clip = TRUE;
                 }else{
                    $clip = FALSE;
                 }


                $getcategory = Category::findFirst($product->category_id);
                $gettype = Type::findFirst($product->type_id);
                $getmerchant = Merchant::findFirst($product->merchant_id);

                $flashdeal = array(
                        'isFlashDeal' => $product->flashdeal,
                        'startFlashDeal' => $product->flashdeal_start,
                        'endFlashDeal' => $product->flashdeal_end
                        );
                $location = array(
                        'address' => $address,
                        'lat'     => ($product->lat != null) ? $product->lat : "0",
                        'long'    => ($product->long != null) ? $product->long : "0",
                        );

                $data['data'][$key] = array(
                    'id'            => $product->id,
                    'merchant_id'   => $product->merchant_id,
                    'merchant_name' => $getmerchant->title,
                    'location' => $location,
                    'product_total' => $totals->total,
                );
                
                if (!empty($latitudeFrom) && !empty($longitudeFrom)) {
                    $data['data'][$key]['location']['distance'] = (double) number_format($distance, 3);
                }else{
                    $data['data'][$key]['location']['distance'] = 0;
                }
            }
            
        } else {
            $data['status']    = "000001";
            $data['message'] = "There is no available Product near your location";
        }
        return parent::responseSuccess($data);
    }

    /**
     * Product Sort.
     *
     * @return       Product Sort
     * @Route(method =      'get', route = 'sort-product', authentication = false)
     */
    public function sortProductAction()
    {
        $latitudeFrom     = $this->request->get('lat');
        $longitudeFrom     = $this->request->get('long');
        $price     = $this->request->get('price');
        $merchant     = $this->request->get('merchant');
        $type     = $this->request->get('type');
        $current_user = parent::getCurrentUser();
                
        if ($type == 'direction') {
            $products = Product::getNearProducts($latitudeFrom, $longitudeFrom);
        }else if ($type == 'price') {
            $products = Product::getPriceProducts($price);
        } else {
            $products = Product::find();
        }

        $data['status']    = "000000";
        $data['message']   = "success";
        if (!empty($products)) {
            $data['data'] = array();
            foreach ($products as $key => $product) {
                $desc          = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->description);
                $desc_string   = str_replace("&nbsp;", " ", $desc);
                $img           = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->image);
                $img_string    = str_replace("&nbsp;", " ", $img);
                $address       = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->address);
                $address = htmlentities($address);
                

                $latitudeTo = $product->lat;
                $longitudeTo = $product->long;
                $theta = $longitudeFrom - $longitudeTo;
                $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $distance = ($miles * 1.609344);
                $getclip = Clip::findFirst([
		                "user_id = ?1 AND product_id = ?1",
		                "bind" => [$current_user['id'], $product->id]
		            ]);
        	
        		if (!empty($getclip)) {
                 	$clip = TRUE;
                 }else{
                 	$clip = FALSE;
                 }

                $getcategory = Category::findFirst($product->category_id);
                $gettype = Type::findFirst($product->type_id);
                $getmerchant = Merchant::findFirst($product->merchant_id);

                $flashdeal = array(
                        'isFlashDeal' => $product->flashdeal,
                        'startFlashDeal' => $product->flashdeal_start,
                        'endFlashDeal' => $product->flashdeal_end
                        );
                $location = array(
                        'address' => $address,
                        'lat'     => ($product->lat != null) ? $product->lat : "0",
                        'long'    => ($product->long != null) ? $product->long : "0",
                        );

                $data['data'][$key] = array(
                    'id'            => $product->id,
                    'category_id'   => $product->category_id,
                    'category_name' => $getcategory->title,
                    'type_id'       => $product->type_id,
                    'type_name'     => $gettype->title,
                    'merchant_id'   => $product->merchant_id,
                    'merchant_name' => $getmerchant->title,
                    'title'         => $product->title,
                    'price'         => $product->price,
                    'flashdeal'     => $flashdeal,
                    'description'   => strip_tags($product_desc_string),
                    'location'       => $location,
                    'clip'       => $clip,
                    'image'         => strip_tags($img_string),
                    'web_view'   => $product->web_view,
                );
                if (!empty($latitudeFrom) && !empty($longitudeFrom)) {
                    $data['data'][$key]['location']['distance'] = (double) number_format($distance, 3);
                }else{
                    $data['data'][$key]['location']['distance'] = 0;
                }
            }
        } else {
            $data['message'] = "There is no available Product near your location";
        }
        return parent::responseSuccess($data);
    }

    /**
     * Product category.
     *
     * @return       Product category
     * @Route(method =      'get', route = 'category-product', authentication = false)
     */
    public function categoryProductAction()
    {
        $category     = $this->request->get('category');
        if (!empty($category)) {
            $products = Product::getCategoryProducts($category);
        } else {
            $products = Product::find();
        }

        $current_user = parent::getCurrentUser();
                
        $data['status']    = "000000";
        $data['message']   = "success";
        if (!empty($products)) {
            $data['data'] = array();
            foreach ($products as $key => $product) {
                $desc          = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->description);
                $desc_string   = str_replace("&nbsp;", " ", $desc);
                $img           = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->image);
                $img_string    = str_replace("&nbsp;", " ", $img);
                $address       = preg_replace('/^\s+|\n|\r|\s+$/m', '', $product->address);
                $address       = htmlentities($address);
                
                $latitudeTo = $product->lat;
                $longitudeTo = $product->long;
                $theta = $longitudeFrom - $longitudeTo;
                $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $distance = ($miles * 1.609344);
                $getclip = Clip::findFirst([
		                "user_id = ?1 AND product_id = ?1",
		                "bind" => [$current_user['id'], $product->id]
		            ]);
        	
        		if (!empty($getclip)) {
                 	$clip = TRUE;
                 }else{
                 	$clip = FALSE;
                 }

                $getcategory = Category::findFirst($product->category_id);
                $gettype = Type::findFirst($product->type_id);
                $getmerchant = Merchant::findFirst($product->merchant_id);
                $flashdeal = array(
                        'isFlashDeal' => $product->flashdeal,
                        'startFlashDeal' => $product->flashdeal_start,
                        'endFlashDeal' => $product->flashdeal_end
                        );
                $location = array(
                        'address' => $address,
                        'lat'     => ($product->lat != null) ? $product->lat : "0",
                        'long'    => ($product->long != null) ? $product->long : "0",
                        );

                $data['data'][$key] = array(
                    'id'            => $product->id,
                    'category_id'   => $product->category_id,
                    'category_name' => $getcategory->title,
                    'type_id'       => $product->type_id,
                    'type_name'   	=> $gettype->title,
                    'merchant_id'   => $product->merchant_id,
                    'merchant_name' => $getmerchant->title,
                    'title'         => $product->title,
                    'price'         => $product->price,
                    'flashdeal'     => $flashdeal,
                    'description'   => strip_tags($product_desc_string),
                    'location'      => $location,
                    'clip'       	=> $clip,
                    'image'         => strip_tags($img_string),
                );
                if (!empty($latitudeFrom) && !empty($longitudeFrom)) {
                    $data['data'][$key]['location']['distance'] = (double) number_format($distance, 3);
                }else{
                    $data['data'][$key]['location']['distance'] = 0;
                }
            }
        } else {
            $data['status']    = "000001";
            $data['message'] = "There is no available Product near your location";
        }
        return parent::responseSuccess($data);
    }
    

   
    

    
}
