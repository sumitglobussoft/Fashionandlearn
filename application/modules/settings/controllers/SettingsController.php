<?php

require_once 'Zend/Controller/Action.php';
require_once 'Engine/Twitter/OAuth.php';
require_once 'Engine/Twitter/twitteroauth.php';
require_once 'Engine/Twitter/TwitterAPIExchange.php';
require_once 'Engine/Twitter/Twitter.php';
require_once 'Engine/Pagarme/Pagarme/RestClient.php';

class Settings_SettingsController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function preDispatch() {
       // Display the recent updated profile picture  
       
      //   $user_id;
      //   echo "<pre>";
      // print_r($this->view->session->storage);
      // die();

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

    function bitly_url_shorten($long_url, $access_token, $domain) {
        $url = 'https://api-ssl.bitly.com/v3/shorten?access_token=' . $access_token . '&longUrl=' . urlencode($long_url) . '&domain=' . $domain;
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $output = json_decode(curl_exec($ch));
            curl_close($ch);
        } catch (Exception $e) {
            
        }
        if (isset($output)) {
            return $output->data->url;
        }
    }

    function compress_image($source_url, $destination_url, $quality) {

        @$info = getimagesize($source_url);

        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source_url);

        elseif ($info['mime'] == 'image/gif')
            $image = imagecreatefromgif($source_url);

        elseif ($info['mime'] == 'image/png')
            $image = imagecreatefrompng($source_url);

        //@imagejpeg($image, $destination_url, $quality);
        return $destination_url;
    }

    public function userSettingsAction() {
        $refer=$this->getRequest()->getParam('refer');
        $notify=$this->getRequest()->getParam('notify');
        if(isset($refer)){
            $this->view->reference = $refer;
        }
        if(isset($notify)){
            $this->view->notify = $notify;
        }
        if(isset($_GET["gamnot"]))
           $this->view->gamnot=$_GET["gamnot"];
            
        //------------------- for email notifications page --------------------------------
        $user_id = '';
        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
            $pagarbank=Application_Model_Pagarbank::getinstance();
            $bankdetails=$pagarbank->getbankdetails($user_id);
            $this->view->bankdetails=$bankdetails;
            $bank=Application_Model_Bankcodes::getinstance();
            $this->view->allbanks=$bank->getallbank();
             $objPagarbankcreq = Application_Model_Pagarbankcreq::getinstance();
             $this->view->anyrequest=$objPagarbankcreq->checkbank($user_id);
           
            
        }
        $objNotificationModel = Application_Model_Notification::getinstance();
        $response = $objNotificationModel->getUserNotificationData($user_id);
        $this->view->notifications = $response;
       
        //------------------- for payment page ---------------------------------------------
        $objPaymentMethods = Application_Model_PaymentMethods::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objUsermetaModel = Application_Model_UsersMeta::getinstance();
        //$objTimezoneModel = Application_Model_Timezone::getinstance();

        $getdetail = $objPaymentMethods->selectCardDetail($user_id);
        $usermetsDetail = $objUsermetaModel->getUserMetaDetail($user_id);
        $this->view->payingemail = $usermetsDetail['paypal_email'];
//        echo '<pre>'; print_r($getdetail); die;
        $this->view->result = $getdetail;
        $updatedStatus = $objUserModel->selectPremiumStatus($user_id);
        $this->view->updatedStatus = $updatedStatus['premium_status'];
        $getprimaryPay = $objPaymentMethods->selectPrimaryPayment($user_id);
        //$this->view->session->storage->primary_card = $getprimaryPay;
        //$objBillDetails = $objPaymentModel->selectBill($user_id);
       // $this->view->billdetails = $objBillDetails;
        //------------------------------profile settings-------------------------------------
        // getting data from Users table and sending it to view
        $res = $objUserModel->getUserDetail($user_id);
        $getmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);
//        echo "<pre>"; print_r($res);die;
        $this->view->UserDataresult = $res;
        $this->view->metaresult = $getmetaresult;
        $oauthVerifier = $this->getRequest()->getParam('oauth_verifier');

        //$objUserModel = Application_Model_Users::getinstance();
        $objUsermetaModel = Application_Model_UsersMeta::getinstance();
        //----------checks if user logged in with Fb------------
        $getFbid = $objUserModel->selectPremiumStatus($user_id);

        if (isset($getFbid)) {
            $this->view->fbexist = 'True';
        }

        //------------------------------------------------------
        if (isset($oauthVerifier)) {

            $objCore = Engine_Core_Core::getInstance();
            $this->_appSetting = $objCore->getAppSetting();
            $consumer_key = $this->_appSetting->twitter->consumerKey;
            $consumer_secret = $this->_appSetting->twitter->consumerSecret;
            if (isset($this->view->session->storage->reqToken)) {
                $reqToken = $this->view->session->storage->reqToken;
            }
            if (isset($this->view->session->storage->reqTokenSecret)) {
                $reqTokenSecret = $this->view->session->storage->reqTokenSecret;
            }
            $accesstoken1 = new TwitterOAuth($consumer_key, $consumer_secret, $reqToken, $reqTokenSecret);

            $access_token = $accesstoken1->getAccessToken($oauthVerifier);
            @ $accesstoken = new TwitterOAuth($consumer_key, $consumer_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
//             echo "<pre>"; print_r($access_token);die;
//            $twitterUserId = $access_token['user_id'];
            if (isset($access_token)) {
                if (isset($access_token['screen_name'])) {
                   $data["screen_name"]= $access_token['screen_name'];
                    $data["tw_id"]= $access_token['user_id'];
                   
                   
                    
              
                    $result = $objUserModel->insertSocialDetails($data, $user_id);

                    $my_detail = $accesstoken->get('statuses/user_timeline');
                    $detail = (array) $my_detail[0];
                    $twitterUserDetail = (array) $detail['user'];

                    $follower_count = $twitterUserDetail['followers_count'];

                    $dataFolloer_count = array('followers_count' => $follower_count);
                    $result1 = $objUserModel->insertSocialDetails($dataFolloer_count, $user_id);
                }
            }
        }


        //$this->view->session->meta_profile_pic = $getmetaresult['user_profile_pic'];
        //echo "<pre>"; print_r($getmetaresult['user_profile_pic']); echo "</pre>"; die('123');
        // $target_dir = "/profileimages/$user_id";
        //  if (!(is_dir($target_dir))) {
        $target_dir = "/profileimages/$user_id";
        $location = getcwd() . $target_dir;
        @mkdir($location, 0777, true);
        //    die('dir created');
        //   }
        //getting data after POST       

        if ($this->getRequest()->getPost('onsubmitaction') == 'profile') {
            $imageName = @$_FILES["fileToUpload"]["name"];
            $imageTmpLoc = @$_FILES["fileToUpload"]["tmp_name"];
            $firstname = $this->getRequest()->getParam('firstname');

            $lastname = $this->getRequest()->getParam('lastname');
            $headline = $this->getRequest()->getParam('headline');
            $web_url = $this->getRequest()->getParam('website');
            $bio = $this->getRequest()->getParam('MyToolbar1');
            $gender = $this->getRequest()->getParam('profileGender');

            $ext = pathinfo($imageName, PATHINFO_EXTENSION);
            $imageNamePath = $imageName;
//                    echo "<pre>"; print_r($_FILES);die;
            if ($imageName) {

                if ($ext != "jpg" && $ext != "png" && $ext != "jpeg" && $ext != "gif") {

                    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                    $ext = 0;
                } else {


//                    $info = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
//echo "<pre>"; print_r($info);
                    $_FILES["fileToUpload"]["tmp_name"] = $this->compress_image($_FILES["fileToUpload"]["name"], $_FILES["fileToUpload"]["tmp_name"], 80);
                    //$buffer = file_get_contents($url);
                    // Path and file name
                    $imagepathAndName = $location . "/" . $imageName;

                    $imagemoveResult = (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $imagepathAndName));
                    $imagepathAndName = $target_dir . "/" . $imageName;
                }
            } else {
                $imagepathAndName = $getmetaresult['user_profile_pic'];
            }
//            $this->view->session->storage->pik = $getmetaresult['user_profile_pic'];
            // $imagepathAndName = "profileimages/$user_id/" . $imageNamePath;
// upadating DB with the values after POST

            if ($firstname != $res['first_name'] || $lastname != $res['last_name'] || $gender != $res['gender'] || isset($imagepathAndName) || $headline != $res['user_headline'] || $web_url != $res['user_website'] || $bio != $res['about_user']) {         // updation -> Users
                $data123 = array('first_name' => $firstname,
                    'last_name' => $lastname,
                    'gender' => $gender
                );
                $data1 = array('user_website' => $web_url,
                    'user_headline' => $headline,
                    'about_user' => $bio,
                    'user_profile_pic' => $imagepathAndName
                );
                $editResultumeta = $objUsermetaModel->editUsermeta($data1, $user_id);
                $editResult = $objUserModel->editUser($data123, $user_id);
//                echo "<pre>"; print_r($editResult);die;
                if (!empty($editResult) || !empty($editResultumeta)) {
                    $this->view->sucess = "Updated Sucessfully";
                }
            }//if block
        }//POST 
        // getting data from Users table and sending it to view
        $resultData = $objUserModel->getUserDetail($user_id);
        $getmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);
//        echo "<pre>"; print_r($resultData);die;
        $this->view->UserDataresult = $resultData;
        $this->view->metaresult = $getmetaresult;
        $this->view->profilepic = $resultData['user_profile_pic'];
        $this->view->session->storage->first_name = $resultData['first_name'];
        //$this->view->session->meta_profile_pic = $postgetmetaresult['user_profile_pic'];
        $fbstatus = $objUserModel->getFbConnectedStatus($user_id);
      
        $this->view->fbstatus = $fbstatus;
        

        //----------------end profile settings----------------------------------------------
        //----------------Account settings--------------------------------------------------
       // $timeZoneList = $objTimezoneModel->getTimeZone();
       // $this->view->timeZoneList = $timeZoneList;
        $objtimezone = Application_Model_Timezone::getinstance();
        $timezone = $objtimezone->getTimeZone();
        //echo "<pre>";print_r($timezone);die();
        $this->view->timezone = $timezone;
        
        $objTeachingclassModel = Application_Model_TeachingClasses::getinstance();
        $getClassUser = $objTeachingclassModel->getClassByUserid($user_id);
        $this->view->userhasclasses = $getClassUser;

        if ($this->getRequest()->getPost('onsubmitaction') == 'account') {

            // Getting POST result for html for manipulation in DB

            $email = $this->getRequest()->getPost('EmailAddress');
            $City = $this->getRequest()->getPost('City');
            $Zip = $this->getRequest()->getPost('Zip');
            $Zone = $this->getRequest()->getPost('TimeZone');
            $Street = $this->getRequest()->getPost('Street');
            $Neighboor = $this->getRequest()->getPost('Neighboor');
            $State = $this->getRequest()->getPost('State');


            if ($City != @$res['city'] || $Zip != @$res['zip'] || $Zone != @$res['timezone'] || $Street != @$res['street'] || $Neighboor != @$res['Neighboor'] || $State != @$res['State']) {
                $data = array('city' => $City,
                    'zip' => $Zip,
                    'timezone' => $Zone,
                    'street' => $Street,
                    'Neighboor' => $Neighboor,
                    'State' => $State
                );
                $editmetaResult = $objUsermetaModel->updateUsermeta($data, $user_id);
            }
            $emailData = array('email' => $email);
            if ($emailData != $res['email']) {
                $editResult = $objUserModel->editUserEmail($emailData, $user_id);
            }
            if (!empty($editResult) || !empty($editmetaResult)) {
                $this->view->sucess = " Account Updated!! ";
            }
            $postresult = $objUserModel->getUserDetail($user_id);
            $this->view->UserDataresult = $postresult;
            $postmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);
            $this->view->metaresult = $postmetaresult;
        }
        //--------------------------------------End Account settings------------------------------------------------------
        //--------------------------------------Referral settings---------------------------------------------------------
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
$host = $realobj->hostLink;
        $this->_appSetting = $objCore->getAppSetting();
        $objUserModel = Application_Model_Users::getinstance();
        $objreferModel = Application_Model_ReferFriends::getinstance();
        $userid = 0;
        $useremail;
        $firstname;
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $useremail = $this->view->session->storage->email;
            // $username = $this->view->session->storage->user_name;
            $firstname = $this->view->session->storage->first_name;
        }
        $mailer = Engine_Mailer_Mailer::getInstance();

        $referral_url = $this->bitly_url_shorten($host.'referrals', '0ac6fd974647efb386cb6f3b509a4ae4f3100df4', 'fsln.me');
        $this->view->referralurl = $referral_url;

        if ($this->getRequest()->getPost('onsubmitaction') == 'referral') {

            //dev:priyanka varanasi
            //desc: multiple mail sending with  encoded refferal link in mail
            $email = $this->getRequest()->getPost('email');
            $array = explode(',', $email);
            foreach ($array as $key => $emailid) {
                $mydata = array('user_id' => $userid,
                    'email' => $emailid);


                $activationKey = base64_encode($userid . "&" . $emailid);
                $link = $host.'refer-fashionlearn/' . $activationKey;

                if ($emailid != "") {
// ! implement for same email ID
                    if (!filter_var($emailid, FILTER_VALIDATE_EMAIL)) {
                        $this->view->email = "E-mail is not valid";
                    } else {
                        //echo $email;  die();

                        $date = date("Y-m-d");
                        $template_name = 'refertemplate';
                        $subject = 'referred';
                        $mergers = array(
                            array(
                                'name' => 'name',
                                'content' => $firstname
                            ),
                            array(
                                'name' => 'subscribe',
                                'content' => $link
                            )
                        );
                        $result = $mailer->refertemplate($template_name, $emailid, $useremail, $subject, $mergers);

                        if ($result) {
                            $data = array('user_id' => $userid,
                                'email' => $emailid,
                                'ref_by_email' => $useremail,
                                'ref_date' => $date
                            );
                            $insertionResult = $objreferModel->insertrefer($data);
                        }
                    }
                } else {
                    $this->view->email = "Please Enter a valid email id";
                }
            }
        }
        $getBonus = $objUserModel->bonusMonth();

        $referral_counts;
        if (isset($getBonus)) {
            foreach ($getBonus as $bonus) {
                if ($bonus['user_id'] == $userid) {
                    $referral_counts = $bonus['referral_counts'];
                }
            }
        }
        if (isset($referral_counts)) {
            $this->view->referral_count = $referral_counts;
        }
        $result = $objreferModel->selectrefer($userid);     //counting no of rows 
        //print_r($result); die('123');
        $this->view->count = $result['num'];
        if(isset($firstname))
        $this->view->firstname = $firstname;

      //dev: priyanka varanasi 
      //dated: 1/9/2015
      //desc: to show the list of cards available for logged users  and also the status list regarding payment
       $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
       $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
        $fashiontransactionmodal = Application_Model_FashionTransactions::getinstance();
       if(isset($this->view->session->storage->user_id)){
       $userid = $this->view->session->storage->user_id;
       $cardslist  = $paymentcardsmodal->getAllCardsDetailsOfUsers($userid);
       $statuslist  = $paymentnewModal->getUserPaymentInfo($userid);
       $billinginfo  = $fashiontransactionmodal->getUserPayTransactionDetails($userid);
//       echo"<pre>";print_r();die('test');
        if($billinginfo){
          $this->view->billinginfo = $billinginfo;      
        }
       if($statuslist){
           
        $this->view->statuslist = $statuslist;   
       }
        if($cardslist){
            $this->view->usercardslist = $cardslist;
        }
        
       } 
    ////////////////////////////////code ends here //////////////////////////   
    }

    
    
    /*
      Dev. Namrata Singh
      Description: for Account settings module
     */

    public function accountAction() {

        //  print_r($this->view->session->storage); 
        $objUsermetaModel = Application_Model_UsersMeta::getinstance();
       // $objTimezoneModel = Application_Model_Timezone::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $user_id;
        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
        }

        $objTeachingclassModel = Application_Model_TeachingClasses::getinstance();
        $getClassUser = $objTeachingclassModel->getClassByUserid($user_id);
        $this->view->userhasclasses = $getClassUser;

        //$timeZoneList = $objTimezoneModel->getTimeZone();
        //echo"<pre>";print_r($timeZoneList);echo"<pre>";die;
        //
        //getting result from DB to show in html
        $getresult = $objUserModel->getUserDetail($user_id);
        //echo"<pre>";print_r($getresult);echo"<pre>";die('123');
        $this->view->result = $getresult;
        $getmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);
        $this->view->resultmeta = $getmetaresult;

        $this->view->timeZoneList = $timeZoneList;
        // Getting POST result for html for manipulation in DB 
        if ($this->getRequest()->isPost()) {

            $email = $this->getRequest()->getPost('EmailAddress');
            $City = $this->getRequest()->getPost('City');
            $Zip = $this->getRequest()->getPost('Zip');
            $Zone = $this->getRequest()->getPost('TimeZone');


            if (isset($City) || isset($Zip)) {
                $data = array('city' => $City,
                    'zip' => $Zip,
                    'timezone' => $Zone
                );
                $emailData = array('email' => $email);
                $editmetaResult = $objUsermetaModel->updateUsermeta($data, $user_id);
                $editResult = $objUserModel->editUserEmail($emailData, $user_id);
                if ($editResult || $editmetaResult) {
                    $this->view->sucess = " Account Updated!! ";
                }
            }
        }
        $postresult = $objUserModel->getUserDetail($user_id);
        $this->view->result = $postresult;
        $postmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);
        $this->view->resultmeta = $postmetaresult;
    }

    /*
      Dev. Namrata Singh
      Description: for Password settings module
     */

    public function passwordAction() {

//        $objUserModel = Application_Model_Users::getinstance();
//        if ($this->getRequest()->isPost()) {
//
//            $Passwordcurrent = $this->getRequest()->getPost('CurrentPassword');
//            $Passwordnew = $this->getRequest()->getPost('NewPassword');
//            $Passwordconfirm = $this->getRequest()->getPost('ConfirmPassword');
//            $user_id;
//            if (isset($this->view->session->storage->user_id)) {
//                $user_id = $this->view->session->storage->user_id;
//            }
//            $pass = $this->view->session->storage->password;
//            $data = '';
//            if (isset($Passwordnew) && isset($Passwordconfirm)) {
//                $data = array('password' => $Passwordnew);
//            }
//
//            if ($pass == $Passwordcurrent) {
//                if ($Passwordnew == $Passwordconfirm) {
//                    $Result = $objUserModel->changePassword($data, $user_id);
//                    if ($Result) {
//                        $this->view->message = $Result;
//                    }
//                } else {
//                    echo "Re-enter password,password do not match";
//                }
//            } else {
//                echo "Current Password donot match";
//            }
//        }
    }

    /*
      Dev. Namrata Singh
      Description: Ajax handler to get the data's from the form
      for validation and authentication of password.
     */

    public function passwordAjaxHandlerAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $objUserModel = Application_Model_Users::getinstance();
            if (isset($this->view->session->storage->user_id)) {
                $userid = $this->view->session->storage->user_id;
            }

