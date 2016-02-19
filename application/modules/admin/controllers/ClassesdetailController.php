<?php

/**
 * ClassesdetailsController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';
require_once 'Engine/Vimeo/vimeo.php';

class  Admin_ClassesdetailController extends Zend_Controller_Action {

    public function init() {
        
    }
    public function preDispatch(){
       $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }
    //dev: priyanka varanasi 
    //desc: to  show list of classes 
    
    public function classesDetailsAction(){
        $objUserModel = Admin_Model_Users::getinstance();
        $objProject= Admin_Model_Projects::getInstance();
        $objTeachingclassModel = Admin_Model_TeachingClasses::getinstance();
        $objTeachingclassvideoModel = Admin_Model_TeachingClassVideo::getinstance();
        $classes  = $objTeachingclassModel->selectClasses();
        if ($classes) {
            $count = 0;
            foreach ($classes as $val) {
                $allstudents = $objProject->getAllStudentFromClass($val['class_id']);
                $allprojects  = $objProject->getAllClassProjects($val['class_id']);
                $total = (count($allstudents)+count($allprojects));
          if($total>0){
                $classstudentspercentage = ((count($allstudents)*70)/ 100);
                $classprojectspercentage = ((count($allprojects)*30)/ 100);
                $popularityclass = ($classstudentspercentage)+($classprojectspercentage);
                $popularclass=  round(($popularityclass * 100)/$total);
                $classes[$count]['popularity'] = $popularclass;
               }else{
                $classes[$count]['popularity'] = 0; 
             }
             $count++;
            }
               foreach ($classes as $key => $row) {
                $value[$key] = $row['popularity'];
            }
            array_multisort($value, SORT_DESC, $classes);
           } 
        
        $this->view->classes = $classes;
        }
         
       
   // dev: priyanka varanasi
  //desc: to display classes units
 public function classUnitsAction(){
      $objUserModel = Admin_Model_Users::getinstance();
      $objTeachingclassModel = Admin_Model_TeachingClasses::getinstance();
      $objTeachingclassvideoModel = Admin_Model_TeachingClassVideo::getinstance();
      $objTeachingclassvideoModel = Admin_Model_TeachingClassVideo::getinstance();
      $classunits = $this->getRequest()->getParam('cuid');
      $objTeachingClassUnit = Admin_Model_TeachingClassUnit::getinstance();
      $res = $objTeachingClassUnit->getClassUnitsIDS($classunits);
      if($res){
         $this->view->videoinfo = $res;
            }
 }
    //dev:priyanka varanasi 
    //desc: to perform various actions 
    //delete album from vimeo
  public function classAjaxHandlerAction(){
      $this->_helper->layout()->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    $objCategoryModel =  Admin_Model_Category::getInstance();
    $objCore = Engine_Core_Core::getInstance();
    $this->_appSetting = $objCore->getAppSetting();
    $objUserModel = Admin_Model_Users::getinstance();
    $objTeachingclassModel = Admin_Model_TeachingClasses::getinstance();
    $objTeachingclassvideoModel = Admin_Model_TeachingClassVideo::getinstance();
    if ($this->getRequest()->isPost()) { 
      $method = $this->getRequest()->getParam('method');
     
     
         switch ($method) {
             
          case 'classdelete':
          $classid = $this->getRequest()->getParam('Classid');
          $album = $objTeachingclassModel->selectTeachingClassesalbum($classid);
          $result = $objTeachingclassModel->selectTeachingClassesId($classid);
          $objCore = Engine_Core_Core::getInstance();
          $this->_appSetting = $objCore->getAppSetting();
         $client_id = $this->_appSetting->vimeo->consumerKey;
         $client_secret = $this->_appSetting->vimeo->consumerSecret;
         $vimeo = new Engine_Vimeo_Vimeo($client_id, $client_secret);
         $redirect_uri = 'http://skillshare.globusapps.com/teachdetails';
         $access_token = 'acb42c6cf52c6f13e9746ffc92fa7e6b';
         $vimeo->setToken($access_token);
            try{
           $response = (array)$vimeo->request('/users/28864880/albums/'.$album['album_id'], array('per_page' => 2),'DELETE');
      }catch(Exception $e){
          }
       if($response['status']){
               $ok = $objTeachingclassModel->deleteclass($classid);
        }
         if($ok){
                  echo  $ok;
                   
           }else{
                    echo "Error";
                }
           break;
           
      case 'classvideos':
                $classuid = $this->getRequest()->getParam('Classid');
                $videos = $objTeachingclassvideoModel->getclasstitlevideos($classuid);
            if($videos){
                echo json_encode($videos);
            }
           break;
           
           
           case 'approveclass':
                $classuid = $this->getRequest()->getParam('Classeid');
                $id =  $objTeachingclassModel->getstatustopublish($classuid);
                
            if($id){
                echo $id;
            }
             break;
          
              case 'Rejectclass':
                $classuid = $this->getRequest()->getParam('Classeid');
                $rid =  $objTeachingclassModel->getstatustopending($classuid);
                
            if($rid){
                echo ($rid);
            }
             break;  
             
             
             
          case 'classunitdelete':
         $classuid = $this->getRequest()->getParam('classunitid');
         $responsemsg = $objTeachingclassvideoModel->getUnitVideos($classuid);
         $objCore = Engine_Core_Core::getInstance();
         $this->_appSetting = $objCore->getAppSetting();
         $client_id = $this->_appSetting->vimeo->consumerKey;
         $client_secret = $this->_appSetting->vimeo->consumerSecret;
         $vimeo = new Engine_Vimeo_Vimeo($client_id, $client_secret);
         $redirect_uri = 'http://skillshare.globusapps.com/teachdetails';
         $access_token = 'acb42c6cf52c6f13e9746ffc92fa7e6b';
         $vimeo->setToken($access_token);
         foreach ($responsemsg as $key => $value) {
             foreach ($value as $a) {
                try{
                      $response = (array)$vimeo->request('/videos/'.$a, array('per_page' => 2),'DELETE');
                 } catch (Exception $ex) {
                   } 
             }
         }
            if( $response['status']){
                     $respn = $objTeachingclassvideoModel->deleteclassunits($classuid);
                     if($respn){
                    echo $respn;
                    return $respn;
                 }else{
                    echo "Error";
                }  
                     }
             break;
          
               case 'classunitdelete':
         $classuid = $this->getRequest()->getParam('classunitid');
         $responsemsg = $objTeachingclassvideoModel->getUnitVideos($classuid);
         $objCore = Engine_Core_Core::getInstance();
         $this->_appSetting = $objCore->getAppSetting();
         $client_id = $this->_appSetting->vimeo->consumerKey;
         $client_secret = $this->_appSetting->vimeo->consumerSecret;
         $vimeo = new Engine_Vimeo_Vimeo($client_id, $client_secret);
         $redirect_uri = 'http://skillshare.globusapps.com/teachdetails';
         $access_token = 'acb42c6cf52c6f13e9746ffc92fa7e6b';
         $vimeo->setToken($access_token);
         foreach ($responsemsg as $key => $value) {
             foreach ($value as $a) {
                try{
                      $response = (array)$vimeo->request('/videos/'.$a, array('per_page' => 2),'DELETE');
                 } catch (Exception $ex) {
                   } 
             }
         }
            if( $response['status']){
                     $respn = $objTeachingclassvideoModel->deleteclassunits($classuid);
                     if($respn){
                    echo $respn;
                    return $respn;
                 }else{
                    echo "Error";
                }  
                     }
             break;
   case 'classvideodelete':
         $classvideoid = $this->getRequest()->getParam('classvideoid');
         $objCore = Engine_Core_Core::getInstance();
         $this->_appSetting = $objCore->getAppSetting();
         $client_id = $this->_appSetting->vimeo->consumerKey;
         $client_secret = $this->_appSetting->vimeo->consumerSecret;
         $vimeo = new Engine_Vimeo_Vimeo($client_id, $client_secret);
         $redirect_uri = 'http://skillshare.globusapps.com/teachdetails';
         $access_token = 'acb42c6cf52c6f13e9746ffc92fa7e6b';
         $vimeo->setToken($access_token);
             try{
               $response = (array)$vimeo->request('/videos/'.$classvideoid, array('per_page' => 2),'DELETE');
                 } catch (Exception $ex) {
                   } 
           if( $response['status']){
                     $respn = $objTeachingclassvideoModel->deleteclassvideos($classvideoid);
                     if($respn){
                    echo $respn;
                    return $respn;
                 }else{
                    echo "Error";
                }  
                 }
             break;
          case 'assignuser':
             $userid = $this->getRequest()->getParam('ownerid');
             $classid = $this->getRequest()->getParam('classid');
             $mailid = $this->getRequest()->getParam('mailid');
             $objuser = Application_Model_Users::getInstance();
             $result = $objuser->fetchuserbyEmailId($mailid);
              $resultclass = 0 ;
              $resultfiles = 0;
              $resultunits = 0 ;
              $resultvideos = 0 ;
             if($result){
              $assignuserid = $result['user_id'];
             $objclasses = Application_Model_TeachingClasses::getInstance();
             $resultclass = $objclasses->updateunassignedClassByUserid($userid,$classid,$assignuserid);
             $objclassesfiles = Application_Model_TechingClassFile::getInstance();
             $resultfiles = $objclassesfiles->updateunassignedClassfilesByUserid($userid,$classid,$assignuserid);
             $objclassesunits = Application_Model_TeachingClassesUnit::getInstance();
             $resultunits = $objclassesunits->updateunassignedClassunitsByUserid($userid,$classid,$assignuserid);
             $objclassesvideos = Application_Model_TeachingClassVideo::getInstance();
             $resultvideos = $objclassesvideos -> updateunassignedClassvideosByUserid($userid,$classid,$assignuserid);
             $objuser->maketeacher($assignuserid);
             }else{
                 $resultclass = 0;
             }
             if($resultvideos && $resultunits && $resultfiles && $resultclass ){
                 echo 1;
             }else{
                echo 0;
             }
             die();
              break;
 
              }
    }
  }
  // dev: priyanka varanasi
  //desc: to edit the classes
  
   public function editClassAction(){
       
      $objUserModel = Admin_Model_Users::getinstance();
      $objTeachingclassModel = Admin_Model_TeachingClasses::getinstance();
      $class = $this->getRequest()->getParam('clid');
       if ($this->getRequest()->isPost()) {
            $data = array();
            $data['class_title'] = $this->getRequest()->getPost('classtitle');
            $data['class_description'] = $this->getRequest()->getPost('MyToolbar5');
            $data['class_tags'] = $this->getRequest()->getPost('classtags');
            $data['publish_status'] = $this->getRequest()->getPost('classstatus');
            $result = $objTeachingclassModel->insertclassdata($data,$class);
           if($result){
               $this->_redirect('/admin/classesdetails');
           }
        } 
        $classes  = $objTeachingclassModel->selectTeaching($class);
        if($classes){
         $this->view->classes =$classes;   
      }
    
   
}

 // dev: priyanka varanasi
  //desc: to edit classes units

 public function editClassUnitsAction(){
    $objUserModel = Admin_Model_Users::getinstance();
      $objTeachingclassModel = Admin_Model_TeachingClasses::getinstance();
        $objTeachingclassvideoModel = Admin_Model_TeachingClassVideo::getinstance();
         $objTeachingclassunitModel = Admin_Model_TeachingClassUnit::getinstance();
        $classunitid = $this->getRequest()->getParam('ecuid');
       if ($this->getRequest()->isPost()) {
            $data = array();
            $classeid = $this->getRequest()->getPost('classid');
            $det['class_unit_titile'] = $this->getRequest()->getPost('classunittitle');
            $result = $objTeachingclassunitModel->insertclassunitdata($det,$classunitid);
            if($result ){
               $this->_redirect('/admin/class-units/'.$classeid.'');
           }
        } 
       
        $ans = $objTeachingclassunitModel->getTheClassUnits($classunitid);
     if($ans){
        $this->view->unitinfo = $ans;
      }
 }
 
  // dev: priyanka varanasi
  //desc: To get the transcoding videos from vimeo
    
 public function getTranscodedVideosAction(){ 
    $this->_helper->layout()->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    $objCore = Engine_Core_Core::getInstance();
    $this->_appSetting = $objCore->getAppSetting();
    $objTeachingclassvideoModel = Admin_Model_TeachingClassVideo::getinstance();
    $objTeachingclassunitModel = Admin_Model_TeachingClassUnit::getinstance();
    $objTeachingclassModel = Admin_Model_TeachingClasses::getinstance();
    $objTeachingclassModel1 = Application_Model_TeachingClasses::getinstance();
    $client_id = $this->_appSetting->vimeo->consumerKey;
    $client_secret = $this->_appSetting->vimeo->consumerSecret;
    $vimeo = new Engine_Vimeo_Vimeo($client_id, $client_secret);
    $redirect_uri = 'http://skillshare.globusapps.com/teachdetails';
    $access_token = 'acb42c6cf52c6f13e9746ffc92fa7e6b';
    $vimeo->setToken($access_token);
    $classvideoids = $objTeachingclassvideoModel->getvideosIds();
    
   echo "################ list of videos to transcode ###########";
   echo"<pre>"; print_r($classvideoids);echo"</pre>"; 
   if($classvideoids){
   foreach($classvideoids as $key=>$val){
      $value = $val['video_id'];
       try{
      $response = (array)$vimeo->request('/videos/'.$value, array('per_page' => 2),'GET');
     
         
      } catch (Exception $e){
            
        }
       if(isset($response['body']['status'])){
          
          $res = $response['body']['status'];
         
              if($res== 'available'){
                  
                 print_r($res);echo"</br>";
                    if(isset($response['body']['files'][0]['link'])){
                     $data['class_video_url'] = $response['body']['files'][0]['link'] ;
                      }
                      if(isset($response['body']['duration'])){
                         $data['video_duration'] = $response['body']['duration']; 
                      }
                     
                                           
                    $data['video_thumb_url'] = $response['body']['pictures']['sizes'][3]['link'];
                    $data['transcode_status']= 0; 
             echo"<pre>"; print_r($data);echo"</pre>";
//                die('ds');
                    $update = $objTeachingclassvideoModel->updateTranscodeId($value,$data);
                     print_r($update);echo"</br>";
                
          if($update){
//              die('ds');
              $dat=  array('publish_status'=>0);
//              var_dump($data); die;
              $where=$val['class_id'];
              $objTeachingclassModel1->updatePublishStatus($where,$dat);
              
          }
                   }
                   
         }
        

    } 
   }
 }
 
  // dev: priyanka varanasi
  //desc: to display class videos  based on class units
 public function classUnitVideosAction(){
        $objUserModel = Admin_Model_Users::getinstance();
      $objTeachingclassModel = Admin_Model_TeachingClasses::getinstance();
      $objTeachingclassvideoModel = Admin_Model_TeachingClassVideo::getinstance();
      $objTeachingclassvideoModel = Admin_Model_TeachingClassVideo::getinstance();
      $classvideos = $this->getRequest()->getParam('ceuid');
      $objTeachingClassUnit = Admin_Model_TeachingClassUnit::getinstance();
       $res = $objTeachingclassvideoModel->getClassVideos($classvideos);
       
       if($res){
         $this->view->videos = $res;
           }
 }   

   // dev: priyanka varanasi
  //desc: to edit class vidoes info
 public function editClassVideoAction(){
     $objUserModel = Admin_Model_Users::getinstance();
     $objTeachingclassModel = Admin_Model_TeachingClasses::getinstance();
     $objTeachingclassvideoModel = Admin_Model_TeachingClassVideo::getinstance();
     $videoid = $this->getRequest()->getParam('videoid');
       if ($this->getRequest()->isPost()) {
            $data = array();
            $data['class_video_title'] = $this->getRequest()->getPost('classtitle');
            $userId = $this->getRequest()->getPost('UserId');
            $unitId = $this->getRequest()->getPost('UnitId');
            $videoName = $_FILES["myfile"]["name"];
            $videoTmpLoc = $_FILES["myfile"]["tmp_name"];
         
            if(!empty($videoName)){
            $videoNamePath = $userId . $videoName;
            $videopathAndName = "coverImages/$userId/" . $videoNamePath;
           $ext = substr($videoName, -4);
           if ($ext != ".gif" && $ext != ".png" && $ext != "" && $ext != ".jpg" && $ext != ".jpeg") {
                echo json_encode("Sorry only png,jpg,gif and jpeg format allowed");
            } else {
                if (!file_exists("coverImages/$userId")) {
                   
                   mkdir("coverImages/$userId", 0777, true);
                }
              move_uploaded_file($_FILES["myfile"]["tmp_name"], $videopathAndName);
              $data['cover_image'] = "/" . $videopathAndName;
           $result = $objTeachingclassvideoModel->updateTranscodeId($videoid,$data);
           if($result){
              $this->_redirect('/admin/class-unit-videos/'.$unitId);
           }
         }
       }
       else{
         $videos  = $objTeachingclassvideoModel->selectTeachingVideos($videoid);  
        $data['cover_image'] = $videos['cover_image'];
            $result = $objTeachingclassvideoModel->updateTranscodeId($videoid,$data);
             if($result){
              $this->_redirect('/admin/class-unit-videos/'.$unitId);   
             }
        }
       }
       
        $videos  = $objTeachingclassvideoModel->selectTeachingVideos($videoid);
        
       if($videos){
         $this->view->videoinfor =$videos;   
         }

}


 public function ordervideoAction(){
      if ($this->getRequest()->isPost()) {
          $objvideoModel = Application_Model_TeachingClassVideo::getinstance();
          $classid=$this->getRequest()->getPost('classid');
          $unitid=$this->getRequest()->getPost('unitid');
          $vidid=$this->getRequest()->getPost('vidid');
          $svidid=$this->getRequest()->getPost('svidid');
          $count=0;
          $count1=0;
          $arr=array();
          foreach ($vidid as $k)
          {
              
          
         
          
           $arr[$count1]=$objvideoModel->getclassvidid($vidid[$count1]);  
             
           
           
              
              $count1++;
          }
          
          
          
          
          foreach ($vidid as $k2)
          {
              
          
          $data['orderid']=$svidid[$count];
          
           $res=$objvideoModel->ordervideo($data,$arr[$count]);  
             
           
           
              
              $count++;
          }
          
          
      }
      echo "1";
     die();
     
     
     
     
 }
 
 public function createclassAction(){
    // unset();

 }
 public function teachdetailAction(){
        $via = 1;
        $classid = $this->getRequest()->getParam('classid');

        if (!$via && !$classid) {
            $this->_redirect('/teach');
        }
//        print_r($classid); die;
//        unset($this->view->session->storage->class_id);
        //unset($this->view->session->storage->teach);
        $objUserModel = Application_Model_Users::getinstance();
        $objClassesModel = Application_Model_Classes::getInstance();
        $teachingclassModel = Application_Model_TeachingClasses::getInstance();
        $objTeachingClassVideo = Application_Model_TeachingClassVideo::getInstance();
        $objTeachingClassUnit = Application_Model_TeachingClassesUnit::getInstance();
        $objUsermetaModel = Application_Model_UsersMeta::getInstance();
        $objAllCategoriesModel = Application_Model_Category :: getInstance();
        $objUserMetaModal = Application_Model_UsersMeta :: getInstance();
        $objFileModal = Application_Model_TechingClassFile :: getInstance();
      
        $userid = $this->view->session->storage->user_id;
        $usermetsDetail = $objUsermetaModel->getUserMetaDetail($userid);
        $this->view->payingemail = $usermetsDetail['paypal_email'];

if($classid)
        {
            
           
                   $ressss=$teachingclassModel->getUserId($classid);
            $this->view->tid=$ressss["user_id"];
          $this->view->session->storage->tid=$ressss["user_id"];
            
        }
      
        //$this->view->userperminssion = $resultperminssion['0'];

        $random = $this->getRequest()->getPost('random');
        $this->view->random = $random;
        $vclassid = $teachingclassModel->authUserid($userid, $classid);

      



        $objCore = Engine_Core_Core::getInstance();
        $this->_appSetting = $objCore->getAppSetting();
        $video_id = $this->getRequest()->getPost('video_id');

//         print_r($classid); die;
        //$classedit = $this->getRequest()->getPost('method');

        if ($video_id) {
            $objTeachingClassVideo->deleteVideo($video_id);
        }
        $this->view->session->storage->teachedit = $classid;

        $allCategories = $objAllCategoriesModel->getAllCategories();
        $this->view->AllCategories = $allCategories;
//        echo "<pre>";print_r($this->view->AllCategories);die;

        $userid = $this->view->session->storage->user_id;


//       $classid1 = $this->getRequest()->getParam('classId');

        $paypalMail = $objUserMetaModal->getUserMetaDetail($userid);
        $paypalMailId = $paypalMail['paypal_email'];
        $this->view->PaypalMail = $paypalMailId;

//print_r($classid); die;
        // Namrata; For edit functionality in teaching module
        if ($classid) {
            $unitresult = $objTeachingClassVideo->getClassUnitID($classid);

            $this->view->unitresult = $unitresult;

            $result = $teachingclassModel->getTeachClassesDetails($classid);

            $this->view->teachresult = $result;
            if (isset($this->view->teachresult)) {
//                echo $userid." ".$classid;die;
                $savedFiles = $objFileModal->getTeachingClassesFile($userid, $classid);
                $this->view->savedFiles = $savedFiles;
            }

            $this->view->session->storage->class_id = $classid;

            $this->view->session->storage->teach_id = $classid;
        }
        if (isset($this->view->session->storage->teach_id)) {
            $classid = $this->view->session->storage->teach_id;
            $result = $teachingclassModel->getClassPublishStatus($classid);
            $result = $result['publish_status'];
            $this->view->publish_status = $result;
//              print_r($result); die;
        }
        if ($this->getRequest()->isPost()) {
            $teach = $this->getRequest()->getPost('teach');

            if (empty($teach)) {
                $data = array(
                    'user_id' => $userid,
                    'class_title' => 'Untitled'
                );
                $success = $objClassesModel->insertTeachingClassesStart($data);
                $this->view->session->storage->class_id = $success;
                $this->view->teach = 'Untitled';
                $this->view->session->storage->teach_id = $success;
            } else {
                $data = array(
                    'user_id' => $userid,
                    'class_title' => $teach,
                    'publish_status' => '2'
                );
                $success = $objClassesModel->insertTeachingClassesStart($data);
                $this->view->session->storage->class_id = $success;
                $result = $teachingclassModel->getClassPublishStatus($success);
                $result = $result['publish_status'];
                $this->view->publish_status = $result;
//                print_r($this->view->session->storage->class_id); die;
                $this->view->session->storage->teach = $teach;
                $this->view->session->storage->teach_id = $success;
            }
        }
//        }
        //dev:priyankav varanasi
        //desc:vimeo integration

        $client_id = $this->_appSetting->vimeo->consumerKey;
        $client_secret = $this->_appSetting->vimeo->consumerSecret;
        $vimeo = new Engine_Vimeo_Vimeo($client_id, $client_secret);
        $redirect_uri = 'http://skillshare.globusapps.com/teachdetails';
        // $url = $vimeo->buildAuthorizationEndpoint($redirect_uri);
        //echo '<pre>'; print_r($url); die;
        //$code= '7a7a180ea5ca953a40bdb0e1dcaec007b014ce60';
        //$returnData = $this->getRequest()->getParams();
        //$this->view->session->newCode = $returnData['code'];
        //$token = $vimeo->accessToken($code,$redirect_uri);
        $access_token = 'acb42c6cf52c6f13e9746ffc92fa7e6b';
        $vimeo->setToken($access_token);
        //$result = $vimeo->request('/videos/123705136', array('per_page' => 2),'GET');
        $this->view->session->storage->user_id = $userid;

//        $notify = Application_Model_Savednotifications::getinstance();
//        $notificationresult = $notify->getNotification($userid);
//
////        $this->view->session->storage->notyfyvalue = $notyfyvalue;
//        $notification_count = count($notificationresult);
//        $this->view->session->storage->notificationresult = $notificationresult;
//        $this->view->session->storage->notification_count = $notification_count;
//        $unseennotification = $notify->seenNotificationStatus($userid);
////        print_r($unseennotification); die;
//        $unseennotification = count($unseennotification);
////        print_r($unseennotification); die;
//        $this->view->session->storage->unseennotification = $unseennotification;
    }
    public function unassignedclassesAction()
    {
            if(isset($this->session->storage->teach))
        unset($this->session->storage->teach);
         if (isset($this->view->session->storage->user_id)) {
           $userid = $this->view->session->storage->user_id;
        } 
        
       $objclasses = Application_Model_TeachingClasses::getInstance();
       $classes = $objclasses->getunassignedClassByUserid($userid);
         $objUserModel = Admin_Model_Users::getinstance();
         $i=0;
         if(isset($classes)){
        foreach($classes as $val){
           $createrid = $val['user_id'];
            $usermetsDetail = $objUserModel->getUsersDeatilsByID($createrid);
        $classes[$i]['creater_name'] = $usermetsDetail['first_name']." ".$usermetsDetail['last_name'];
        $i++;
        }
       $this->view->unassignedclass = $classes;
    }
    }
    public function invitationsAction(){
         if (isset($this->view->session->storage->user_id)) {
           $userid = $this->view->session->storage->user_id;
        } 
        
       $objinvitations = Application_Model_Invitationtable::getInstance();
       $result = $objinvitations->getallinvitations();
       $this->view->invitations = $result;
    }

}
 
