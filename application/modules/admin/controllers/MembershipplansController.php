<?php

/* require "pagarme-php/Pagarme.php"; */

/**
 * MembershipplansController
 *
 * Dev: Namrata Singh
 * Date: 28-feb-2015
 */
class Admin_MembershipplansController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function preDispatch() {
        $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }


    public function createPlansAction() {
      
        $subModel = Admin_Model_Subscription::getInstance();
        /* require ( "pagarme-php / Pagarme.php" ); */
        $objPagar = new Engine_Pagarme_PagarmeClass();
        if ($this->getRequest()->isPost()) {
            $planname = $this->getRequest()->getPost('planname');
            $amount = $this->getRequest()->getPost('amount');

            $duration = $this->getRequest()->getPost('duration');
            $trial = $this->getRequest()->getPost('trial');
            if ($duration > 30) {
                $paymenttype = 2;
            } else {
                $paymenttype = 1;
            }
        
            // $amountsent = (int) $amount * 100;


            Pagarme :: setApiKey("ak_test_H8XElSFHXeO5BChZnfGbLyS3CdYvMU");

            if ($trial == 0) {

                $Plan = new PagarMe_Plan(array(
                    'amount' => $amount,
                    'days' => $duration,
                    'name' => $planname
                ));
            } else {
                $Plan = new PagarMe_Plan(array(
                    'amount' => $amount,
                    'days' => $duration,
                    'name' => $planname,
                    'trial_days' => $trial
                ));
            }

            $Plan->create();
            //echo "<pre>"; print_r($Plan); die('-----');
            $pid = $Plan->id;
            $amount_R = $amount / 100;
            if ($trial == 0) {

                $insertdata = array('subscription_type' => $planname,
                    'billing_period' => $duration,
                    'payment_amount' => $amount_R,
                    'plan_id' => $pid,
                    'trial_billing_period' => $trial,
                    'description' => 0,
                    'payment_type' => $paymenttype
                );

                $insertSub = $subModel->insertSubscriptionPlan($insertdata);
            } else {

                $insertdata = array('subscription_type' => $planname,
                    'billing_period' => $duration,
                    'payment_amount' => $amount_R,
                    'plan_id' => $pid,
                    'trial_billing_period' => $trial,
                    'description' => 1,
                    'payment_type' => $paymenttype
                );

                $insertSub = $subModel->insertSubscriptionPlan($insertdata);
            }

            if ($insertSub) {
              echo $insertSub;
//                $this->_redirect("/admin/membership-plans");
            }
        }
    }