//            $currentpassword = $this->getRequest()->getParam('currentpassword');

            $newpassword = $this->getRequest()->getParam('newpassword');

            $confirmpassword = $this->getRequest()->getParam('confirmpassword');

//            $response = $objUserModel->validatePassword($userid);
//            $responsepass = $response[0];
//
//            if ($currentpassword === $responsepass) {
                if ($newpassword === $confirmpassword) {
                    $data = array('password' =>  sha1(md5($confirmpassword)));
                    $Result = $objUserModel->changePassword($data, $userid);
                    if ($Result) {
                        $arr = "Password Successfully changed !";
                        echo json_encode($arr);
                    }else {
                    $arr = "* NewPassword and confirmPassword not Matched";
                    echo json_encode($arr);
                }
                } else {
                    $arr = "* NewPassword and confirmPassword not Matched";
                    echo json_encode($arr);
                }
//            } else {
//                $arr = "* Current password not matched";
//                echo json_encode($arr);
//            }
        }
    }

    public function deleteAjaxHandlerAction() {
         $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
//            $userid = $this->view->session->storage->user_id;
            $userid = $this->getRequest()->getPost('userid');

//            print_r($userid); die;
            $objUserModel = Application_Model_Users::getinstance();
            $result = $objUserModel->deleteUser($userid);
           
            if ($this->view->auth->hasIdentity()) {
                $this->view->auth->clearIdentity();
                Zend_Session::destroy(true);
            }
            if ($result) {
                echo json_encode($result);
                die();
            }
        }
    }

    /*
      Dev. Namrata Singh
      Description:Method to accept the email and refer a friend via email
     */

    public function referralsAction() {
        $objCore = Engine_Core_Core::getInstance();
        $this->_appSetting = $objCore->getAppSetting();
        $objUserModel = Application_Model_Users::getinstance();
        $objreferModel = Application_Model_ReferFriends::getinstance();
        
        $realobj = $objCore->getAppSetting();
        $host = $realobj->hostLink;
        $userid = 0;
        $useremail;
        $firstname;
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $useremail = $this->view->session->storage->email;
            // $username = $this->view->session->storage->user_name;
            $firstname = $this->view->session->storage->first_name;
        }
        $mailer = Engine_Mailer_Mailer::getInstance();

        $referral_url = $this->bitly_url_shorten($host.'referrals', '0ac6fd974647efb386cb6f3b509a4ae4f3100df4', 'fsln.me');
        $this->view->referralurl = $referral_url;

        if ($this->getRequest()->isPost()) {

            //dev:priyanka varanasi
            //desc: multiple mail sending with  encoded refferal link in mail
            $email = $this->getRequest()->getPost('email');
            $array = explode(',', $email);
            foreach ($array as $key => $emailid) {
                $mydata = array('user_id' => $userid,
                    'email' => $emailid);


                $activationKey = base64_encode($userid . "&" . $emailid);
                $link = $host.'refer-fashionlearn/' . $activationKey;

                if ($emailid != "") {
// ! implement for same email ID
                    if (!filter_var($emailid, FILTER_VALIDATE_EMAIL)) {
                        $this->view->email = "E-mail is not valid";
                    } else {
                        //echo $email;  die();

                        $date = date("Y-m-d");
                        $template_name = 'refertemplate';
                        $subject = 'referred';
                        $mergers = array(
                            array(
                                'name' => 'name',
                                'content' => $firstname
                            ),
                            array(
                                'name' => 'subscribe',
                                'content' => $link
                            )
                        );
                        $result = $mailer->refertemplate($template_name, $emailid, $useremail, $subject, $mergers);

                        if ($result) {
                            $data = array('user_id' => $userid,
                                'email' => $emailid,
                                'ref_by_email' => $useremail,
                                'ref_date' => $date
                            );
                            $insertionResult = $objreferModel->insertrefer($data);
                        }
                    }
                } else {
                    $this->view->email = "Please Enter a valid email id";
                }
            }
        }
        $getBonus = $objUserModel->bonusMonth();

        $referral_counts;
        if (isset($getBonus)) {
            foreach ($getBonus as $bonus) {
                if ($bonus['user_id'] == $userid) {
                    $referral_counts = $bonus['referral_counts'];
                }
            }
        }
        if (isset($referral_counts)) {
            $this->view->referral_count = $referral_counts;
        }
        $result = $objreferModel->selectrefer($userid);     //counting no of rows 
        //print_r($result); die('123');
        $this->view->count = $result['num'];
        $this->view->firstname = $firstname;
    }

    /*
      Dev. Namrata Singh
      Edited By :Rakesh Jha 
      Date of Edit : 22/06/15
      Action: CheckBox ajax handler for Email Notification Action
     *        This will accept the checked box's and unchecked box's id and update in DB
     */



    /* Developer:Jeyakumar N
      Desc : Getting all notification data from db for specific user
     *       
     */

    public function emailnotificationAction() {
//        $user_id;
//        if (isset($this->view->session->storage->user_id)) {
//            $user_id = $this->view->session->storage->user_id;
//        }
//        $objNotificationModel = Application_Model_Notification::getinstance();
//
//        $response = $objNotificationModel->getUserNotificationData($user_id);
//
//        $this->view->notifications = $response;
    }



    /* Dev: Namrata Singh
     * Date: 6 feb'15
     * Desc:payment ajax action to get data from add payment methods and show data there
     */

    public function paymentAjaxHandlerAction() {
        $user_id = '';
        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
        }
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $objPaymentMethods = Application_Model_PaymentMethods::getinstance();
        $cardnumber = $this->getRequest()->getParam('cardnumb');
//        print_r($cardnumber);  die('--');
        $expirymonth = $this->getRequest()->getParam('expiry');
        $year = $this->getRequest()->getParam('year');
        $id = $this->getRequest()->getParam('id');
        $lastdigit = substr((string) $cardnumber, -4);
        $cardType = $this->CreditCardType($cardnumber);
        $getCardAdded = $objPaymentMethods->selectCardDetail($user_id);
       
        if (empty($getCardAdded)) {
            if (isset($cardnumber) && isset($expirymonth) && isset($year)) {
                $data = array('user_id' => $user_id,
                    'card_last_digits' => $lastdigit,
                    'expiry' => $expirymonth,
                    'year' => $year,
                    'primary_card' => 1,
                    'card_type' => $cardType,
                    'card_number' => $cardnumber
                );
                $result = $objPaymentMethods->insertCardDetail($data);
                $getCardAdded = $objPaymentMethods->selectCardDetail($user_id);
                $count = count($getCardAdded);
            }
            if ($result) {
                $data = array('card_last_digits' => $lastdigit,
                    'expiry' => $expirymonth,
                    'year' => $year,
                    'flag' => 1,
                    'card_type' => $cardType,
                    'count' => $count);
                echo json_encode($data);
            }
        } else {
            if ($id == "primary") {
                $getdetail = $objPaymentMethods->selectPrimaryPayment($user_id);
                $payment_type_id = $getdetail['payment_type_id'];
                if ($getdetail) {
                    $primarycard = array('primary_card' => 0);
                    $res = $objPaymentMethods->updatePrimaryPayment($primarycard, $payment_type_id);
                    if (isset($cardnumber) && isset($expirymonth) && isset($year)) {
                        $data = array('user_id' => $user_id,
                            'card_last_digits' => $lastdigit,
                            'expiry' => $expirymonth,
                            'year' => $year,
                            'primary_card' => 1,
                            'card_type' => $cardType,
                            'card_number' => $cardnumber
                        );
                        $result = $objPaymentMethods->insertCardDetail($data);
                        $getCardAdded = $objPaymentMethods->selectCardDetail($user_id);
                        $count = count($getCardAdded);
                    }
                    if ($result) {
                        $data = array('card_last_digits' => $lastdigit,
                            'expiry' => $expirymonth,
                            'year' => $year,
                            'flag' => 1,
                            'card_type' => $cardType,
                            'count' => $count);
                        echo json_encode($data);
                    }
                } else {
                    if (isset($cardnumber) && isset($expirymonth) && isset($year)) {
                        $data = array('user_id' => $user_id,
                            'card_last_digits' => $lastdigit,
                            'expiry' => $expirymonth,
                            'year' => $year,
                            'primary_card' => 1,
                            'card_type' => $cardType,
                            'card_number' => $cardnumber
                        );
                        $result = $objPaymentMethods->insertCardDetail($data);
                        $getCardAdded = $objPaymentMethods->selectCardDetail($user_id);
                        $count = count($getCardAdded);
                    }
                    if ($result) {
                        $data = array('card_last_digits' => $lastdigit,
                            'expiry' => $expirymonth,
                            'year' => $year,
                            'flag' => 1,
                            'card_type' => $cardType,
                            'count' => $count);
                        echo json_encode($data);
                    }
                }
            } else {
                if (isset($cardnumber) && isset($expirymonth) && isset($year)) {
                    $data = array('user_id' => $user_id,
                        'card_last_digits' => $lastdigit,
                        'expiry' => $expirymonth,
                        'year' => $year,
                        'primary_card' => 0,
                        'card_type' => $cardType,
                        'card_number' => $cardnumber
                    );
                    $result = $objPaymentMethods->insertCardDetail($data);
                    $getCardAdded = $objPaymentMethods->selectCardDetail($user_id);
                    $count = count($getCardAdded);
                }
                if ($result) {
                    $data = array('card_last_digits' => $lastdigit,
                        'expiry' => $expirymonth,
                        'year' => $year,
                        'flag' => 0,
                        'card_type' => $cardType,
                        'count' => $count);
                    echo json_encode($data);
                }
            }
        }
    }

    //----------------------------------------------
    public function CreditCardType($cardnumber) {
        /*
          '*CARD TYPES            *PREFIX           *WIDTH
          'American Express       34, 37            15
          'Diners Club            300 to 305, 36    14
          'Carte Blanche          38                14
          'Discover               6011              16
          'EnRoute                2014, 2149        15
          'JCB                    3                 16
          'JCB                    2131, 1800        15
          'Master Card            51 to 55          16
          'Visa                   4                 13, 16
         */
//Just in case nothing is found
        $CreditCardType = "Unknown";

//Remove all spaces and dashes from the passed string
        $cardnumber = str_replace("-", "", str_replace(" ", "", $cardnumber));

//Check that the minimum length of the string isn't less
//than fourteen characters and -is- numeric
        If (strlen($cardnumber) < 14 || !is_numeric($cardnumber))
            return false;

//Check the first two digits first
        switch (substr($cardnumber, 0, 2)) {
            Case 34: Case 37:
                $CreditCardType = "American Express";
                break;
            Case 36:
                $CreditCardType = "Diners Club";
                break;
            Case 38:
                $CreditCardType = "Carte Blanche";
                break;
            Case 51: Case 52: Case 53: Case 54: Case 55:
                $CreditCardType = "Master Card";
                break;
        }

//None of the above - so check the
        if ($CreditCardType == "Unknown") {
            //first four digits collectively
            switch (substr($cardnumber, 0, 4)) {
                Case 2014:Case 2149:
                    $CreditCardType = "EnRoute";
                    break;
                Case 2131:Case 1800:
                    $CreditCardType = "JCB";
                    break;
                Case 6011:
                    $CreditCardType = "Discover";
                    break;
            }
        }

//None of the above - so check the
        if ($CreditCardType == "Unknown") {
            //first three digits collectively
            if (substr($cardnumber, 0, 3) >= 300 && substr($cardnumber, 0, 3) <= 305) {
                $CreditCardType = "American Diners Club";
            }
        }

//None of the above -
        if ($CreditCardType == "Unknown") {
            //So simply check the first digit
            switch (substr($cardnumber, 0, 1)) {
                Case 3:
                    $CreditCardType = "JCB";
                    break;
                Case 4:
                    $CreditCardType = "Visa";
                    break;
            }
        }

        return $CreditCardType;
    }
    
    
