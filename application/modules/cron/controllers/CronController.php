<?php

/**
 * AdminController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';
require_once 'Engine/Pagarme/Pagarme/RestClient.php';

class Cron_CronController extends Zend_Controller_Action {

    public $teams;
    public $serachurl;
    public $teamcode;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    /**
      Developer: Namrata Singh
      Description: Cancelling subscription of users whose end date is crossed
      Date: 11 March'2015
     * */
    public function manageSubscriptionAction() {

// $currentDateTime = DATE("Y-m-d H:i:s");
//                    echo "<pre>"; print_r(strtotime($currentDateTime)); die('---');
        $objPagar = new Engine_Pagarme_PagarmeClass();
        Pagarme :: setApiKey("ak_test_H8XElSFHXeO5BChZnfGbLyS3CdYvMU");
        //$userid = $this->view->session->storage->user_id;
        $objPayment = Application_Model_Payment::getinstance();
        $currentDateTime = DATE("Y-m-d H:i:s");
        $current_unix_timestamp = STRTOTIME($currentDateTime);
        $getPaidMember = $objPayment->selectMember();
        //echo "<pre>"; print_r($current_unix_timestamp) ; die('---current time'); 
//   echo "<pre>"; print_r($getPaidMember) ; die('hello'); 
        foreach ($getPaidMember as $val) {

            if (($current_unix_timestamp >= $val['unix_start_timestamp']) && ($current_unix_timestamp <= $val['unix_end_timestamp'])) {

                $pagarSubId = $val['pagar_subscription_id'];
                $subscription = PagarMe_Subscription :: findById($pagarSubId);
                $status = $subscription->getStatus();
                $data = array('status' => $status);
                $where = $val['payment_id'];
                $getMembers = $objPayment->updateStatus($data, $where);
            } elseif ($current_unix_timestamp > $val['unix_end_timestamp']) {

                $objPagar = new Engine_Pagarme_PagarmeClass();
                Pagarme :: setApiKey("ak_test_H8XElSFHXeO5BChZnfGbLyS3CdYvMU");
                $subscription = PagarMe_Subscription :: findById($val['pagar_subscription_id']);

                $subscription->cancel();  // Cancel
                $status = $subscription->getStatus();

                $data = array('status' => $status);
                $where = $val['payment_id'];
                $getMembers = $objPayment->updateStatus($data, $where);
            }
        }
    }

    /**
      Developer: Namrata Singh
      Description: Giving Free month to the user who has received free months
      Date: 12 March'2015
     * */
    public function freeSubscriptionAction() {
        $objUserModel = Application_Model_Users::getinstance();
        $objPayment = Application_Model_Payment::getinstance();
        //---------------------------------
//        $count = 2;
//        $totalCountDays = $count * 30;
        // $currentDateTime = DATE("Y-m-d H:i:s");
//        // echo "<pre>"; print_r($totalCountDays); die('---');
//        $endDate = date('Y-m-d H:i:s', strtotime('+ ' . $totalCountDays . 'days'));
        //   $unixStart = STRTOTIME($currentDateTime);
//        $unixEnd = STRTOTIME($endDate);
//        echo $currentDateTime;
//        echo "<pre>";
        //     echo $unixStart;
//        echo "<pre>";
//        echo $endDate;
//        echo "<pre>";
//        echo $unixEnd;
//        echo "<pre>";
        //       die('all dates');
        //------------------------------------
        $getBonusMonths = $objUserModel->bonusMonth();
        echo "<pre>";
        print_r($getBonusMonths);
        die('---');
        foreach ($getBonusMonths as $bonusVal) {
            $bonusUserId = $bonusVal['user_id'];
            // echo "<pre>"; print_r($bonusVal); die('@@');
            $getLatestPayment = $objPayment->bonusFreeMonth(60);                  //gives the details of last payment made
//           echo "<pre>"; print_r($getLatestPayment); die('@#');
            if (!empty($getLatestPayment)) {

                foreach ($getLatestPayment as $latestVal) {

                    $latestStatus = $latestVal['status'];
//                 echo "<pre>"; print_r($latestStatus); die('@#');
                    if ($latestStatus == 'canceled') {
                        // die($latestStatus);
                        // echo "<pre>"; print_r($latestVal['payment_id']); die('---');
                        $count = $bonusVal['referral_counts'];

                        $totalCountDays = $count * 30;
                        $currentDateTime = DATE("Y-m-d H:i:s");
                        // echo "<pre>"; print_r($totalCountDays); die('---');
                        $endDate = date('Y-m-d H:i:s', strtotime('+ ' . $totalCountDays . 'days'));

                        $unixStart = STRTOTIME($currentDateTime);
                        $unixEnd = STRTOTIME($endDate);

                        $data = array('current_period_start' => $currentDateTime,
                            'current_period_end' => $endDate,
                            'unix_start_timestamp' => $unixStart,
                            'unix_end_timestamp' => $unixEnd,
                            'status' => 'free');
                        $where = $latestVal['payment_id'];

                        $updateDetail = $objPayment->updateStatus($data, $where);              //updates status and date in payment table
                        if (isset($updateDetail) == 1) {

                            $dataCount = array('referral_counts' => 0);
                            $updateCount = $objUserModel->userUpdateCount($dataCount, $bonusUserId);               //updates user table with the count 0
                        }
                    } else {
                        // die('outside here');
                    }
                }
            } else {
                // die('no latest payment record');
            }
        }
    }

    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// ALL NEW USERS CREDIT CARD CRONS START //////////////////////////////////
    /////////////////////////////// TRAIL USERS CRON, POST BACK URL HITS FROM PAGAR/////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
    
    //dev: priyanka varanasi
    //dated: 25/8/2015
    /*     * **** desc: This  cron action performs***** */
    // 1.get the details of all trail users 
    // 2.pay for the users whose trail going to end and use payment is awaiting 
    // 3 .saving the respective details in db
    // 4. saving the details of transactions in transactions db  and updating the respective status

    public function trailusersCronAction() {

        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $host = $realobj->hostLink;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
        $currentDate = date('Y-m-d');
        $alertperiod = date('Y-m-d', strtotime('-' . $warningdays . 'days'));
        $alltrailusersinfo = $paymentnewModal->getAllTrailUsers($currentDate,$alertperiod);
        echo '<-------------------------------------credit card trail cron started-------------------------------------------->';
        
        echo '<html><table border="1">';
        print '<th>userid</th><th>email</th><th>transactionid</th><th>status</th><th>comments</th>';
        if($alltrailusersinfo){
            echo "<tbody>";
         foreach ($alltrailusersinfo as $usera) {
            echo "<tr>";
              try {
                    
                    $data["api_key"] = $apikey;
                    $data["amount"] = $usera["discounted_val"]*100;
                    $data["card_id"] = $usera["pagar_id"];
                    $data["postback_url"] = $host.'/postback-url/'.$usera["user_id"];
                    $params["url"] = 'https://api.pagar.me/1/transactions';
                    $params["parameters"] = $data;
                    $params["method"] = "POST";
                    $rs = new RestClient($params);
                    $result = $rs->run();
                    echo "<pre>";
                    $result = (array) $result;
                    $body = (array) json_decode($result["body"]);
                    if ($result["code"] == 200) {
                     print "<td> " . $usera["user_id"] . "</td>" . "<td> " . $usera["email"] . "</td>" . "<td> " . $body["id"] . "</td>" . "<td> " . $body["status"] . "</td>" . "<td> " . $body["status_reason"] . "</td>";
                        $ndata = array();
                        $ndata["user_id"] = $usera["user_id"];
                        $ndata["transaction_id"] = $body["id"];
                        $ndata["ip"] = $body["ip"];
                        $ndata["amount"] = $body["amount"]/100;
                        $ndata["status"] = $body["status"];
                        $ndata["status_reason"] = $body["status_reason"];
                        $ndata["transaction_date"] = date('Y-m-d');
                        $ndata["cardfirstdigits"] = $body['card_first_digits'];
                        $ndata["cardlastdigits"] =  $body['card_last_digits'];
                        $ndata["brand"] = $body['card_brand'];
                        $ndata["plantype"] = $usera["plan_type"];
                        $ndata["pay_type"] = 1;
                        $res=$transmodel->insertUserTransactionsInfo($ndata);
                        
                        //to update in payment new table 
                        
                        $currentdate = date('Y-m-d');
                        $payin =array();
                        if($usera['plan_type'] == $plan[1]['plan_type_id']){
                        $payin['subscription_end']= date('Y-m-d', strtotime( $currentdate.'+'.$plan[1]['subscription_period'].'days'));
                        }else{
                        $payin['subscription_end']= date('Y-m-d', strtotime( $currentdate.'+'.$plan[2]['subscription_period'].'days'));   
                        }
                        $payin['transaction_no']=  $body["id"];
                        $payin['subscription_start']= date('Y-m-d');
                        $payin['customer_status']= 3;
                        $payin['new_user']= 0;
                        $payin['paid_status']= $body["status"];
                        
                       $updatedresult  = $paymentnewModal->updateUserPaymentInfo($usera["user_id"],$payin);
                       ///////////code ends //////////////////////
                      
                                //desc:referal code 
                                //dev: priyanka varanasi
                                ///date:13/10/2015
               $referralpaymenttableModal = Application_Model_ReferralPaymentTable::getinstance();
               $referralcommissiontableModal = Application_Model_ReferralCommissionTable::getinstance();
               $refcommission = $referralcommissiontableModal->getReferralCommissionDetails();
               $refcom1 = $refcommission[0];
               $refcom2 = $refcommission[1];
                   if($usera['teacherrefral'] && $usera['teacherrefral']!=0 ){
                  $repo  =  $referralpaymenttableModal->getReferalRowByRefferredId($usera['teacherrefral']);
                     if(!empty($repo)) { 
         // if a row is present for thi suser in current month then update data
                          $refdata['user_id'] = $usera['teacherrefral'];
                   if($usera['plan_type'] ==  $plan[1]['plan_type_id']){
                          $refdata['students_monthly'] = 1;
                   if($refcom1['commission_type']==1){
                          $refdata['amount_monthly'] = ($plan[1]['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                    }else{
                          $refdata['students_annually']= 1;
                       if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan[2]['amount'] * $refcom2['commission_value'])/100;
                     }else{
                          $refdata['amount_annually'] = $refcom2['commission_value'];
                       }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                     }
                    
                     $refdata['pay_status'] = 0;
                  
                 $referralpaymenttableModal->updateReferralPaymentInfo($repo['user_id'],$repo['ref_id'],$refdata);
                 $uinfo['teacherrefral'] = 0;
                 $paymentnewModal->updateUserPaymentInfo($usera['user_id'],$uinfo);
                   }else{
                    // if row is not present for user then insert data
                      $refdata['user_id'] = $usera['teacherrefral'];
                       if($usera['plan_type'] == $plan[1]['plan_type_id']){
                       $refdata['students_monthly'] = 1;
                       $refdata['students_annually'] = 0;
                       $refdata['amount_annually'] = 0;
                      if($refcom1['commission_type']==1){
                         $refdata['amount_monthly'] = ($plan[1]['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                     }else{
                          $refdata['students_annually'] = 1;
                          $refdata['students_monthly'] = 0;
                          $refdata['amount_monthly'] = 0;
                      if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan[2]['amount'] * $refcom2['commission_value'])/100;
                    }else{
                          $refdata['amount_annually'] = $refcom2['commission_value'];
                         }  
                     }
                     $refdata['total_earned'] = $refdata['amount_monthly']+ $refdata['amount_annually'];
                     $refdata['pay_status'] = 0;
                     $refdata['payment_date'] = date('Y-m-d');
                    
                     $referralpaymenttableModal->insertReferralPaymentInfo($refdata);
                     $uinfo['teacherrefral'] = 0;
                     $paymentnewModal->updateUserPaymentInfo($usera['user_id'],$uinfo); 
                     } 
                       
                    }
               
              ///////////////////////////code ends //////////////////////
                       
                       
                        $mailer = Engine_Mailer_Mailer::getInstance();
                        $template_name = 'Blank-transaction';
                 
                        $name = "test";
                        $email = $usera['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Your subscription is started , now you can enjoy unlimited services in fashion learn upto your end of the subscription.i.e, on".date("F j, Y", strtotime($payin['subscription_end'])),
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                    } else {
                        print "<td> " . $usera["user_id"] . "</td>" . "<td> " . $usera["email"] . "</td>" . "<td>Null</td>" . "<td> Payment failed</td>" . "<td>".json_encode($body['errors'])."</td>";
                       
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $usera['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "payment failure"
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                    }
                } catch (Exception $e) {
                    
                }
           echo "</tr>";  
        }
        
          echo "</tbody>";
        }else{
          echo "</tbody>";
          print '<td>NO TRIAL USERS FOR THIS DAY</td><td>-</td><td>-</td><td>-</td><td>-</td>';  
          echo "</tbody>";
        }
        
        echo '</table></html>';
        echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die();
    }
    //dev: priyanka varanasi
    //dated: 27/8/2015
          /****** desc: This  cron action performs***** */
    // 1.return the response from pagar regarding user status to this action 
    // 2. fetching the post params and updating them in db
    
    public function postbackUrlAction() {
        
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $UsersModal = Application_Model_Users::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $fashiontransactionmodal = Application_Model_FashionTransactions::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails(); 
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
        $data = array();
        $userid = $this->getRequest()->getParam('uid');
        $usersinfo = $UsersModal->getUserDetail($userid);
        $userpayinfo =  $paymentnewModal->getUserPaymentInfo($userid);
       if ($this->getRequest()->isPost()){
             $fingerprint = $this->getRequest()->getPost('fingerprint');
             $trans_id = $this->getRequest()->getPost('id');
             $currentstatus= $this->getRequest()->getPost('current_status');
             $matchresult  = $objCore->validateFingerprint($trans_id,$fingerprint);
        if($matchresult){
            $usertransinfo = $fashiontransactionmodal->getTransInfoById($userid,$trans_id);
            if($usertransinfo['status']== "canceled"){
               
                
            }else if($currentstatus== "refused"){
          $dat['customer_status'] = 7;
        
                      
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "sorry your transaction has been failed due to some technical reasons, please check your bank account"
                            )
                        );

         $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
        $dat['paid_status'] =  $currentstatus;
        $result = $paymentnewModal->updateUserPaymentInfo($userid,$dat);
        $data['finger_print'] = $this->getRequest()->getPost('fingerprint');
        $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$data); 
        
       } else if($currentstatus == "paid"){
        $det['paid_status'] =  $currentstatus;
        $result = $paymentnewModal->updateUserPaymentInfo($userid,$det);
        $in['finger_print'] = $fingerprint;
        $in['status'] = $currentstatus;
        $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$in);
                                 //desc:referal code 
                                //dev: priyanka varanasi
                                ///date:13/10/2015
               $referralpaymenttableModal = Application_Model_ReferralPaymentTable::getinstance();
               $referralcommissiontableModal = Application_Model_ReferralCommissionTable::getinstance();
               $refcommission = $referralcommissiontableModal->getReferralCommissionDetails();
               $refcom1 = $refcommission[0];
               $refcom2 = $refcommission[1];
                   if($userpayinfo['teacherrefral'] && $userpayinfo['teacherrefral']!=0 ){
                  $repo  =  $referralpaymenttableModal->getReferalRowByRefferredId($userpayinfo['teacherrefral']);
                     if(!empty($repo)) { 
         // if a row is present for thi suser in current month then update data
                          $refdata['user_id'] = $userpayinfo['teacherrefral'];
                   if($userpayinfo['plan_type'] ==  $plan[1]['plan_type_id']){
                          $refdata['students_monthly'] = 1;
                   if($refcom1['commission_type']==1){
                          $refdata['amount_monthly'] = ($plan[1]['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                    }else{
                          $refdata['students_annually']= 1;
                       if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan[2]['amount'] * $refcom2['commission_value'])/100;
                     }else{
                          $refdata['amount_annually'] = $refcom2['commission_value'];
                       }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                     }
                    
                     $refdata['pay_status'] = 0;
                  
                 $referralpaymenttableModal->updateReferralPaymentInfo($repo['user_id'],$repo['ref_id'],$refdata);
                 $uinfo['teacherrefral'] = 0;
                 $paymentnewModal->updateUserPaymentInfo($userpayinfo['user_id'],$uinfo);
                   }else{
                    // if row is not present for user then insert data
                      $refdata['user_id'] = $userpayinfo['teacherrefral'];
                       if($userpayinfo['plan_type'] == $plan[1]['plan_type_id']){
                       $refdata['students_monthly'] = 1;
                       $refdata['students_annually'] = 0;
                       $refdata['amount_annually'] = 0;
                      if($refcom1['commission_type']==1){
                         $refdata['amount_monthly'] = ($plan[1]['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                     }else{
                          $refdata['students_annually'] = 1;
                          $refdata['students_monthly'] = 0;
                          $refdata['amount_monthly'] = 0;
                      if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan[2]['amount'] * $refcom2['commission_value'])/100;
                    }else{
                          $refdata['amount_annually'] = $refcom2['commission_value'];
                         }  
                     }
                     $refdata['total_earned'] = $refdata['amount_monthly']+ $refdata['amount_annually'];
                     $refdata['pay_status'] = 0;
                     $refdata['payment_date'] = date('Y-m-d');
                    
                     $referralpaymenttableModal->insertReferralPaymentInfo($refdata);
                     $uinfo['teacherrefral'] = 0;
                     $paymentnewModal->updateUserPaymentInfo($userpayinfo['user_id'],$uinfo); 
                     } 
                       
                    }
               
              ///////////////////////////code ends //////////////////////
       $mailer = Engine_Mailer_Mailer::getInstance();
                        $template_name = 'Blank-transaction';
                 
                        $name = "test";
                        $email = $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "you have successfully paid , now you can enjoy unlimited services from us , THANK YOU!"
                            )
                        );

                        
                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
              
                        
       }else if($currentstatus == "refunded"){
            $int['status'] = "refunded";
            $int['finger_print'] = $fingerprint;
            $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$int);
            if($userpayinfo['transaction_no']==$trans_id){
            $da['paid_status'] = "refunded";
            $da['customer_status'] = 8;
            $result = $paymentnewModal->updateUserPaymentInfo($userid,$da);
             }
              $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Your money has been refunded to your account,please once check your details.You are now free user , subscribe now to access unlimited services",
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject,$mergers, "financeiro@fashionlearn.com.br");   
            }else{
                
            $lt['finger_print'] =  $fingerprint;
            $lt['status'] = $currentstatus;
            $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$lt);   
            $dt['paid_status'] = $currentstatus;
            $result = $paymentnewModal->updateUserPaymentInfo($userid,$dt);  
                
            }
        }else{
                  
                echo "UnAuthorized Source" ;
                  
              }
     
         }
         
         die();
    }
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// ALL NEW USERS BOlETO CRONS START /////////////////////////////////////////
    /////////////////////////////// TRAIL USERS CRON, WARNING DAYS CRON,POST BACK URL HITS FROM PAGAR/////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    
        
    
       //dev: priyanka varanasi
    //dated: 2/9/2015
          /****** desc: This  cron action performs***** */
    // 1.check the payement for trail end users and make the status in db regarding response 
    // 2. fetching the post params and updating them in db
    // 3 For payment type boleto trail end users
    
    public function trialboletoUrlAction() {
        
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $host = $realobj->hostLink;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $fashiontransactionmodal = Application_Model_FashionTransactions::getinstance();
        $paymentboletomodal =  Application_Model_PaymentBoleto::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
        $currentdate = date('Y-m-d');
        $boletotrailusers  = $paymentnewModal->getAllTrailBoletoUsers($currentdate);
          echo '<-------------------------------------cron started-------------------------------------------->';
        echo '<html><table border="1">';
        print '<th>userid</th><th>email</th><th>transactionid</th><th>status</th><th>comments</th>';
//        echo"<pre>";print_r($boletotrailusers);
        if($boletotrailusers){
            echo "<tbody>"; 
        foreach ($boletotrailusers as $value) {
             echo "<tr>"; 
//         if($value['paid_status'] === "paid"){
//            $data['subscription_start'] = date('Y-m-d');
//            $data['customer_status'] = 3;
//            if($value['plan_type'] == $plan[1]['plan_type_id']){
//                 $data['subscription_end']= date('Y-m-d', strtotime('+'.$plan[1]['subscription_period'].'days'));
//                   }else{
//                $data['subscription_end']= date('Y-m-d', strtotime('+'.$plan[2]['subscription_period'].'days'));   
//                 }
//         $result = $paymentnewModal->updateUserPaymentInfo($value['user_id'],$data);
//            $template_name = 'Blank-transaction';
//                
//                        $name = "test";
//                        $email =  $value['email'];
//                        $subject = "payment";
//
//                        $mergers = array(
//                            array(
//                                'name' => 'text',
//                                'content' => "Your subscription is started , now you can enjoy unlimited services in fashion learn upto your end of the subscription.i.e, on".date("F j, Y", strtotime($data['subscription_end'])),
//                            )
//                        );
//
//                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
 //           }else{
            
            //again generate the new boleto url //
                
                if($value['discounted']==1){
                    $couponres =  $couponsModal->checkCouponCode($couponcode);
                    if($couponres){
                        if($value['plan_type']= $plan[1]['plan_type_id']){
                   if($couponres['discount_type']==0){
                     $amount = ($plan[1]['amount']* $couponres['discount_offered'])/100;
                     $actualamount = $plan[1]['amount'] - $amount;
                     
                         }else{
                     $actualamount = ($plan[1]['amount'] - $couponres['discount_offered']);
                       }
                       }else{
                         if($couponres['discount_type']==0){
                     $amount = ($plan[1]['amount']* $couponres['discount_offered'])/100;
                     $actualamount = $plan[1]['amount'] - $amount;
                     
                         }else{
                     $actualamount = ($plan[1]['amount'] - $couponres['discount_offered']);
                       }    
                        }
                  $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);     
                    }else{
                     $actualamount = $plan[1]['amount']; 
                     $paymentnewdetails['discounted'] =0; 
                    }
                    
                }else{
                 $actualamount = $plan[1]['amount'];     
                }
            try {
             $usertransinfo = $fashiontransactionmodal->getTransInfoById($value['user_id'],$value['transaction_no']);
            
             if($usertransinfo['status']== "waiting_pay"){
               $userinfo['status'] = "canceled";
               $result  = $fashiontransactionmodal->updateUserTransInfo($value['transaction_no'],$userinfo); 
              
            }
                  $address = $paymentboletomodal->getRecentUserAddressByUserID($value['user_id']); 
                 
                    $data["api_key"] = $apikey;
                    $data["amount"] = $actualamount*100;
                    $data["payment_method"] = "boleto";
                    $data['postback_url']= $host.'/postbackboletoreactivation-url/'.$value['user_id'];
                    $data["boleto_expiration_date"] = date('m-d-Y', strtotime('+'.$warningdays.'days'));
                     $data['customer']=array(
            "name" => $address['name'],
            "document_number" =>$address['cpf'],
            "email" => $value['email'],
            "address" => array(
                "street" => $address['street_name'],
                "neighborhood" => $address['neighborhood'],
                "zipcode" => $address['zip_code'],
                "street_number" => $address['street_number'],
                
            ),
            "phone" => array(
                "ddd" => $address['phone_ddd'],
                "number" => $address['phone_no'] 
            )
        );
                    $params["url"] = 'https://api.pagar.me/1/transactions';
                    $params["parameters"] = $data;
                    $params["method"] = "POST";
                    $rs = new RestClient($params);
                    $result = $rs->run();
                    $result = (array) $result;
                    $body = (array) json_decode($result["body"]);
                    if ($result["code"] == 200) {
                 print "<td> " . $value["user_id"] . "</td>" . "<td> " . $value["email"] . "</td>" . "<td> " . $body["id"] . "</td>" . "<td> " . $body["status"] . "</td>" . "<td> " . $body["status_reason"] . "</td>";       
                        $ndata = array();
                        $ndata["user_id"] = $value['user_id'];
                        $ndata["transaction_id"] = $body["id"];
                        $ndata["ip"] = $body["ip"];
                        $ndata["amount"] = $body["amount"]/100;
                        $ndata["status"] = $body["status"];
                        $ndata["status_reason"] = $body["status_reason"];
                        $ndata["transaction_date"] = date('Y-m-d');
                        $ndata["plantype"] = $value['plan_type'];
                        $ndata["boleto_url"] = $body['boleto_url'];
                        $ndata["boleto_barcode"] = $body['boleto_barcode'];
                        $ndata["boleto_expdate"] = $body['boleto_expiration_date'];
                        $ndata["pay_type"] = 2;
                        $ndata["address_id"] = $address['b_id'];
                     
                     $res=$transmodel->insertUserTransactionsInfo($ndata);///////here it will insert the transaction details
                     $paymentnewdetails['customer_status'] =4 ;
                     $paymentnewdetails['discounted_val'] = $actualamount ;
                     $paymentnewdetails['transaction_no'] = $body["id"] ;
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($value['user_id'],$paymentnewdetails);
            
                     
                        $mailer = Engine_Mailer_Mailer::getInstance();
                        $template_name = 'Blank-transaction';
                 
                        $name = "test";
                        $email =  $value['email'];
                        $subject = "payment";

                        $mergers = array(
                            
                            
                              array(
                                'name' => 'yourboletourl',
                                'content' => $body["boleto_url"]
                                
                            ),
                              array(
                                'name' => 'yourboletobarcode',
                                'content' => $body["boleto_barcode"]
                                
                            ),
                            
                              array(
                                'name' => 'Yourexpirydate',
                                'content' => $body["boleto_expiration_date"]
                                
                            ),
                                array(
                                'name' => 'message',
                                'content' =>"Please make payment on or before  your boleto expiration date  "
                                
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                    } else {
          print "<td> " . $value["user_id"] . "</td>" . "<td> " . $value["email"] . "</td>" . "<td>Null</td>" . "<td> Payment failed</td>" . "<td>".json_encode($body['errors'])."</td>";              
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $value['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "you have not yet paid to start services , please check the boleto account details "
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                    }
                } catch (Exception $e) {
                    
                }
              
               echo "</tr>";  
                    }
                     echo "</tbody>";  
    }
    else{
          echo "</tbody>";
          print '<td>NO TRIAL BOLETO USERS FOR THIS DAY</td><td>-</td><td>-</td><td>-</td><td>-</td>';  
          echo "</tbody>";
        }
        echo '</table></html>';
        echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die(); 
       
    }
    

           //dev: priyanka varanasi
    //dated: 2/9/2015
          /****** desc: This  cron action performs***** */
    // 1.check the all users whose customer status is awaiting payment
   
    
  public function trailWarningdaysurlAction(){
      
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $fashiontransactionmodal = Application_Model_FashionTransactions::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
        $currentDate = date('Y-m-d');
        $alertperiod = date('Y-m-d', strtotime('-' . $warningdays . 'days'));
       $trailwarnusers =  $paymentnewModal->getAllWarnTrailUsersForBoleto($alertperiod);
        echo '<------------------------------------- warning cron started-------------------------------------------->';
        echo '<html><table border="1">';
        print '<th>userid</th><th>email</th><th>status</th><th>comments</th>';
      if($trailwarnusers){
            echo '<tbody>';
          foreach ($trailwarnusers as $value) {
               echo '<tr>';
            $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $value['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "As you have exceeeded the warning date for payment, you are not allowed to access pur service, please make payment to use our services"
                            )
                        );

            $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
            $dat['customer_status'] = 7;
            $result = $paymentnewModal->updateUserPaymentInfo($value['user_id'],$dat);
           print "<td> " . $value["user_id"] . "</td>" . "<td> " . $value["email"] . "</td><td>Unpaid User</td>" . "<td>Warning date exceeded</td>";
           echo "</tr> ";
             
          }
          echo '</tbody>';
          
          
      }else{
       echo "</tbody>";
            print '<td>NO Warning users for this day</td><td>-</td><td>-</td><td>-</td>';  
          echo "</tbody>";   
          
      }
       echo "</table></html>";
        echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die();
  }
      
    
    //dev: priyanka varanasi
    //dated: 2/9/2015
          /****** desc: This  cron action performs***** */
    // 1.return the response from pagar regarding user status to this action 
    // 2. fetching the post params and updating them in db
    // 3 For payment type boleto
    
    public function postbackboletoUrlAction() {
        
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $fashiontransactionmodal = Application_Model_FashionTransactions::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails(); 
        $data = array();
        $dat = array();
      
      $plan[1] = $allplaninfo[0];
      $plan[2] = $allplaninfo[1];
      $userid = $this->getRequest()->getParam('uid');
      $userpayinfo =  $paymentnewModal->getUserPaymentInfo($userid);
        if ($this->getRequest()->isPost()){
             $fingerprint = $this->getRequest()->getPost('fingerprint');
             $trans_id = $this->getRequest()->getPost('id');
             $status = $this->getRequest()->getPost('current_status');
             $matchresult  = $objCore->validateFingerprint($trans_id,$fingerprint);
             
       if($matchresult){
             $usertransinfo = $fashiontransactionmodal->getTransInfoById($userid,$trans_id);
             if($usertransinfo['status']== "canceled"){
               
            }else if($status == "paid"){
                  if($userpayinfo['subscription_end'] >= date('Y-m-d')){
              $dat['subscription_start'] = date('Y-m-d', strtotime($userpayinfo['subscription_end'].'+1days'));
           if($userpayinfo['plan_type'] == $plan[1]['plan_type_id']){
                 $dat['subscription_end']= date('Y-m-d', strtotime($dat['subscription_start'].'+'.$plan[1]['subscription_period'].'days'));
                   }else{
                $dat['subscription_end']= date('Y-m-d', strtotime($dat['subscription_start'].'+'.$plan[2]['subscription_period'].'days'));   
                 }
            }else{
              $dat['subscription_start'] = date('Y-m-d');    
            if($userpayinfo['plan_type'] == $plan[1]['plan_type_id']){
                 $dat['subscription_end']= date('Y-m-d', strtotime($dat['subscription_start'].'+'.$plan[1]['subscription_period'].'days'));
                   }else{
                $dat['subscription_end']= date('Y-m-d', strtotime($dat['subscription_start'].'+'.$plan[2]['subscription_period'].'days'));   
                 }
            }
            $dat['customer_status'] = 3;
            $data['finger_print'] =  $fingerprint;
            $data['status'] = $status;
            $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$data);
            $dat['paid_status'] = $status;
            $payresult = $paymentnewModal->updateUserPaymentInfo($userid,$dat);
                                       
                                //desc:referal code 
                                //dev: priyanka varanasi
                                ///date:13/10/2015
               $referralpaymenttableModal = Application_Model_ReferralPaymentTable::getinstance();
               $referralcommissiontableModal = Application_Model_ReferralCommissionTable::getinstance();
               $refcommission = $referralcommissiontableModal->getReferralCommissionDetails();
               $refcom1 = $refcommission[0];
               $refcom2 = $refcommission[1];
                   if($userpayinfo['teacherrefral'] && $userpayinfo['teacherrefral']!=0 ){
                  $repo  =  $referralpaymenttableModal->getReferalRowByRefferredId($userpayinfo['teacherrefral']);
                 if(!empty($repo)) { 
               // if a row is present for thi suser in current month then update data
                          $refdata['user_id'] = $userpayinfo['teacherrefral'];
                   if($usera['plan_type'] ==  $plan[1]['plan_type_id']){
                          $refdata['students_monthly'] = 1;
                   if($refcom1['commission_type']==1){
                          $refdata['amount_monthly'] = ($plan[1]['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                    }else{
                          $refdata['students_annually']= 1;
                       if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan[2]['amount'] * $refcom2['commission_value'])/100;
                     }else{
                          $refdata['amount_annually'] = $refcom2['commission_value'];
                       }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                     }
                    
                     $refdata['pay_status'] = 0;
                  
                 $referralpaymenttableModal->updateReferralPaymentInfo($repo['user_id'],$repo['ref_id'],$refdata);
                 $uinfo['teacherrefral'] = 0;
                 $paymentnewModal->updateUserPaymentInfo($userpayinfo['user_id'],$uinfo);
                   }else{
                 // if row is not present for user then insert data
                      $refdata['user_id'] = $userpayinfo['teacherrefral'];
                       if($usera['plan_type'] == $plan[1]['plan_type_id']){
                       $refdata['students_monthly'] = 1;
                       $refdata['students_annually'] = 0;
                       $refdata['amount_annually'] = 0;
                      if($refcom1['commission_type']==1){
                         $refdata['amount_monthly'] = ($plan[1]['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                     }else{
                          $refdata['students_annually'] = 1;
                          $refdata['students_monthly'] = 0;
                          $refdata['amount_monthly'] = 0;
                      if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan[2]['amount'] * $refcom2['commission_value'])/100;
                    }else{
                          $refdata['amount_annually'] = $refcom2['commission_value'];
                         }  
                     }
                     $refdata['total_earned'] = $refdata['amount_monthly']+ $refdata['amount_annually'];
                     $refdata['pay_status'] = 0;
                     $refdata['payment_date'] = date('Y-m-d');
                    
                     $referralpaymenttableModal->insertReferralPaymentInfo($refdata);
                     $uinfo['teacherrefral'] = 0;
                     $paymentnewModal->updateUserPaymentInfo($userpayinfo['user_id'],$uinfo); 
                     } 
                       
                    }
               
              ///////////////////////////code ends //////////////////////
            
            
            $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Your subscription is started , now you can enjoy unlimited services in fashion learn upto your end of the subscription.i.e, on".date("F j, Y", strtotime($dat['subscription_end'])),
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject,$mergers, "financeiro@fashionlearn.com.br");   
                
                
            }else if($status == "refunded"){
            $in['status'] = "refunded";
            $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$in);
            if($userpayinfo['transaction_no']==$trans_id){
            $da['paid_status'] = "refunded";
            $da['customer_status'] = 8;
            $result = $paymentnewModal->updateUserPaymentInfo($userid,$da);
             }
              $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Your money has been refunded to your account,please once check your details.You are now free user , subscribe now to access unlimited services",
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject,$mergers, "financeiro@fashionlearn.com.br");   
            }else if($status== "refused"){
          $tran['customer_status'] = 7;
        
                      
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "sorry your transaction has been failed due to some technical reasons, please check your bank account"
                            )
                        );

        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
        $tran['paid_status'] =  $status;
        $result = $paymentnewModal->updateUserPaymentInfo($userid,$tran);
        $at['finger_print'] = $fingerprint;
        $at['status'] = $status;
        $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$at); 
        
       }else{
           
            $data['finger_print'] =  $fingerprint;
            $data['status'] = $status;
            $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$data);   
            $dat['paid_status'] = $status;
            $result = $paymentnewModal->updateUserPaymentInfo($userid,$dat);
            }
        }else{
              
                echo "UnAuthorized Source" ;
                  
              }
     
         }
         
         die();
    }
   
     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// ALL REACTIVATION USERS BOlETO START /////////////////////////////////////////
    /////////////////////////////// POST BACK URL HITS FROM PAGAR/////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
     //dev: priyanka varanasi
    //dated: 07/9/2015
  //1. IT WILL UPDATE THE SUBSCRIPTION DATE IF PAID STATUS OR ELSE MAKE 
   ///2. THIS WILL BE RUN BY PAGAR ITSELF
   ////////THIS POST BACK URL FOR BOLETO IS FOR REACTIVATION PREMIUM USERS ///////////////////
    
    public function postbackboletoreactivationUrlAction() {
        
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $fashiontransactionmodal = Application_Model_FashionTransactions::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails(); 
        $data = array();
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
       $userid = $this->getRequest()->getParam('uid');
       
       $userpayinfo =  $paymentnewModal->getUserPaymentInfo($userid);
       
       if ($this->getRequest()->isPost()){
             $fingerprint = $this->getRequest()->getPost('fingerprint');
             $trans_id = $this->getRequest()->getPost('id');
             $currentstatus =  $data['status'] = $this->getRequest()->getPost('current_status');
             $matchresult  = $objCore->validateFingerprint($trans_id,$fingerprint);
       if($matchresult){
            $usertransinfo = $fashiontransactionmodal->getTransInfoById($userid,$trans_id);
             if($usertransinfo['status']== "canceled"){
               
            }else if($currentstatus === "paid"){
                  if($userpayinfo['subscription_end'] >= date('Y-m-d')){
            $dat['subscription_start'] = date('Y-m-d', strtotime($userpayinfo['subscription_end'].'+1days'));
           if($userpayinfo['plan_type'] == $plan[1]['plan_type_id']){
                 $dat['subscription_end']= date('Y-m-d', strtotime($dat['subscription_start'].'+'.$plan[1]['subscription_period'].'days'));
                   }else{
                $dat['subscription_end']= date('Y-m-d', strtotime($dat['subscription_start'].'+'.$plan[2]['subscription_period'].'days'));   
                 }
            }else{
              $dat['subscription_start'] = date('Y-m-d');    
            if($userpayinfo['plan_type'] == $plan[1]['plan_type_id']){
                 $dat['subscription_end']= date('Y-m-d', strtotime($dat['subscription_start'].'+'.$plan[1]['subscription_period'].'days'));
                   }else{
                $dat['subscription_end']= date('Y-m-d', strtotime($dat['subscription_start'].'+'.$plan[2]['subscription_period'].'days'));   
                 }
            }
         $dat['paid_status'] = $currentstatus;
         $dat['customer_status'] = 3;
         $result = $paymentnewModal->updateUserPaymentInfo($userpayinfo['user_id'],$dat);
         $data['status'] = $currentstatus;
         $data['finger_print'] = $fingerprint;
         $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$data);
         
                                 //desc:referal code 
                                //dev: priyanka varanasi
                                ///date:13/10/2015
               $referralpaymenttableModal = Application_Model_ReferralPaymentTable::getinstance();
               $referralcommissiontableModal = Application_Model_ReferralCommissionTable::getinstance();
               $refcommission = $referralcommissiontableModal->getReferralCommissionDetails();
               $refcom1 = $refcommission[0];
               $refcom2 = $refcommission[1];
                   if($userpayinfo['teacherrefral'] && $userpayinfo['teacherrefral']!=0 ){
                  $repo  =  $referralpaymenttableModal->getReferalRowByRefferredId($userpayinfo['teacherrefral']);
                     if(!empty($repo)) { 
         // if a row is present for thi suser in current month then update data
                          $refdata['user_id'] = $userpayinfo['teacherrefral'];
                   if($userpayinfo['plan_type'] ==  $plan[1]['plan_type_id']){
                          $refdata['students_monthly'] = 1;
                   if($refcom1['commission_type']==1){
                          $refdata['amount_monthly'] = ($plan[1]['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                    }else{
                          $refdata['students_annually']= 1;
                       if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan[2]['amount'] * $refcom2['commission_value'])/100;
                     }else{
                          $refdata['amount_annually'] = $refcom2['commission_value'];
                       }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                     }
                    
                     $refdata['pay_status'] = 0;
                  
                 $referralpaymenttableModal->updateReferralPaymentInfo($repo['user_id'],$repo['ref_id'],$refdata);
                 $uinfo['teacherrefral'] = 0;
                 $paymentnewModal->updateUserPaymentInfo($userpayinfo['user_id'],$uinfo);
                   }else{
                    // if row is not present for user then insert data
                      $refdata['user_id'] = $userpayinfo['teacherrefral'];
                       if($userpayinfo['plan_type'] == $plan[1]['plan_type_id']){
                       $refdata['students_monthly'] = 1;
                       $refdata['students_annually'] = 0;
                       $refdata['amount_annually'] = 0;
                      if($refcom1['commission_type']==1){
                         $refdata['amount_monthly'] = ($plan[1]['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                     }else{
                          $refdata['students_annually'] = 1;
                          $refdata['students_monthly'] = 0;
                          $refdata['amount_monthly'] = 0;
                      if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan[2]['amount'] * $refcom2['commission_value'])/100;
                    }else{
                          $refdata['amount_annually'] = $refcom2['commission_value'];
                         }  
                     }
                     $refdata['total_earned'] = $refdata['amount_monthly']+ $refdata['amount_annually'];
                     $refdata['pay_status'] = 0;
                     $refdata['payment_date'] = date('Y-m-d');
                    
                     $referralpaymenttableModal->insertReferralPaymentInfo($refdata);
                     $uinfo['teacherrefral'] = 0;
                     $paymentnewModal->updateUserPaymentInfo($userpayinfo['user_id'],$uinfo); 
                     } 
                       
                    }
               
              ///////////////////////////code ends //////////////////////
         
         
         
                        $template_name = 'Blank-transaction';
                        $name = "test";
                        $email =  $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Your subscription is started , now you can enjoy unlimited services in fashion learn upto your end of the subscription.i.e, on".date("F j, Y", strtotime($dat['subscription_end'])),
                            )
                        );

            $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject,$mergers, "financeiro@fashionlearn.com.br");
            }else if($currentstatus == "refunded"){
            $in['status'] = "refunded";
            $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$in);
            if($userpayinfo['transaction_no']== $trans_id){
            $da['paid_status'] = "refunded";
            $da['customer_status'] = 8;
            $result = $paymentnewModal->updateUserPaymentInfo($userid,$da);
             }
              $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Your money has been refunded to your account,please once check your details.You are now free user , subscribe now to access unlimited services",
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject,$mergers, "financeiro@fashionlearn.com.br");      
                
            }else if($currentstatus== "refused"){
                        $tran['customer_status'] = 7;
        
                      
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "sorry your transaction has been failed due to some technical reasons, please check your bank account"
                            )
                        );

        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
        $tran['paid_status'] =  $currentstatus;
        $result = $paymentnewModal->updateUserPaymentInfo($userid,$tran);
        $at['finger_print'] = $fingerprint;
        $at['status'] = $currentstatus;
        $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$at); 
        
       }else{
         $customer['customer_status'] = 7;
         $result = $paymentnewModal->updateUserPaymentInfo($userpayinfo['user_id'],$customer);
         $det['status'] = $currentstatus;
         $result  = $fashiontransactionmodal->updateUserTransInfo($trans_id,$data); 
            }
            }else{
            echo "UnAuthorized Source" ;
              }
     
         }
         
         die();
    }
    //dev: priyanka varanasi
    //dated: 07/9/2015
     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// ALL SUBSCRIPTION CRON FOR CREDITCARD  USERS  START /////////////////////////////////////////
    /////////////////////////////// POST BACK URL HITS FROM PAGAR/////////////////////////
   //////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function subscribeduserscardCronAction() {

        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $host = $realobj->hostLink;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
        $currentDate = date('Y-m-d');
        $alertperiod = date('Y-m-d', strtotime('-' . $warningdays . 'days'));
        $allsubscribedusersinfo = $paymentnewModal->getAllSubscribedUsers($currentDate,$alertperiod);
        
        
        echo '<-------------------------------------subscribed users cron started-------------------------------------------->';
        echo '<html><table border="1">';
        print '<th>userid</th><th>email</th><th>transactionid</th><th>status</th><th>comments</th>';
        if($allsubscribedusersinfo){
            echo "<tbody>";
         foreach ($allsubscribedusersinfo as $usera) {
             if($usera['autopayment']==1){
                   if($usera['discounted']==1){
                    $couponres =  $couponsModal->checkCouponCode($usera['couponcode']);
                    if($couponres){
                        if($usera['plan_type']= $plan[1]['plan_type_id']){
                   if($couponres['discount_type']==0){
                     $amount = ($plan[1]['amount']* $couponres['discount_offered'])/100;
                     $actualamount = $plan[1]['amount'] - $amount;
                     
                         }else{
                     $actualamount = ($plan[1]['amount'] - $couponres['discount_offered']);
                       }
                       }else{
                         if($couponres['discount_type']==0){
                     $amount = ($plan[1]['amount']* $couponres['discount_offered'])/100;
                     $actualamount = $plan[1]['amount'] - $amount;
                         }else{
                     $actualamount = ($plan[1]['amount'] - $couponres['discount_offered']);
                       }    
                        }
                  $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);     
                    }else{
                     $actualamount = $plan[1]['amount']; 
                     $payin['discounted'] = 0; 
                    }
                    
                }else{
                 $actualamount = $plan[1]['amount'];     
                }
            echo "<tr>";
              try {
                    
                    $data["api_key"] = $apikey;
                    $data["amount"] = $actualamount*100;
                    $data["card_id"] = $usera["pagar_id"];
                    $data["postback_url"] = $host.'/postback-url/'.$usera["user_id"];
                    $params["url"] = 'https://api.pagar.me/1/transactions';
                    $params["parameters"] = $data;
                    $params["method"] = "POST";
                    $rs = new RestClient($params);
                    $result = $rs->run();
                    echo "<pre>";
                    $result = (array) $result;
                    $body = (array) json_decode($result["body"]);
                    if ($result["code"] == 200) {
                     print "<td> " . $usera["user_id"] . "</td>" . "<td> " . $usera["email"] . "</td>" . "<td> " . $body["id"] . "</td>" . "<td> " . $body["status"] . "</td>" . "<td> " . $body["status_reason"] . "</td>";
                        $ndata = array();
                        $ndata["user_id"] = $usera["user_id"];
                        $ndata["transaction_id"] = $body["id"];
                        $ndata["ip"] = $body["ip"];
                        $ndata["amount"] = $body["amount"]/100;
                        $ndata["status"] = $body["status"];
                        $ndata["status_reason"] = $body["status_reason"];
                        $ndata["transaction_date"] = date('Y-m-d');
                        $ndata["cardfirstdigits"] = $body['card_first_digits'];
                        $ndata["cardlastdigits"] =  $body['card_last_digits'];
                        $ndata["brand"] = $body['card_brand'];
                        $ndata["plantype"] = $usera["plan_type"];
                        $ndata["pay_type"] = 1;
                        $res=$transmodel->insertUserTransactionsInfo($ndata);
                        
                        //to update in payment new table 
                        
                        $currentdate = date('Y-m-d');
                        $payin =array();
                        if($usera['plan_type'] == $plan[1]['plan_type_id']){
                        $payin['subscription_end']= date('Y-m-d', strtotime( $currentdate.'+'.$plan[1]['subscription_period'].'days'));
                        }else{
                        $payin['subscription_end']= date('Y-m-d', strtotime( $currentdate.'+'.$plan[2]['subscription_period'].'days'));   
                        }
                        $payin['subscription_start']= date('Y-m-d');
                        $payin['customer_status']= 3;
                        $payin['transaction_no']= $body["id"];
                        $payin['discounted_val'] = $actualamount;
                        $payin['paid_status']= $body["status"];
                       
                       $updatedresult  = $paymentnewModal->updateUserPaymentInfo($usera["user_id"],$payin);
                       ///////////code ends //////////////////////
                        $mailer = Engine_Mailer_Mailer::getInstance();
                        $template_name = 'Blank-transaction';
                 
                        $name = "test";
                        $email = $usera['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Thank you to continue services of fashionlearn, $'.$actualamount.' is deducted for continuing services and you can enjoy services up to" .date("F j, Y", strtotime($payin['subscription_end'])),
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                    } else {
                        print "<td> " . $usera["user_id"] . "</td>" . "<td> " . $usera["email"] . "</td>" . "<td>Null</td>" . "<td> Payment failed</td>" . "<td>".json_encode($body['errors'])."</td>";
                       
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $usera['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "payment failure"
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                    }
                } catch (Exception $e) {
                    
                }
           echo "</tr>";
               
         }else{
            print '<td>this user  canceled his subscription</td><td>-</td><td>-</td><td>-</td><td>-</td>'; 
             $paymentdet['customer_status'] = 8 ;
             $paymentdet['paid_status'] = 'unpaid' ;
             $returnresponse  = $paymentnewModal->updateUserPaymentInfo($usera['user_id'],$paymentdet);   
            $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $usera['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "You have canceled your subscription now you are a free user"
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br"); 
        }
        }
        
          echo "</tbody>";
        }else{
          echo "</tbody>";
          print '<td>NO CURRENT SUBCRIPTION END  USERS FOR THIS DAY</td><td>-</td><td>-</td><td>-</td><td>-</td>';  
          echo "</tbody>";
        }
        
        echo '</table></html>';
        echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die();
    }
    //dev: priyanka varanasi
    //dated: 10/9/2015
     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// ALL SUBSCRIPTION CRON FOR BOLETO  USERS  START /////////////////////////////////////////
    /////////////////////////////// POST BACK URL HITS FROM PAGAR/////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function subscribedusersboletoCronAction(){
        
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $host = $realobj->hostLink;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $paymentboletomodal =  Application_Model_PaymentBoleto::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
        $currentDate = date('Y-m-d');
        $warningdays = $realobj->warningdays;
        $alertperiod = date('Y-m-d', strtotime('+7days'));
      
        $subscribedboletousers = $paymentnewModal->getAllSubscribedBoletoUsers($alertperiod);
       
        
       
     echo '<-------------------------------------subscribed users cron started-------------------------------------------->';
        echo '<html><table border="1">';
        print '<th>userid</th><th>email</th><th>transactionid</th><th>status</th><th>comments</th>';
    
         if($subscribedboletousers){
           echo '<tbody>';
        foreach ($subscribedboletousers as $value) {
              $address = $paymentboletomodal->getRecentUserAddressByUserID($value['user_id']);
         echo '<tr>';
               if($value['autopayment']==1){
         if($value['discounted']==1){
                    $couponres =  $couponsModal->checkCouponCode($usera['couponcode']);
                    if($couponres){
                        if($value['plan_type']= $plan[1]['plan_type_id']){
                   if($couponres['discount_type']==0){
                     $amount = ($plan[1]['amount']* $couponres['discount_offered'])/100;
                     $actualamount = $plan[1]['amount'] - $amount;
                     
                         }else{
                     $actualamount = ($plan[1]['amount'] - $couponres['discount_offered']);
                       }
                       }else{
                         if($couponres['discount_type']==0){
                     $amount = ($plan[1]['amount']* $couponres['discount_offered'])/100;
                     $actualamount = $plan[1]['amount'] - $amount;
                     
                         }else{
                     $actualamount = ($plan[1]['amount'] - $couponres['discount_offered']);
                       }    
                        }
                  $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);     
                    }else{
                     $actualamount = $plan[1]['amount']; 
                     $payin['discounted'] =0; 
                    }
                    
                }else{
                 $actualamount = $plan[1]['amount'];     
                }
             
             try {
                    
                    $data["api_key"] = $apikey;
                    $data["amount"] = $actualamount*100;
                    $data["payment_method"] = "boleto";
                    $data['postback_url']= $host.'/postbackboletoreactivation-url/'.$value['user_id'];
                    $data["boleto_expiration_date"] = date('m-d-Y', strtotime('+7days'));
                    $data['customer']=array(
            "name" => $address['name'],
            "document_number" =>$address['cpf'],
            "email" => $value['email'],
            "address" => array(
                "street" => $address['street_name'],
                "neighborhood" => $address['neighborhood'],
                "zipcode" => $address['zip_code'],
                "street_number" => $address['street_number'],
                
            ),
            "phone" => array(
                "ddd" => $address['phone_ddd'],
                "number" => $address['phone_no'] 
            )
        );
                    $params["url"] = 'https://api.pagar.me/1/transactions';
                    $params["parameters"] = $data;
                    $params["method"] = "POST";
                    $rs = new RestClient($params);
                    $result = $rs->run();
                    $result = (array) $result;
                    $body = (array) json_decode($result["body"]);
                    if ($result["code"] == 200) {
                   print "<td> " . $value["user_id"] . "</td>" . "<td> " . $value["email"] . "</td>" . "<td> " . $body["id"] . "</td>" . "<td> " . $body["status"] . "</td>" . "<td> " . $body["status_reason"] . "</td>";            
                        $ndata = array();
                        $ndata["user_id"] = $value['user_id'];
                        $ndata["transaction_id"] = $body["id"];
                        $ndata["ip"] = $body["ip"];
                        $ndata["amount"] = $body["amount"]/100;
                        $ndata["status"] = $body["status"];
                        $ndata["status_reason"] = $body["status_reason"];
                        $ndata["transaction_date"] = date('Y-m-d');
                        $ndata["plantype"] = $value['plan_type'];
                        $ndata["boleto_url"] = $body['boleto_url'];
                        $ndata["boleto_barcode"] = $body['boleto_barcode'];
                        $ndata["boleto_expdate"] = $body['boleto_expiration_date'];
                        $ndata["pay_type"] = 2;
                        $ndata["address_id"] = $address['b_id'];
                     
                     $res=$transmodel->insertUserTransactionsInfo($ndata);///////here it will insert the transaction details
                     $paymentnewdetails['customer_status'] = 4;
                     $paymentnewdetails['transaction_no'] = $body["id"] ; 
                     $paymentnewdetails['paid_status'] = $body["status"] ;
                     $paymentnewdetails['discounted_val'] = $actualamount;
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($value['user_id'],$paymentnewdetails);
                         $mailer = Engine_Mailer_Mailer::getInstance();
                        $template_name = 'Blank-transaction';
                 
                        $name = "test";
                        $email =   $value['email'];
                        $subject = "payment";

                        $mergers = array(
                            
                            
                              array(
                                'name' => 'yourboletourl',
                                'content' => $body["boleto_url"]
                                
                            ),
                              array(
                                'name' => 'yourboletobarcode',
                                'content' => $body["boleto_barcode"]
                                
                            ),
                            
                              array(
                                'name' => 'Yourexpirydate',
                                'content' => $body["boleto_expiration_date"]
                                
                            ),
                                array(
                                'name' => 'message',
                                'content' =>"your subscription is going to end ,Please make payment on or before  your boleto expiration date  "
                                
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                    } else {
                      print "<td> " . $value["user_id"] . "</td>" . "<td> " . $value["email"] . "</td>" . "<td>Null</td>" . "<td> Payment failed</td>" . "<td>".json_encode($body['errors'])."</td>";  
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $value['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Error occurs while payment to continue your services , please check the boleto account details "
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                    }
                } catch (Exception $e) {
                    
                } 
                }
                else{
                    $paymentdet['customer_status'] = 8;
                    $paymentdet['paid_status'] = 'unpaid' ;
                    $returnresponse  = $paymentnewModal->updateUserPaymentInfo($value['user_id'],$paymentdet);   
                     print "<td> " . $value["user_id"] . "</td>" . "<td> " . $value["email"] . "</td><td>free users</td><td> canceled subscription</td><td>subscription cron cannot run for this users</td>";            
                    $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $value['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => " As you have canceled your subscription you became a free user now"
                            )
                        );

                       $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                     
                     
                    }
        }
    } else{
        echo "</tbody>";
          print '<td>NO CURRENT SUBCRIPTION END  USERS FOR THIS DAY</td><td>-</td><td>-</td><td>-</td><td>-</td>';  
          echo "</tbody>";
        
    }
      echo '</table></html>';
        echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die();
    }
    
    
    //dev: priyanka varanasi
    //dated: 12/9/2015
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// Subscription Cron For Freemium user/////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    public function freemiumendCronAction(){
        
      
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $host = $realobj->hostLink;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
        $currentDate = date('Y-m-d');
        $warningdays = $realobj->warningdays;
       $freemiumendusers = $paymentnewModal->getFreemiumEndUsers();
           
     echo '<-------------------------------------Freemium subscription cron started ------------------------------------------->';
        echo '<html><table border="1">';
        print '<th>userid</th><th>email</th>status<th>comments</th>';
       if($freemiumendusers){
            echo "<tbody>"; 
          foreach ($freemiumendusers as $value) {
               echo "<tr>"; 
                        $payin['customer_status']= 8;
                        $updatedresult  = $paymentnewModal->updateUserPaymentInfo($value["user_id"],$payin);
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $value['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Your freemium usage is ended ,Now you are a free user, to enjoy unlimited services from  us please subscribe now and become premium users",
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject,$mergers, "financeiro@fashionlearn.com.br");
          print "<td> " . $value["user_id"] . "</td>" . "<td> " . $value["email"] . "</td>" . "<td>freemium user</td>" . "<td>changing to free user</td>";    
               
              echo "</tr>";    
           }
         echo "</tbody>";    
       }else{
          echo "</tbody>";
          print '<td>NO CURRENT FREEMIUM END  USERS FOR THIS DAY</td><td>-</td><td>-</td>';  
          echo "</tbody>";
       }
       echo "</table></html>";
       echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die(); 
    }
     //dev: priyanka varanasi
    //dated: 14/9/2015
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////Trial Warn users exceeded trial end date /////////////////////////////////////////
    ////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function warningCreditcardAction(){
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $host = $realobj->hostLink;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
        $currentDate = date('Y-m-d');
        $warningdays = $realobj->warningdays;
        $alertperiod = date('Y-m-d', strtotime('-' . $warningdays . 'days'));
        $trailwarnusers = $paymentnewModal->getAllCreditCardTrialWarnUsers($alertperiod); 
       echo '<-------------------------------------creditcard trial warning cron started ------------------------------------------->';
        echo '<html><table border="1">';
        print '<th>userid</th><th>email</th>status<th>comments</th>';
      if($trailwarnusers){
          echo "<tbody>";  
          foreach ($trailwarnusers as $value) {
               echo "<tr>"; 
                    $payin['customer_status']= 7;
                    $updatedresult  = $paymentnewModal->updateUserPaymentInfo($value["user_id"],$payin);
          print "<td> " . $value["user_id"] . "</td>" . "<td> " . $value["email"] . "</td>" . "<td>Trial user</td>" . "<td> exceeded trail end date for payment</td>";    
                             
               $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $value['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "You have exceeded the trail period , but your payment is not processed till now , once check your card details and proceed to continue services"
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br"); 

              echo "</tr>";  
          }
          echo "</tbody>";   
          
      }else{
          echo "</tbody>";
          print '<td>NO CURRENT TRIAL END EXCEEDED USERS FOR THIS DAY</td><td>-</td><td>-</td>';  
          echo "</tbody>";
       }
       echo "</table></html>";
       echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die(); 
          
        
        
        
        
    }
    //dev: priyanka varanasi
    //dated: 14/9/2015
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////subscription Warn users exceeded subsciption end date /////////////////////////////////////////
   //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function subscribedusersWarningcronAction(){
        
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $host = $realobj->hostLink;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
        $currentDate = date('Y-m-d');
        $warningdays = $realobj->warningdays;
        $alertperiod = date('Y-m-d', strtotime('-' . $warningdays . 'days'));
       $subscribedwarnusers = $paymentnewModal->getAllCreditCardSubscribedWarnUsers($alertperiod); 
       
       echo '<-------------------------------------creditcard trial warning cron started ------------------------------------------->';
        echo '<html><table border="1">';
        print '<th>userid</th><th>email</th>status<th>comments</th>';
      if($subscribedwarnusers){
          echo "<tbody>";  
          foreach ($subscribedwarnusers as $value) {
               echo "<tr>"; 
                    $payin['customer_status']= 7;
                    $updatedresult  = $paymentnewModal->updateUserPaymentInfo($value["user_id"],$payin);
          print "<td> " . $value["user_id"] . "</td>" . "<td> " . $value["email"] . "</td>" . "<td>Subscribed user</td>" . "<td> exceeded subscription end date for payment</td>";    
                             
               $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $value['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "You have exceeded the subscription period , but your payment is not processed till now, once check your card details and proceed to continue services"
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br"); 

              echo "</tr>";  
          }
          echo "</tbody>";   
          
      }else{
          echo "</tbody>";
          print '<td>NO CURRENT SUBSCRIBED END EXCEEDED USERS FOR THIS DAY</td><td>-</td><td>-</td>';  
          echo "</tbody>";
       }
       echo "</table></html>";
       echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die(); 
          
        
        
        
        
    }
    
         //dev: priyanka varanasi
    //dated: 18/9/2015
    // warning end cron to check at last whether user is paid or not after trial or subscription end period , if not make it as free user
    public function pagarendCronAction(){
      
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $host = $realobj->hostLink;
        $paymentnewModal = Application_Model_PaymentNew::getinstance();
        $plandetailsmodal = Application_Model_Plans::getinstance();
        $paymentcardsmodal = Application_Model_PaymentCards::getinstance();
        $couponsModal = Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan[1] = $allplaninfo[0];
        $plan[2] = $allplaninfo[1];
        $warningdays = $realobj->warningdays;
        $currentDate = date('Y-m-d');
        $alertdate = date('Y-m-d', strtotime($currentDate.'-7days'));
        $warntrialusers = $paymentnewModal->getAllWarnTrialUsers($alertdate);
        $warnsubscribedusers = $paymentnewModal->getAllWarnSubscribeUsers($alertdate);
          
     echo '<-------------------------------------Trial and Subscription end exceeded cron started ------------------------------------------->';
        echo '<html><table border="1">';
        print '<th>userid</th><th>email</th><th>status</th><th>comments</th>';
        if($warntrialusers){
              echo "<tbody>"; 
            foreach ($warntrialusers as $v) {
                 echo "<tr>"; 
                   $data['customer_status'] = 8;
                   if(!empty($v["user_id"])){
                    $updatedresult  = $paymentnewModal->updateUserPaymentInfo($v["user_id"],$data);
                     print "<td> " . $v['user_id'] . "</td>" . "<td> " . $v['email'] . "</td>" . "<td>trailuser</td>" . "<td>excceeded trial period , payment not yet done,changing to free user</td>";    
                     }
                echo "</tr>";    
                }
            echo "</tbody>";  
        }
        else{
          echo "<tbody>";
          print '<td>NO CURRENT TRIAL END EXCEEDED USERS  EXITS FOR THIS DAY</td><td>-</td><td>-</td><td>-</td>';  
          echo "</tbody>";
         
       }
        if($warnsubscribedusers){
                echo "<tbody>"; 
            foreach ($warnsubscribedusers as $val) {
                  echo "<tr>"; 
                   $data['customer_status'] = 8;
                    if(!empty($val["user_id"])){
                    $updatedresult  = $paymentnewModal->updateUserPaymentInfo($val["user_id"],$data);
                print "<td> " . $val["user_id"] . "</td>" . "<td> " . $val["email"] . "</td>" . "<td>subscribed user </td>" . "<td>exceeded subscription end  period , payment not yet done,changing to free user</td>";    
               }
              echo "</tr>";   
            }
             echo "</tbody>";  
          }
        else{
          echo "<tbody>";
          print '<td>NO CURRENT SUBSCRIPTION  END EXCEEDED USERS  EXITS FOR THIS DAY</td><td>-</td><td>-</td><td>-</td>';  
          echo "</tbody>";
        } 
     echo "</table></html>";
       echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die();  
    
}


    }