// Dev: Priyanka V
    public function membershipAjaxHandlerAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $subModel = Admin_Model_Subscription::getInstance();
        $subid = $this->getRequest()->getParam('delsubscripton');
        $res = $subModel->subdelete($subid);
        if ($res) {
            echo $res;
            return $res;
        } else {
            echo "error";
        }
    }

    
 //dev: priyanka varanasi
 //desc; added action for coupans 
 //date:22/8/2015
    
    public function couponsAction(){
        
     $couponsmodal = Admin_Model_Coupons::getInstance();
     
         if($this->getRequest()->isXmlHttpRequest()){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
       $coupdelid = $this->getRequest()->getParam('coupondelid'); 
        $res  = $couponsmodal->couponDelete($coupdelid);
        if($res){
            echo json_encode($res);
        }
       
     }
     
      $couponsdetails   = $couponsmodal->getCouponDetails();
            if($couponsdetails){
                $this->view->coupondetails =   $couponsdetails;
              }
          
    }
    
    
    
 //dev: priyanka varanasi
 //desc; added action for user transaction details for credit card and boleto
 //date:22/8/2015
    
    public function transactionDetailsAction(){
         
      $fashiontransactionModal = Admin_Model_FashionTransactions::getInstance(); 
      
      $transactionsinfo = $fashiontransactionModal->getAllFashionTransactionDetails();
      $transactionsboletoinfo = $fashiontransactionModal->getAllBoletoFashionTransactionDetails();
       $transactionsboletocanceledinfo = $fashiontransactionModal->getAllBoletoCanceledTransactionDetails();
   
          
      if($transactionsboletocanceledinfo){
          
          
          $this->view->transactionsboletocanceledinfo = $transactionsboletocanceledinfo;
      }
       
      if($transactionsboletoinfo){
          
          $this->view->alltransactionboletoinfo = $transactionsboletoinfo;
      }
      if($transactionsinfo){
          
          $this->view->alltransactioninfo = $transactionsinfo;
      }
        
        
    }
    
    
        
 //dev: priyanka varanasi
 //desc; added action for creating coupon 
 //date:22/8/2015
    
    public function createCouponAction(){
    
    $couponsmodal = Admin_Model_Coupons::getInstance();    
     if ($this->getRequest()->isPost()) {
      $coupondata = array();
      $coupondata['coupon_code'] = $this->getRequest()->getPost('couponcode');
      $coupondata['coupon_name'] = $this->getRequest()->getPost('couponname');
      $coupondata['discount_offered'] = $this->getRequest()->getPost('coupondiscount');
      $coupondata['coupon_limit'] = $this->getRequest()->getPost('couponlimit');
      $coupondata['coupon_type'] = $this->getRequest()->getPost('subscriptionoffers');
      $coupondata['discount_type'] = $this->getRequest()->getPost('discount_type');
      $coupondatastartdate = $this->getRequest()->getPost('couponstartdate');
      $coupondataenddate = $this->getRequest()->getPost('couponenddate'); 
      $coupondata['coupon_startdate'] =  date('Y-m-d', strtotime($coupondatastartdate));
      $coupondata['coupon_enddate'] =  date('Y-m-d', strtotime($coupondataenddate));
    
      $couponid   = $couponsmodal->insertCouponDetails($coupondata);
      
      if($couponid){
          $this->_redirect('/admin/coupons');
          
      }
  
     } 
 
      
    }
 //dev: priyanka varanasi
 //desc; added action for editing coupon details 
 //date:22/8/2015
    
   public function editCouponAction(){
     $couponsmodal = Admin_Model_Coupons::getInstance();    
    
      $couponeditid = $this->getRequest()->getParam('edid');
       
        if($couponeditid){
        $couponinfo   = $couponsmodal->getCouponDetailsById($couponeditid); 
      
        $this->view->couponinfo = $couponinfo;
        }
       
          if ($this->getRequest()->isPost()) {
      $couponinfor = array();
      $couponinfor['coupon_code'] = $this->getRequest()->getPost('editcouponcode');
      $couponinfor['coupon_name'] = $this->getRequest()->getPost('editcouponname');
      $couponinfor['discount_offered'] = $this->getRequest()->getPost('editcoupondiscount');
      $couponinfor['coupon_limit'] = $this->getRequest()->getPost('editcouponlimit');
      $couponinfor['coupon_type'] = $this->getRequest()->getPost('editsubscriptionoffers');
      $couponinfor['discount_type'] = $this->getRequest()->getPost('editdiscounttype');
      
      $coupondatastartdate  = $this->getRequest()->getPost('editcouponstartdate');
      $coupondataenddate = $this->getRequest()->getPost('editcouponenddate');
      $couponinfor['coupon_startdate'] =  date('Y-m-d', strtotime($coupondatastartdate));
      $couponinfor['coupon_enddate'] =  date('Y-m-d', strtotime($coupondataenddate));
     
      $response   = $couponsmodal->updateCouponDetails($couponeditid,$couponinfor);
      
      if($response){
          $this->_redirect('/admin/coupons');
          
      }
  
     } 
   } 
   
 //dev: priyanka varanasi
 //desc; action for view of plan details
 //date:27/8/2015
   
   
    public function membershipPlansAction() {
        
        $PlansModel = Admin_Model_Plans::getInstance();
        $subResult = $PlansModel->getPlanDetails();
         $this->view->subResult = $subResult;
    }
    
    
    
    
   
 //dev: priyanka varanasi
 //desc; To edit plans and update in db
 //date:27/8/2015
    public function editPlansAction() {
      
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
       $PlansModel = Admin_Model_Plans::getInstance();
        $subID = $this->getRequest()->getParam('sid');
        $selectSub = $PlansModel->selectPlanOnId($subID);
        $this->view->selectSub = $selectSub;
        if ($this->getRequest()->isPost()) {
            $subdata['trail_days'] = $this->getRequest()->getPost('trial_duration');
            $subdata['amount'] = $this->getRequest()->getPost('amount');
            $subdata['subscription_period'] = $this->getRequest()->getPost('duration');
            $updateSubResult = $PlansModel->updatePlanInfo($subID, $subdata);
            echo $updateSubResult;

    }
    
    }
    
     //dev: priyanka varanasi
 //desc; To get all referral commission details 
 //date:14/10/2015
    public function  referralCommissionAction(){
       $commissionDetailsModal =  Admin_Model_ReferralCommissionTable::getInstance();
        
       $commissiondetails  = $commissionDetailsModal->getReferralCommissionDetails();
       if($commissiondetails){
           $this->view->commissiondetails = $commissiondetails;
           
       }
        
        
    }
    
        
   
 //dev: priyanka varanasi
 //desc; To edit commission values and update in db
 //date:27/8/2015
    public function editCommissionvaluesAction() {
      
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
       $commissionDetailsModal =  Admin_Model_ReferralCommissionTable::getInstance();
       
        if ($this->getRequest()->isPost()) {
            $subdata['commission_value'] = $this->getRequest()->getPost('com_value');
            $subdata['commission_type'] = $this->getRequest()->getPost('com_type');
            $id = $this->getRequest()->getPost('com_id');
            $commissiondetails  = $commissionDetailsModal->updateReferralCommissionDetails($subdata,$id);
            if($commissiondetails){
               $res = 200;
               echo json_encode($res);
               die();
               
           }else{
               $res =198;
                echo json_encode($res);
                die();
               
           }

    }
    
    }
    
}