//DEV : priyanka varanasi 
//DESC : commented these lines of action for remodification
//DATE: 21/8/2015
    public function pagarTransaction() {
        $user_id;
        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
        }
        $this->_helper->viewRenderer->setNoRender(true);
        $objPagar = new Engine_Pagarme_PagarmeClass();
        $objUsermetaModel = Application_Model_UsersMeta::getinstance();
        $objPayment = Application_Model_Payment::getinstance();
        $objPaymentMethods = Application_Model_PaymentMethods::getinstance();
        $amount;
        if (isset($this->view->session->storage->subvalue)) {
            $amount = $this->view->session->storage->subvalue;
        }
        $email;
        if (isset($this->view->session->storage->email)) {
            $email = $this->view->session->storage->email;
        }
        $cardHash = $this->getRequest()->getPost('card_hash');
        $cardNum = $this->getRequest()->getPost('card');
        $expMonth = $this->getRequest()->getPost('expmonth');
        $expYear = $this->getRequest()->getPost('expyear');
        $cardType = $this->CreditCardType($cardNum);
        $lastdigit = substr((string) $cardNum, -4);
        $getCardAdded = $objPaymentMethods->selectCardDetail($user_id);
        $selectCardAdded = $objPaymentMethods->selectCardDetail($user_id);
        //--------------------steps Details---------------------
        $getStepDetail = $objUsermetaModel->getStepsDetail($user_id);
        $steps;
        if (empty($getStepDetail['interested_categories'])) {
            $steps = 0;
        }
        if (empty($getCardAdded)) {
            if (isset($cardNum) && isset($expMonth) && isset($expYear)) {
                $data = array('user_id' => $user_id,
                    'card_last_digits' => $lastdigit,
                    'expiry' => $expMonth,
                    'year' => $expYear,
                    'primary_card' => 1,
                    'card_type' => $cardType,
                    'card_number' => $cardNum
                );
                $result = $objPaymentMethods->insertCardDetail($data);
            }
        } else {
            foreach ($selectCardAdded as $value) {
                if (!($value['card_number'] == $cardNum)) {
                    $data = array('user_id' => $user_id,
                        'card_last_digits' => $lastdigit,
                        'expiry' => $expMonth,
                        'year' => $expYear,
                        'primary_card' => 0,
                        'card_type' => $cardType,
                        'card_number' => $cardNum
                    );
                    $result = $objPaymentMethods->insertCardDetail($data);
                }
            }
        }
        $pid;
        if (isset($this->view->session->storage->pid)) {
            $pid = $this->view->session->storage->pid;
        }
        $subid;
        if (isset($this->view->session->storage->subid)) {
            $subid = $this->view->session->storage->subid;
        }
         Pagarme :: setApiKey("ak_test_H8XElSFHXeO5BChZnfGbLyS3CdYvMU");
   
        $objUserModel = Application_Model_Users::getinstance();
        
       
        $subscription = new PagarMe_Subscription(array(
            'plan' => PagarMe_Plan :: findById($pid),
            'card_hash' => $cardHash,
            'customer' => array(
                'email' => $email
            )
        ));



        $subscription->create();
        
        if ($subscription) {
            
        } else {
            $this->_redirect('/credit-card-error');
        }

        if ($subscription) {
            //echo "<pre>"; print_r($subscription); die('123');     
            $start = substr(($subscription['current_period_start']), 0, 10);
            $end = substr(($subscription['current_period_end']), 0, 10);
            $pagarEndDate = $subscription['current_period_end'];
            $pagarStartDate = $subscription['current_period_start'];
            $unix_end_timestamp = STRTOTIME($pagarEndDate);
            $unix_start_timestamp = STRTOTIME($pagarStartDate);

            if (isset($this->view->session->storage->teacher_refferal) && $this->view->session->storage->teacher_refferal == 1) {   //Dev: Rakesh Jha
                if ($subscription['status'] == 'paid') {
                    $data = array('user_id' => $user_id,
                        'amount' => $amount,
                        'payment_method' => 0,
                        'card_holder_name' => $subscription['card']['holder_name'],
                        'card_last_digits' => $subscription['card']['last_digits'],
                        'transaction_id' => $subscription['current_transaction']['id'],
                        'card_hash' => $cardHash,
                        'status' => $subscription['status'],
                        'current_period_start' => $start,
                        'current_period_end' => $end,
                        'unix_end_timestamp' => $unix_end_timestamp,
                        'unix_start_timestamp' => $unix_start_timestamp,
                        'pagar_subscription_id' => $subscription['id'],
                        'subscription_id' => $subid,
                        'reffered' => 1
                    );
                    $resonse = $objPayment->insertPayment($data);
//                echo "<pre>";
//                print_r($resonse);
//                die('***');
                    if ($resonse) {
                        $status = array('premium_status' => 1);
                        $updateResult = $objUserModel->updatePremiumStatus($status, $user_id);
                      $points = Application_Model_Points::getinstance();
                      $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                            $p = $points->getpointsinfo(8);
                            $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
                    }

                    if ($steps == 0) {
                          $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                        $this->_redirect('/step1');
                    } else {
                          $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                        $this->_redirect('/dashboard');
                    }
                } elseif ($subscription['status'] == 'trialing') {
                    $data = array('user_id' => $user_id,
                        'amount' => $amount,
                        'payment_method' => 0,
                        'card_holder_name' => $subscription['card']['holder_name'],
                        'card_last_digits' => $subscription['card']['last_digits'],
                        'transaction_id' => $subscription['current_transaction']['id'],
                        'card_hash' => $cardHash,
                        'status' => $subscription['status'],
                        'current_period_start' => $start,
                        'current_period_end' => $end,
                        'unix_start_timestamp' => $unix_start_timestamp,
                        'unix_end_timestamp' => $unix_end_timestamp,
                        'pagar_subscription_id' => $subscription['id'],
                        'subscription_id' => $subid,
                        'reffered' => 1
                    );
                    $resonse = $objPayment->insertPayment($data);
                    $classid;
                    if (isset($this->view->session->storage->refferal_class_id)) {
                        $classid = $this->view->session->storage->refferal_class_id;
                    }
                    $this->_redirect('/teachclass/' . $classid . '?via=referal&classid=' . $classid);
                } else {
                    $this->_redirect('/pagarerror');
                    //print_r("Transaction Refused");
                }
            } else {

                if ($subscription['status'] == 'paid') {
                    $data = array('user_id' => $user_id,
                        'amount' => $amount,
                        'payment_method' => 0,
                        'card_holder_name' => $subscription['card']['holder_name'],
                        'card_last_digits' => $subscription['card']['last_digits'],
                        'transaction_id' => $subscription['current_transaction']['id'],
                        'card_hash' => $cardHash,
                        'status' => $subscription['status'],
                        'current_period_start' => $start,
                        'current_period_end' => $end,
                        'unix_end_timestamp' => $unix_end_timestamp,
                        'unix_start_timestamp' => $unix_start_timestamp,
                        'pagar_subscription_id' => $subscription['id'],
                        'subscription_id' => $subid
                    );

                    $resonse = $objPayment->insertPayment($data);

                    if ($resonse) {
                        $status = array('premium_status' => 1);
                        $updateResult = $objUserModel->updatePremiumStatus($status, $user_id);
                         $points = Application_Model_Points::getinstance();
                      $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                            $p = $points->getpointsinfo(8);
                            $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
                        
                    }
                    //print_r($resonse);die('in r4esponse');
                    if ($steps == 0) {
                          $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                        $this->_redirect('/step1');
                    } else {
                          $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                        $this->_redirect('/dashboard');
                    }
                } elseif ($subscription['status'] == 'trialing') {
                    $data = array('user_id' => $user_id,
                        'amount' => $amount,
                        'payment_method' => 0,
                        'card_holder_name' => $subscription['card']['holder_name'],
                        'card_last_digits' => $subscription['card']['last_digits'],
                        'transaction_id' => $subscription['current_transaction']['id'],
                        'card_hash' => $cardHash,
                        'status' => $subscription['status'],
                        'current_period_start' => $start,
                        'current_period_end' => $end,
                        'unix_start_timestamp' => $unix_start_timestamp,
                        'unix_end_timestamp' => $unix_end_timestamp,
                        'pagar_subscription_id' => $subscription['id'],
                        'subscription_id' => $subid
                    );
                    $resonse = $objPayment->insertPayment($data);

                    if ($steps == 0) {
                         $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                        $this->_redirect('/step1');
                    } else {
                         $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                        $this->_redirect('/dashboard');
                    }
                } else {
                    $this->_redirect('/pagarerror');
                    // print_r("Transaction Refused");
                }
            }
        } else {
            $this->_redirect('/credit-card-error');
        }
    }
   
    public function pagarBankTransactionAction() {
        $user_id;
        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
        }
        $this->_helper->viewRenderer->setNoRender(true);
        $objPagar = new Engine_Pagarme_PagarmeClass();
        $objPayment = Application_Model_Payment::getinstance();
        Pagarme :: setApiKey("ak_test_H8XElSFHXeO5BChZnfGbLyS3CdYvMU");
        $amount;
        if (isset($this->view->session->storage->subvalue)) {
            $amount = $this->view->session->storage->subvalue;
        }
        // print_r($this->view->session->storage->subvalue); die;
        $name = $this->getRequest()->getPost("name");
        $street = $this->getRequest()->getPost("street");
        $streetnumber = $this->getRequest()->getPost("streetnumber");
        $neighborhood = $this->getRequest()->getPost("neighbourhood");
        $zipcode = $this->getRequest()->getPost("zipcode");
        $number = $this->getRequest()->getPost("number");
        $ddd = $this->getRequest()->getPost("ddd");
        $pid;
        $subid;
        $email;
        if (isset($this->view->session->storage->pid)) {
            $pid = $this->view->session->storage->pid;
        }

        if (isset($this->view->session->storage->subid)) {
            $subid = $this->view->session->storage->subid;
        }
        if (isset($this->view->session->storage->email)) {
            $email = $this->view->session->storage->email;
        }

        $subscription = new PagarMe_Subscription(array(
            'plan' => PagarMe_Plan :: findById($pid),
            'amount' => $amount,
            'payment_method' => "boleto",
            'customer' => array(
                'address' => array(
                    'street' => $street,
                    'street_number' => $streetnumber,
                    'neighborhood' => $neighborhood,
                    'zipcode' => $zipcode
                ),
                "phone" => array(
                    "ddd" => $ddd,
                    "number" => $number
                ),
                'name' => $name,
                'email' => $email
            )
        ));

        $subscription->create();
        //  echo "<pre>"; print_r($subscription['status']); die('123');
        // $error = $subscription->getErrors();
//        if ($abc) {
//            die('right');
//        } else {
//            die('wrong');
//            //$this->_redirect('/payment-error');
//        }
        //  echo "<pre>"; print_r($subscription); die('---');
        if ($subscription) {
            // die('11111');
            // echo "<pre>"; print_r($subscription); die('---');
            $start = substr(($subscription['current_period_start']), 0, 10);
            $end = substr(($subscription['current_period_end']), 0, 10);
            $pagarEndDate = $subscription['current_period_end'];
            $pagarStartDate = $subscription['current_period_start'];
            $unix_end_timestamp = STRTOTIME($pagarEndDate);
            $unix_start_timestamp = STRTOTIME($pagarStartDate);
            if ($subscription['status'] == 'paid') {

                $data = array('user_id' => $user_id,
                    'amount' => $amount,
                    'payment_method' => 1,
                    'boleto_barcode' => $subscription['current_transaction']['boleto_barcode'],
                    'transaction_id' => $subscription['current_transaction']['id'],
                    'status' => $subscription['status'],
                    'current_period_start' => $start,
                    'current_period_end' => $end,
                    'unix_end_timestamp' => $unix_end_timestamp,
                    'unix_start_timestamp' => $unix_start_timestamp,
                    'pagar_subscription_id' => $subscription['id'],
                    'street' => $street,
                    'neighborhood' => $neighborhood,
                    'zipcode' => $zipcode,
                    'street_number' => $streetnumber,
                    'subscription_id' => $subid,
                    'ddd' => $ddd,
                    'number' => $number,
                    'name' => $name
                );

                $resonse = $objPayment->insertPayment($data);

                if ($steps == 0) {
                     $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                    $this->_redirect('/step1');
                } else {
                     $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                    $this->_redirect('/dashboard');
                }
            } elseif ($subscription['status'] == 'trialing') {

                $data = array('user_id' => $user_id,
                    'amount' => $amount,
                    'payment_method' => 1,
                    'boleto_barcode' => $subscription['current_transaction']['boleto_barcode'],
                    'transaction_id' => $subscription['current_transaction']['id'],
                    'status' => $subscription['status'],
                    'current_period_start' => $start,
                    'current_period_end' => $end,
                    'unix_end_timestamp' => $unix_end_timestamp,
                    'unix_start_timestamp' => $unix_start_timestamp,
                    'pagar_subscription_id' => $subscription['id'],
                    'street' => $street,
                    'neighborhood' => $neighborhood,
                    'zipcode' => $zipcode,
                    'street_number' => $streetnumber,
                    'subscription_id' => $subid,
                    'ddd' => $ddd,
                    'number' => $number,
                    'name' => $name
                );
                $resonse = $objPayment->insertPayment($data);

                if ($resonse) {
               
                    if ($steps == 0) {
                          $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                        $this->_redirect('/step1');
                    } else {
                          $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                        $this->_redirect('/dashboard');
                    }
                }
            } elseif ($subscription['status'] == 'unpaid') {

                $data = array('user_id' => $user_id,
                    'amount' => $amount,
                    'payment_method' => 1,
                    'boleto_barcode' => $subscription['current_transaction']['boleto_barcode'],
                    'transaction_id' => $subscription['current_transaction']['id'],
                    'status' => $subscription['status'],
                    'current_period_start' => $start,
                    'current_period_end' => $end,
                    'unix_end_timestamp' => $unix_end_timestamp,
                    'unix_start_timestamp' => $unix_start_timestamp,
                    'pagar_subscription_id' => $subscription['id'],
                    'street' => $street,
                    'neighborhood' => $neighborhood,
                    'zipcode' => $zipcode,
                    'street_number' => $streetnumber,
                    'subscription_id' => $subid,
                    'ddd' => $ddd,
                    'number' => $number,
                    'name' => $name
                );
                $resonse = $objPayment->insertPayment($data);

                if (isset($resonse)) {
                  
                    if ($steps == 0) {
                          $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                        $this->_redirect('/step1');
                    } else {
                          $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                        $this->_redirect('/dashboard');
                    }
                }
            } else {
                //$this->_redirect('/pagar-error');
            }
        } else {
            //$this->_redirect('/payment-error');
        }
    }

    public function pagarErrorAction() {
        
        $errormessage = $this->getRequest()->getParam('response'); 
        if($errormessage){
           
            $this->view->errormessage = $errormessage;
        }
        
    }

    //dev:priyanka varanasi
    //desc: ajax referrals function email sending through email importer

    public function emailRefferalsAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
