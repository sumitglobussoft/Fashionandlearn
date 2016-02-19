<?php
/**
 * PromotionController
 *
 * Name: Abhinish Kumar Singh
 * Date: 16/07/2014
 * Description: This controller contains various actions for contest promotion
 *              section.
 */
require_once 'Zend/Controller/Action.php';

class Admin_PromotionController extends Zend_Controller_Action {



    public function init() {    
     }

     
     public function preDispatch(){
       $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }
    /*
     * Name:priyanka varanasi
     * Date: 20/11/2014
     * Description: This action sets up the view data for contest-promotion
     */
    public function contestPromotionAction() {          
   
        $objPromotions = Admin_Model_Promotions::getInstance();
        $result = $objPromotions->getPromotions();
        if($result){
        $this->view->promotion = $result;
        }
    }
    
    /*
     * Name: Abhinish Kumar Singh
     * Date: 16/07/2014
     * Description: This actions edits the contest promotions data as set by the user
     */
    public function editPromosAction() {    
        
        $id = $this->getRequest()->getParam('pmid');
        $objPromotions = Admin_Model_Promotions::getInstance();
        $getDetails = $objPromotions->getPromotionsDetailsById($id);
//        
//        if ($this->getRequest()->isPost()):
//            $data = array();
//                  $data['promotion_content'] = $this->getRequest()->getPost('promotion_content');
//                  $data['status'] = $this->getRequest()->getPost('status');
//            $result = $objPromotions->updatePromotionDetails($id, $data);
//           // print_r($result);die;
//            if($result){    
//             $this->_redirect('/admin/contest-promotion');
//            
//            }
//           endif;
//        
       if($getDetails):
           $this->view->editpromotion = $getDetails;
       
        endif;
   
    }
    
    
    /*
     * Name: Abhinish Kumar Singh
     * Date: 16/07/2014
     * Description: This action allows the admin to add contest-promotions
     */
    public function addContestPromotionAction() {          
   
        $objPromotions = Admin_Model_Promotions::getInstance();
        
        if ($this->getRequest()->isPost()) :          
            $data = array();
                  $data['promotion_url'] = strtolower(str_replace(" ","-",$this->getRequest()->getPost('promotion_display_name')));
                  $data['promotion_display_name'] = $this->getRequest()->getPost('promotion_display_name');
                  $data['promotion_content'] = $this->getRequest()->getPost('promotion_content');
                  $data['status'] = $this->getRequest()->getPost('status');
                  
                 $response = $objPromotions->addPromotionDetails($data);
                 if($response):
                     $this->view->success = $response;
                 
                 endif;
        endif;
        
//        if($response){
//            $this->_redirect('admin/contest-promotion');
//        }
    }

}
