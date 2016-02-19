

<?php

class Membership_MembershipController extends Zend_Controller_Action {

    protected $setExpressResponse;
    protected $paymentAmount;
    protected $paymentOneTimeAmt;

    public function init() {
        
    }

    public function preDispatch() {

        // Display the recent updated profile picture  
        if (isset($this->view->session->storage->user_id)) {
       
            $user_id = $this->view->session->storage->user_id;
            $objUsermetaModel = Application_Model_UsersMeta::getinstance();
            $getmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);
            $teachingclass = Application_Model_TeachingClasses::getinstance();
            $this->view->userdetails = $getmetaresult;
 $this->view->profilepic = $getmetaresult['user_profile_pic'];
            $Coursesinstructing = $teachingclass->getCountOfClasses($user_id);

            $this->view->session->storage->courses = count($Coursesinstructing);
            $objnotifiModel = Application_Model_Notificationcenter::getInstance();

            $notificount = $objnotifiModel->getnotificount($user_id);
            $notifi = $objnotifiModel->getnotifi($user_id);

            $this->view->notificount = $notificount;
            $this->view->notifi = $notifi;
        }
        
        $objCategoryModel = Application_Model_Category::getInstance();
        $allCategories = $objCategoryModel->getAllCategories();
        $this->view->AllCategories = $allCategories;
    }

    public function membershipAction() {
    
        $user_id;
        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
        }

        //dev: priyanka varanasi
        //dated: 9/9/2015
        //desc: to redirect user to premium reatcivate page if he is not new user
        
        $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
        $plandetailsmodal =  Application_Model_Plans::getinstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        if($allplaninfo){
            
          $this->view->planinfo = $allplaninfo;
        }
        if(isset($this->view->session->storage->user_id)){
        $user_id = $this->view->session->storage->user_id;
        $result = $paymentnewModal->getUserPaymentInfo($user_id);
       
         if($result){
          if(($result['new_user'])==0){

         $this->_redirect('/premium-reactivate');
       }
         }
        
    }
    }

    public function upgradeAction() {
        $user_id;
        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
        }
   
             // dev: priyanka varanasi added this line to get the plan id from url and send to view
        //date : 21/8/2014
            
        $planvalue =  $this->getRequest()->getParam('val');
        $plandetailsmodal =  Application_Model_Plans::getinstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plandetailsmodal =  Application_Model_Plans::getinstance();
         $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
        $user_id = $this->view->session->storage->user_id;
        $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
         $couponsModal  =  Application_Model_Coupons::getinstance();
         $result = $paymentnewModal->getUserPaymentInfo($user_id);
         if($result){
           $this->view->memberstatusreport =  $result; 
         }
        $plan1 = $allplaninfo[0];
        $plan2 = $allplaninfo[1]; 
        if($planvalue == $plan1['plan_type_id']){
           $this->view->planval = $plan1; 
        }else{
          $this->view->planval = $plan2;   
        }
        //////////////////////////code ends //////////////
//        $this->view->year = $dateVal[0];
//        $this->view->month = $m;
//        $this->view->date = $dateVal[2];
    }

    public function membershipAjaxHandlerAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        if ($this->getRequest()->isPost()) {
            $subid = $this->getRequest()->getParam('sub_id');
            $planid = $this->getRequest()->getParam('plan_id');
            $subvalue = $this->getRequest()->getParam('select');
            $valueSub = $this->getRequest()->getParam('valueSub');
            //echo $valueSub; die('123');
            unset($this->view->session->storage->subid);
            $this->view->session->storage->subid = $subid;
            unset($this->view->session->storage->pid);
            $this->view->session->storage->pid = $planid;
            unset($this->view->session->storage->subvalue);
            $this->view->session->storage->subvalue = $subvalue;
            
            if (isset($valueSub)) {
               $this->view->session->storage->valueSubBox = $valueSub;
            }
            echo json_encode($subid);
        }
    }

    public function upgradeMembershipAjaxHandlerAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        if ($this->getRequest()->isPost()) {

            $subsid = $this->getRequest()->getParam('subsc_id');
            $planidval = $this->getRequest()->getParam('plan_id_val');
            $subscvalue = $this->getRequest()->getParam('selected_val');
          
            $this->view->session->storage->pid = $planidval;
            $this->view->session->storage->subid = $subsid;
            $this->view->session->storage->subvalue = $subscvalue;
//           echo json_encode("1"); 
        }
    }

    public function cancelAjaxHandlerAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        //$subid = $this->getRequest()->getParam('method');
        $objPagar = new Engine_Pagarme_PagarmeClass();
        $objPayment = Application_Model_Payment::getinstance();
        $pagarSubId;
        $paymentid;
        if (isset($this->view->session->storage->member)) {
            $pagarSubId = $this->view->session->storage->member['pagar_subscription_id'];
            $paymentid = $this->view->session->storage->member['payment_id'];
        }
        //$this->session->storage->member =1;
        Pagarme :: setApiKey("ak_test_H8XElSFHXeO5BChZnfGbLyS3CdYvMU");
        $subscription = PagarMe_Subscription :: findById($pagarSubId);
        $subscription->cancel();  // Cancel
        $status = $subscription->getStatus();
        if ($status) {
            $data = array('status' => $status);
            $updtaeStatus = $objPayment->updateStatus($data, $paymentid);
        }
        echo $status; die('123');
        if ($updtaeStatus) {
            $this->view->session->storage->member = $updtaeStatus;
        }
        echo json_encode($status);
    }

}
?>