$host = $realobj->hostLink;
        $this->_appSetting = $objCore->getAppSetting();
        $objreferModel = Application_Model_ReferFriends::getinstance();
        $userid;
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
        }
        $useremail;
        if (isset($this->view->session->storage->email)) {
            $useremail = $this->view->session->storage->email;
        }
        // $username = $this->view->session->storage->user_name;
        $firstname;
        if (isset($this->view->session->storage->first_name)) {
            $firstname = $this->view->session->storage->first_name;
        }
        $mailer = Engine_Mailer_Mailer::getInstance();
        if ($this->getRequest()->isPost()) {
            $email = $this->getRequest()->getPost('email');
            $singleemail = $this->getRequest()->getPost('method');
            if ($singleemail) {
                $activationKey = base64_encode($userid . "&" . $email);
                $link = $host.'refer-fashionlearn/' . $activationKey;

                if ($email != "") {
// ! implement for same email ID
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $this->view->email = "E-mail is not valid";
                    } else {
                        //echo $email;  die();
                        $date = date("Y-m-d");
                        $template_name = 'refertemplate';
                        $subject = 'referred';
                        $mergers = array(
                            array(
                                'name' => 'name',
                                'content' => $email
                            ),
                            array(
                                'name' => 'subscribe',
                                'content' => $link
                            )
                        );
                        $result = $mailer->refertemplate($template_name, $email, $useremail, $subject, $mergers);


                        if ($result) {
                            $data = array('user_id' => $userid,
                                'email' => $email,
                                'ref_by_email' => $useremail,
                                'ref_date' => $date
                            );
                            $insertionResult = $objreferModel->insertrefer($data);
//                            print_r($insertionResult);die;
                        }
                    }
                } else {
                    $this->view->email = "Please Enter a valid email id";
                }
            } else {

                foreach ($email as $key => $email) {
//                print_r($email); die;
                    $activationKey = base64_encode($userid . "&" . $email);
                    $link = $host.'refer-fashionlearn/' . $activationKey;
//                print_r($email); die;

                    if ($email != "") {
// ! implement for same email ID
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $this->view->email = "E-mail is not valid";
                        } else {
                            //echo $email;  die();
                            $date = date("Y-m-d");
                            $template_name = 'refertemplate';
                            $subject = 'referred';
                            $mergers = array(
                                array(
                                    'name' => 'name',
                                    'content' => $email
                                ),
                                array(
                                    'name' => 'subscribe',
                                    'content' => $link
                                )
                            );
                            $result = $mailer->refertemplate($template_name, $email, $useremail, $subject, $mergers);

                            if ($result) {
                                $data = array('user_id' => $userid,
                                    'email' => $email,
                                    'ref_by_email' => $useremail,
                                    'ref_date' => $date
                                );
                                $insertionResult = $objreferModel->insertrefer($data);
                            }
                        }
                    } else {
                        $this->view->email = "Please Enter a valid email id";
                    }
                }
            }
        }
        $result = $objreferModel->selectrefer($userid);     //counting no of rows 
        //print_r($result); die('123');
        $this->view->count = $result['num'];
        $this->view->firstname = $firstname;
    }

    //dev:priyanka varanasi 
    // refferals action for fashion learn
    public function referFashionlearnAction() {
        $mailer = Engine_Mailer_Mailer::getInstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objSecurity = Engine_Vault_Security::getInstance();
        $objUsermetaModel = Application_Model_UsersMeta::getinstance();
        $objNotificationModel = Application_Model_Notification::getinstance();
        $key = $this->getRequest()->getParam('codeid');
        if ($key) {
            $decodeKey = base64_decode($key);
            $data = explode('&', $decodeKey);
            $myinfo['user_id'] = $data[0];
            $myinfo['email'] = $data[1];
            if ($myinfo) {
                $this->view->info = $myinfo;
            }
        }
        if ($this->getRequest()->isPost()) {
            $firstname = $this->getRequest()->getPost('firstname');
            $lastname = $this->getRequest()->getPost('lastname');
            $email = $this->getRequest()->getPost('email');
            $password = $this->getRequest()->getPost('password');
            $referralid = $this->getRequest()->getPost('referralid');
            $validateEmail = $objUserModel->validateEmailId($email);
            if ($validateEmail) {

                $this->_redirect('/refer-error-page');
            } else if (isset($referralid)) {


                if (isset($firstname) && isset($lastname) && isset($email) && isset($password)) {

                    $data = array('first_name' => $firstname,
                        'last_name' => $lastname,
                        'password' =>  sha1(md5($password)),
                        'email' => $email,
                        'status' => '1',
                        'role' => '1',
                        'referrals' => $referralid,
                    );
                    $insertionResult = $objUserModel->insertUser($data);
                    if ($insertionResult) {
                        $metaData = array('user_id' => $insertionResult);
                        $notifydata = array('user_id' => $insertionResult);
                        $username = $firstname;
                        $objUsermetaModel->insertUsermeta($metaData);
                         $points = Application_Model_Points::getinstance();
                            $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                            $p = $points->getpointsinfo(7);
                            $objUsermetaModel->updatepoints($insertionResult, $p['points'], $p['gems']);
                        $objNotificationModel->insertNotification($notifydata);
                        // Mandrill implementation                           
                        $template_name = 'Welcome_to_fashionlearn';
                        $email = $email;
                        //$username = $this->getRequest()->getPost('username');
                        $subject = 'Welcome Mail';
                        $mergers = array(
                            array(
                                'name' => 'username',
                                'content' => $firstname
                            ),
                            array(
                                'name' => 'myaccountlink',
                                'content' => 'fashiontuts.globusapps.com'
                            )
                        );
                        $result = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                        $authStatus = $objSecurity->authenticate($email,  sha1(md5($password)));
                        // echo "<pre>"; print_r($authStatus->code); die('----');
                        if ($authStatus->code == 200) {
                            
                           
                            $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                            $p = $points->getpointsinfo(6);
                            $objUsermetaModel->updatepoints($referralid, $p['points'], $p['gems']);
                            
                            
                            
                             $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                            $this->_redirect('/step1?referid='.$referralid);
                        } else {
                            $this->_redirect('/refer-error-page');
                        }
                    }
                }
            } else {
                if (isset($firstname) && isset($lastname) && isset($email) && isset($password)) {


                    $data = array('first_name' => $firstname,
                        'last_name' => $lastname,
                        'password' =>  sha1(md5($password)),
                        'email' => $email,
                        'status' => '1',
                        'role' => '1',
                    );
                    $insertionResult = $objUserModel->insertUser($data);
                    if ($insertionResult) {
                        $metaData = array('user_id' => $insertionResult);
                        $notifydata = array('user_id' => $insertionResult);

                        $objUsermetaModel->insertUsermeta($metaData);
                        $objNotificationModel->insertNotification($notifydata);
                        // Mandrill implementation                           
                        $template_name = 'Welcome_to_fashionlearn';
                        $email = $email;
                        //$username = $this->getRequest()->getPost('username');
                        $subject = 'Welcome Mail';
                        $mergers = array(
                            array(
                                'name' => 'username',
                                'content' => $firstname
                            ),
                            array(
                                'name' => 'myaccountlink',
                                'content' => 'fashiontuts.globusapps.com'
                            )
                        );
                        $result = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                        $authStatus = $objSecurity->authenticate($email,  sha1(md5($password)));

                        if ($authStatus == 200) {
                             $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                            $this->_redirect('/step1');
                        } else {
                            $this->_redirect('/refer-error-page');
                        }
                    }
                }
            }
        }
    }

    public function notificationEmailAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $method = $this->getRequest()->getPost("method");


        $mailer = Engine_Mailer_Mailer::getInstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $projects = Application_Model_Projects::getinstance();
        $objreferModel = Application_Model_ReferFriends::getinstance();
        $classDiscussioncomments = Application_Model_DiscussionComments::getinstance();
        $user = Application_Model_Users::getinstance();
//        print_r($email); die;
        $notify_topic = $this->getRequest()->getPost("notify_topic");
        $userid;
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
        }
        $useremail;
        if (isset($this->view->session->storage->email)) {
            $useremail = $this->view->session->storage->email;
        }
        $pic = "";
        $topic = "";
        switch ($method) {
            case 'projectlike':

                $projectid = $this->getRequest()->getPost("projectid");
                $result = $projects->getUserProject($projectid);
                $projecttitle = $result['project_title'];

                $subject = "Like";
                $liketype = "project";
                $email = $result['email'];
                $name = $result['first_name'];
                $link = "http://skillshare.globusapps.com/teachclass/" . $result['class_id'] . "?via=browse&action=project&id=" . $projectid;
                $pic = "http://skillshare.globusapps.com" . $result['project_cover_image'];
                $template_name = "likes";
                break;
            case 'followuser':
                $subject = "Follow";
                $userid2 = $this->getRequest()->getPost("userid");

                $followModel = Application_Model_Followers::getinstance();
                $res = $followModel->getFollowDetail($userid2, $userid);
                $followStatus = $res["follow_status"];



                $followers = $followModel->getnooffollowers($userid);
                $following = $followModel->getnooffollowing($userid);



                if ($followStatus == 0) {
                    $template_name = 'MeFollow';
                } else {
                    $template_name = 'MeNotFollow';
                }
                $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                $getmetaresult = $objUsermetaModel->getUserMetaDetail($userid);
                $pic = "http://skillshare.globusapps.com" . $getmetaresult['user_profile_pic'];


                $result = $user->getTeachername($userid2);
//                print_r($result); die;
                $email = $result['email'];

                $name = $result['first_name'];
                $link = 'http://skillshare.globusapps.com/profile/' . $userid;
                break;
            case 'projectcomment':
                $projectid = $this->getRequest()->getPost("project_id");
                $result = $projects->getUserProject($projectid);
                $subject = "comment on project";
                $topic = "project";

                $topic1 = $result['project_title'];
                $email = $result['email'];
                $name = $result['first_name'];
                //  $pic="http://skillshare.globusapps.com".$result['project_cover_image'];
                $readmore = "http://skillshare.globusapps.com/teachclass/" . $result['class_id'] . "?via=browse&action=project&id=" . $projectid;
                $template_name = 'comments';
                $topic2 = "commented " . $this->getRequest()->getPost("projectcomment");

                $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                $getmetaresult = $objUsermetaModel->getUserMetaDetail($userid);
                $pic = "http://skillshare.globusapps.com" . $getmetaresult['user_profile_pic'];
                break;

            case 'DiscussionComment':

                $projecttitle = "Disscussion";


                $subject = "discussion comment";
                $subject = "comment on Discussion";
                $topic = "Your Discussion";
                $classid = $this->getRequest()->getPost("classid");
                $template_name = 'comments';
                //$pic=" http://securityaffairs.co/wordpress/wp-content/uploads/2015/02/facebook-comments-hacking-3.png";
                $readmore = "http://skillshare.globusapps.com/teachclass/" . $classid;
                $result = $teachingclass->getClassTitle($classid);
                print_r($result);

                $topic2 = "commented " . $this->getRequest()->getPost("discussioncomment");
//                  echo '<pre>'; print_r($result); die;
                foreach ($result as $key => $value) {
                    $email = $value['email'];
                    $name = $value['first_name'];
                    $topic1 = $value['class_title'];
                }
                $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                $getmetaresult = $objUsermetaModel->getUserMetaDetail($userid);
                $pic = "http://skillshare.globusapps.com" . $getmetaresult['user_profile_pic'];
                break;
            case 'DiscussionCommentlike':
                $subject = "Discussion like";

                $notify_id = $this->getRequest()->getPost("notify_id");
                $result = $classDiscussioncomments->getDiscussionCreatorDetails($notify_id);
                $projecttitle = $result['discussion_title'];


                $classid = $this->getRequest()->getPost("classid");
                $email = $result['email'];
                $liketype = "discussion";
//                 $email ='rakeshjha@globussoft.com';
                $name = $result['first_name'];
//                  print_r($name); die;
                $template_name = "likes";
                $link = "http://skillshare.globusapps.com/teachclass/" . $classid;
                $pic = "http://img2.wikia.nocookie.net/__cb20120118234601/callofduty/images/c/cf/Facebook_like_buton.png";
                break;
        }

        if ($this->getRequest()->isPost()) {



            $mydata = array('user_id' => $userid,
                'email' => $email);
            if ($email != "") {
// ! implement for same email ID
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->view->email = "E-mail is not valid";
                } else {
//                    echo $email;  die();
                    $date = date("Y-m-d");

                    $plink = "http://skillshare.globusapps.com/profile/" . $userid;

                    $mergers = array(
                        array(
                            'name' => 'name',
                            'content' => $name
                        ),
                        array(
                            'name' => 'name2',
                            'content' => $this->view->session->storage->first_name . " " . $this->view->session->storage->last_name
                        ),
                        array(
                            'name' => 'topic',
                            'content' => $topic
                        ),
                        array(
                            'name' => 'name2link',
                            'content' => $plink
                        ),
                        array(
                            'name' => 'projectlink',
                            'content' => $link
                        ),
                        array(
                            'name' => 'projecttitle',
                            'content' => $projecttitle
                        ),
                        array(
                            'name' => 'topic',
                            'content' => $topic
                        ),
                        array(
                            'name' => 'topic1',
                            'content' => $topic1
                        ),
                        array(
                            'name' => 'topic2',
                            'content' => $topic2
                        ),
                        array(
                            'name' => 'followers',
                            'content' => $followers
                        ),
                        array(
                            'name' => 'following',
                            'content' => $following
                        ),
                        array(
                            'name' => 'readmore',
                            'content' => $readmore
                        ),
                        array(
                            'name' => 'liketype',
                            'content' => $liketype
                        ),
                        array(
                            'name' => 'pic',
                            'content' => $pic
                        )
                    );
                    print_r($mergers);
//die($email);
                    $result = $mailer->refertemplate($template_name, $email, $useremail, $subject, $mergers);
//                    if ($result) {
//                        $data = array('user_id' => $userid,
//                            'email' => $email,
//                            'ref_by_email' => $useremail,
//                            'ref_date' => $date
//                        );
//                        $insertionResult = $objreferModel->insertrefer($data);
//                    }
                }
            } else {
                $this->view->email = "Please Enter a valid email id";
            }
        }
    }

    public function referErrorAction() {
        
    }

    /* Dev: Namrata Singh
     * Date: 18 March'15
     * Desc: Cancelling a membership temporarily(for user premium membership Activate/Deactivate functionality)
     */

    public function cancellationAction() {
        $user_id;
      
        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
        }
        $objUserModel = Application_Model_Users::getinstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $trending = $teachingclass->getAllCLasses();
        $this->view->trendingclass = $trending;
        //echo "<pre>"; print_r($this->view->session->storage); die();
        //$date1 = date("Y-m-d");
        //$date2 = $this->view->session->storage->member['current_period_end'];
        $date1 = date("Y-m-d");
        $date2;
        if (isset($this->view->session->storage->member)) {
            $date2 = $this->view->session->storage->member['current_period_end'];
        }
        $diff = abs(strtotime($date2) - strtotime($date1));
        $years = floor($diff / (365 * 60 * 60 * 24));
        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
        $tot = ($years * 12 * 30) + ($months * 30) + $days;
        $this->view->total = $tot;

        if ($this->getRequest()->isPost()) {

            $cancel = $this->getRequest()->getParam('cancel');

            if ($cancel) {
                $data = array('premium_status' => 2);
                $updateResult = $objUserModel->updatePremiumStatus($data, $user_id);
                if (isset($updateResult)) {
 $points = Application_Model_Points::getinstance();
                      $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                            $p = $points->getpointsinfo(8);
                            $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
                    $this->_redirect('/dashboard');
                }
            }
        }
    }

    public function creditcardErrorAction() {
        
    }

    public function paymentErrorAction() {
        
    }

    /*
      Developer:Rakesh Jha
      Desc:Connect with Social media facebook,tweeter
      Created :1/04/2015
     */

    public function socialConnectAction() {
//       die('dasd');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $mehtod = $this->getRequest()->getPost('method');

        $user_id;
        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
        }
        $users = Application_Model_Users::getinstance();


        switch ($mehtod) {
            case 'facebook':
                $link = $this->getRequest()->getPost('link');
                $fbcount = $this->getRequest()->getPost('fbcount');
                $fbname = $this->getRequest()->getPost('fbname');
               
                $data = array('facebook_count' => $fbcount, 'fbconnectedstatus' => 1, 'fb_uid' => $link,'fb_id' => $link, 'fb_name' => $fbname);
                $result = $users->insertSocialDetails($data, $user_id);
                echo json_encode($result);
                exit();
                break;
            case 'facebookdisconnect':

                $data = array('fbconnectedstatus' => 0);
                $result = $users->fbSocialStatus($data, $user_id);
                echo json_encode($result);
                exit();
                break;

            case 'twitterconnect':

                $objCore = Engine_Core_Core::getInstance();
                $this->_appSetting = $objCore->getAppSetting();
                $consumer_key = $this->_appSetting->twitter->consumerKey;
                $consumer_secret = $this->_appSetting->twitter->consumerSecret;
                $to = new TwitterOAuth($consumer_key, $consumer_secret);
                $tok = $to->getRequestToken();
                $token = $tok['oauth_token'];
                $tokenSecret = $tok['oauth_token_secret'];
                $this->view->session->storage->reqToken = $token;
                $this->view->session->storage->reqTokenSecret = $tokenSecret;

                //-----------------------------------------------------------------------------------------------------------
//                $config = array(
//                    'access_token' => array(
//                        'token' => ' 2922395624-kXjzno7m48tp0R7lvpKVHy00h4QatxsQBP3vXr4',
//                        'secret' => '1gaCtPdsq7Li6VLMdlOZIzfrKA7hiLUSYO1LNX20cNoyb',
//                    ),
//                    'oauth_options' => array(
//                        'consumerKey' => 'LCcb6W6EcFSvU8knKQZ8zaLM4',
//                        'consumerSecret' => ' 9LSklloyMWAfrQcuxZR937n2AsU3LJVamA3foOoRQXcC7lGAb5',
//                    ),
//                    'http_client_options' => array(
//                        'adapter' => 'Zend\Http\Client\Adapter\Curl',
//                        'curloptions' => array(
//                            CURLOPT_SSL_VERIFYHOST => false,
//                            CURLOPT_SSL_VERIFYPEER => false,
//                        ),
//                    ),
//                );
//                
//                $twitter = new Engine_Twitter_Twitter($config);
//                echo "<pre>"; print_r($twitter); die;
//                $settings = array(
//                    'oauth_access_token' => $token,
//                    'oauth_access_token_secret' => $tokenSecret,
//                    'consumer_key' => $consumer_key,
//                    'consumer_secret' => $consumer_secret
//                );
//                $twitter = new TwitterAPIExchange($settings);
//                
                $twresponse = array('token' => $token,
                );
                $data = array('tw_connect' => 1);
                $result = $users->insertSocialDetails($data, $user_id);
                echo json_encode($twresponse);
                break;

            case'twdisconnect':
                $data = array('tw_connect' => 0);
                $result = $users->insertSocialDetails($data, $user_id);
                echo json_encode(true);
                exit();
                break;

            default:
                break;
        }
    }

    /*
      Developer:Namrata Singh
      Desc:Show the invoice details
      Created :3/04/2015
     */

    public function invoiceAction() {
        $userid;
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
        }
        $this->_helper->layout()->disableLayout();
        $payId = $this->getRequest()->getParam('payid');
        $objPaymentModel = Application_Model_Payment::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $getPayResult = $objPaymentModel->getStatus($payId);
        $this->view->payResult = $getPayResult;
        $userDetail = $objUserModel->getUserDetail($userid);
        $this->view->userPayDetail = $userDetail;
        $objBillDetails = $objPaymentModel->selectBill($userid);
        foreach ($objBillDetails as $value) {
            if ($value['payment_id'] == $payId) {
                $this->view->billdetails = $value;
            }
        }

        // echo "<pre>"; print_r($objBillDetails); die('@@');
    }


