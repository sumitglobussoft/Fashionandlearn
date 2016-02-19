<?php

/**
 * AdminController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class Authentication_AuthenticationController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function preDispatch() {
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

    /*
      Developer: Namrata Singh
      Action: Sign up
     * */

    public function signupAction() {
       
        $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
     //     setcookie('referalid', $randomresult);
        
        @ $reffered = $_SERVER["HTTP_REFERER"];
        $id = substr($reffered, strrpos($reffered, '=') + 1);
        

        $mailer = Engine_Mailer_Mailer::getInstance();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $objUserModel = Application_Model_Users::getinstance();
        $objSecurity = Engine_Vault_Security::getInstance();
        $objUsermetaModel = Application_Model_UsersMeta::getinstance();
        $objNotificationModel = Application_Model_Notification::getinstance();
        $gameobj = Application_Model_Usergamestats::getinstance();  
         //session_start();
      if(isset($_SESSION['referalid'])){
                    $teacher_refferal=$_SESSION['referalid'];
                }
                else {
                    $teacher_refferal=0;
                }
// --------------------------- Signup through POST------------------------------------
        if ($this->getRequest()->isPost()) {
           
            $firstname = $this->getRequest()->getPost('firstname');
            $classid = $this->getRequest()->getParam('classid');
            $lastname = $this->getRequest()->getPost('lastname');
            $email = $this->getRequest()->getPost('email');
            $password = $this->getRequest()->getPost('password');
            $data =array();
                
            if ($classid != 0) {
                if (isset($firstname) && isset($lastname) && isset($email) && isset($password)) {
               
                    $data = array('first_name' => $firstname,
                        'last_name' => $lastname,
                        'password' => sha1(md5($password)),
                        'email' => $email,
                        'status' => '1',
                        'role' => '1',
                        'teacher_refferal' => $teacher_refferal,
                        'refferal_class_id' => $classid,
                        'reg_date' => date('y/m/d')
                    );
                }
            } else {
                if (isset($firstname) && isset($lastname) && isset($email) && isset($password)) {

                    $data = array('first_name' => $firstname,
                        'last_name' => $lastname,
                        'password' => sha1(md5($password)),
                        'email' => $email,
                        'status' => '1',
                        'role' => '1',
                        'reg_date' => date('y/m/d')
                            //'isRefferal'=>0
                    );
                }
                }
             
            $insertionResult = $objUserModel->insertUser($data);
           
            if ($insertionResult) {
                
                    
                $metaData = array('user_id' => $insertionResult);
                $notifydata = array('user_id' => $insertionResult);
                $gameobj->insertuser($insertionResult);
               
                $objUsermetaModel->insertUsermeta($metaData);
                
                
                //dev:priyanka,
                // added these lines of code to insert only userid in payments new table while signup
                //dated: 25/8/2015
                
                $paydata = array('user_id' => $insertionResult,'teacherrefral'=>$teacher_refferal);
                $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
                $paymentnewModal->insertUserPaymentInfo($paydata);
                
                 ////////code ends here ///////////////
             
                $points = Application_Model_Points::getinstance();
                      $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                      $p = $points->getpointsinfo(7);
                      $objUsermetaModel->updatepoints($insertionResult, $p['points'], $p['gems']);
                $objNotificationModel->insertNotification($notifydata);
                
                // Mandrill implementation    
                
                $template_name = 'welcome_to_fashionlearn';
                $email = $email;
                $username = $this->getRequest()->getPost('first_name');
                $subject = 'Welcome Mail';
                $mergers = array(
                    array(
                        'name' => 'username',
                        'content' => $firstname
                    ),
                    array(
                        'name' => 'myaccountlink',
                        'content' => 'http://version2.fashionlearn.com.br/'
                    )
                );
                $result = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                $authStatus = $objSecurity->authenticate($email,  sha1(md5($password)));

                if ($classid != 0) {
                    $this->_redirect('/teachclass/' . $classid . '?via=referal&classid=' . $classid);
                }
                if ($authStatus) {
                    if ($id == "referal") {
                        $this->_redirect('/membership');
                    } else {
                
                        $this->view->session->storage->signup = 1;
                        $this->_redirect('/membership');
                    }
                }
            }
        }

        // --------------------------- Signup through Facebook ------------------------------------

        $objFacebookModel = Engine_Facebook_Facebookclass::getInstance();
        $url = $objFacebookModel->getLoginUrl();
        $users = Application_Model_Users::getinstance();
        $this->view->fbLogin = $url;

        if (isset($this->view->session->fbsession)) {

            $fbUserDetails = $objFacebookModel->getUserDetails();

               
            if ($fbUserDetails) {
                $email = "";
                $fbUserDetails = $fbUserDetails->asArray();
                $password = md5(strtotime(date('Y-m-d H:i:s')));
                $username = $fbUserDetails['first_name'];
                $fullname = $fbUserDetails['name'];
                $fbid = $fbUserDetails['id'];
                if (isset($fbUserDetails['email'])) {
                    $email = $fbUserDetails['email'];
                } else {

                    $email = "g!@()*(&)&*&*&*()^*&^%&$^*%^#$%";
                 }
                
                 
                 
                 
                $fbData = $objUserModel->checkFBUserExist($email,$fbid);
             
                if ($fbData) {

                    $fbupdatedata = array('fb_id' => $fbid);
                    $objUserModel->updateFBID($fbupdatedata, $fbData['user_id']);
                    $authStatus = $objSecurity->authenticate($fbData['email'], $fbData['password']);
                    $data = array('fbconnectedstatus' => 1);
                    $result = $users->fbSocialStatus($data, $fbData['user_id']);
                } else {
                    $checkUserName = $objUserModel->validateUserName($username);

                    if (!empty($checkUserName)) {

                        $username.=rand(1, 99);
                    }
//                    if ($classid != 0) {
//                        $fbinsertdata = array('first_name' => $username,
//                            // 'full_name' => $fullname,
//                            'password' => $password,
//                            'email' => $email,
//                            'fb_id' => $fbid,
//                            'status' => '1',
//                            'role' => '1',
//                            'reg_date' => date('Y-m-d'),
//                            'teacher_refferal' => 1,
//                            'refferal_class_id' => $classid
//                        );
//                    } else {
                  
                    $fbinsertdata = array('first_name' => $username,
                        // 'full_name' => $fullname,
                        'password' => $password,
                        'email' => $email,
                        'fb_id' => $fbid,
                        'status' => '1',
                        'role' => '1',
                        'reg_date' => date('y/m/d'),
                        'teacher_refferal' => $teacher_refferal
                       
                    );
//                    }
                    if ($email == "g!@()*(&)&*&*&*()^*&^%&$^*%^#$%") {
                        
                        $result = $objUserModel->getEmailBasedOnFBId($fbinsertdata['fb_id']);
                        if (!empty($result['email'])) {
                            $authStatus = $objSecurity->authenticate($result['email'], $result['password']);
                        } else {
                            $this->view->session->fbuserdetails = $fbinsertdata;
                            $this->_redirect('/facebookauth');
                        }
                      
                    }

                    $insertionResult = $objUserModel->insertUser($fbinsertdata);
                    
                    $data = array('fbconnectedstatus' => 1);
                    $result = $users->fbSocialStatus($data, $insertionResult);
                }
                if ($insertionResult) {
                     $points = Application_Model_Points::getinstance();
                          
                    $metaData = array('user_id' => $insertionResult);
                    $objUsermetaModel->insertUsermeta($metaData);
                //dev:priyanka,
                // added these lines of code to insert the data of user in payments new table while signup
                //dated: 25/8/2015
                
                $paydata = array('user_id' => $insertionResult,'teacherrefral'=>$teacher_refferal);
                $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
                $paymentnewModal->insertUserPaymentInfo($paydata);
                
                 ////////code ends here ///////////////
                
                      $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                            $p = $points->getpointsinfo(7);
                            $objUsermetaModel->updatepoints($insertionResult, $p['points'], $p['gems']);
                    $authStatus = $objSecurity->authenticate($email, $password);
                    $gameobj->insertuser($insertionResult);
                }

                if ($classid != 0) {
                    $this->_redirect('/teachclass/' . $classid . '?via=referal&classid=' . $classid);
                }
                if ($authStatus) {
                    $this->_redirect('/dashboard');
                }
            }
        } else {
            $this->_redirect($url);
        }
    }

    /* Developer:Namrata Singh
      Desc :Navigation after signup to the step1 page,
     *       where the user has to choose atleast 3 categories.
     */

    public function step1Action() {
        /* Developer:priyanka varanasi
          Desc : to show the categories frm db, and insert the selected categories in db
         */

        $objUserModel = Application_Model_Users::getinstance();
        $objSecurity = Engine_Vault_Security::getInstance();
        $objUsermetaModel = Application_Model_UsersMeta::getinstance();
        $objNotificationModel = Application_Model_Notification::getinstance();
        $objAllCategoriesModel = Application_Model_Category :: getInstance();
        $user_id = $this->view->session->storage->user_id;
//        $objPayment = Application_Model_Payment::getinstance();
        //$getStatus = $objPayment->selectMemberships($user_id);
         $points = Application_Model_Points::getinstance();
             $p = $points->getpointsinfo(6);
              if (isset($_GET["referid"])) {
                   $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points for inviting ".$this->view->session->storage->first_name;
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems for inviting ".$this->view->session->storage->first_name;
                    $messss="";
                     foreach($messs as $temps)
                     {
            $messss.="<li class='border-bottom-light'><a>".$temps."</a></li>";
        }
        $this->view->referid=$_GET["referid"];
         $this->view->noti=$messss;
                  
              }

        $allcats = $objAllCategoriesModel->getAllCats();
        if ($allcats) {
            $this->view->cats = $allcats;
        }
        if ($this->getRequest()->isPost()) {
            $kitty = $this->getRequest()->getPost('checkbox');
            $value['interested_categories'] = json_encode($kitty, true);
            $result = $objUsermetaModel->updateUsermeta($value, $user_id);

            if ($result == '1') {

                $this->_redirect('/step2');
            } else {
                $this->view->catmsg = "please tick the checkbox again and save";
            }
        }
    }

    //dev:priyanka varanasi
    //desc: to show class of a category which has high ratings and more students 

    public function step2Action() {
        $objUserModel = Application_Model_Users::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $objSecurity = Engine_Vault_Security::getInstance();
        $objUsermetaModel = Application_Model_UsersMeta::getinstance();
        $objNotificationModel = Application_Model_Notification::getinstance();
        $objAllCategoriesModel = Application_Model_Category :: getInstance();
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $objTeachingClassesModel = Application_Model_TeachingClasses::getinstance();
        $objsave = Application_Model_Myclasses::getInstance();
        $user_id = $this->view->session->storage->user_id;
        $interestedclasses = $objUsermetaModel->getinterestedcategories($user_id);
        $intclass = json_decode($interestedclasses['interested_categories'], true);
        if ($intclass) {
            $interestedclass = $objTeachingClassesModel->getClassesonIntclass($intclass);
            if (!empty($interestedclass)) {
                $count = 0;
                foreach ($interestedclass as $val) {
                    $allreview = $objClassReview->getAllReview($val['class_id']);

                    $calculatereview = $objClassReview->getCalculateReview($val['class_id']);
                    if (count($allreview) != 0) {
                        $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                    } else {
                        $classreviewpercentage = 0;
                    }
                    $interestedclass[$count]['review_per'] = $classreviewpercentage;
                    $count++;
                }
                foreach ($interestedclass as $key => $row) {
                    $value[$key] = $row['stud_count'];
                    $reviewper[$key] = $row['review_per'];
                }
                array_multisort($value, SORT_DESC, $reviewper, SORT_DESC, $interestedclass);

                foreach ($interestedclass as $key => $value) {
                    $funarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                    if ($funarray) {
                        foreach ($funarray as $value) {
                            $interestedclass[$key]['class_video_title'] = $value['class_video_title'];
                            $interestedclass[$key]['class_video_url'] = $value['class_video_url'];
                            $interestedclass[$key]['class_video_id'] = $value['class_video_id'];
                            $interestedclass[$key]['cover_image'] = $value['cover_image'];
                            $interestedclass[$key]['video_thumb_url'] = $value['video_thumb_url'];
                        }
                    }
                }
                $this->view->recommendedclass = $interestedclass;
            }
            //if classes related to the categories which is selected by the user is not present then below condition will works//
            $higlyrated = $objTeachingClassesModel->gettrendingclasses();
            if ($higlyrated) {
                $count = 0;
                foreach ($higlyrated as $val) {
                    $allreview = $objClassReview->getAllReview($val['class_id']);

                    $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                    if (count($allreview) != 0) {
                        $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                    } else {
                        $classreviewpercentage = 0;
                    }
                    $higlyrated[$count]['review_per'] = $classreviewpercentage;
                    $count++;
                }
                $value = "";
                foreach ($higlyrated as $key => $row) {
                    $value[$key] = $row['review_per'];
                }
                array_multisort($value, SORT_DESC, $higlyrated);
                foreach ($higlyrated as $key => $value) {
                    $funniestarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                    if ($funniestarray) {
                        foreach ($funniestarray as $value) {
                            $higlyrated[$key]['class_video_title'] = $value['class_video_title'];
                            $higlyrated[$key]['class_video_url'] = $value['class_video_url'];
                            $higlyrated[$key]['class_video_id'] = $value['class_video_id'];
                            $higlyrated[$key]['cover_image'] = $value['cover_image'];
                            $higlyrated[$key]['video_thumb_url'] = $value['video_thumb_url'];
                        }
                    }
                }
                $this->view->higlyrated = $higlyrated;
            }
        }
        //if user deosnt select any categories in step 1 form then this conditon will execute//
        else {
            $higlyrated = $objTeachingClassesModel->gettrendingclasses();
            if ($higlyrated) {
                $count = 0;
                foreach ($higlyrated as $val) {
                    $allreview = $objClassReview->getAllReview($val['class_id']);

                    $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                    if (count($allreview) != 0) {
                        $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                    } else {
                        $classreviewpercentage = 0;
                    }
                    $higlyrated[$count]['review_per'] = $classreviewpercentage;
                    $count++;
                }

                foreach ($higlyrated as $key => $row) {
                    $value[$key] = $row['review_per'];
                }
                array_multisort($value, SORT_DESC, $higlyrated);
                foreach ($higlyrated as $key => $value) {
                    $funniestarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                    if ($funniestarray) {
                        foreach ($funniestarray as $value) {
                            $higlyrated[$key]['class_video_title'] = $value['class_video_title'];
                            $higlyrated[$key]['class_video_url'] = $value['class_video_url'];
                            $higlyrated[$key]['class_video_id'] = $value['class_video_id'];
                            $higlyrated[$key]['cover_image'] = $value['cover_image'];
                            $higlyrated[$key]['video_thumb_url'] = $value['video_thumb_url'];
                        }
                    }
                }
                $this->view->higlyrated = $higlyrated;
            }
        }
        $saveclass = $this->getRequest()->getParam('saveclass');
        if ($saveclass) {
            $result = $objsave->updateSave($user_id, $saveclass);
        }
    }

    /**
      Developer: Namrata Singh
         Action: Sign in
           note: Ajax is being called from functions.js
     * */
    public function signinAction() {
       
             if ($this->view->auth->hasIdentity()==1) {
             
                   $this->_redirect('/dashboard');
                }
                else
                {
           
            if ($this->_request->isXmlHttpRequest()) {
               
                      
        
                $objSecurity = Engine_Vault_Security::getInstance();
                $objuse = Application_Model_Users::getInstance();
                $email = $this->getRequest()->getPost('email');
                $pass = $this->getRequest()->getPost('password');
  

                if ($email != "" && $pass != "") {
                    $authStatus = $objSecurity->authenticate($email, sha1(md5($pass)));
                  
                       
                            $this->_helper->layout->disableLayout();
                            $this->_helper->viewRenderer->setNoRender(true);

                            if ($authStatus->code == 200) {
                             
                                $response = new stdClass();
                                $response->message = 'success';
                                $response->code = 200;
                                echo json_encode($response);
                            } else if ($authStatus->code == 196) {

                                $response = new stdClass();
                                $response->message = 'Your account has been blocked. Please contact admin for further information.';
                                $response->code = 196;
                                echo json_encode($response);
                            } else if ($authStatus->code == 198) {
                                $response = new stdClass();
                                $response->message = 'Incorrect email or Password';
                                $this->view->message = 'Incorrect email or Password';
                                $response->code = 198;
                                echo json_encode($response);
                            }
                        
                    
                }
            }
                }
    }

    /**
      Developer: Namrata Singh
     * Date:12-20-2014
      Action: Auth ajax handler for validation of Email while signup
     *        and for insertion of data while signup via facebook.
     * */
    public function authAjaxHandlerAction() {
         

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $ajaxMethod = $this->getRequest()->getParam('ajaxMethod');

        if ($ajaxMethod) {

            switch ($ajaxMethod) {
// Validating Email at the time of signup 
                case 'validateEmail':
                    $userEmail = $this->getRequest()->getParam('email');
                    $objUserModel = Application_Model_Users::getinstance();
                    $response = $objUserModel->validateEmailId($userEmail);
                    if ($response) {
                        $arr = array("Email Already exist");
                        echo json_encode($arr);
                    } else {
                        echo json_encode(true);
                    }
                    break;

// Insertion of data while signing up via facebook               
                case 'facebookuserinsert' :
$request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
                    $objUserModel = Application_Model_Users::getinstance();
                    $objSecurity = Engine_Vault_Security::getInstance();
                    $name = $this->getRequest()->getParam('name');
                    $lastname = $this->getRequest()->getParam('lastname');
                    $userEmail = $this->getRequest()->getParam('email');
                    $password = $this->getRequest()->getParam('password');
                    $fbId = $this->getRequest()->getParam('fbid');
                    $gender = $this->getRequest()->getParam('gender');
                    if ($gender == "male") {
                        $gid = 0;
                    } else if ($gender == "female") {
                        $gid = 1;
                    }

                    $data['user_name'] = $name;
                    $data['first_name'] = $name;
                    $data['last_name'] = $lastname;
                    $data['email'] = $userEmail;
                    $data['password'] = sha1(md5($password));
                    $data['fb_id'] = $fbId;
                    $data['gender'] = $gid;
                    $userId = $objUserModel->insertUser($data);

                    if ($userId) {
                           //dev:priyanka,
                // added these lines of code to insert the data of user in payments new table while signup
                //dated: 25/8/2015
                
                $paydata = array('user_id' => $userId,'teacherrefral'=>$teacher_refferal);
                $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
                $paymentnewModal->insertUserPaymentInfo($paydata);
                
                 ////////code ends here ///////////////
                
                        $gameobj->insertuser($userId);
                        $authStatus = $objSecurity->authenticate($userEmail, sha1(md5($password)));
                        if ($authStatus->code == 200) {
                            echo json_encode($userId);
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Developer : Bhojraj Rawte
     * Description : destroying session at logout
     */
    public function logoutAction() {

        $this->_helper->layout->disableLayout();
        if ($this->view->auth->hasIdentity()) {

            $this->view->auth->clearIdentity();
          
            Zend_Session::destroy(true);

            $this->_redirect('/');
        } else {
            $this->_redirect('/');
        }
    }

    /**
     * Developer : Namrata Singh
     * Description : Authentication action while signing in via facebook
     */
    public function facebookauthAction() {
         $request = new Zend_Controller_Request_Http();
        setcookie("fashionsignup", 0, time() + (86400 * 30), "/");
         setcookie("fashioncount", 1, time() + (86400 * 30), "/");
         $gameobj = Application_Model_Usergamestats::getinstance(); 
        $objUserModel = Application_Model_Users::getInstance();
        $objSecurity = Engine_Vault_Security::getInstance();
        $objUserMetaModel = Application_Model_UsersMeta::getInstance();
//        echo 'test';die;
        if (isset($this->view->session->fbuserdetails)) {
            $this->view->fbData = $this->view->session->fbuserdetails;
            unset($this->view->session->fbuserdetails);
        }

        if ($this->getRequest()->isPost()) {
            $data = [];
            $data['email'] = $this->getRequest()->getParam('email');
            //$this->view->fbData['email'] -= $this->getRequest()->getParam('email');
            $data['first_name'] = $this->getRequest()->getParam('username');
            $data['last_name'] = $this->getRequest()->getParam('lastname');
            $data['first_name'] = $this->getRequest()->getParam('username');
            $data['gender'] = $this->getRequest()->getParam('gender');
            //$data['user_profile_pic'] = $this->getRequest()->getParam('profilepic');
            $data['password'] = $this->getRequest()->getParam('password');
            $data['fb_id'] = $this->getRequest()->getParam('facebookrid');
            $data['status'] = '1';
            $data['role'] = '1';
            $data['reg_date'] = date('Y-m-d');

            $username = $data['first_name'];
            $password = sha1(md5($data['password']));
//            echo "<pre>";print_r($data);die;
            $insertionResult = $objUserModel->insertUser($data);


            if ($insertionResult) {
                  $gameobj->insertuser($insertionResult);
                $metaData = array('user_id' => $insertionResult);
                $objUserMetaModel->insertUsermeta($metaData);
                
                 //dev:priyanka,
                // added these lines of code to insert the data of user in payments new table while signup
                //dated: 25/8/2015
                
                $paydata = array('user_id' => $insertionResult,'teacherrefral'=>$teacher_refferal);
                $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
                $paymentnewModal->insertUserPaymentInfo($paydata);
                
              ////////code ends here ///////////////
                
                $authStatus = $objSecurity->authenticate($data['email'], sha1(md5($password)));
                if ($authStatus) {

                    $this->_redirect('/membership');
                }
            }
        }
    }
    
    
    
    	    public function langAction() {
                $this->_helper->layout()->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);
                $response = new stdClass();
                $lang = $this->getRequest()->getParam('lang');
                setcookie('lang', $lang, time() + 3600 * 24 * 365); // Setting the cookie for 1 year
                $response->code = 100;
                echo json_encode($response);
    }
    

}
