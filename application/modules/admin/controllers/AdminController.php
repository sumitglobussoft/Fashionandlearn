<?php

/**
 * AdminController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class Admin_AdminController extends Zend_Controller_Action {

    public function init() {
    }
    public function preDispatch(){
       $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }

    /**
     * Developer : Bhojraj Rawte
     * Date : 14/11/2014
     * Description : Authenticate the admin.
     */
    public function indexAction() {
        if (isset($this->view->session->storage->role)) {
            if ($this->view->session->storage->role == '2') {
                $this->_redirect('/dashboard/admin');
            }
        } else {
            
            
          
            if ($this->_request->isPost()) {
                $method = $this->getRequest()->getPost('method');
                $username = $this->getRequest()->getPost('username');
                $password = $this->getRequest()->getPost('password');
                if (isset($username) && isset($password)&&$username!=""&&$password!="") {

                    $objSecurity = Engine_Vault_Security::getInstance();
                    $authStatus = $objSecurity->authenticate($username,sha1(md5($password)));
               
                    if ($authStatus->code == 200) {

                        if ($this->view->session->storage->role == '2') {

                            $this->_redirect('/dashboard/admin');
                        } 
                    }
                    else {

                            $this->view->error = "Invalid credentials";
                           
                        }
                }
            }
        }
    }

    public function dashboardAction() {
        
         $objss = Application_Model_Sitestatistics::getInstance();
       $this->view->daysreport=$objss->getdaysstatistics(); 
       $this->view->weeksreport=$objss->getweekssstatistics(); 
       $this->view->monthsreport=$objss->getmonthsstatistics(); 
       

        $teachingclass = Admin_Model_TeachingClasses::getInstance();
        $alldashboarddata = Admin_Model_AdminPaymentMonthly::getInstance();
        $admindata = Admin_Model_AdminData::getInstance();
        //$paiduser = Admin_Model_Payment::getInstance();
        $totalcourseresult = $teachingclass->countTotalClasses(1);
        $countofclass= $teachingclass->totalClasses();
        $totalteachercount = $teachingclass->totalTeacher();
        $objUser = Admin_Model_Users::getInstance();
        $teachingvideo = Admin_Model_TeachingClassVideo::getInstance();
        $totalteacherresult = $teachingclass->countTotalTeacher(1);
        $objProject = Admin_Model_Projects::getInstance();
        $classenroll = Admin_Model_Classenroll::getInstance();
        $totalcountprojects = $objProject->countTotalProject(1);
        $totalcountstudents = $objProject->countTotalNoOfStudents(1);
//      die('dasdas');
      $date=  date("Y/m/d");
      $date=split('/',$date);
      $year=$date[0];
      $month=$date[1]-1;
         $totalteachercount=count($totalteachercount);
      $countofproj= $objProject->getallproject();
//         print_r($countofproj); die;
    ;
      
         $this->view->countofproj=$countofproj['project_count'];
         $this->view->countofclass=$countofclass;
         $this->view->totalteachercount=$totalteachercount;
       $admindashboarddata= $alldashboarddata->currentmonth($month,$year);
       //$allmonthlyenrolled=$paiduser->currentmonth();
//       print_r($allmonthlyenrolled); 
       //$allenrolledmember=$paiduser->getPaidStudents();
       //$allenrolledmember=count($allenrolledmember);
//       $total_premium_member=$allenrolledmember;
//     echo '<pre>';  print_r($total_premium_member); die;
//       $this->view->total_premium_member=$total_premium_member;
       //$this->view->total_premium_monthly=$allmonthlyenrolled;
//       echo '<pre>';       print_r($admindashboarddata); die;
        
        $getallprojects = $objProject->getallproject();
//        print_r($allprojects); die;
        $getallprojects=$getallprojects['project_count'];
//        echo $allprojects;die;
        // Dv: priyanka varanasi added these two lines of code for fetching paid users from newly modified paymentnewtable
        // Date : 10/12/2015  
        $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
//        $paidstudent = $paiduser->countPaidStudents();
         $paidstudent = $paymentnewtable->getTotalPremiumMembers();
         
         ////////////////////////////code ends ///////////////////////////////////
//         
//         
        //$trailuser = $paiduser->gettrailusers();
        //$this->view->trailuser=$trailuser;
         $allusers=$objUser->getUsersDetails();
      $allusers=count($allusers);
//      print_r($allusers); die;
           //$freemember=   $allusers-($total_premium_member+$trailuser);
           //$this->view->freemember=$freemember;
       $studentpay=$admindata->studentPay();
     $studentpay=  $studentpay['studentpay'];
       $this->view->totalpayment=$studentpay;
        //$this->view->allstudent=$paidstudent;
        $this->view->toatalcourse = count($totalcourseresult);
        $this->view->totalteachercountresult = count($totalteacherresult);
        $this->view->totalteacherresult = count($totalcountprojects);
        $this->view->totalcountstudents = count($totalcountstudents);
//            echo'<pre>';print_r(count($totalcountstudents)); 
//            echo'<pre>';print_r(count($totalcountprojects)); die;
        $classes = $teachingclass->selectClasses();
//    echo '<pre>';    print_r($classes); die;
        if ($classes) {
            $count = 0;
            foreach ($classes as $val) {
//                $val['class_id']=161;
                
                $allstudents = $classenroll->getAllStudentFromClass($val['class_id']);
            $duration=  $teachingvideo->classLength($val['class_id']);
           
            $duration=$duration['SUM(tcv.video_duration)'];
//            print_r($duration); die;
             $classes[$count]['duration'] = $duration;
                $allprojects = $objProject->getAllClassProjects($val['class_id']);
                $getclassprojects = $objProject->getclassProjectsCount($val['class_id']);
                $allstudents = $allstudents['student_count'];
               
                $classes[$count]['allprojectcount']=count($allprojects);
                $classes[$count]['allstudents']=count($allstudents);
               
                
                $total = ($allstudents*75 + count($allprojects)*25);
                if ($total > 0) {
//                    $classstudentspercentage = ((count($allstudents) * 75));
//                    $classprojectspercentage = ((count($allprojects) * 25));
                    
                    $popularityclass = (($paidstudent['premiumtotal']*75)+($getallprojects*25));
//                      print_r($total); 
//                      print_r(' '. $popularityclass); die;
                    $popularityclass=(($total)/$popularityclass)*100;
                    $popularclass = round($popularityclass);
                  
                    $classes[$count]['popularity'] = $popularclass;
                } else {
                    $classes[$count]['popularity'] = 0;
                }
                $count++;
            }
//            echo '<pre>';  print_r($classes); die;
           
            foreach ($classes as $key => $row) {
                $value[$key] = $row['popularity'];
            }
            array_multisort($value, SORT_DESC, $classes);
        }
//       echo '<pre>'; print_r($classes); die;
        $this->view->classes = $classes;
    
        //abhishekm
        if ($this->_request->isxmlhttprequest()) {
            
        }
        
        //dev: priyanka varanasi 
        //desc: to show the values according to new payment table
        //date:25/9/2015
        $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
        $result  = $paymentnewtable->getAllTrialUsers();
        if($result){
          $this->view->tusers = count($result); 
            
        }
        
      $totalpremium =   $paymentnewtable->getTotalPremiumMembers();
      if($totalpremium){
          $this->view->ptotal = $totalpremium['premiumtotal'];
      }
      
        
      $totalfree =   $paymentnewtable->getTotalFreeMembers();
      if($totalfree){
          $this->view->ftotal = $totalfree['freetotal'];
      }
      
       $totalmonthly =   $paymentnewtable->getNoOfMonthlyPaidUsers();
      if($totalmonthly){
          $this->view->mtotal = $totalmonthly['monthlyusers'];
      }
        
      
        $totalyearly =   $paymentnewtable->getNoOfYearlyPaidUsers();
      if($totalyearly){
          $this->view->ytotal = $totalyearly['yearlyusers'];
      }
  /////////////////////////////////////code ends //////////////////      
        
    }

    //dev:priyanka varanasi 
    //desc: to change the password of admin
    public function changePasswordAction() {
        $objUserModel = Admin_Model_Users::getInstance();
        if ($this->getRequest()->isPost()) {
            $Passwordcurrent = $this->getRequest()->getPost('currentpassword');
            $Passwordnew = $this->getRequest()->getPost('newpassword');
            $Passwordconfirm = $this->getRequest()->getPost('confirmpassword');
            $user_id = $this->view->session->storage->user_id;
            $response = $objUserModel->validatePassword($user_id);
            $responsepass = $response[0];
            if (isset($Passwordnew) && isset($Passwordconfirm)) {


                if ($Passwordcurrent === $responsepass) {
                    if ($Passwordnew === $Passwordconfirm) {
                        $data = array('password' => $Passwordnew);
                        $Result = $objUserModel->changePasswordsettings($data, $user_id);
                        if ($Result) {
                            $this->view->message = "password change sucessfully";
                        }
                    }
                }
            }
        }
    }

    //dev: priyanka varanasi
    //desc: to reset the password by the admin
    public function resetMyPasswordAction() {
//      echo "<pre>"; print_r($this->getRequest()); die;
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $mailer = Engine_Mailer_Mailer::getInstance();
        $objUserModel = Admin_Model_Users::getInstance();

        $email = $this->getRequest()->getParam('email');
        if ($email != "") {
            $result = $objUserModel->validateUserEmail($email);
             
            if ($result) {
                $objCore = Engine_Core_Core::getInstance();
                $this->_appSetting = $objCore->getAppSetting();


                $userID = $result['user_id'];
                $activationKey = base64_encode($result['user_id'] . '@' . $random = mt_rand(10000000, 99999999));
                $link = 'http://' . $this->_appSetting->host . '/admin/reset/' . $activationKey;
                $objUserModel->updateActivationLink($activationKey, $userID);
                $template_name = 'Reset-password-fashionlearn';
                $username = $email;
                $subject = 'PasswordReset Mail';
                $mergers = array(
                    array(
                        'name' => 'username',
                        'content' => $username
                    ),
                    array(
                        'name' => 'passwordresetlink',
                        'content' => $link
                    )
                );
                $result = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                if($result){
                    echo json_encode("email submitted successfully , please verify your mail");
                }
            }
        }
    }

    //dev: priyanka varanasi
    //desc: to reset the password by the admin
    public function resetAction() {
        $objUserModel = Admin_Model_Users::getInstance();

        $key = $this->getRequest()->getParam('code');
        if ($key) {
            $decodeKey = base64_decode($key);
            $userId = explode('@', $decodeKey);

            $result = $objUserModel->checkActivationKey($userId[0], $key);
            if ($result) {
                $this->view->userData = $result;
            }
            if ($this->getRequest()->isPost()) {
                $newPassword = $this->getRequest()->getParam('password');
                $confPassword = $this->getRequest()->getParam('confirmpassword');
                if ($newPassword == $confPassword) {
                    $data['password'] = $newPassword;
                    $resultData = $objUserModel->changePasswordsettings($data, $userId[0]);
                    if ($resultData) {
                        $this->view->message = "successfully reset your pasword ";
                        $this->view->success = $resultData;
                        $this->_redirect('/admin');
                    }
                }
            }
        }
    }

    /**
     * Developer : Bhojraj Rawte
     * Date : 09/07/2014
     * Description : Logout admin.
     * @Todo :  
     */
    public function logoutAction() {
        $this->_helper->layout->disableLayout();
        if ($this->view->auth->hasIdentity()) {

            $this->view->auth->clearIdentity();

            Zend_Session::destroy(true);

            $this->_redirect('/admin');
        }
    }
    public function pointsandscoresAction(){
        $objpoints = Application_Model_Points::getInstance();
        $result = $objpoints->getallpointsinfo();
        $this->view->points = $result;
    }
     public function adminAjaxHandlerAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $method = $this->getRequest()->getPost('method');
        switch($method){
            case "points":
                 $points = $this->getRequest()->getPost('points');
                 $gems = $this->getRequest()->getPost('gems');
                 $pointid = $this->getRequest()->getPost('pointid');
                 $objpoints = Application_Model_Points::getInstance();
                 $result = $objpoints->updatepoints($pointid,$points,$gems);
                 echo $result;
                 die();
                break;
            case "levels":
                 $requiredpoints = $this->getRequest()->getPost('requiredpoints');
                 $levels = $this->getRequest()->getPost('levels');
                 $levelid = $this->getRequest()->getPost('levelid');
                 $objlevels = Application_Model_Levels::getInstance();
                 $result = $objlevels->updatelevels($requiredpoints,$levels,$levelid);
                 if($result){
                     if($result[1]){
                     echo $result[1] ;
                     die();
                     }else{
                         echo 1;die();
                     }}elseif($result == 0){
                         echo 1;
                         die();
                     }
                 die();
                break;
             case "levelsdelete":
                 $levelid = $this->getRequest()->getPost('levelid');
                 $objlevels = Application_Model_Levels::getInstance();
                 $result = $objlevels->deletelevel($levelid);
                 if($result){
                 echo 1;}else{echo 0;}
                 die();
                break;
            case "deleteachivements":
                 $id = $this->getRequest()->getPost('id');
                 $objlevels = Application_Model_Achievements::getInstance();
                 $result = $objlevels->deleteachivement($id);
                 if($result){
                 echo 1;}else{echo 0;}
                 die();
                break;
            case "achivements":
                $link = $this->getRequest()->getPost('link');
                $badgetitle = $this->getRequest()->getPost('badgetitle');
                $comments = $this->getRequest()->getPost('comments');
                $projects_created = $this->getRequest()->getPost('projects_created');
                $classes_completed = $this->getRequest()->getPost('classes_completed');
                $likes = $this->getRequest()->getPost('likes');
                $discussion = $this->getRequest()->getPost('discussion');
                $invite = $this->getRequest()->getPost('invite');
                $freemember = $this->getRequest()->getPost('freemember');
                $premimummember = $this->getRequest()->getPost('premimummember');
                $id = $this->getRequest()->getPost('id');
                //echo "<pre>";print_r($share ."  ".$freemember."  ".$premimummember);die();
                if($id == ""){$id = 0;}
                $data = array("likes"=>$likes,"classes_completed"=>$classes_completed,"projects_created"=>$projects_created,"comments"=>$comments,"discussion"=>$discussion,"badge_title"=>$badgetitle,"badge_link"=>$link,"invite"=>$invite,"freesignup"=>$freemember,"premiumsignup"=>$premimummember); 
                 $objachivement = Application_Model_Achievements::getInstance();
                 $result = $objachivement->updateachivements($id,$data);
                if($result){
                     if($result[1]){
                     echo $result[1] ;
                     die();
                     }else{
                         echo 1;die();
                     }}elseif($result == 0){
                         echo 1;
                         die();
                     }
                 die();
                break;
                
                 case "fashionlearnclub":
                $pic= $this->getRequest()->getPost('pic');
                $gems = $this->getRequest()->getPost('gems');
                $description = $this->getRequest()->getPost('description');
                $availablecount = $this->getRequest()->getPost('availablecount');
                $title = $this->getRequest()->getPost('title');
                $id = $this->getRequest()->getPost('id');
                if($id == ""){$id = 0;}
                $data = array("pic"=>$pic,"gems"=>$gems,"description"=>$description,"title"=>$title,"avl_count"=>$availablecount); 
                 $objachivement = Application_Model_Fashionlearnclub::getInstance();
                 $result = $objachivement->updatefashionlearnclub($id,$data);
                if($result){
                     if($result[1]){
                     echo $result[1] ;
                     die();
                     }else{
                         echo 1;die();
                     }}elseif($result == 0){
                         echo 1;
                         die();
                     }
                 die();
                break;
                
                
                
                 case "deletefashionlearnclub":
                 $id = $this->getRequest()->getPost('id');
                 $objlevels = Application_Model_Fashionlearnclub::getInstance();
                 $result = $objlevels->deletefashionlearnclub($id);
                 if($result){
                 echo 1;}else{echo 0;}
                 die();
                break;
                
                
                
                
                
                
                
                
                
                
                
                
        }
     }
     public function levelsAction(){
        $objlevels = Application_Model_Levels::getInstance();
        $result = $objlevels->getalllevelsinfo();
        $this->view->levels = $result;
    }
    public function achievementsAction(){
        $objachievements = Application_Model_Achievements::getInstance();
        $result = $objachievements->getallachievementsinfo();
        $this->view->Achievement = $result;
    }
    
     public function fashionlearnclubAction(){
         $objachievements = Application_Model_Fashionlearnclub::getInstance();
        $result = $objachievements->getall();
        $this->view->club = $result;
    }
    
    
    
    
    
    public function imageuploadhandlerAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $date = date_create();
        $date = date_timestamp_get($date);
        
        if($_FILES){
            $projectlogo = "";
            $upload = new Zend_File_Transfer();
            $upload->addValidator('Extension', false, array('jpg,png'));
            $newName = $date . '.jpg';
            $upload->addFilter('Rename', $newName);
            $files = $upload->getFileInfo();
            $errorNotify = 0;
            foreach ($files as $file => $info) {
                if (!$upload->isUploaded($file)) {
                    $errmsg = "Please select image to Upload!";
                    $errorNotify = 1;
                    continue;
                }
                if (!$upload->isValid($file)) {
                    $errmsg = "Invalid File extension. Please upload only *.jpg or *.png file";
                    $errorNotify = 1;
                    continue;
                }
            }
            if ($errorNotify == 0) {
                $path = 'assets/img/adminpic';
                if (!file_exists($path)) {
                    mkdir($path);
                    if (!file_exists($path)) {
                        die('Failed to create folders...');
                    }
                }if (file_exists($path)) {
                    $destination = getcwd() . "/" . $path;
                    $destination = str_replace('\\', "/", $destination);
                    $upload->setDestination($destination);
                    $upload->receive();
                    $projectlogo = "/".$path . "/" . $newName;
                }
                echo $projectlogo;die();
            }
        }else{
            echo "error";
        }
        
    }
     public function fashioncluborderAction(){
         $objachievements = Application_Model_Fashioncluborders::getInstance();
        $result = $objachievements->getallorders();
        $this->view->cluborder = $result;
    }

}
