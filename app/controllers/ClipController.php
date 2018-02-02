<?php
namespace Controllers;

//use Helper\RedisHelper;
use \Models\Clip as Clip;
use \Models\Product as Product;
use \Models\Category as Category;
use \Models\Type as Type;
use \Models\Merchant as Merchant;
use \Models\User as User;
use \Phalcon\Http\Request;
/**
 * Add Clip controller
 *
 * @Route(route = "/v1/clip/")
 */
class ClipController extends \Application\Controller
{

     /**
     * Clip List.
     *
     * @return       Clip List
     * @Route(method =      'get', route = 'list-clip', authentication = false)
     */
    public function listClipAction()
    {
        $current_user = parent::getCurrentUser();
        $user_id = $current_user['id'];
        if (!empty($user_id)) {
            $clips = Clip::find('user_id="'.$user_id.'" AND is_deleted="false"'); 
        }

        $getproduct = Product::findFirst($clip->product_id);
        $getcategory = Category::findFirst($getproduct->category_id);
        $gettype = Type::findFirst($getproduct->type_id);
        $getmerchant = Merchant::findFirst($getproduct->merchant_id);

        $flashdeal = array(
                        'isFlashDeal' => $getproduct->flashdeal,
                        'startFlashDeal' => $getproduct->flashdeal_start,
                        'endFlashDeal' => $getproduct->flashdeal_end
                        );
        $latitudeTo = $getproduct->lat;
        $longitudeTo = $getproduct->long;
        $theta = $longitudeFrom - $longitudeTo;
        $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $distance = ($miles * 1.609344);

        $location = array(
                        'address' => $address,
                        'lat'     => ($getproduct->lat != null) ? $getproduct->lat : "0",
                        'long'    => ($getproduct->long != null) ? $getproduct->long : "0",
                        );

        $data['status']    = "000000";
        $data['message']   = "success";
        if (!empty($clips)) {
            $data['data'] = array();
            foreach ($clips as $key => $clip) {

                if (!empty($latitudeFrom) && !empty($longitudeFrom)) {
                    //$datas[]['location']['distance'] = (double) number_format($distance, 3);
                    $distance = (double) number_format($distance, 3);
                }else{
                    //$datas[]['location']['distance'] = 0;
                    $distance = 0;
                }

                $location = array(
                        'address' => $address,
                        'lat'     => ($getproduct->lat != null) ? $getproduct->lat : "0",
                        'long'    => ($getproduct->long != null) ? $getproduct->long : "0",
                        'distance' => $distance
                        );

                $datas[] = array(
                    'id'            => $clip->id,
                    'category_id'   => $getproduct->category_id,
                    'category_name' => $getcategory->title,
                    'type_id'       => $getproduct->type_id,
                    'type_name'     => $gettype->title,
                    'merchant_id'   => $getproduct->merchant_id,
                    'merchant_name' => $getmerchant->title,
                    'product_id'    => $clip->product_id,
                    'title'         => $getproduct->title,
                    'flashdeal'     => $flashdeal,
                    'description'  => $getproduct->description,
                    'location'       => $location,
                    'image'        => $getproduct->image,
                    'price'        => $getproduct->price,
                    'web_view'     => $getproduct->web_view,
                    'created_at'           => $clip->created_at,
                    
                );

                
                
            }

            $data['data'] = $datas;

        } else {
            $data['message'] = "There is no available clip";
        }
        return parent::responseSuccess($data);
    }

    /**
     * Clip Add.
     *
     * @return       Clip Add
     * @Route(method =      'post', route = 'add-clip', authentication = false)
     */
    public function addClipAction()
    {
       $body = parent::getBody();
        if ($body === null) {
            return parent::responseClientError(['status' => '001001', "message" => "Body is required."]);
        }

        $current_user = parent::getCurrentUser();
        $user_id = $current_user['id'];

        if ($body['product_id'] == "") {
            return parent::responseClientError(['status' => '001002', "message" => "product_id is mandatory."]);
        }

        $getclip = Clip::findFirst([
		                "user_id = ?1 AND product_id = ?1",
		                "bind" => [$current_user['id'], $body["product_id"]]
		            ]);

       if ($getclip == null) {
       	$clip =  new Clip();
        $clip->user_id = $user_id;
        $clip->product_id = $body["product_id"];
        $clip->is_deleted = "false";

        $data = array();
        if ($clip->save() == false) {
            $data['status'] = "001013|" . $clip->id . "|";

            foreach ($clip->getMessages() as $message) {
                $data['message'] .= $message . ".";
            }
            return parent::responseServerError($data);
        }

        $data['status']    = "000000";
        $data['message']   = "success";
        return parent::responseSuccess($data);
       }else{
       	$data['status']    = "001013";
        $data['message']   = "failed";
        return parent::responseSuccess($data);
       }
        


    }

     /**
     * Clip Remove.
     *
     * @return       Clip Remove
     * @Route(method =      'post', route = 'remove-clip', authentication = false)
     */
    public function removeClipAction()
    {
        $body = parent::getBody();
        if ($body === null) {
            return parent::responseClientError(['status' => '001001', "message" => "Body is required."]);
        }

        if ($body['product_id'] == "") {
            return parent::responseClientError(['status' => '001002', "message" => "product_id is mandatory."]);
        }

       	$current_user = parent::getCurrentUser();

        $this->db->begin();
        try {
            $clip = Clip::findFirst([
                "product_id = ?0 AND user_id = ?0",
                "bind" => [$body["product_id"],$current_user['id']],
                "order" => "id DESC"
                ]);

            if ($clip == false) {
                return parent::responseNotFound(["status" => "011008", "message" => "Clip not found."]);
            }
            
            $clip->is_deleted = true;

            if ($clip->save() == false) {
                return parent::responseServerError(['status' => '011004', "message" => "Failed to update clip."]);
            } 

        } catch (\Phalcon\Exception $e) {
            $this->db->rollback();
            return parent::responseClientError([
                "status" => "011005",
                "message" => "Error while update Clip."
            ]);
        }

        $this->db->commit();
        $data['data'] = $clip;
        $data['status']    = "000000";
        $data['message']   = "success";
        return parent::responseSuccess($data);
    }


}