/** 
 *Developer:Abhishek m
 *Desc:send request to admin for create/change bank account in pagar for teacherpayment
 * 
 */
    public function pagarbankreqAction() {

        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
            $name = $this->view->session->storage->first_name;
            $email1 = $this->view->session->storage->email;

            $bankd = array();
            $bankd["bank_code"] = $_POST["bank_code"];
            $bankd["agency"] = $_POST["pagency"];
            $bankd["agencia_dv"] = $_POST["pagenciadv"];
            $bankd["account_no"] = $_POST["paccountno"];
            $bankd["conta_dv"] = $_POST["pcontadv"];
            $bankd["document_number"] = $_POST["pdocumentnumber"];
            $bankd["legal_name"] = $_POST["plegalname"];
            $bankd["user_id"] = $user_id;
            $bankd["type"] = $_POST["type"];
            $bankd["requestid"] = md5(sha1($bankd["account_no"] + time()));
            $objCore = Engine_Core_Core::getInstance();
            $realobj = $objCore->getAppSetting();
            $host = $realobj->hostLink;

            $objPagarbankcreq = Application_Model_Pagarbankcreq::getinstance();
            $res = $objPagarbankcreq->insertbankreqc($bankd);
            if ($res) {
                $mailer = Engine_Mailer_Mailer::getInstance();
                $template_name = 'teacherbank';
                $email = "financeiro@fashionlearn.com.br";
                //$username = $this->getRequest()->getPost('username');
                $subject = 'Bank account add/edit request';
                $mergers = array(
                    array(
                        'name' => 'name',
                        'content' => $name
                    ),
                    array(
                        'name' => 'requestid',
                        'content' => $res["requestid"]
                    ),
                    array(
                        'name' => 'rlink',
                        'content' => $host."/admin/editbankdetails?rid=".$bankd["requestid"]
                    ),
                    array(
                        'name' => 'email',
                        'content' => $email1
                    )
                );
                $mresult = $mailer->sendtemplate($template_name, $email, $name, $subject, $mergers);


                if ($mresult)
                    echo json_encode ($res["requestid"]);
                else
                    echo "0";
            }
            else {
                echo 0;
            }
        }


        die();
    }

    
    
    
        public function checkBoxAjaxHandlerAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $user_id = '';
        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
        }
        $ajaxMethod = $this->getRequest()->getParam('ajaxMethod');
        
        if ($ajaxMethod) {
            switch ($ajaxMethod) {

                // when checkbox is checked
                case 'checkedOption':

                    $val = 1;

                    $dataname = $this->getRequest()->getParam('dbdataname');


                    $objNotificationModel = Application_Model_Notification::getinstance();

                    $response = $objNotificationModel->updateNotification($val, $user_id, $dataname);


                    if ($response) {
                        $arr = array("saved changes!");
                        echo json_encode($arr);
                    }
                    break;

                // when checkbox is unchecked
                case 'unCheckedOption':
                    $val = 0;
                    $dataname = $this->getRequest()->getParam('dbdataname');

                    $objNotificationModel = Application_Model_Notification::getinstance();
                    $response = $objNotificationModel->updateNotification($val, $user_id, $dataname);
                    if ($response) {
                        $arr = array("saved changes!");
                        echo json_encode($arr);
                    }

                    break;

                case'savepaypal':
                    $email = $this->getRequest()->getParam('paypalemail');
                    $data = array('paypal_email' => $email);
                    $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                    $result = $objUsermetaModel->editUsermeta($data, $user_id);
                    if ($result == 1) {

                        $message = array("updated successfully");
                        echo json_encode($message);
                    } else {

                        $mess = array("already existed");
                        echo json_encode($mess);
                    }

                    break;
                case'unsavepaypal':
                    $email = $this->getRequest()->getParam('payemail');
                    $data = array('paypal_email' => '');
                    $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                    $result = $objUsermetaModel->editUsermeta($data, $user_id);
                    if ($result == 1) {
                        $message = array("deleted successfully");
                        echo json_encode($message);
                    } else {
                        $mess = array("already existed");
                        echo json_encode($mess);
                    }

                    break;
              //dev: priyanka varanasi
              //dated : 24/08/2015
              //desc: to check whether coupon code exists or not and show respective messages 
               
              case'checkdiscount':
                  
               $couponsModal  =  Application_Model_Coupons::getinstance();
               $plandetailsmodal =  Application_Model_Plans::getinstance();
               $allplaninfo = $plandetailsmodal->getAllPlanDetails();
               $couponcode = $this->getRequest()->getParam('coupon_code');
               $plantype = $this->getRequest()->getParam('plantype');
               $couponres   =  $couponsModal->checkCouponCode($couponcode);
              if(!empty($couponres) && ($couponres['coupon_limit']>0)){
                 if($couponres['discount_type']==0){
                     $amountdeducted1 ="";
                     $amount="";
                     if($plantype == $allplaninfo[0]['plan_type_id']){
                     $amountdeducted1 = ($allplaninfo[0]['amount']*$couponres['discount_offered'])/100;
                     $amount = $allplaninfo[0]['amount'] - $amountdeducted1;
                      $responsecode = array('code'=>200,
                                            'data'=>$couponres['coupon_code'],
                                            'message'=> 'your coupon is saved successfully for this payment.',
                                             'amount'=> $amount,
                                             'discount'=> $amountdeducted1,
                                              'plan'=> $allplaninfo[0]['plan_type_id'] ,
                                             'trailend' =>   date("F j, Y", strtotime('+'.$allplaninfo[0]['trail_days'].'days'))
                          );
                     }else{
                       $amountdeducted1 = ($allplaninfo[1]['amount']* $couponres['discount_offered'])/100;  
                       $amount = $allplaninfo[1]['amount'] - $amountdeducted1;
                       $responsecode = array('code'=>200,
                                            'data'=>$couponres['coupon_code'],
                                            'message'=> 'your coupon is saved successfully for this payment.',
                                            'amount'=> $amount,
                                            'plan'=>$allplaninfo[1]['plan_type_id'] ,
                                            'discount'=> $amountdeducted1,
                                            'trailend' =>   date("F j, Y", strtotime('+'.$allplaninfo[1]['trail_days'].'days'))
                            );
                   }
                   echo json_encode($responsecode);
                   exit();  
                   }else{
                     $amountdeducted1 ="";
                     if($plantype == $allplaninfo[0]['plan_type_id']){
                     $amountdeducted1 = ($allplaninfo[0]['amount']- $couponres['discount_offered']);
                     $responsecode = array('code'=>200,
                                            'data'=>$couponres['coupon_code'],
                                            'message'=> 'your coupon is saved successfully for this payment.',
                                            'amount'=> $amountdeducted1,
                                              'plan'=>$allplaninfo[0]['plan_type_id'] ,
                                            'trailend' =>   date("F j, Y", strtotime('+'.$allplaninfo[0]['trail_days'].'days')),
                                            'discount'=> $couponres['discount_offered']);
                     }else{
                      $amountdeducted1 = ($allplaninfo[1]['amount']- $couponres['discount_offered']);
                      $responsecode = array('code'=>200,
                                            'data'=>$couponres['coupon_code'],
                                            'message'=> 'your coupon is saved successfully for this payment.',
                                             'amount'=> $amountdeducted1,
                                              'plan'=>$allplaninfo[1]['plan_type_id'] ,
                                             'trailend' =>  date("F j, Y", strtotime('+'.$allplaninfo[1]['trail_days'].'days')),
                                            'discount'=> $couponres['discount_offered']
                                             );
                   }  
                  echo json_encode($responsecode);
                  exit();
                 } 
               }else{
                   $response = array('code'=>198,
                                     'message'=>'please check the coupon code, limit and its validity');
                  echo json_encode($response);
                  exit();
               }
                break;
                 //dev: priyanka varanasi
              //dated : 28/08/2015
              //desc: to make the card primary on click
                 case'makethisprimary':
                     $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
                     $user_id = $this->view->session->storage->user_id;
                     $cardid = $this->getRequest()->getParam('cardid');
                    $resultcard = $paymentcardsmodal->makeThisCardPrimary($cardid,$user_id);
               
                    if($resultcard){
                          $response = array('code'=>200,
                                     'data'=>$cardid);
                       echo json_encode($response);  
                    }else{
                          $resp = array('code'=>198,
                                     'message'=>'sorry your card is not make primary please try after sometime');
                       echo json_encode($resp);   
                    }
                      break;
                           //dev: priyanka varanasi
              //dated : 07/09/2015
              //desc: to cancel subscription
                case'cancelthissubscription':
                    
                        $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
                        $plandetailsmodal =  Application_Model_Plans::getinstance();
                          $user_id = $this->view->session->storage->user_id;
                        $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
                        $couponsModal  =  Application_Model_Coupons::getinstance();
                        $result = $paymentnewModal->getUserPaymentInfo($user_id);
                        
                        $delthis = $this->getRequest()->getParam('delthis');
                        if($delthis ==='1'){
                         $user_id = $this->view->session->storage->user_id;
                         $sub['autopayment'] = 0;
                         $update   = $paymentnewModal->cancelThisSubscription($user_id,$sub);
                         if($update){
                             $res = array(
                                 'code'=>200,
                                 'data' => 'you have canceled your subscription successfully',
                                 'date' => date("F j, Y", strtotime($result['subscription_end'])),
                                 'customerstatus'=>$result['customer_status']
                             );
                             echo json_encode($res);
                         }
                        }
                    
                    break;
                    
                                      //dev: priyanka varanasi
              //dated : 07/09/2015
              //desc: to reactivate subscription
                  case 'reactivatethissubscription':
                     $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
                     $plandetailsmodal =  Application_Model_Plans::getinstance();
                     $user_id = $this->view->session->storage->user_id;
                     $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
                     $couponsModal  =  Application_Model_Coupons::getinstance();
                     $result = $paymentnewModal->getUserPaymentInfo($user_id);
                        
                        $delthis = $this->getRequest()->getParam('user_id');
                        $sub['autopayment'] = 1;
                        $update   = $paymentnewModal->cancelThisSubscription($user_id,$sub);
                         if($update){
                           $res = array(
                                 'code'=>200,
                                 'data' => 'you have successfully reactivated account',
                                
                             );
                          echo json_encode($res);  
                         }
                      break;
                      
             //dev: priyanka varanasi
              //dated : 07/09/2015
              //desc: to change plan        
           case 'getplandetails';
               $plandetailsmodal =  Application_Model_Plans::getinstance();
               $allplaninfo = $plandetailsmodal->getAllPlanDetails();
               
               $plan1 = $allplaninfo[0];
               $plan2 = $allplaninfo[1];
               $plan = $this->getRequest()->getParam('plandet');
           
               if(!empty($plan) && $plan!=0){
                  
                   if($plan === $plan1['plan_type_id']){
                    $res = array(
                                 'code'=>200,
                                 'data' => $plan2,
                                 
                                
                             );
                          echo json_encode($res);  
                          die();
                   }else{
                       $res = array(
                                 'code'=>200,
                                 'data' => $plan1,
                                
                             );
                          echo json_encode($res);
                          die();
                  }
                }
               break;
                                 
             //dev: priyanka varanasi
              //dated : 07/09/2015
              //desc: to update  plan   
                 case 'updateplan':
                     $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
                     $plandetailsmodal =  Application_Model_Plans::getinstance();
                     $user_id = $this->view->session->storage->user_id;
                     $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
                     $couponsModal  =  Application_Model_Coupons::getinstance();
                     $result = $paymentnewModal->getUserPaymentInfo($user_id);
                        
                        $planid = $this->getRequest()->getParam('plandetails');
                        $sub['plan_type'] = $planid;
                        $update   = $paymentnewModal->cancelThisSubscription($user_id,$sub);
                         if($update){
                           $res = array(
                                 'code'=>200,
                                 'data' => 'you have successfully switched to another plan',
                                'datamessage'=> $sub['plan_type'],
                                
                             );
                          echo json_encode($res);  
                         }
                      break;
               
               
            }
        }
    }
    
    
    
    
    
    ///////////////////////////////////CARD PAYMENTS/////////////////////////////////////////////////////   
 
    //dev: priyanka varanasi
    //dated: 20/8/2015
     /****** desc: This action performs******/
            // 1.stores  card details in pagar for future charges, return card id as response
            // 2.stores  card data in db  using card id 
            // 3.stores the coupon data if user enters coupon code 
            // 4. amount calculation on the basis of coupon code and enters in to db and plan which users take
            // 5. subtracts the coupon limit 
    
 public function pagarTransactionAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $user_id = $this->view->session->storage->user_id;
        $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
        $plandetailsmodal =  Application_Model_Plans::getinstance();
        $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
        $couponsModal  =  Application_Model_Coupons::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $objUserModel = Application_Model_Users::getinstance();
        $userinfo = $objUserModel->getUserDetail($user_id);
        $response = $paymentnewModal->getAllPaymentDetails();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan1 = $allplaninfo[0];
        $plan2 = $allplaninfo[1];
        $userpayinfo = $paymentnewModal->getUserPaymentInfo($user_id);
        $cardhash = $this->getRequest()->getParam('cardcode'); 
        $couponcode = $this->getRequest()->getParam('couponcode'); 
        $plan = $this->getRequest()->getParam('plan'); 

            $data["api_key"] = $apikey;
            $data["card_hash"] = $cardhash;
      try {
               $params["url"]='https://api.pagar.me/1/cards';
               $params["parameters"]= $data;        
               $params["method"]="POST";        
               $rs = new RestClient($params);
                $result =  $rs->run();

               if($result['code']=== 200){
                  
                $pagarinfo = (array)json_decode($result['body']);
               if($userpayinfo['new_user'] == 1){
                     $cardinfo['pagar_id'] = $pagarinfo['id'];
                     $cardinfo['user_id'] = $user_id;
                     $cardinfo['card_firstdigit'] = $pagarinfo['first_digits'];
                     $cardinfo['card_lastdigits'] = $pagarinfo['last_digits'];
                     $cardinfo['date'] = $pagarinfo['date_created'];
                     $cardinfo['brand'] = $pagarinfo['brand'];
                     $cardinfo['primary'] = 1;
               $insertedpagar = $paymentcardsmodal->insertPagarCardInfo($cardinfo); 
              if($insertedpagar){
             if($plan == $plan1['plan_type_id']){
                    
               if($couponcode){
                    
                     $couponres =  $couponsModal->checkCouponCode($couponcode);
                     if($couponres){
                         $amount =0;
                         $actualamount=0;
                  if($couponres['discount_type']==0){
                     $amount = ($plan1['amount']*$couponres['discount_offered'])/100;
                     $actualamount = $plan1['amount'] - $amount;
                   }else{
                     $actualamount = ($plan1['amount'] - $couponres['discount_offered']);
                       }
                  if($actualamount==0){
                     $paymentnewdetails['discounted_val'] = $actualamount ;
                     $paymentnewdetails['customer_status'] = 9 ;
                     $paymentnewdetails['plan_type'] =  $plan;
                     $paymentnewdetails['new_user'] = 0 ;
                     $paymentnewdetails['payment_type'] = 1 ;
                     if($couponres['coupon_type']==0){
                     $paymentnewdetails['discounted'] = 0 ;
                     }else{
                       $paymentnewdetails['discounted'] = 1 ;   
                     }
                     $paymentnewdetails['couponcode'] = $couponres['coupon_code'];
                     $paymentnewdetails['subscription_start']= date('Y-m-d');
                     $paymentnewdetails['subscription_end']= date('Y-m-d', strtotime('+'.$plan1['subscription_period'].'days'));   
                    
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);  
                     $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);
                     
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Congratulations you have won a coupon of full discount and now you can enjoy the services with free of cost till".date("F j, Y", strtotime($paymentnewdetails['subscription_end'])),
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                     }else{
                     $paymentnewdetails['discounted_val'] =  $actualamount;
                     $paymentnewdetails['customer_status'] =2 ;
                     $paymentnewdetails['new_user'] =0 ;
                     $paymentnewdetails['payment_type'] = 1 ;
                       if($couponres['coupon_type']==0){
                     $paymentnewdetails['discounted'] = 0 ;
                     }else{
                       $paymentnewdetails['discounted'] = 1 ;   
                     }
                     $paymentnewdetails['couponcode'] = $couponres['coupon_code'];
                     $paymentnewdetails['trail_start'] =  date('Y-m-d');
                     $paymentnewdetails['plan_type'] =  $plan;
                     $paymentnewdetails['trail_end'] =  date('Y-m-d', strtotime('+'.$plan1['trail_days'].'days'));
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);  
                     $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);
                    $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => " Congratulations..! your have successfully subscribed to fashionlearn and now you can enjoy services with 14days free trail ",
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                       } }
                 
                 }else{
                     $paymentnewdetails['new_user'] =0 ;
                     $paymentnewdetails['discounted_val'] =  $plan1['amount'];
                     $paymentnewdetails['payment_type'] = 1 ;
                     $paymentnewdetails['customer_status'] = 2 ;
                     $paymentnewdetails['trail_start'] =  date('Y-m-d');
                     $paymentnewdetails['plan_type'] =  $plan;
                     $paymentnewdetails['trail_end'] =  date('Y-m-d', strtotime('+'.$plan1['trail_days'].'days'));
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);
                      $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => " Congratulations..! your have successfully subscribed to fashionlearn and now you can enjoy services with 14days free trail ",
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                      }
               }else{
                  if($couponcode){
                   $couponres =  $couponsModal->checkCouponCode($couponcode);
                   if($couponres){
                      $amount =0;
                      $actualamount=0;
                    if($couponres['discount_type']==0){
                     $amount = ($plan2['amount']* $couponres['discount_offered'])/100;
                     $actualamount = $plan2['amount'] - $amount;
                   }else{
                     $actualamount = ($plan2['amount'] - $couponres['discount_offered']);
                      }
                      
                     if($actualamount==0){
                     $paymentnewdetails['discounted_val'] = $actualamount ;
                     $paymentnewdetails['customer_status'] =9 ;
                     $paymentnewdetails['plan_type'] =  $plan;
                     $paymentnewdetails['new_user'] =0 ;
                          if($couponres['coupon_type']==0){
                     $paymentnewdetails['discounted'] = 0 ;
                     }else{
                      $paymentnewdetails['discounted'] = 1 ;   
                     }
                     $paymentnewdetails['couponcode'] = $couponres['coupon_code'];
                     $paymentnewdetails['subscription_start']= date('Y-m-d');
                     $paymentnewdetails['subscription_end']= date('Y-m-d', strtotime('+'.$plan2['subscription_period'].'days'));   
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);  
                     $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);  
                      
                      $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Congratulations you have won a coupon of full discount and now you can enjoy the services with free of cost till".date("F j, Y", strtotime($paymentnewdetails['subscription_end'])),
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                     
                     
                     }else{ 
                    $paymentnewdetails['discounted_val'] =  $actualamount;
                    $paymentnewdetails['customer_status'] = 2 ;
                    $paymentnewdetails['new_user'] = 0 ;
                    $paymentnewdetails['payment_type'] =1 ;
                    if($couponres['coupon_type']==0){
                     $paymentnewdetails['discounted'] = 0 ;
                     }else{
                       $paymentnewdetails['discounted'] = 1 ;   
                     }
                    $paymentnewdetails['couponcode'] = $couponres['coupon_code'];
                    $paymentnewdetails['trail_start'] =  date('Y-m-d');
                    $paymentnewdetails['plan_type'] =  $plan;
                    $paymentnewdetails['trail_end'] =  date('Y-m-d', strtotime('+'.$plan2['trail_days'].'days')); 
                $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);
                $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);
                
                
                $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => " Congratulations..! your have successfully subscribed to fashionlearn and now you can enjoy services with 14days free trail ",
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                
                
                }
                  } } else{
                    $paymentnewdetails['customer_status'] =2 ;
                    $paymentnewdetails['discounted_val'] =  $plan2['amount'];
                    $paymentnewdetails['new_user'] = 0 ;
                    $paymentnewdetails['payment_type'] =1 ;
                    $paymentnewdetails['trail_start'] =  date('Y-m-d');
                    $paymentnewdetails['plan_type'] =  $plan;
                    $paymentnewdetails['trail_end'] =  date('Y-m-d', strtotime('+'.$plan2['trail_days'].'days'));    
                  $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);  
                  $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => " Congratulations..! your have successfully subscribed to fashionlearn and now you can enjoy services with 14days free trail ",
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                  
                  
                  }
              }
                 $success = array(
                    'code' => 200,
                    'message' => "Your transaction is successful please refer your email ");
                echo json_encode($success); 
                exit();
               }else{
                 
                   $error = array(
                    'code' => 197,
                    'message' => "Sorry ..! Error occurs while saving card details please try later. please check the card has already been used  ");
                echo json_encode($error);
                exit();
               }
            }else{
                ///////////already existed user , wriiten functionality seperatly, throwing user error messages///////////
                  $error = array(
                    'code' => 197,
                    'message' => "sorry you are not new user to this site , please check the credentials prefectly");
                echo json_encode($error);
                exit();
               }
             }else {
                  
                  $failure =  array(
                    'code' => 400,
                    'message' => "Sorry..! your transaction has been failed, please check your card details correctly and enter "
                ); 
                echo json_encode($failure);
               }
              } catch (Exception $e) {
                
            }
    }
    
    
 ///////////////////////////////////BOLETO PAYMENTS/////////////////////////////////////////////////////   
    
    
      //dev: priyanka varanasi
    //dated: 28/8/2015
     /****** desc: This action performs******/
            // 1.stores  boleto details in pagar for future charges
            // 2.update the transaction details in return response 
            // 3.stores the coupon data if user enters coupon code 
            // 4. amount calculation on the basis of coupon code and enters in to db and plan which users take
            // 5. subtracts the coupon limit
   
 public function pagarboletoUserpaymentAction()
 {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $host = $realobj->hostLink;
        $user_id = $this->view->session->storage->user_id;
        $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $plandetailsmodal =  Application_Model_Plans::getinstance();
        $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
        $paymentboletomodal =  Application_Model_PaymentBoleto::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $couponsModal  =  Application_Model_Coupons::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $userinfo = $objUserModel->getUserDetail($user_id);
        $response = $paymentnewModal->getAllPaymentDetails();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan1 = $allplaninfo[0];
        $plan2 = $allplaninfo[1]; 
        $currentdate = date('Y-m-d');
        $userpayinfo = $paymentnewModal->getUserPaymentInfo($user_id);
        
        $couponcode = $this->getRequest()->getParam('couponcode');
        $plan = $this->getRequest()->getParam('plan'); 
        $address['name'] = $this->getRequest()->getParam('boletoname'); 
        $address['street_name'] = $this->getRequest()->getParam('boletostreetname'); 
        $address['street_number'] = $this->getRequest()->getParam('boletostreetnumber'); 
        $address['neighborhood'] = $this->getRequest()->getParam('boletoneighborhood'); 
        $address['zip_code'] = $this->getRequest()->getParam('boletozipcode'); 
        $address['phone_ddd'] = $this->getRequest()->getParam('boletoddd');
        $address['phone_no'] = $this->getRequest()->getParam('boletonumber');
        $address['state'] = $this->getRequest()->getParam('boletostate');
        $address['cpf'] = $this->getRequest()->getParam('boletocpf');
        $address['city'] = $this->getRequest()->getParam('boletocity');
        $address['user_id'] = $user_id;
        $address['add_date'] = date('Y-m-d');
        
       
        $paymentnewdetails = array();
          if($userpayinfo['new_user'] == 1){
             if($plan == $plan1['plan_type_id']){
                   $amount =0;
                   $actualamount=0;
               if($couponcode){
                     $couponres =  $couponsModal->checkCouponCode($couponcode);
                     if($couponres){
                        
                         if($couponres['discount_type']==0){
                     $amount = ($plan1['amount']* $couponres['discount_offered'])/100;
                     $actualamount = $plan1['amount'] - $amount;
                     
                         }else{
                     $actualamount = ($plan1['amount'] - $couponres['discount_offered']);
                       }
                          if($couponres['coupon_type']==0){
                     $paymentnewdetails['discounted'] = 0 ;
                     }else{
                       $paymentnewdetails['discounted'] = 1 ;   
                     }
                       $paymentnewdetails['couponcode'] = $couponres['coupon_code'];
                       $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);// this will update the coupon limit in coupon table  
               } }else{
                 $actualamount = $plan1['amount'];    
                }
                
                
                if($actualamount==0){
                     $paymentnewdetails['discounted_val'] = $actualamount ;
                     $paymentnewdetails['customer_status'] = 9 ;
                     $paymentnewdetails['plan_type'] =  $plan;
                     $paymentnewdetails['payment_type'] =  2;
                     $paymentnewdetails['new_user'] =0 ;
                     $paymentnewdetails['subscription_start']= date('Y-m-d');
                     $paymentnewdetails['subscription_end']= date('Y-m-d', strtotime('+'.$plan1['subscription_period'].'days'));   
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);  
                     $boletoaddress = $paymentboletomodal->insertUserBoletoInfo($address);     
                    $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Congratulations you have won a coupon of full discount and now you can enjoy the services with free of cost till".date("F j, Y", strtotime($paymentnewdetails['subscription_end'])),
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                     $success = array(
                    'code' => 200,
                    'message' => "Your Transaction is Successfull");
                echo json_encode($success); 
                die();
                }else{
                  try {
                    
                    $data["api_key"] = $apikey;
                    $data["amount"] = $actualamount *100;
                    $data["payment_method"] = "boleto";
                    $data['postback_url']= $host.'/postbackboleto-url/'.$user_id;
                    $data["boleto_expiration_date"] = date('m-d-Y', strtotime('+'.$plan1['trail_days'].'days'));
                    $data['customer']=array(
            "name" => $address['name'],
            "document_number" =>$address['cpf'],
            "email" => $userpayinfo['email'],
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
                        $boletoaddress = $paymentboletomodal->insertUserBoletoInfo($address);//this line will insert the address in address table
                        $ndata = array();
                        $ndata["user_id"] = $user_id;
                        $ndata["transaction_id"] = $body["id"];
                        $ndata["ip"] = $body["ip"];
                        $ndata["amount"] = $body["amount"]/100;
                        $ndata["status"] = $body["status"];
                        $ndata["status_reason"] = $body["status_reason"];
                        $ndata["transaction_date"] = date('Y-m-d');
                        $ndata["plantype"] = $plan1['plan_type_id'];
                        $ndata["boleto_url"] = $body['boleto_url'];
                        $ndata["boleto_barcode"] = $body['boleto_barcode'];
                        $ndata["boleto_expdate"] = $body['boleto_expiration_date'];
                        $ndata["pay_type"] = 2;
                        $ndata["address_id"] = $boletoaddress ;
                     $res=$transmodel->insertUserTransactionsInfo($ndata);///////here it will insert the transaction details
                        
                     $paymentnewdetails['discounted_val'] =   $actualamount;
                     $paymentnewdetails['customer_status'] =2 ;
                     $paymentnewdetails['transaction_no'] = $body["id"];
                     $paymentnewdetails['new_user'] = 0 ;
                     $paymentnewdetails['payment_type'] = 2 ;
                     $paymentnewdetails['trail_start'] =  date('Y-m-d');
                     $paymentnewdetails['plan_type'] =  $plan;
                     $paymentnewdetails['trail_end'] =  date('Y-m-d', strtotime('+'.$plan1['trail_days'].'days'));
                     $paymentnewdetails['paid_status']= $body["status"];
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);  // update the details in payment new table
                    
                  
                      
                        $mailer = Engine_Mailer_Mailer::getInstance();
                        $template_name = 'Blank-transaction';
                 
                        $name = "test";
                        $email =  $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                              array(
                                'name' => 'text',
                                'content' => "payment success"
                            ),
                            
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
                                'content' =>"Please make payment on or before  your boleto expiration date "
                                
                            )
                        );

                   $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                   $success = array(
                    'code' => 200,
                    'message' => "Your Transaction is successfull please refer your mail for payment details");
                echo json_encode($success); 
                    die();
                    } else {
                       
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "payment failure"
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                    
                    $success = array(
                    'code' => 198,
                    'message' => "Your Transaction is failed please check the boleto details you have entered");
                echo json_encode($success);  
                    }
                } catch (Exception $e) {
                    
                }
             } }else{
                  $amount =0;
                  $actualamount=0;
                 if($couponcode){
                     $couponres =  $couponsModal->checkCouponCode($couponcode);
                     if($couponres){
                        if($couponres['discount_type']==0){
                     $amount = ($plan2['amount']* $couponres['discount_offered'])/100;
                     $actualamount = $plan2['amount'] - $amount;
                     
                         }else{
                     $actualamount = ($plan2['amount'] - $couponres['discount_offered']);
                       }
                           if($couponres['coupon_type']==0){
                     $paymentnewdetails['discounted'] = 0 ;
                     }else{
                       $paymentnewdetails['discounted'] = 1 ;   
                     }
                       $paymentnewdetails['couponcode'] = $couponres['coupon_code'];
                       $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);// this will update the coupon limit in coupon table  
               } }else{
                 $actualamount = $plan2['amount'];   
                }
                
                if($actualamount==0){
                     $paymentnewdetails['discounted_val'] = $actualamount ;
                     $paymentnewdetails['customer_status'] =9 ;
                     $paymentnewdetails['plan_type'] =  $plan;
                     $paymentnewdetails['new_user'] =0 ;
                     $paymentnewdetails['subscription_start']= date('Y-m-d');
                     $paymentnewdetails['subscription_end']= date('Y-m-d', strtotime('+'.$plan2['subscription_period'].'days'));   
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails); 
                     $boletoaddress = $paymentboletomodal->insertUserBoletoInfo($address); 
                     
                     $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Congratulations you have won a coupon of full discount and now you can enjoy the services with free of cost till".date("F j, Y", strtotime($paymentnewdetails['subscription_end'])),
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                     $success = array(
                    'code' => 200,
                    'message' => "Your Transaction is Successfull");
                echo json_encode($success); 
                die();
                    
                }else{
                
                      try {
                    $data["api_key"] = $apikey;
                    $data["amount"] = $actualamount*100;
                    $data["payment_method"] = "boleto";
                    $data['postback_url']= $host.'/postbackboleto-url/'.$user_id;
                    $data["boleto_expiration_date"] = date('m-d-Y', strtotime('+'.$plan2['trail_days'].'days'));
                    $data['customer']=array(
            "name" => $address['name'],
            "document_number" =>$address['cpf'],
            "email" => $userpayinfo['email'],
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
                        $boletoaddress = $paymentboletomodal->insertUserBoletoInfo($address);//this line will insert the address in address table
                        $ndata = array();
                        $ndata["user_id"] = $user_id;
                        $ndata["transaction_id"] = $body["id"];
                        $ndata["ip"] = $body["ip"];
                        $ndata["amount"] = $body["amount"]/100;
                        $ndata["status"] = $body["status"];
                        $ndata["status_reason"] = $body["status_reason"];
                        $ndata["transaction_date"] = date('Y-m-d');
                        $ndata["plantype"] = $plan2['plan_type_id'];
                        $ndata["boleto_url"] = $body['boleto_url'];
                        $ndata["boleto_barcode"] = $body['boleto_barcode'];
                        $ndata["boleto_expdate"] = $body['boleto_expiration_date'];
                        $ndata["pay_type"] = 2;
                        $ndata["address_id"] = $boletoaddress ;
                        
                    
                      $res=$transmodel->insertUserTransactionsInfo($ndata);///////here it will insert the transaction details
                        
                     $paymentnewdetails['discounted_val'] =  $actualamount;
                     $paymentnewdetails['customer_status'] =2 ;
                     $paymentnewdetails['transaction_no'] = $body["id"];
                     $paymentnewdetails['new_user'] = 0 ;
                     $paymentnewdetails['payment_type'] =2;
                     $paymentnewdetails['trail_start'] =  date('Y-m-d');
                     $paymentnewdetails['plan_type'] =  $plan;
                     $paymentnewdetails['trail_end'] =  date('Y-m-d', strtotime('+'.$plan2['trail_days'].'days'));
                     $paymentnewdetails['paid_status']= $body["status"];
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);  // update the details in payment new table
                    
                  
                      
                        $mailer = Engine_Mailer_Mailer::getInstance();
                        $template_name = 'Blank-transaction';
                 
                        $name = "test";
                        $email =   $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                              array(
                                'name' => 'text',
                                'content' => "payment success"
                            ),
                            
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
                   $success = array(
                    'code' => 200,
                    'message' => "Your Transaction is successfull please refer your mail for payment details");
                echo json_encode($success); 
                    die();
                        
                        
                    } else {
                       
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "payment failure"
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                         $success = array(
                    'code' => 198,
                    'message' => "Your Transaction is Failed , Please check your boleto detials");
                echo json_encode($success); 
                    die();
                        
                    }
                
          }catch (Exception $e) {
                    
                }
           
             }
                
          } } else{
           ///////////already existed user , wriiten functionality seperatly, throwing user error messages///////////
                  $error = array(
                    'code' => 197,
                    'message' => "sorry you are not new user to this site , please check the credentials prefectly");
                echo json_encode($error);
                exit(); 
          
      }
      }
             
             
         //dev: priyanka varanasi
    //dated: 1/9/2015
     /****** desc: This action performs******/
            // 1.add the card data in db and also in pagar by generating the card id 
                     
  public function addnewCardprimaryAction(){
      
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $user_id = $this->view->session->storage->user_id;
        $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
        $cardhash = $this->getRequest()->getParam('cardcode');
        $cardprimary = $this->getRequest()->getParam('makeprimary');
        
            $data["api_key"] = $apikey;
            $data["card_hash"] = $cardhash;
      try {
               $params["url"]='https://api.pagar.me/1/cards';
               $params["parameters"]= $data;        
               $params["method"]="POST";        
               $rs = new RestClient($params);
                $result =  $rs->run();
            if($result['code']=== 200){
                  
                $pagarinfo = (array)json_decode($result['body']);
             
              if($cardprimary ==='primary'){
                  $dat['primary'] = 0;
               $updateresult = $paymentcardsmodal->UpdateCardsPrimarystatus($user_id,$dat);
               $cardinfo['primary'] = 1;
               }else{
                  $cardinfo['primary'] = 0;
                 }
                     $cardinfo['pagar_id'] = $pagarinfo['id'];
                     $cardinfo['user_id'] = $user_id;
                     $cardinfo['card_firstdigit'] = $pagarinfo['first_digits'];
                     $cardinfo['card_lastdigits'] = $pagarinfo['last_digits'];
                     $cardinfo['date'] = $pagarinfo['date_created'];
                     $cardinfo['brand'] = $pagarinfo['brand'];
              $insertedpagar = $paymentcardsmodal->insertPagarCardInfo($cardinfo); 
             
                if($insertedpagar){
                   $cardinfo['dat'] =  date("F j, Y", strtotime($cardinfo['date']));
                   $cardinfo['ID'] =  $insertedpagar;
                  $error = array(
                    'code' => 200,
                    'message' => "Your card has beed added successfull make it primary to access the card for payment",
                    'data'=> $cardinfo) ;
                echo json_encode($error);
                exit();
               }else{
                 
                   $error = array(
                    'code' => 197,
                    'message' => "Sorry ..! Error occurs while saving card details please try later. please check this card has already been used  ");
                echo json_encode($error);
                exit();
               }
             }else {
                  $failure =  array(
                    'code' => 400,
                    'message' => "Sorry..! your transaction has been failed, please check your card details correctly and enter "
                ); 
                echo json_encode($failure);
               }
              }catch (Exception $e) {
                
              }
      
  }   
  
            
         //dev: priyanka varanasi
    //dated: 1/9/2015
     /****** desc: This action performs******/
            // 1.delete the saved card from db 
             
      public function deleteCardAjaxHandlerAction() {
    
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
        $thrashid = $this->getRequest()->getParam('thrashid');
        $cardDeleted = $paymentcardsmodal->deleteThisCard($thrashid);
         if ($cardDeleted) {
            echo json_encode($cardDeleted);
        }
    }           

    
       //dev: priyanka varanasi
      //dated: 3/9/2015
     /****** desc: This action performs******/
            // 1.this action will perform payment to already subscribed users through predefined  credit card
    
    public function subscriptionRenewalAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $user_id = $this->view->session->storage->user_id;
        $host = $realobj->hostLink;
        $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $plandetailsmodal =  Application_Model_Plans::getinstance();
        $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
        $couponsModal  =  Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $response = $paymentnewModal->getAllPaymentDetails();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan1 = $allplaninfo[0];
        $plan2 = $allplaninfo[1];
        $currentDate = date('Y-m-d');
        $userpaymentinfo = $paymentnewModal->getUserTransPayDetails($user_id);
        $couponcode = $this->getRequest()->getParam('coupon_code');
        $discamount = $this->getRequest()->getParam('amount');
        $pay_type = $this->getRequest()->getParam('payty');
        $payin = array();
       if($couponcode){
        $couponres =  $couponsModal->checkCouponCode($couponcode);
        if($couponres){
          if($couponres['coupon_type']==0){
                      $payin['discounted'] = 0 ;
                      $payin['couponcode']= $couponcode;
                      $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);
                     }else{
                     $payin['discounted'] = 1 ;  
                     $payin['couponcode']= $couponcode;
                     $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);
                     }
        }
        }
       if($discamount==0){
                   
                     $payin['discounted_val'] = $discamount ;
                     $payin['customer_status'] = 9 ;
                     $payin['plan_type'] =  $userpaymentinfo['plan_type'];;
                     $payin['new_user'] =0 ;
                     $payin['subscription_start']= date('Y-m-d');
                     $payin['subscription_end']= date('Y-m-d', strtotime('+'.$userpaymentinfo['subscription_period'].'days'));   
                    if($userpaymentinfo['transaction_no']){
            if(($userpaymentinfo['paid_status']= "waiting_payment")){
                $trans['status']= "canceled";
          $res  =   $transmodel->updateUserTransInfo($userpaymentinfo['transaction_no'],$trans);          
                
            }}
                     
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$payin); 
                      $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpaymentinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Congratulations you have won a coupon of full discount and now you can enjoy the services with free of cost till".date("F j, Y", strtotime($paymentnewdetails['subscription_end'])),
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                     $success = array(
                    'code' => 200,
                    'message' => "Your Transaction is Successfull");
                echo json_encode($success); 
                die();
            }else{
                    try {
                    
                    $data["api_key"] = $apikey;
                    $data["amount"] = $discamount*100;
                    $data["card_id"] = $userpaymentinfo["pagar_id"];
                    $data["postback_url"] = $host.'/postback-url/'.$user_id;
                    $params["url"] = 'https://api.pagar.me/1/transactions';
                    $params["parameters"] = $data;
                    $params["method"] = "POST";
                    $rs = new RestClient($params);
                    $result = $rs->run();
                    $result = (array) $result;
                    $body = (array) json_decode($result["body"]);
                  if ($result["code"] == 200) {
           
                        if($userpaymentinfo['transaction_no']){
            if(($userpaymentinfo['paid_status']= "waiting_payment")){
                $trans['status']= "canceled";
          $res  =   $transmodel->updateUserTransInfo($userpaymentinfo['transaction_no'],$trans);          
                
            } }
                        $ndata = array();
                 
                        $ndata["user_id"] = $user_id;
                        $ndata["transaction_id"] = $body["id"];
                        $ndata["ip"] = $body["ip"];
                        $ndata["amount"] = $body["amount"]/100;
                        $ndata["status"] = $body["status"];
                        $ndata["status_reason"] = $body["status_reason"];
                        $ndata["transaction_date"] = date('Y-m-d');
                        $ndata["cardfirstdigits"] = $body['card_first_digits'];
                        $ndata["cardlastdigits"] =  $body['card_last_digits'];
                        $ndata["brand"] = $body['card_brand'];
                        $ndata["plantype"] = $userpaymentinfo['plan_type'];
                        $ndata["pay_type"] = $pay_type;
                        
                        $res=$transmodel->insertUserTransactionsInfo($ndata);
                        
                        
                        //to update in payment new table 
                        
                        $currentdate = date('Y-m-d');
                        $payin =array();
                        $payin['subscription_end']= date('Y-m-d', strtotime('+'.$userpaymentinfo['subscription_period'].'days'));   
                        $payin['subscription_start']= date('Y-m-d');
                        $payin['customer_status']= 3;
                        $payin['transaction_no']= $body["id"];
                        $payin['paid_status']= $body["status"];
                        $payin['plan_type']= $userpaymentinfo['plan_type'];
                        $payin['payment_type']= 1;
                        $payin['discounted_val']= $discamount;
                        $payin['autopayment']= 1;
                                //desc:referal code 
                                //dev: priyanka varanasi
                                ///date:13/10/2015
               $referralpaymenttableModal = Application_Model_ReferralPaymentTable::getinstance();
               $referralcommissiontableModal = Application_Model_ReferralCommissionTable::getinstance();
               $refcommission = $referralcommissiontableModal->getReferralCommissionDetails();
               $refcom1 = $refcommission[0];
               $refcom2 = $refcommission[1];
                   if($userpaymentinfo['teacherrefral'] && $userpaymentinfo['teacherrefral']!=0 ){
                  $repo  =  $referralpaymenttableModal->getReferalRowByRefferredId($userpaymentinfo['teacherrefral']);
                     if(!empty($repo)) { 
         // if a row is present for thi suser in current month then update data
                          $refdata['user_id'] = $userpaymentinfo['teacherrefral'];
                   if($userpaymentinfo['plan_type'] ==  $plan1['plan_type_id']){
                          $refdata['students_monthly'] = 1;
                   if($refcom1['commission_type']==1){
                          $refdata['amount_monthly'] = ($plan1['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                    }else{
                          $refdata['students_annually']= 1;
                       if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan2['amount'] * $refcom2['commission_value'])/100;
                     }else{
                          $refdata['amount_annually'] = $refcom2['commission_value'];
                       }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                     }
                    
                     $refdata['pay_status'] = 0;
                  
                 $referralpaymenttableModal->updateReferralPaymentInfo($repo['user_id'],$repo['ref_id'],$refdata);
                 $uinfo['teacherrefral'] = 0;
                 $paymentnewModal->updateUserPaymentInfo($userpaymentinfo['user_id'],$uinfo);
                   }else{
                    // if row is not present for user then insert data
                      $refdata['user_id'] = $userpaymentinfo['teacherrefral'];
                       if($userpaymentinfo['plan_type'] == $plan1['plan_type_id']){
                       $refdata['students_monthly'] = 1;
                       $refdata['students_annually'] = 0;
                       $refdata['amount_annually'] = 0;
                      if($refcom1['commission_type']==1){
                         $refdata['amount_monthly'] = ($plan1['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                     }else{
                          $refdata['students_annually'] = 1;
                          $refdata['students_monthly'] = 0;
                          $refdata['amount_monthly'] = 0;
                      if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan2['amount'] * $refcom2['commission_value'])/100;
                    }else{
                          $refdata['amount_annually'] = $refcom2['commission_value'];
                         }  
                     }
                     $refdata['total_earned'] = $refdata['amount_monthly']+ $refdata['amount_annually'];
                     $refdata['pay_status'] = 0;
                     $refdata['payment_date'] = date('Y-m-d');
                    
                     $referralpaymenttableModal->insertReferralPaymentInfo($refdata);
                     $uinfo['teacherrefral'] = 0;
                     $paymentnewModal->updateUserPaymentInfo($userpaymentinfo['user_id'],$uinfo); 
                     } 
                       
                    }
               
              ///////////////////////////code ends //////////////////////
                    
                    
                      $updatedresult  = $paymentnewModal->updateUserPaymentInfo($user_id,$payin);
                        $template_name = 'Blank-transaction';
                 
                        $name = "test";
                        $email = $userpaymentinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "You have successfully reactivated your services , now enjoy unlimited services"
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                
                   $success = array(
                    'code' => 200,
                    'message' => "Your transaction is successful please refer your email ");
                echo json_encode($success); 
                exit();
                    } else {
                      
                       
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $userpaymentinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "payment failure"
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                   $success = array(
                    'code' => 198,
                    'message' => "Your transaction is Failed please check the card details ");
                    echo json_encode($success); 
                     exit();
                        }
                } catch (Exception $e) {
                    
                }
            }  
            
        }
   
    
      
       //dev: priyanka varanasi
      //dated: 3/9/2015
     /****** desc: This action performs******/
    // 1.this action will perform payment to already subscribed users through credit card and boleto
    
    public function premiumReactivateAction(){
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $user_id = $this->view->session->storage->user_id;
        $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
        $plandetailsmodal =  Application_Model_Plans::getinstance();
        $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
        $couponsModal  =  Application_Model_Coupons::getinstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan1 = $allplaninfo[0];
        $plan2 = $allplaninfo[1];
        $userpaymentinfo = $paymentnewModal->getUserTransPayDetails($user_id);
       
        if($userpaymentinfo){
          
            $this->view->userpayinfo = $userpaymentinfo;
        }
        
    }
      //dev: priyanka varanasi
      //dated: 3/9/2015
     /****** desc: This action performs******/
    // 1.this action will perform payment for reactivation premium  through  boleto
    
    public function pagarboletoSubscribepaymentAction(){
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $warningdays = $realobj->warningdays;
        $user_id = $this->view->session->storage->user_id;
        $host = $realobj->hostLink;
        $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $plandetailsmodal =  Application_Model_Plans::getinstance();
        $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
        $couponsModal  =  Application_Model_Coupons::getinstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $response = $paymentnewModal->getAllPaymentDetails();
        $paymentboletomodal =  Application_Model_PaymentBoleto::getinstance();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan1 = $allplaninfo[0];
        $plan2 = $allplaninfo[1];
        $currentDate = date('d-m-Y');
        $userpaymentinfo = $paymentnewModal->getUserTransPayDetails($user_id); 
        $couponcode = $this->getRequest()->getParam('couponcode');
        $actamount = $this->getRequest()->getParam('amount'); 
        $address['name'] = $this->getRequest()->getParam('boletoname'); 
        $address['street_name'] = $this->getRequest()->getParam('boletostreetname'); 
        $address['street_number'] = $this->getRequest()->getParam('boletostreetnumber'); 
        $address['neighborhood'] = $this->getRequest()->getParam('boletoneighborhood'); 
        $address['zip_code'] = $this->getRequest()->getParam('boletozipcode'); 
        $address['phone_ddd'] = $this->getRequest()->getParam('boletoddd');
        $address['phone_no'] = $this->getRequest()->getParam('boletonumber');
        $address['state'] = $this->getRequest()->getParam('boletostate');
        $address['cpf'] = $this->getRequest()->getParam('boletocpf');
        $address['city'] = $this->getRequest()->getParam('boletocity');
        $address['user_id'] = $user_id;
        $address['add_date'] = date('Y-m-d');
      
        $couponres =  $couponsModal->checkCouponCode($couponcode);
        if($couponres){
          if($couponres['coupon_type']==0){
                     $payin['discounted'] = 0 ;
                     $payin['couponcode']= $couponcode;
                     $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);
                     }else{
                     $payin['discounted'] = 1 ;  
                     $payin['couponcode']= $couponcode;
                     $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);
                     }
        }
     if($actamount==0){
                   
                     $payin['discounted_val'] = $actamount ;
                     $payin['customer_status'] =9 ;
                     $payin['plan_type'] =  $userpaymentinfo['plan_type'];
                     $payin['payment_type'] =  2;
                     $payin['new_user'] =0 ;
                     $payin['subscription_start']= date('Y-m-d');
                     $payin['subscription_end']= date('Y-m-d', strtotime('+'.$userpaymentinfo['subscription_period'].'days'));   
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$payin); 
                     $boletoaddress = $paymentboletomodal->insertUserBoletoInfo($address);
                     $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpaymentinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Congratulations you have won a coupon of full discount and now you can enjoy the services with free of cost till".date("F j, Y", strtotime($paymentnewdetails['subscription_end'])),
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                     $success = array(
                    'code' => 200,
                    'message' => "Your Transaction is Successfull");
                echo json_encode($success); 
                die();
       }else{
           try {
                    
                    $data["api_key"] = $apikey;
                    $data["amount"] = $actamount*100;
                    $data["payment_method"] = "boleto";
                    $data['customer']=array(
            "name" => $address['name'],
            "document_number" =>$address['cpf'],
            "email" => $userpaymentinfo['email'],
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
                    $data['postback_url']= $host.'/postbackboletoreactivation-url/'.$user_id;
                    $data["boleto_expiration_date"] = date('m-d-Y', strtotime('+7days'));
                    $params["url"] = 'https://api.pagar.me/1/transactions';
                    $params["parameters"] = $data;
                    $params["method"] = "POST";
                    $rs = new RestClient($params);
                    $result = $rs->run();
                    $result = (array) $result;
                    $body = (array) json_decode($result["body"]);
                
                    if ($result["code"] == 200) {
                       $boletoaddress = $paymentboletomodal->insertUserBoletoInfo($address);//this line will insert the address in address table
                        $ndata = array();
                        $ndata["user_id"] = $user_id;
                        $ndata["transaction_id"] = $body["id"];
                        $ndata["ip"] = $body["ip"];
                        $ndata["amount"] = $body["amount"]/100;
                        $ndata["status"] = $body["status"];
                        $ndata["status_reason"] = $body["status_reason"];
                        $ndata["transaction_date"] = date('Y-m-d');
                        $ndata["plantype"] = $userpaymentinfo['plan_type'];
                        $ndata["boleto_url"] = $body['boleto_url'];
                        $ndata["boleto_barcode"] = $body['boleto_barcode'];
                        $ndata["boleto_expdate"] = $body['boleto_expiration_date'];
                        $ndata["pay_type"] = 2;
                        $ndata["address_id"] = $boletoaddress ;
                     
                      $res=$transmodel->insertUserTransactionsInfo($ndata);///////here it will insert the transaction details
                        
                     $paymentnewdetails['discounted_val'] =  $actamount;
                     $paymentnewdetails['customer_status'] =4 ;
                     $paymentnewdetails["transaction_no"] = $body["id"];
                     $paymentnewdetails['payment_type'] = 2 ;
                     $paymentnewdetails['plan_type'] =  $userpaymentinfo['plan_type'];
                     $paymentnewdetails['paid_status']= $body["status"];
                     $paymentnewdetails['autopayment']= 1;
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);  // update the details in payment new table
                     
                  
                      
                        $mailer = Engine_Mailer_Mailer::getInstance();
                        $template_name = 'Blank-transaction';
                 
                        $name = "test";
                        $email =  $userpaymentinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                              array(
                                'name' => 'text',
                                'content' => "payment success"
                            ),
                            
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
                  
                        $success = array(
                    'code' => 200,
                    'message' => "Your transaction is successful, please refer your mail");
                echo json_encode($success); 
                    die();
                        
                        
                    } else {
                       
                        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpaymentinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "payment failure"
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                     $success = array(
                    'code' => 200,
                    'message' => "Your transaction is failed , please check your boleto details");
                echo json_encode($success); 
                    die();
                        }
                } catch (Exception $e) {
                    
                }
    
             }
    }
  
      //dev: priyanka varanasi
      //dated: 3/9/2015
     /****** desc: This action performs******/
    // 1.this action will perform payment to already subscribed users through  credit card creating new card hash
    public function pagarnewcardTransactionAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        $host = $realobj->hostLink;
        $user_id = $this->view->session->storage->user_id;
        $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
        $plandetailsmodal =  Application_Model_Plans::getinstance();
        $paymentcardsmodal =  Application_Model_PaymentCards::getinstance();
        $couponsModal  =  Application_Model_Coupons::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $transmodel=Application_Model_FashionTransactions::getinstance();
        $response = $paymentnewModal->getAllPaymentDetails();
        $allplaninfo = $plandetailsmodal->getAllPlanDetails();
        $plan1 = $allplaninfo[0];
        $plan2 = $allplaninfo[1];
        $userpayinfo = $paymentnewModal->getUserTransPayDetails($user_id);
        
        $cardhash = $this->getRequest()->getParam('cardcode'); 
        $couponcode = $this->getRequest()->getParam('couponcode'); 
        $ammount = $this->getRequest()->getParam('amount'); 
       
            $data["api_key"] = $apikey;
            $data["card_hash"] = $cardhash;
        try {
               $params["url"]='https://api.pagar.me/1/cards';
               $params["parameters"]= $data;        
               $params["method"]="POST";        
               $rs = new RestClient($params);
               $result =  $rs->run();
            if($result['code']=== 200){
               $pagarinfo = (array)json_decode($result['body']);
                     $cardinfo['pagar_id'] = $pagarinfo['id'];
                     $cardinfo['user_id'] = $user_id;
                     $cardinfo['card_firstdigit'] = $pagarinfo['first_digits'];
                     $cardinfo['card_lastdigits'] = $pagarinfo['last_digits'];
                     $cardinfo['date'] = $pagarinfo['date_created'];
                     $cardinfo['brand'] = $pagarinfo['brand'];
                     $cardinfo['primary'] = 1;
              
            $insertedpagar = $paymentcardsmodal->insertPagarCardInfoWithUpdate($cardinfo,$user_id); 
           
          if($couponcode){
           $couponres =  $couponsModal->checkCouponCode($couponcode);
          if($couponres['coupon_type']==0){
                     $paymentnewdetails['discounted'] = 0 ;
                      $paymentnewdetails['couponcode']= $couponcode;
                      $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);
                     }else{
                     $paymentnewdetails['discounted'] = 1 ;  
                     $paymentnewdetails['couponcode']= $couponcode;
                     $limitcoupon =  $couponsModal->updateCouponLimit($couponres['coupon_id']);
                     }
        }
         if($ammount==0){
                 if($userpayinfo['transaction_no']){
            if(($userpayinfo['paid_status']= "waiting_payment")){
                $trans['status']= "canceled";
          $res  =   $transmodel->updateUserTransInfo($userpayinfo['transaction_no'],$trans);          
                
            } }
                     $paymentnewdetails['discounted_val'] = $ammount ;
                     $paymentnewdetails['customer_status'] =9 ;
                     $paymentnewdetails['plan_type'] =  $userpayinfo['plan_type'];
                     $paymentnewdetails['new_user'] =0 ;
                     $paymentnewdetails['subscription_start']= date('Y-m-d');
                     $paymentnewdetails['subscription_end']= date('Y-m-d', strtotime('+'.$userpayinfo['subscription_period'].'days'));   
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails); 
                      $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email =  $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "Congratulations you have won a coupon of full discount and now you can enjoy the services with free of cost till".date("F j, Y", strtotime($paymentnewdetails['subscription_end'])),
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                     $success = array(
                    'code' => 200,
                    'message' => "Your Transaction is Successfull");
                echo json_encode($success); 
                die();
            }else{
            
             $dat["api_key"] = $apikey;
                    $dat["amount"] = $ammount*100;
                    $dat["card_id"] = $pagarinfo['id'];
                    $dat["postback_url"] = $host.'/postback-url/'.$user_id;
                    $params["url"] = 'https://api.pagar.me/1/transactions';
                    $params["parameters"] = $dat;
                    $params["method"] = "POST";
                    $rs = new RestClient($params);
                    $result = $rs->run();
                   
                    $result = (array) $result;
                    $body = (array) json_decode($result["body"]);
                    
                   if ($result["code"] == 200) {
                       
                         if($userpayinfo['transaction_no']){
            if(($userpayinfo['paid_status']= "waiting_payment")){
                $trans['status'] = "canceled";
          $res  =   $transmodel->updateUserTransInfo($userpayinfo['transaction_no'],$trans);          
                
            } }
                       
                    $currentdate = date('Y-m-d');
                   
                     $paymentnewdetails['plan_type'] =  $userpayinfo['plan_type'];
                     $paymentnewdetails['transaction_no'] =  $body["id"];
                     $paymentnewdetails['payment_type'] = 1 ;
                     $paymentnewdetails['discounted_val'] =   $ammount;
                     $paymentnewdetails['customer_status'] = 3 ;
                     $paymentnewdetails['autopayment']= 1;
                     $paymentnewdetails['subscription_end']= date('Y-m-d', strtotime( $currentdate.'+'.$userpayinfo['subscription_period'].'days'));
                     $paymentnewdetails['subscription_start']= date('Y-m-d');
                     $paymentnewdetails['paid_status']= $body["status"];
                     
                     $returnresponse  = $paymentnewModal->updateUserPaymentInfo($user_id,$paymentnewdetails);  
                        $ndata["user_id"] = $user_id;
                        $ndata["transaction_id"] = $body["id"];
                        $ndata["ip"] = $body["ip"];
                        $ndata["amount"] = $body["amount"]/100;
                        $ndata["status"] = $body["status"];
                        $ndata["status_reason"] = $body["status_reason"];
                        $ndata["transaction_date"] = date('d-m-Y');
                        $ndata["cardfirstdigits"] = $body['card_first_digits'];
                        $ndata["cardlastdigits"] =  $body['card_last_digits'];
                        $ndata["brand"] = $body['card_brand'];
                        $ndata["plantype"] = $userpayinfo['plan_type'];
                        $ndata["pay_type"] = 1;
                        
                    $res=$transmodel->insertUserTransactionsInfo($ndata);
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
                   if($userpayinfo['plan_type'] ==  $plan1['plan_type_id']){
                          $refdata['students_monthly'] = 1;
                   if($refcom1['commission_type']==1){
                          $refdata['amount_monthly'] = ($plan1['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                          $refdata['total_earned'] = $refdata['amount_monthly'];
                    }else{
                          $refdata['students_annually']= 1;
                       if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan2['amount'] * $refcom2['commission_value'])/100;
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
                       if($userpayinfo['plan_type'] == $plan1['plan_type_id']){
                       $refdata['students_monthly'] = 1;
                       $refdata['students_annually'] = 0;
                       $refdata['amount_annually'] = 0;
                      if($refcom1['commission_type']==1){
                         $refdata['amount_monthly'] = ($plan1['amount'] * $refcom1['commission_value'])/100;
                    }else{
                          $refdata['amount_monthly'] = $refcom1['commission_value'];
                         }
                     }else{
                          $refdata['students_annually'] = 1;
                          $refdata['students_monthly'] = 0;
                          $refdata['amount_monthly'] = 0;
                      if($refcom2['commission_type']==1){
                          $refdata['amount_annually'] = ($plan2['amount'] * $refcom2['commission_value'])/100;
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
                                'content' => "Congratulations..! your have successfully subscribed to fashionlearn and now you can enjoy services with 14days free trail",
                            )
                        );

                   $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                   $success = array(
                    'code' => 200,
                    'message' => "Your transaction is successful please refer your email ");
                echo json_encode($success);
                die();
                   }else{
                       $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "payment failure"
                            )
                        );

                    $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");     
                   $error = array(
                    'code' => 197,
                    'message' => "Sorry ..! Error occurs while saving card details please try later. please check this card has already been used  ");
                echo json_encode($error);
                exit();
        }    
         }
             }else {
                    $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = $userpayinfo['email'];
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => "payment failure"
                            )
                        );

                  $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
                  $failure =  array(
                    'code' => 400,
                    'message' => "Sorry..! your transaction has been failed, please check your card details correctly and enter "
                ); 
                echo json_encode($failure);
               }
              } catch (Exception $e) {
                
            }  
    }

}
