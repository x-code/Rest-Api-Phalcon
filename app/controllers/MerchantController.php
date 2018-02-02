<?php
namespace Controllers;

//use Helper\RedisHelper;
use \Models\Merchant as Merchant;

/**
 * Add Merchant controller
 *
 * @Route(route = "/v1/merchant/")
 */
class MerchantController extends \Application\Controller
{

	 /**
     * Merchant List.
     *
     * @return       Merchant List
     * @Route(method =      'get', route = 'list-merchant', authentication = false)
     */
    public function listMerchantAction()
    {
        $merchants = Merchant::find();
        $data['status']    = "000000";
        $data['message']   = "success";
        if (!empty($merchants)) {
            $data['data'] = array();
            foreach ($merchants as $key => $merchant) {
                // $img           = preg_replace('/^\s+|\n|\r|\s+$/m', '', $merchant->image);
                // $img_string    = str_replace("&nbsp;", " ", $img);
                
                
                $data['data'][$key] = array(
                    'id'            => $merchant->id,
                    'title'         => $merchant->title,
                    'status'         => $merchant->status,
                    
                );
                
            }
        } else {
            $data['message'] = "There is no available merchant";
        }
        return parent::responseSuccess($data);
    }


}
