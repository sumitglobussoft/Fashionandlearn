<?php

/**
 * AdminController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';
require_once 'Engine/Vimeo/vimeo.php';
//require_once 'html2pdf/html2pdf.class.php';
require_once 'Engine/html2pdf_v4.03/html2pdf.class.php';

class Teach_TeachController extends Zend_Controller_Action {

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

            $objCore = Engine_Core_Core::getInstance();
            $realobj = $objCore->getAppSetting();
            $this->view->host = $realobj->hostLink;

//            $data=array();
//            $i=0;
//            foreach ($notifi as $key) {
//                
//                if($notifi["type"]==1)
//                {
//                $data[$i]="";
//                }f
//                
//                
//                
//                $i++;
//            }
        }
        $objCategoryModel = Application_Model_Category::getInstance();
        $allCategories = $objCategoryModel->getAllCategories();
        $this->view->AllCategories = $allCategories;

// Display the recent updated profile picture
        if (isset($this->view->session->storage->user_id)) {

            $user_id = $this->view->session->storage->user_id;

            $objUsermetaModel = Application_Model_UsersMeta::getinstance();
            $getmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);
            $this->view->profilepic = $getmetaresult['user_profile_pic'];
            //        echo "<pre>";print_r($allCategories);die;
        }
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
            try {
                return $output->data->url;
            } catch (Exception $e) {
                
            }
        }
    }

    function fbCount($uri) {
        $url = $uri;
        $base_url = "http://graph.facebook.com/?id=" . $url;
        $data = @file_get_contents($base_url);
        $data = json_decode($data);
        $data = json_decode(json_encode($data), true);
        if (!empty($data['shares'])) {
            $this->view->fbshare = $data['shares'];
        } else {
            $this->view->fbshare = 0;
        }
//        echo "<pre>";print_r($data);die;
//        $this->view->fbshare=$data['shares'];
    }

    /*
     * Developer: Ankit Singh
     * Date: 27/12/2014
     */

    public function teachAction() {
        unset($this->view->session->storage->class_id);
        $info = $this->getRequest()->getParam('teachinfo');
        if ($info) {
            $this->view->info = $info;
        }
        $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->userperminssion = $resultperminssion['0'];
    }

    public function videostatusAction() {
        $objTeachingClassVideoStatus = Application_Model_uservideostatus::getInstance();
        $objclassenroll = Application_Model_ClassEnroll::getInstance();
        if ($this->getRequest()->getPost()) {

            $videoid = $this->getRequest()->getPost('videoid');
            $classid = $this->getRequest()->getPost('classid');
            $userid = $this->view->session->storage->user_id;

            $data = array(
                'user_id' => $userid,
                'class_id' => $classid,
                'video_id' => $videoid,
                //'class_unit_id' => $videoid,
                'view_status' => 0,
                'watched_date' => gmdate('Y-m-d H:i:s', time())
            );
            $result = $objTeachingClassVideoStatus->insertUserVideoStatus($data);
            $getvideoscount = $objTeachingClassVideoStatus->getvideoscount($userid, $classid);
            $getviewedvideoscount = $objTeachingClassVideoStatus->getviewedvideoscount($userid, $classid);

            $videosviewedpercentage = ($getviewedvideoscount / $getvideoscount) * 100;
            $data = array('percentage' => $videosviewedpercentage);
            $objclassenroll->updateClassViewedPercentage($data, $classid);
            $resultArry['video_id'] = (int) $videoid;
            $resultArry['percentage'] = (int) $videosviewedpercentage;
            $resultArry['viwedcount'] = (int) $getviewedvideoscount;
            echo json_encode($resultArry);
            die;
        }
    }

    function compress_image($source_url, $destination_url, $quality) {

        $info = getimagesize($source_url);

        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source_url);

        elseif ($info['mime'] == 'image/gif')
            $image = imagecreatefromgif($source_url);

        elseif ($info['mime'] == 'image/png')
            $image = imagecreatefrompng($source_url);

        imagejpeg($image, $destination_url, $quality);
        return $destination_url;
    }

    function base64_to_jpeg($base64_string, $output_file) {
        $ifp = fopen($output_file, "wb");

        $data = explode(',', $base64_string);

        fwrite($ifp, base64_decode($data[1]));
        fclose($ifp);

        return $output_file;
    }

    /*
     * Developer: Ankit Singh
     * Date: 27/12/2014 
     */

    public function teachdetailAction() {
        $via = isset($_GET['via']);
        $classid = $this->getRequest()->getParam('classid');

        if (!$via && !$classid) {
            $this->_redirect('/teach');
        }
//        print_r($classid); die();
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
        $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $userid = $this->view->session->storage->user_id;
        $usermetsDetail = $objUsermetaModel->getUserMetaDetail($userid);
        $this->view->payingemail = $usermetsDetail['paypal_email'];



        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->userperminssion = $resultperminssion['0'];

        $random = $this->getRequest()->getPost('random');
        $this->view->random = $random;
        $vclassid = $teachingclassModel->authUserid($userid, $classid);

        if ($vclassid) {
            
        } else
            die("login with correct userid");




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

    /*
     * Developer: Ankit Singh
     * Date: 27/12/2014
     * Description: ajax handler function
     */

    public function teachAjaxHandlerAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $objClassesModel = Application_Model_Classes::getInstance();
        $teachingclassunit = Application_Model_TeachingClassesUnit::getInstance();
        $teachingclassvideo = Application_Model_TeachingClassVideo::getInstance();
        $teachingclasses = Application_Model_TeachingClasses::getInstance();
        $objUserMetaModal = Application_Model_UsersMeta :: getInstance();
        $objCategoryModel = Application_Model_Category :: getInstance();
        $objstatistics = Application_Model_Sitesettings::getInstance();
        if ($this->getRequest()->isPost()) {

            $method = $this->getRequest()->getPost('method');
            $everyoneTeach = $this->getRequest()->getPost('everyoneTeach');
            $uploadVideoEdit = $this->getRequest()->getPost('uploadVideoEdit');

            switch ($method) {
                case'updatevideoesname':
                    $class_video_ids = $this->getRequest()->getPost('class_video_id');
                    $uploadVideoEdit = $this->getRequest()->getPost('uploadVideoEdit');
//                    var_dump($class_video_ids); die;
                    $class_video_id = (int) $class_video_ids;
//                    print_r(updatevideoesname); die;
                    $data = array(
                        'class_video_title' => $uploadVideoEdit,
                    );
                    $teachingclassvideo->updateClassVideoname($data, $class_video_id);
                    break;
                case 'updateUnitname':
                    $unit_id = $this->getRequest()->getPost('unit_id');
                    $uploadVideoEdit = $this->getRequest()->getPost('uploadVideoEdit');
                    $unit_id = (int) $unit_id;
                    $data = array('class_unit_titile' => $uploadVideoEdit);
                    $teachingclassunit->updateUnitTitle($data, $unit_id);
                    break;
                case 'updateCorcesName':
                    $class_video_ids = $this->getRequest()->getPost('class_video_ids');
                    $class_video_ids = (int) $class_video_ids;
                    $uploadVideoEdit = $this->getRequest()->getPost('uploadVideoEdit');
                    $uploadVideoEdit;
                    $data = array('class_video_title' => $uploadVideoEdit);

                    $result = $teachingclassvideo->updateVideoname($data, $class_video_ids);
                    break;
                case 'updateVideoName':
                    $unit_name = $this->getRequest()->getPost('uploadVideoEdit');
                    $user_id = $this->getRequest()->getPost('user_id');
//                    print_r($this->view->session->storage->teach_id); die;

                    $class_id = $this->getRequest()->getPost('clasid');
                    //print_r($user_id); print_r($class_id); print_r($unit_name);die;
                    $data = array(
                        'user_id' => $user_id,
                        'class_id' => $class_id,
                        'class_unit_titile' => $unit_name,
                    );
                    //	print_r($data); die;
//                    print_r($user_id);                    print_r($class_id);                    print_r($user_id); die;
                    $result = $teachingclassunit->insertUnitName($data);
                    echo $result;
                    exit();
                    break;
                case 'getClassDetails':
                    $class_id = $this->getRequest()->getPost('class_id');
                    $uploadVideoEdit = $this->getRequest()->getPost('uploadVideoEdit');
                    $user_id = $this->session->storage->user_id;
                    $teachingclassunit->insertUnitName($user_id, $class_id, $uploadVideoEdit);
                    break;
                case 'getCategory':
                    $result = $objCategoryModel->getAllCategories();
                    if ($result) {
                        echo json_encode($result);
                    }
                    break;
                case 'getUnitID':
                    $result = rand(1000, 2000);
                    if ($result) {
                        echo json_encode($result);
                    }
                    break;
                case 'getUserId':
                    if (!isset($this->session->storage->user_id)) {
                        echo "/teachdetails/";
                    }
                    break;


                case 'getTeachDetails':
                    $objUserMetaModal = Application_Model_UsersMeta :: getInstance();
                    $teachingclassesModal = Application_Model_TeachingClasses :: getInstance();
                    if ($this->getRequest()->getPost()) {
//                        echo 1;die;
                        $userId = $this->getRequest()->getPost('userId');
                        $classTeachId = $this->getRequest()->getPost('classTeachId');

                        $classTeach = $this->getRequest()->getPost('classTeach');
                        $projectTitle = $this->getRequest()->getPost('projectTitle');
                        $projectDescription = $this->getRequest()->getPost('projectDescription');
                        $unitTitle = $this->getRequest()->getPost('unitTitle');
                        $moreDetailsCategory = $this->getRequest()->getPost('moreDetailsCategory');
                        $moreDetailsClassTags = $this->getRequest()->getPost('moreDetailsClassTags');
                        $moreDetailsClassDesc = $this->getRequest()->getPost('moreDetailsClassDesc');
                        $upload_image = $this->getRequest()->getPost('upload_image');
                        $uploadVideoTitleEdit = $this->getRequest()->getPost('uploadVideoTitleEdit');
                        $uploadVideoTitleEdit = rtrim($uploadVideoTitleEdit, ",");
                        $upload_link = $this->getRequest()->getPost('upload_link');
                        $uploadVideoEdit = $this->getRequest()->getPost('uploadVideoEdit');
                        $paypal = $this->getRequest()->getPost('paypal');
                        $info = $this->getRequest()->getPost('infodata');
                        $class_id = $this->getRequest()->getPost('class_id');

//                        $result=  $teachingclassesModal>getClassPublishStatus($classid);
//                        $result=$result['publish_status'];
                        //$teachingclassModel = Application_Model_TeachingClasses::getInstance();
                        $selectUserClassId = $objClassesModel->selectUserClassId($classTeachId);
//                        print_r($selectUserClassId);die;
                        if (isset($selectUserClassId)) {

                            $data = array(
                                'user_id' => $userId,
                                'assignment_project_title' => $projectTitle,
                                'assignment_project_description' => $projectDescription,
                                'class_description' => $moreDetailsClassDesc,
                                'class_tags' => $moreDetailsClassTags,
                                'category_id' => $moreDetailsCategory,
                                'class_created_date' => gmdate('Y-m-d H:i:s', time()),
                                'class_title' => $classTeach,
                                'class_description' => $moreDetailsClassDesc,
                                'class_url' => "",
                                'publish_status' => '1'
                            );
//
                            $updateClassesDetails = $objClassesModel->updateTeachingClasses($data, $classTeachId);
                            //dev:priyanka varanasi
                            //desc: to sort the videos accoding to the data-no and update them in db
                            $infodata = $this->getRequest()->getPost('infodata');

                            foreach ($infodata as $value) {
                                $sortno = array('class_no' => $value[0]);
                                $id = $value[1];
                                $infoback = $teachingclassvideo->updateTheDbBasedOnSortOrder($sortno, $id);
                            }
                            //////////////////code ends////////////////////////////         
                            if ($updateClassesDetails) {
                                $res = $objUserMetaModal->updateEamil($paypal, $userId);

//                            $data = array(
//                                'class_file_path' => "/doc/$userId/" . $upload_link,
//                                'class_id' => $classTeachId,
//                                'user_id' => $userId,
//                                'file_uploaded_date' => date('Y-m-d H:i:s')
//                            );
//                            $objTeachingClassFile = Application_Model_TechingClassFile::getInstance();
//                            $fileSuccess = $objTeachingClassFile->insertTeachingClassesFile($data);
//                            $teachingClassUnit = array(
//                                'class_id' => $classTeachId,
//                                'user_id' => $userId,
//                                'class_unit_date' => date('Y-m-d H:i:s'),
//                                'class_unit_titile' => $unitTitle
//                            );
//
//                            $objTeachingImage = Application_Model_TeachingClassesUnit::getInstance();
//                            $unitSuccess = $objTeachingImage->insertTeachingClassUnit($teachingClassUnit);
//                            $teachingClassVideo = array(
//                                'class_id' => $classTeachId,
//                                'user_id' => $userId,
//                                'class_unit_id' => $unitSuccess,
//                                'class_unit_title' => $unitTitle,
//                                'video_uploaded_date' => date('Y-m-d H:i:s'),
//                                'class_video_url' => "/videos/$userId/" . $video_lesson_file
//                            );
//                            echo json_encode($teachingClassVideo);
//                            die;
//                            $dataVideo = array(
//                                'user_id' => $userId,
//                                'class_id' => $classTeachId,
//                                'class_unit_id' => $unitSuccess,
//                                'class_video_title' => $uploadVideoEdit,
//                                'class_video_url' => $video_lesson_file_new,
//                                'video_uploaded_date' => date('Y-m-d H:i:s'),
//                                'cover_image' => 'Testing Cover Image'
//                            );
////                            echo json_encode($dataVideo);
////                            die;
//                            $objTeachingClassVideo = Application_Model_TeachingClassVideo::getInstance();
//                            $getTeachingClassVideo = $objTeachingClassVideo->insertTeachingClassesVideo($dataVideo);
                                echo json_encode($updateClassesDetails);
                                die;
                            }
                        } else {
                            echo 1;
                            die;
                        }
                    }
                    break;

                case 'followuser':
                    if ($this->getRequest()->getPost()) {

                        $followerid = $this->view->session->storage->user_id;
                        $followingid = $this->getRequest()->getPost('userid');
                        $status = $this->getRequest()->getPost('status');
                        $objfollow = Application_Model_Followers::getInstance();

                        $followresponse = $objfollow->updateFollow($followerid, $followingid, $status);

                        echo json_encode($followresponse);
                    }

                    break;
                case 'saveclass':
                    if ($this->getRequest()->getPost()) {
                        $userid = $this->view->session->storage->user_id;
                        $classid = $this->getRequest()->getPost('classid');
                        $objsave = Application_Model_Myclasses::getInstance();
                        $saveresponse = $objsave->updateSave($userid, $classid);

                        echo json_encode($saveresponse);
                    }

                    break;
                case 'createproject':
                    if ($this->getRequest()->getPost()) {
                        $userid = $this->view->session->storage->user_id;
                        $classid = $this->getRequest()->getPost('classid');
                        $projecttitle = $this->getRequest()->getPost('projecttitle');
                        $projectdescription = $this->getRequest()->getPost('desc');
                        $privacy = $this->getRequest()->getPost('privacy');
                        $coverimage = $this->getRequest()->getPost('coverimage');
                        $coverphoto = $_FILES["coverphoto"]["name"];
                        // echo $coverimage;
                        echo $coverphoto;
                        $_FILES["coverphoto"]["tmp_name"] = $this->base64_to_jpeg($coverimage, $_FILES["coverphoto"]["tmp_name"]);



                        $dirpath = 'projectimages/' . $userid . '/' . $classid . '/';


                        if (!(is_dir($dirpath))) {
                            if (!$dirpath = mkdir($dirpath, 0777, true)) {
                                die('could not create directory');
                            }
                        }



                        if (!empty($coverphoto)) {
                            $imagepath = $dirpath . $coverphoto;
                            // $imageTmpLoc = $_FILES["$img"]["tmp_name"];
                            $ext = pathinfo($coverphoto, PATHINFO_EXTENSION);
                            if ($ext != "jpg" && $ext != "png" && $ext != "jpeg" && $ext != "gif") {
                                echo json_encode("Somethig went wrong image upload");
                            } else {
//$_FILES["coverphoto"]["tmp_name"] = $this->compress_image($_FILES["coverphoto"]["name"], $_FILES["coverphoto"]["tmp_name"], 80);
//    
                                $coverphoto = $_FILES["coverphoto"]["tmp_name"];
// Run the move_uploaded_file() function here
                                $imagemoveResult = (move_uploaded_file($coverphoto, $imagepath));
                                $imagepath = "/" . $imagepath;
                                $data = array("user_id" => $userid, "class_id" => $classid, "project_title" => $projecttitle, "project_cover_image" => $imagepath, "project_workspace" => $projectdescription, "project_privacy" => $privacy, "project_created_date" => gmdate('Y-m-d H:i:s', time()));

                                $objproject = Application_Model_Projects::getInstance();
                                $saveresponse = $objproject->updateProject($userid, $classid, $data);
                                echo json_encode("successfully inserted");
                            }
                        } else {
                            // $imagemoveResult = (move_uploaded_file($_FILES["coverphoto"]["tmp_name"], $imagepath));
                            $imagepath = $coverimage;
                            $data = array("user_id" => $userid, "class_id" => $classid, "project_title" => $projecttitle, "project_cover_image" => $imagepath, "project_workspace" => $projectdescription, "project_privacy" => $privacy, "project_created_date" => gmdate('Y-m-d H:i:s', time()));
                            // print_r($data);                            
                            $objproject = Application_Model_Projects::getInstance();
                            $saveresponse = $objproject->updateProject($userid, $classid, $data);
                            echo json_encode("successfully inserted");
                        }
                    }
                    break;

                case 'saveDraftDetails':
                    if ($this->getRequest()->getPost()) {
                        $userId = $this->getRequest()->getPost('userId');
                        $classTeachId = $this->getRequest()->getPost('classTeachId');
                        $classTeach = $this->getRequest()->getPost('classTeach');
                        $projectTitle = $this->getRequest()->getPost('projectTitle');
                        $projectDescription = $this->getRequest()->getPost('projectDescription');
                        $unitTitle = $this->getRequest()->getPost('unitTitle');
                        $moreDetailsCategory = $this->getRequest()->getPost('moreDetailsCategory');
                        $moreDetailsClassTags = $this->getRequest()->getPost('moreDetailsClassTags');
                        $moreDetailsClassDesc = $this->getRequest()->getPost('moreDetailsClassDesc');
                        $upload_image = $this->getRequest()->getPost('upload_image');
                        $video_lesson_file = $this->getRequest()->getPost('video_lesson_file');
                        $video_lesson_file = rtrim($video_lesson_file, ", ");
                        $upload_link = $this->getRequest()->getPost('upload_link');

                        $date123 = date_create();
                        $time = date_timestamp_get($date123);
                        $discussioncommentone = "";
                        $projectdesc1 = $projectDescription;


                        $projectdesc1 = split("<img data", $projectDescription);
                        if (count($projectdesc1) == 1)
                            $projectdesc1 = split("<img style", $projectDescription);
                        if (count($projectdesc1) == 1)
                            $projectdesc1 = split('<img src="data', $projectDescription);

                        $projectDescription = $projectdesc1;

                        $userid = $userId;

                        $i = 0;
                        $saperatetext = array();
                        foreach ($projectDescription as $value) {
                            if ($i != 0) {
                                $saperatetext = split(";\">", $value);
                                $saperatetext[0] = split("style=", $saperatetext[0]);
                                $image = split(",", $saperatetext[0][0]);
                                $ifp = fopen("assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg", "wb");
                                $result = fwrite($ifp, base64_decode($image[1]));
                                fclose($ifp);
                                $path = "/assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg";
                                $saperatetext[0] = "<img src=" . $path . " alt='Smiley face'>";
                            }
                            if ($i == 0) {
                                $discussioncommentone = $value;
                            } else {
                                if (isset($saperatetext[0])) {
                                    $discussioncommentone = $discussioncommentone . $saperatetext[0];
                                }
                                if (isset($saperatetext[1])) {
                                    $discussioncommentone = $discussioncommentone . $saperatetext[1];
                                }
                            }$i++;
                        }
                        $projectDescription = $discussioncommentone;

                        $date123 = date_create();
                        $time = date_timestamp_get($date123);
                        $discussioncommentone = "";
                        $projectdesc1 = $moreDetailsClassDesc;


                        $projectdesc1 = split("<img data", $moreDetailsClassDesc);
                        if (count($projectdesc1) == 1)
                            $projectdesc1 = split("<img style", $moreDetailsClassDesc);
                        if (count($projectdesc1) == 1)
                            $projectdesc1 = split('<img src="data', $moreDetailsClassDesc);

                        $moreDetailsClassDesc = $projectdesc1;

                        $userid = $userId;

                        $i = 0;
                        $saperatetext = array();
                        foreach ($moreDetailsClassDesc as $value) {
                            if ($i != 0) {
                                $saperatetext = split(";\">", $value);
                                $saperatetext[0] = split("style=", $saperatetext[0]);
                                $image = split(",", $saperatetext[0][0]);
                                $ifp = fopen("assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg", "wb");
                                $result = fwrite($ifp, base64_decode($image[1]));
                                fclose($ifp);
                                $path = "/assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg";
                                $saperatetext[0] = "<img src=" . $path . " alt='Smiley face'>";
                            }
                            if ($i == 0) {
                                $discussioncommentone = $value;
                            } else {
                                if (isset($saperatetext[0])) {
                                    $discussioncommentone = $discussioncommentone . $saperatetext[0];
                                }
                                if (isset($saperatetext[1])) {
                                    $discussioncommentone = $discussioncommentone . $saperatetext[1];
                                }
                            }$i++;
                        }
                        $moreDetailsClassDesc = $discussioncommentone;





                        $uploadVideoEdit = $this->getRequest()->getPost('uploadVideoEdit');
                        $classid = $this->getRequest()->getPost('classId');
                        $uploadVideoEdit = rtrim($uploadVideoEdit, ",");

                        $objClassesModel1 = Application_Model_TeachingClasses::getInstance();
                        $selectUserClassId = $objClassesModel1->getClassById($classTeachId);
//                        echo $selectUserClassId;die;
                        $result = $teachingclasses->getClassPublishStatus($classTeachId);
                        $status = $result['publish_status'];
//                      print_r($status);die;
                        if (!$status) {
                            $status = '2';
                        }

                        if ($selectUserClassId) {
//                            echo 1;
//                            die;
                            $data = array(
                                'user_id' => $userId,
                                'assignment_project_title' => $projectTitle,
                                'assignment_project_description' => $projectDescription,
                                'class_description' => $moreDetailsClassDesc,
                                'class_tags' => $moreDetailsClassTags,
                                'category_id' => $moreDetailsCategory,
                                'class_created_date' => gmdate('Y-m-d H:i:s', time()),
                                'class_title' => $classTeach,
                                'class_description' => $moreDetailsClassDesc,
//                                'class_url' => "/doc/$userId/" . $userId . $upload_link,
                                'publish_status' => $status
                            );

                            $updateClassesDetails = $objClassesModel->updateTeachingClasses($data, $classTeachId);

                            $data = array(
                                'class_file_path' => "/doc/$userId/" . $userId . $upload_link,
                                'class_id' => $classTeachId,
                                'user_id' => $userId,
                                'file_uploaded_date' => gmdate('Y-m-d H:i:s', time())
                            );

                            $objTeachingClassFile = Application_Model_TechingClassFile::getInstance();


                            //before
                            //   $fileSuccess = $objTeachingClassFile->insertTeachingClassesFile($data);
                            //after
                            $output_dir = "/doc/$userId/" . $userId . $upload_link;
                            $filedate = gmdate('Y-m-d H:i:s', time());
//                            $fileSuccess = $objTeachingClassFile->insertTeachingClassesFile($userId, $classTeachId, $output_dir, $filedate);
                            echo json_encode('True');
                            $teachingClassUnit = array(
                                'class_id' => $classTeachId,
                                'user_id' => $userId,
                                'class_unit_date' => gmdate('Y-m-d H:i:s', time()),
                                'class_unit_title' => $unitTitle
                            );

                            $objTeachingImage = Application_Model_TeachingClassesUnit::getInstance();
                            $unitSuccess = $objTeachingImage->insertTeachingClassUnit($teachingClassUnit);

                            $teachingClassVideo = array(
                                'class_id' => $classTeachId,
                                'user_id' => $userId,
                                'class_unit_id' => $unitSuccess,
                                'class_unit_title' => $unitTitle,
                                'video_uploaded_date' => gmdate('Y-m-d H:i:s', time()),
                                'class_video_url' => "/videos/$userId/" . $userId . $video_lesson_file
                            );

//                            $data = array(
//                                'user_id' => $userId,
//                                'class_id' => $classTeachId,
//                                'class_unit_id' => $unitSuccess,
//                                'user_id' => $userId,
//                                'class_video_title' => $uploadVideoEdit,
//                                'class_video_url' => $video_lesson_file,
//                                'video_uploaded_date' => date('Y-m-d H:i:s'),
//                                'cover_image' => 'Tesing + Image'
//                            );
//                           
//                            $objTeachingClassVideo = Application_Model_TeachingClassVideo::getInstance();
//                            $getTeachingClassVideo = $objTeachingClassVideo->insertTeachingClassesVideo($data);
//                            echo json_encode($getTeachingClassVideo);
                            die;
                        }
                    }

                    break;
                case 'populardiscussion':

                    if ($this->getRequest()->getPost()) {
                        $userid = $this->view->session->storage->user_id;
                        $classid = $this->getRequest()->getPost('classid');
                        $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();
                        $result = $objTeachingClassDiscussions->getTrendDetail($classid);
                        $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();

                        if ($result) {
                            $i = 0;
                            foreach ($result as $val) {

                                $discussion_id = $val['discussion_id'];
                                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                                if (isset($this->view->session->storage->user_id)) {
                                    $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                                    if ($userresultlike) {
                                        $result[$i]['islike'] = 1;
                                    } else {
                                        $result[$i]['islike'] = 0;
                                    }
                                }
                                $result[$i]['discussslikecount'] = $resultlike['num'];
                                $i++;
                            }
                        }

                        echo json_encode($result);
                        die;
                        // }
                    }

                    break;
                case 'recentdiscussion':

                    if ($this->getRequest()->getPost()) {
                        $userid = $this->view->session->storage->user_id;
                        $classid = $this->getRequest()->getPost('classid');
                        $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();
                        $result = $objTeachingClassDiscussions->getRecentDetail($classid);
                        $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();

                        if ($result) {
                            $i = 0;
                            foreach ($result as $val) {

                                $discussion_id = $val['discussion_id'];
                                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                                if (isset($this->view->session->storage->user_id)) {
                                    $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                                    if ($userresultlike) {
                                        $result[$i]['islike'] = 1;
                                    } else {
                                        $result[$i]['islike'] = 0;
                                    }
                                }
                                $result[$i]['discussslikecount'] = $resultlike['num'];
                                $i++;
                            }
                        }






                        echo json_encode($result);
                        die;
                        // }
                    }

                    break;

                case 'trendingdiscussion':

                    if ($this->getRequest()->getPost()) {
                        $userid = $this->view->session->storage->user_id;
                        $classid = $this->getRequest()->getPost('classid');
                        $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();
                        $result = $objTeachingClassDiscussions->getTrendDetail($classid);
                        $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();

                        if ($result) {
                            $i = 0;
                            foreach ($result as $val) {

                                $discussion_id = $val['discussion_id'];
                                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                                if (isset($this->view->session->storage->user_id)) {
                                    $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                                    if ($userresultlike) {
                                        $result[$i]['islike'] = 1;
                                    } else {
                                        $result[$i]['islike'] = 0;
                                    }
                                }
                                $result[$i]['discussslikecount'] = $resultlike['num'];
                                $i++;
                            }
                        }


                        echo json_encode($result);
                        die;
                        // }
                    }

                    break;




                case 'classdiscussion':

                    if ($this->getRequest()->getPost()) {
                        $userid = $this->view->session->storage->user_id;
                        $discussionid = $this->getRequest()->getPost('discussionid');
                        $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();
                        $result = $objTeachingClassDiscussions->getDiscussion($discussionid, $userid);
                        $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();
                        // print_r($result);die;
                        if ($result) {
                            $i = 0;
                            foreach ($result as $val) {

                                $discussion_id = $val['discussion_id'];
                                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                                if (isset($this->view->session->storage->user_id)) {
                                    $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                                    if ($userresultlike) {
                                        $result[$i]['islike'] = 1;
                                    } else {
                                        $result[$i]['islike'] = 0;
                                    }
                                }
                                $result[$i]['discussslikecount'] = $resultlike['num'];
                                $i++;
                            }
                        }
                        //print_r($result);
                        echo json_encode($result);
                        die;
                        // }
                    }

                    break;
                case 'discussionlike':




                    break;




                    break;
                case 'discussioncommentlike':

                    if ($this->getRequest()->getPost()) {

                        $classid = $this->getRequest()->getPost('classid');
                        $discussionid = $this->getRequest()->getPost('discussionid');
                        $commentid = $this->getRequest()->getPost('commentid');
                        $userid = $this->view->session->storage->user_id;
                        //echo $classid . $discussionid . $userid;
                        $objClassDiscussionCommentLikes = Application_Model_DiscussionCommentLikes::getinstance();
                        $result = $objClassDiscussionCommentLikes->discusscommentlikes($userid, $classid, $discussionid, $commentid);

                        echo json_encode($result);
                        die;
                        // }
                    }
                    break;
                case 'projectcommentlike':

                    if ($this->getRequest()->getPost()) {
                        $classid = $this->getRequest()->getPost('classid');
                        $projectid = $this->getRequest()->getPost('projectid');
                        $commentid = $this->getRequest()->getPost('commentid');
                        $userid = $this->view->session->storage->user_id;
                        //echo $classid . $discussionid . $userid;
                        $objClassProjectCommentLikes = Application_Model_ProjectCommentLikes::getinstance();
                        $result = $objClassProjectCommentLikes->projectcommentlikes($userid, $classid, $projectid, $commentid);
                        echo json_encode($result);
                        die;
                        // }
                    }
                    break;

//                case 'projectcommentlike':
//
//                    if ($this->getRequest()->getPost()) {
//
//                        $userid = $this->getRequest()->getPost('userid');
//                        $classid = $this->getRequest()->getPost('classid');
//
//                        //echo $classid . $discussionid . $userid;
//                        $objEnrollClassModel = Application_Model_ClassEnroll::getinstance();
//                        $result = $objEnrollClassModel->insertClassEnroll($userid, $classid);
//
//                        echo json_encode($result);
//                        die;
//                        // }
//                    }
//                    break;

                case 'insertClassEnroll':

                    if ($this->getRequest()->getPost()) {

                        $userid = $this->getRequest()->getPost('userid');
                        $classid = $this->getRequest()->getPost('classid');
                        $referal = $this->getRequest()->getPost('referal');
                        if ($referal == 'referal') {
                            $referal = 1;
                        } else {
                            $referal = 0;
                        }
                        $objEnrollClassModel = Application_Model_ClassEnroll::getinstance();
                        $result = $objEnrollClassModel->insertClassEnroll($userid, $classid, $referal);

                        echo json_encode($result);
                        die;
                        // }
                    }
                    break;



                case 'updateclass':
                    $objClassesModel1 = Application_Model_TeachingClasses::getInstance();
                    $video_id = $this->getRequest()->getPost('video_id');
                    $userId = $this->getRequest()->getPost('userId');
                    $classTeachId = $this->getRequest()->getPost('classTeachId');
                    $moreDetailsCategory = $this->getRequest()->getPost('moreDetailsCategory');
                    $moreDetailsClassTags = $this->getRequest()->getPost('moreDetailsClassTags');
                    $moreDetailsClassDesc = $this->getRequest()->getPost('moreDetailsClassDesc');
                    $uploadVideoTitleEdit = $this->getRequest()->getPost('uploadVideoTitleEdit');
                    $projectDescription = $this->getRequest()->getPost('projectDescription');
                    $projectTitle = $this->getRequest()->getPost('projectTitle');
                    $classTeach = $this->getRequest()->getPost('classTeach');
                    $classid = $this->getRequest()->getPost('class_id');
                    $paypalemail = $this->getRequest()->getPost('paypal');
                    $userid = $userId;

                    $date123 = date_create();
                    $time = date_timestamp_get($date123);
                    $discussioncommentone = "";
                    $classdicreption = $moreDetailsClassDesc;


                    $classdicreption = split("<img data", $moreDetailsClassDesc);
                    if (count($classdicreption) == 1)
                        $classdicreption = split("<img style", $moreDetailsClassDesc);
                    if (count($classdicreption) == 1)
                        $classdicreption = split('<img src="data', $moreDetailsClassDesc);

                    $moreDetailsClassDesc = $classdicreption;



                    $i = 0;
                    $saperatetext = array();
                    foreach ($moreDetailsClassDesc as $value) {
                        if ($i != 0) {
                            $saperatetext = split(";\">", $value);
                            $saperatetext[0] = split("style=", $saperatetext[0]);
                            $image = split(",", $saperatetext[0][0]);
                            $ifp = fopen("assets/discussioncommentimgs/" . $time . $userid . $i . "clasdic.jpg", "wb");
                            $result = fwrite($ifp, base64_decode($image[1]));
                            fclose($ifp);
                            $path = "/assets/discussioncommentimgs/" . $time . $userid . $i . "clasdic.jpg";
                            $saperatetext[0] = "<img src=" . $path . " alt='Smiley face'>";
                        }
                        if ($i == 0) {
                            $discussioncommentone = $value;
                        } else {
                            if (isset($saperatetext[0])) {
                                $discussioncommentone = $discussioncommentone . $saperatetext[0];
                            }
                            if (isset($saperatetext[1])) {
                                $discussioncommentone = $discussioncommentone . $saperatetext[1];
                            }
                        }$i++;
                    }
                    $moreDetailsClassDesc = $discussioncommentone;


                    $date123 = date_create();
                    $time = date_timestamp_get($date123);
                    $discussioncommentone = "";
                    $projectdesc1 = $projectDescription;


                    $projectdesc1 = split("<img data", $projectDescription);
                    if (count($projectdesc1) == 1)
                        $projectdesc1 = split("<img style", $projectDescription);
                    if (count($projectdesc1) == 1)
                        $projectdesc1 = split('<img src="data', $projectDescription);

                    $projectDescription = $projectdesc1;

                    $i = 0;
                    $saperatetext1 = array();
                    foreach ($projectDescription as $value) {
                        if ($i != 0) {
                            $saperatetext1 = split(";\">", $value);
                            $saperatetext1[0] = split("style=", $saperatetext1[0]);
                            $image = split(",", $saperatetext1[0][0]);
                            $ifp = fopen("assets/discussioncommentimgs/" . $time . $userid . $i . "projdic.jpg", "wb");
                            $result = fwrite($ifp, base64_decode($image[1]));
                            fclose($ifp);
                            $path = "/assets/discussioncommentimgs/" . $time . $userid . $i . "projdic.jpg";
                            $saperatetext1[0] = "<img src=" . $path . " alt='Smiley face'>";
                        }
                        if ($i == 0) {
                            $discussioncommentone = $value;
                        } else {
                            if (isset($saperatetext1[0])) {
                                $discussioncommentone = $discussioncommentone . $saperatetext1[0];
                            }
                            if (isset($saperatetext1[1])) {
                                $discussioncommentone = $discussioncommentone . $saperatetext1[1];
                            }
                        }$i++;
                    }
                    $projectDescription = $discussioncommentone;


                    $publishstatusresult = $objClassesModel1->getClassPublishStatus($classid);
                    $publishstatus = $publishstatusresult['publish_status'];
                    if ($publishstatus != 0) {
                        $publishstatus = 1;
                    }

                    $result = $objClassesModel1->updateClass($classid, $moreDetailsCategory, $moreDetailsClassTags, $classTeach, $moreDetailsClassDesc, $projectTitle, $projectDescription, $publishstatus);
                    //dev:priyanka varanasi
                    //desc: to sort the videos accoding to the data-no and update them in db
                    $infodata = $this->getRequest()->getPost('infodata');

                    foreach ($infodata as $value) {
                        $sortno = array('class_no' => $value[0]);
                        $id = $value[1];
                        $infoback = $teachingclassvideo->updateTheDbBasedOnSortOrder($sortno, $id);
                    }
                    //////////////////code ends////////////////////////////  

                    if ($result) {
                        $res = $objUserMetaModal->updateEamil($paypalemail, $userId);
                        echo json_encode($result);
                    }


                    break;

                case 'deleteSavedFile':

                    if ($this->getRequest()->getPost()) {

                        $filepath = $this->getRequest()->getPost('filepath');
                        $objClassFile = Application_Model_TechingClassFile::getInstance();
                        $output_dir = $filepath;
                        // echo $output_dir;die;
                        $resultfile = $objClassFile->deleteTeachingClassesFile($output_dir);

                        echo json_encode($resultfile);
                        die;
                        // }
                    }
                    break;


                case 'editSavedFile':

                    if ($this->getRequest()->getPost()) {

                        $filename = $this->getRequest()->getPost('name');
                        $id = $this->getRequest()->getPost('id');

                        $objClassFile = Application_Model_TechingClassFile::getInstance();

                        // echo $output_dir;die;
                        $resultfile = $objClassFile->renameClassesFile($id, $filename);

                        echo json_encode($resultfile);
                        die;
                        // }
                    }
                    break;


                case 'updatedcomment':

                    if ($this->getRequest()->getPost()) {

                        $cmid = $this->getRequest()->getParam('comentid');
                        $cmhtml = $this->getRequest()->getParam('commenthtml');
                        $data = array('discussion_comment' => $cmhtml);
                        $objClassDiscussionsComments = Application_Model_DiscussionComments::getinstance();
                        $resp = $objClassDiscussionsComments->updateComments($data, $cmid);
                        echo json_encode($resp);
                    }
                    break;
                case 'updatedprojectcomment':

                    if ($this->getRequest()->getPost()) {

                        $cmid = $this->getRequest()->getParam('comentid');
                        $cmhtml = $this->getRequest()->getParam('commenthtml');
                        $data = array('project_comment' => $cmhtml);
                        $objClassProjectComments = Application_Model_ProjectComments::getinstance();
                        $resp = $objClassProjectComments->updateProjectComments($data, $cmid);
                        echo json_encode($resp);
                    }
                    break;


                default:
                    break;
                case 'getpermissionstatuse':
                    $result = $objstatistics->permissionstatus();
                    print_r($result[0]['teachurl']);
                    die();
                    break;
                case 'submitinvetation':
                    $data['user_name'] = $this->getRequest()->getParam('name');
                    $data['user_mail'] = $this->getRequest()->getParam('mailid');
                    $data['user_num'] = $this->getRequest()->getParam('number');
                    $data['porfolio'] = $this->getRequest()->getParam('profileio');
                    $data['experince'] = $this->getRequest()->getParam('experience');
                    $data['class_idea'] = $this->getRequest()->getParam('classidea');
                    $data['user_id'] = $this->view->session->storage->user_id;
                    $data['date'] = gmdate('Y-m-d H:i:s', time());
                    $objinvetation = Application_Model_Invitationtable::getinstance();
                    $result = $objinvetation->insertinvetation($data);
                    if ($result) {
                        $template_name = 'teacherinvite';
                        $email = "professor@fashionlearn.com.br";
                        $username = $this->view->session->storage->first_name;
                        $subject = 'teacherapplication';


                        $mergers = array(
                            array(
                                'name' => 'name',
                                'content' => $data['user_name']
                            ),
                            array(
                                'name' => 'email',
                                'content' => $data['user_mail']
                            ),
                            array(
                                'name' => 'phone',
                                'content' => $data['user_num']
                            ),
                            array(
                                'name' => 'protfolio',
                                'content' => $data['porfolio']
                            ),
                            array(
                                'name' => 'experience',
                                'content' => $data['experince']
                            ),
                            array(
                                'name' => 'classidea',
                                'content' => $data['class_idea']
                            ),
                        );
                        $mailer = Engine_Mailer_Mailer::getInstance();
                        $invite = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                        if ($invite) {
                            echo 1;
                            die();
                        }
                    } else {
                        echo 0;
                    }
                    echo 0;
                    die();
                    break;
            }
        }
    }

    public function unique($use = "") {
        $mt = str_replace(".", "", str_replace(" ", "", microtime()));
        $unique = substr(number_format($mt * rand(), 0, '', ''), 0, 10);
        return $unique;
    }

    public function teachFileHandlerAction() {

        $userId = $this->getRequest()->getPost('userid');
        if (!file_exists("images/$userId")) {

            mkdir("images/$userId", 0777, true);
        }
        $imageName = $_FILES["image_upload"]["name"];
        $imageTmpLoc = $_FILES["image_upload"]["tmp_name"];
        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
        $imageNamePath = $userId . $imageName;
        if ($ext != "jpg" && $ext != "png" && $ext != "jpeg" && $ext != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $ext = 0;
            die;
        } else {
// Path and file name
            $imagepathAndName = "images/$userId/" . $imageNamePath;
            if (file_exists($imagepathAndName)) {
                echo "Image Already Exist";
                die;
            } else {
// Run the move_uploaded_file() function here
                $imagemoveResult = (move_uploaded_file($_FILES["image_upload"]["tmp_name"], $imagepathAndName));
            }
// Evaluate the value returned from the function if needed
            if ($imagemoveResult) {
                echo $imagepathAndName . " " . "Uploaded Successfully";
                //   die;
            }
        }
    }

//dev:priyanka varanasi
//edited by:abhishek m
    //desc: TO insert video info from ajax post and vimeo request
    public function teachVideoHandlerAction() {

        $teachingclassModel = Application_Model_TeachingClasses::getInstance();
        $objTeachingClassUnit = Application_Model_TeachingClassesUnit::getInstance();
        $objTeachingClassVideo = Application_Model_TeachingClassVideo::getInstance();
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $objCore = Engine_Core_Core::getInstance();
        $this->_appSetting = $objCore->getAppSetting();
        $client_id = $this->_appSetting->vimeo->consumerKey;
        $client_secret = $this->_appSetting->vimeo->consumerSecret;
        $vimeo = new Engine_Vimeo_Vimeo($client_id, $client_secret);
        $redirect_uri = 'http://version2.fashionlearn.com.br/admin/teachdetail';
        $access_token = '07ecf4de0bc8fdef19133ccfd9cdfbf9';
        $vimeo->setToken($access_token);
        $class_id = $this->view->session->storage->teach_id;
        $teachname = "";
        if (isset($this->view->session->storage->teach)) {
            $teachname = $this->view->session->storage->teach;
        }
        $userid = $this->view->session->storage->user_id;
        if ($this->getRequest()->getPost('user_id'))
            $userid = $this->getRequest()->getPost('user_id');


        $coverimage = "";
        $video_title = $this->getRequest()->getPost('videoTitle');
        $cover = $this->getRequest()->getPost('imageurl');
        $unitId = $this->getRequest()->getPost('unit_id');
        $unitIds = (int) $unitId;
        $class_no = $this->getRequest()->getPost('video_no');

        $class_no = (int) $class_no;
        // $thish = $this->getRequest()->getPost('this');

        $class_video_title = $this->getRequest()->getPost('class_video_title');
        ;

        if ($cover != "none") {
            $values = parse_url($cover);
            $host = explode('/', $values['path']);
            $coverimage = "/" . $host[3] . "/" . $host[4] . "/" . $host[5] . " ";
        }

        $uploadVideoTitle = $this->getRequest()->getPost('uploadVideoEdit');

        $video_id = $this->getRequest()->getPost('videoId');
        ;
        $this->view->session->storage->video_id = $video_id;

        if ($video_id) {
            $album_id = $teachingclassModel->getClassById($this->view->session->storage->teach_id);
            if (!empty($album_id['album_id'])) {

                $albumid = $album_id['album_id'];
                $url = "https://api.vimeo.com/me/albums";
                $params = array(
                    'album_id' => $albumid,
                    'videos' => 'videos',
                    'video_id' => $video_id,
                );
                try {
                    $video_result = $vimeo->request('/users/28864880/albums/' . $albumid . '/videos/' . $video_id, $params, 'PUT');
                    //$result = (array)$vimeo->request('/videos/124022723', array('per_page' => 2),'GET');
                    $det = array(
                        'user_id' => $userid,
                        'class_id' => $class_id,
                        'video_id' => $video_id,
                        'class_video_title' => $class_video_title,
                        'cover_image' => $coverimage,
                        'class_unit_id' => $unitIds,
                        'video_uploaded_date' => gmdate('Y-m-d H:i:s', time()),
                        'transcode_status' => 2,
                        'class_no' => $class_no
                    );

                    $response = $objTeachingClassVideo->insertvideoinformation($det);
                    $temp = explode('.', $class_video_title);
                    $ext = array_pop($temp);
                    $name = implode('.', $temp);

                    echo $unitIds . '?';
                    echo $response . '?';
                    echo $name;

                    exit();
                } catch (Exception $e) {
                    
                }
            } else {
                try {
                    $params = array(
                        'name' => $teachname
                    );

                    $album = $vimeo->request('/users/28864880/albums/', $params, 'POST');
                    $url;
                    foreach ($album as $album_result) {
                        $uri = $album_result['uri'];
                        $url = explode('/', $uri);
                        $url = $url[4];
                        $GLOBALS['url'] = $url;
                        break;
                    }
                    $album_id = $GLOBALS['url'];
                    $params = array(
                    );
                    $video_result = $vimeo->request('/users/28864880/albums/' . $album_id . '/videos/' . $video_id, $params, 'PUT');
                    $data = array(
                        'album_id' => $album_id,
                    );

                    $responseid = $teachingclassModel->insertingAlbumId($data, $class_id);
                } catch (Exception $e) {
                    
                }
                //$result = (array)$vimeo->request('/videos/'.$video_id, array('per_page' => 2),'GET');
                $det = array(
                    'user_id' => $userid,
                    'class_id' => $class_id,
                    'video_id' => $video_id,
                    'class_video_title' => $class_video_title,
                    'cover_image' => $coverimage,
                    'class_unit_id' => $unitIds,
                    'video_uploaded_date' => gmdate('Y-m-d H:i:s', time()),
                    'transcode_status' => 2,
                    'class_no' => $class_no
                );
                $response = $objTeachingClassVideo->insertvideoinformation($det);

                echo $unitIds . '?';
                echo $response . '?';
                echo $class_video_title;
                exit();
            }
        } else {
            $sUploadResult = 'Video Fails to Upload, try again later.';
            echo $sUploadResult;
            exit();
        }
    }

    public function teachCoverImageAction() {


        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->isPost()) {
            $userId = $this->view->session->storage->user_id;
            if ($this->view->session->storage->tid)
                $userId = $this->view->session->storage->tid;
//$userId = $this->getRequest()->getPost('userid');
            $videoName = $_FILES["file"]["name"];
            $videoTmpLoc = $_FILES["file"]["tmp_name"];
            $videoid = $this->getRequest()->getPost('video-id');

//                $ext = pathinfo($videoName, PATHINFO_EXTENSION);
            $ext = substr($videoName, -4);
// Path and file name
            $videoNamePath = $userId . $videoName;
            $videopathAndName = "coverImages/$userId/" . $videoNamePath;

            if ($ext != ".gif" && $ext != ".png" && $ext != "" && $ext != ".jpg" && $ext != ".jpeg") {
                echo json_encode("Sorry only png,jpg,gif and jpeg format allowed");
            } else {
                if (!file_exists("coverImages/$userId")) {

                    mkdir("coverImages/$userId", 0777, true);
                }

//                if ($ext == ".gif")
//                    $testimage = imagecreatefromgif($_FILES["file"]["tmp_name"]);
//                if ($ext == ".jpg")
//                    $testimage = imagecreatefromjpeg($_FILES["file"]["tmp_name"]);
//                if ($ext == ".png")
//                    $testimage = imagecreatefrompng($_FILES["file"]["tmp_name"]);
//
                $videomoveResult = move_uploaded_file($_FILES["file"]["tmp_name"], $videopathAndName);
//                    $original_info = getimagesize($videopathAndName);
//                    $original_w = $original_info[0];
//                    $original_h = $original_info[1];
//
//                    if ($ext == ".gif")
//                        $original_img = imagecreatefromgif($videopathAndName);
//                    if ($ext == ".jpg")
//                        $original_img = imagecreatefromjpeg($videopathAndName);
//                    if ($ext == ".png")
//                        $original_img = imagecreatefrompng($videopathAndName);
//
//                    $thumb_w = 115;
//                    $thumb_h = 65;
//                    $thumb_img = imagecreatetruecolor($thumb_w, $thumb_h);
//                    imagecopyresampled($thumb_img, $original_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $original_w, $original_h);
//
//                    if ($ext == ".gif")
//                        $videomoveResult = imagegif($thumb_img, $videopathAndName);
//                    if ($ext == ".jpg")
//                        $videomoveResult = imagejpeg($thumb_img, $videopathAndName);
//                    if ($ext == ".png")
//                        $videomoveResult = imagepng($thumb_img, $videopathAndName);
// Evaluate the value returned from the function if needed
                if ($videomoveResult) {
                    if ($videoid != "") {

                        $data = array(
                            'cover_image' => "/" . $videopathAndName
                        );
                        $objTeachingClassVideo = Application_Model_TeachingClassVideo::getInstance();
                        $response = $objTeachingClassVideo->updateVideoCoverImage($data, $videoid);
                        if ($response) {
                            $res = array('coverpath' => $videopathAndName,
                                'code' => 200);
                            echo json_encode($res);
                            exit();
                        }
                    } else {
                        $resume = array('coverpath' => $videopathAndName,
                            'code' => 198);
                        echo json_encode($resume);
                        exit();
                    }
                }
//                        }
            }
        } else {
            $msg = "Please upload image first";
            echo json_encode($msg);
            exit();
        }
    }

    public function teachDocHandlerAction() {

        $userId = $this->view->session->storage->user_id;

        if ($this->view->session->storage->tid)
            $userId = $this->view->session->storage->tid;

        $class_id = $this->view->session->storage->teach_id;

        if (!file_exists("doc/$userId/$class_id")) {
            mkdir("doc/$userId/$class_id", 0777, true);
        }
        $fileName = $_FILES["myfile"]["name"];
        $fileName = preg_replace("/[\s]/", "_", $fileName);
        $fileNamePath = $fileName;
        $output_dir = "doc/$userId/$class_id/" . $fileNamePath;
        if (isset($_FILES["myfile"])) {

            $ret = array();
            $error = $_FILES["myfile"]["error"];
            //You need to handle  both cases
            //If Any browser does not support serializing of multiple files using FormData() 
            if (!is_array($_FILES["myfile"]["name"])) { //single file
                $fileName = $_FILES["myfile"]["name"];
                move_uploaded_file($_FILES["myfile"]["tmp_name"], $output_dir);
                $ret[] = $fileName;

                $objClassFile = Application_Model_TechingClassFile::getInstance();

                $filedate = gmdate('Y-m-d H:i:s', time());
                ;
                $output_dir = "/" . $output_dir;
                $resultfile = $objClassFile->insertTeachingClassesFile($userId, $class_id, $output_dir, $filedate);
//                
            } else {  //Multiple files, file[]
                $fileCount = count($_FILES["myfile"]["name"]);
                for ($i = 0; $i < $fileCount; $i++) {
                    $fileName = $_FILES["myfile"]["name"][$i];
                    move_uploaded_file($_FILES["myfile"]["tmp_name"][$i], $output_dir);
                    $ret[] = $fileName;
                }
            }
            echo json_encode($ret);
        }
        exit();
    }

    public function teachDeleteHandlerAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $userId = $this->view->session->storage->user_id;
        $class_id = $this->view->session->storage->teach_id;
        if (isset($_POST["op"]) && $_POST["op"] == "delete" && isset($_POST['name'])) {

            $fileName = $_POST['name'];
            $output_dir = "doc/$userId/$class_id/" . $fileName;

            $filePath = $output_dir;
            if (file_exists($filePath)) {
                unlink($filePath);
                $objClassFile = Application_Model_TechingClassFile::getInstance();
                $output_dir = "/" . $output_dir;
                // echo $output_dir;die;
                $resultfile = $objClassFile->deleteTeachingClassesFile($output_dir);
            }
            // echo "Deleted File " . $fileName . "<br>";
        }
    }

    public function teachClassAction() {
        $objUsersModel = Application_Model_Users::getinstance();
        $objUsersMetaModel = Application_Model_UsersMeta::getinstance();

        $objFacebookModel = Engine_Facebook_Facebookclass::getInstance();
        $url = $objFacebookModel->getLoginUrl();
        $users = Application_Model_Users::getinstance();

        $this->view->fbLogin = $url;
        $objTeachingClassesModel = Application_Model_TeachingClasses::getinstance();
        $objClassEnrollModel = Application_Model_ClassEnroll::getinstance();
        $classid = $this->getRequest()->getParam('classid');
        $objTeachingCertificateModel = Application_Model_Certificate::getinstance();
        if (isset($this->view->session->storage->user_id)) {
            $certires = $objTeachingCertificateModel->getCertificateDetailss($this->view->session->storage->user_id, $classid);
            if ($certires)
                $this->view->certificatelink = "/certificate/" . $certires["certificate_id"] . "/?cid=" . $classid;
        }



        $objClassReview = Application_Model_ClassReview::getinstance();
        $allreview = $objClassReview->getAllReview($classid);
        $allpreview = $objClassReview->getAllpReview($classid);
        $this->view->allreview = $allreview;
        $this->view->reviewcount = count($allreview);
        $this->view->reviewpcount = count($allpreview);
        if (count($allpreview) > 0) {
            $this->view->reviewpercent = ceil((count($allpreview) / count($allreview)) * 100);
        } else {

            $this->view->reviewpercent = 0;
        }

//        print_r($allreview);
//        die();
        $refer = $this->getRequest()->getParam('via');
        $rederalid = $this->getRequest()->getParam('rederalid');
        if(isset($refer)){
        $referids=  explode('?', $rederalid);
                
    
            $_SESSION['referalid']=$referids[1];
        }
        $this->view->classid = $classid;
        $this->view->referal = $refer;
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $getEnrollCLass = $objClassEnrollModel->getEnrollClass($userid, $classid);
        }
        $classexist = $objTeachingClassesModel->getClassById($classid);
        $this->view->classid = $classid;
        if ((isset($classexist))) {                                   // If class Exist then only it will proceed
            if (isset($this->view->session->storage->premium_status)) {
                $pstatus = $this->view->session->storage->premium_status;
                $this->view->pstatus = $pstatus;
            }
            $actionname = $this->getRequest()->getParam('actionname');
            if ($actionname == "discussion") {
                if (isset($this->view->session->storage->user_id)) {
                    $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();
                    $classdiscuss = $objTeachingClassDiscussions->getTrendDetail($classid);
                    $discussionid = $this->getRequest()->getParam('discussionid');
                    $mydiscussion = array();
                    foreach ($classdiscuss as $value) {
                        if ($discussionid == $value['discussion_id']) {
                            $mydiscussion = $value;
                        }
                    }
                    $this->view->requiedaction = $actionname;
                    $this->view->requireddicussion = $mydiscussion;
                } else {
                    header('Location:/teachclass/' . $classid);
                }
            }
            $objClassProjectComments = Application_Model_ProjectComments::getinstance();
            $objfollowres = Application_Model_Followers::getinstance();
            $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
            if ($actionname == "project") {
                if (isset($this->view->session->storage->user_id)) {

                    $projectid = $this->getRequest()->getParam('projectid');
                } else {
                    header('Location:/teachclass/' . $classid);
                }
            }
            if ($this->getRequest()->getParam('comid') != "") {
                $this->view->opencomment = $this->getRequest()->getParam('comid');
            }
            if ($this->getRequest()->getParam('disid') != "") {
                $this->view->opendiscussion = $this->getRequest()->getParam('disid');
            }
            $objproject = Application_Model_Projects::getInstance();
            $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();
            $teachinclassresult = $objproject->projectsOnClassEnroll($classid);
            $teachinclasssresult = $objTeachingClassesModel->getClassById($classid);
            $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();
            $classdiscuss = $objTeachingClassDiscussions->getTrendDetail($classid);
            $allProject = array();
            $classDetails = $objTeachingClassesModel->getClassById($classid);
            $classcreateid = $classDetails['user_id'];
            $this->view->createrid = $classcreateid;
            $objsave = Application_Model_Myclasses::getInstance();
            if (isset($userid)) {


                $saveresponse = $objsave->getSave($userid, $classid);
                $this->view->myclass = $saveresponse;
                $objClassEnrollModel = Application_Model_ClassEnroll::getinstance();
                $getEnrollCLass = $objClassEnrollModel->getEnrollClass($userid, $classid);
//        echo "<pre>"; print_r($userid); die;
                $this->view->CheckEnrollClass = $getEnrollCLass;
            }





            if ($teachinclasssresult['user_id']) {

                $teacherid = $teachinclasssresult['user_id'];
                $userresult = $objUsersModel->getUserDetail($teacherid);
                $usermetaresult = $objUsersMetaModel->getUserMetaDetail($teacherid);
                $this->view->userresult = $userresult;
                $this->view->usermetaresult = $usermetaresult;
//       echo '<pre>'; print_r($usermetaresult);
//        die();
            }
            if (isset($teachinclassresult)) {
                $lk = 0;
                foreach ($teachinclassresult as $project) {
                    if (isset($projectid)) {
                        if ($projectid == $project['project_id'])
                            $this->view->openproject = $lk;
                    }
                    $lk++;
                    $project['class_id'] = $classid;
                    $resultlike = $objClassProjectLikes->getprojectlikes($project['project_id']);
                    $followersdata = $objfollowres->getFollowMe($project['user_id']);
                    $flag = 0;
                    if (isset($followersdata)) {
                        foreach ($followersdata as $value) {
                            if (isset($this->view->session->storage->user_id)) {
                                if ($value['follower_user_id'] == $this->view->session->storage->user_id) {
                                    $flag = 1;
                                }
                            }
                        }
                    }
                    $project['projectLikesCount'] = $resultlike;
                    if (isset($this->view->session->storage->user_id)) {
                        $project['youLiked'] = $objClassProjectLikes->getuserprojectlikes($userid, $project['project_id']);
                    }
                    $comment = $objClassProjectComments->getComments($project['project_id']);
                    $project['commentsCount'] = count($comment);
                    $project['following'] = $flag;

                    if (($project["project_privacy"] == 1 && isset($this->view->CheckEnrollClass)) || ($project["project_privacy"] == 0 && ($project['user_id'] == $this->view->session->storage->user_id)) || ($project["project_privacy"] == 2)) {
                        $allProject[] = $project;
                    }
                }
            }
            $this->view->projectcreatedstatus = 0;
            if (isset($allProject)) {
                foreach ($allProject as $proj) {

                    if (isset($this->view->session->storage->user_id)) {
                        if ($proj['user_id'] == $this->view->session->storage->user_id) {
                            $this->view->projectcreatedstatus = 1;
                        }
                    }
                }
            }
            $this->view->allProjects = $allProject;
//        echo "<pre>";
//        print_r($allProject);
//        die();
            $objclassvideoModel = Application_Model_TeachingClassVideo::getinstance();
            $objUserVideoStatus = Application_Model_uservideostatus::getInstance();
            $objclassvideoModel = Application_Model_TeachingClassVideo::getinstance();
            $objUserVideoStatus = Application_Model_uservideostatus::getInstance();
            $objTeachingclassunitvideo = Application_Model_TeachingClassesUnit::getInstance();
            $playervideo = $objTeachingclassunitvideo->getclassunitDetails($classid);
            $projectcount = 0;
            $getvideoscount = 0;
            $getviewedvideoscount = 0;
            $totaltime = 0;
            $totalvideoDuration = 0;
            $allVideos = array();
//        echo "<pre>"; print_r($playervideo);die;
            if (isset($playervideo)) {
                $j = 0;
                foreach ($playervideo as $val) {

                    $res = $objclassvideoModel->getvideodetails($val['class_unit_id'], $classid);
//                         echo "<pre>"; print_r($res);

                    if ($res) {
                        $i = 0;
                        foreach ($res as $vale) {

                            $seconds = $vale['video_duration'];
                            $hours = $seconds / 3600;
                            $minutes = ($seconds % 3600) / 60;
                            $seconds = ($seconds % 3600) % 60;
                            if ($hours < 10) {
                                $hours = (int) $hours;
                                if ($hours === 0) {
                                    $hours = "00";
                                } else {
                                    $hours = "0" . $hours;
                                }
                            }if ($minutes < 10) {
                                $minutes = (int) $minutes;
                                if ($minutes <= 0) {
                                    $minutes = "00";
                                } else {
                                    $minutes = "0" . $minutes;
                                }
                            }if ($seconds < 10) {
                                $seconds = (int) $seconds;
                                if ($seconds <= 0) {
                                    $seconds = "00";
                                } else {
                                    $seconds = "0" . $seconds;
                                }
                            }
                            $videoDuration = $hours . ":" . $minutes . ":" . $seconds;

                            $totaltime = $totaltime + $vale['video_duration'];
                            $vale['video_duration'] = $videoDuration;
                            //echo "<pre>"; print_r($userid); print_r($classid); print_r($vale['video_id']);die;
                            if (isset($userid)) {
                                $seenVideo = $objUserVideoStatus->userVideoSeen($userid, $classid, $vale['video_id']);
                            }


                            if (isset($seenVideo)) {
                                $vale['youviewed'] = 0;
                            } else {

                                $vale['youviewed'] = 1;
                            }
//                        print_r($Videos); die;

                            $Videos[$j][$i] = $vale;

                            $i++;
//                        echo "<pre>";print_r($Videos);
//                        echo "===========";
                        }
//                    echo "<pre>";print_r($Videos);
//                    echo "============";
                        $getvideoscount = $getvideoscount + count($res);

                        $finalVideos['class_unit_titile'] = $val['class_unit_titile'];
                        $finalVideos['class_unit_id'] = $val['class_unit_id'];
                        $finalVideos[0] = $Videos[$j];

                        $allVideos[] = $finalVideos;
//                      echo "<pre>";                print_r($finalVideos);
                    }
//                echo "<pre>";print_r($allVideos);
//                echo "===========";
                    $j++;
                }

//            die;
            }
//echo "<pre>"; print_r($playervideo);die;
            $seconds = $totaltime;
            $hours = $seconds / 3600;
            $minutes = ($seconds % 3600) / 60;
            $seconds = ($seconds % 3600) % 60;
            if ($hours < 10) {
                $hours = (int) $hours;
                if ($hours === 0) {
                    $hours = "00";
                } else {
                    $hours = "0" . $hours;
                }
            }if ($minutes < 10) {
                $minutes = (int) $minutes;
                if ($minutes <= 0) {
                    $minutes = "00";
                } else {
                    $minutes = "0" . $minutes;
                }
            }if ($seconds < 10) {
                $seconds = (int) $seconds;
                if ($seconds <= 0) {
                    $seconds = "00";
                } else {
                    $seconds = "0" . $seconds;
                }
            }
            $totalvideoDuration = $hours . "h " . $minutes . "m " . $seconds . "s";



            $objTeachingClassesModel = Application_Model_TeachingClasses::getinstance();
            $teachinclassresult = $objTeachingClassesModel->getClassById($classid);

            $objfollow = Application_Model_Followers::getInstance();
            $objCore = Engine_Core_Core::getInstance();
            $realobj = $objCore->getAppSetting();
            $host = $realobj->hostLink;
            if (isset($teachinclassresult)) {


                $class_url = $this->bitly_url_shorten($host . '/teachclass/' . $classid, '0ac6fd974647efb386cb6f3b509a4ae4f3100df4', 'fsln.me');
//                $resclassurl = $objTeachingClassesModel->updateClassUrl($teachinclassresult['class_id'], $class_url);
                $teachinclassresult['class_url'] = $class_url;

//            if ($teachinclassresult['publish_status'] == 0 && $teachinclassresult['student_refferal'] == "") {
              
                       $randomresult=  md5(uniqid(rand(), true));
                       $referaluid=(string)$this->view->session->storage->user_id;
                       
                     
                $randomresult=$randomresult.'?'."$referaluid";
             
                $student_refferal = $this->bitly_url_shorten($host . '/teachclass/' . $classid . '?via=referal&rederalid='.$randomresult, '0ac6fd974647efb386cb6f3b509a4ae4f3100df4', 'fsln.me');
                $teachinclassresult['student_refferal'] = $student_refferal;
//                $resreffaralurl = $objTeachingClassesModel->updateRefferalUrl($teachinclassresult['class_id'], $student_refferal);
//            }


                if (isset($this->view->session->storage->user_id)) {
                    $followresult = $objfollow->getIsFollow($userid, $teachinclassresult['user_id']);

                    $teachinclassresult['followstatus'] = $followresult;
                }
                $this->view->ClassDetails = $teachinclassresult;
            }
            if (isset($finalVideos))
                $this->view->playervideoresult = $finalVideos;

            if (isset($isres)) {
                $this->view->certificate = $isres;
            }
            $this->view->classid = $classid;
//         echo '<pre>';         print_r($allVideos); die;       
            $this->view->allClassVideos = $allVideos;

            $this->view->classid = $classid;

            $this->view->allVideosDuration = $totalvideoDuration;

            if (isset($this->view->session->storage->user_id)) {
                $getEnrollCLass = $objClassEnrollModel->getEnrollClass($userid, $classid);

                $this->view->CheckEnrollClass = $getEnrollCLass;

                $objTeachingClassVideoStatus = Application_Model_uservideostatus::getInstance();


                // $getvideoscount = $objTeachingClassVideoStatus->getvideoscount($userid, $classid);
                $getviewedvideoscount = $objTeachingClassVideoStatus->getviewedvideoscount($userid, $classid);

                $videosviewedpercentage = 0;
                if ($getvideoscount) {
                    if ($getvideoscount != 0) {
                        $videosviewedpercentage = ($getviewedvideoscount / $getvideoscount) * 100;
                    } else {
                        $videosviewedpercentage = 0;
                    }
                }

                if ($getvideoscount) {
                    $this->view->getvideoscount = (int) $getvideoscount;
                }
                if ($getviewedvideoscount) {
                    $this->view->getviewedvideoscount = (int) $getviewedvideoscount;
                }
                if ($videosviewedpercentage) {
                    $this->view->videosviewedpercentage = (int) $videosviewedpercentage;
                }
            }


            $tags = $objTeachingClassesModel->getTeachClassesDetails($classid);

            if (isset($tags['class_tags'])) {
                $tagsarray = explode(',', $tags['class_tags']);
                $this->view->newtags = $tagsarray;
            } else {
                $this->view->newtags = $tags['class_tags'];
            }
            $this->view->tags = $tags;
            $objClassEnrollModel = Application_Model_ClassEnroll::getinstance();
            $studentcountresult = $objClassEnrollModel->getStudentsCount($classid);
            if ($studentcountresult) {
                $this->view->stud_count = $studentcountresult;
            }

            $resultCountProjects = $objproject->getProjectsCount($classid);
            $this->view->resultCountProjects = $resultCountProjects;

            /*
             * Partha Neog
             * Added the following part to retrieve discussion details
             * 
             */

            $objClassDiscussionsc = Application_Model_DiscussionComments::getInstance();
            if ($classdiscuss) {
                $i = 0;
                foreach ($classdiscuss as $val) {

                    $discussion_id = $val['discussion_id'];
                    $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                    $commentcount = $objClassDiscussionsc->getDiscussionscCount($discussion_id);
                    if (isset($this->view->session->storage->user_id)) {
                        $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);

                        if ($userresultlike) {
                            $classdiscuss[$i]['islike'] = 1;
                        } else {
                            $classdiscuss[$i]['islike'] = 0;
                        }
                    }
                    $classdiscuss[$i]['discussslikecount'] = $resultlike['num'];
                    $classdiscuss[$i]['discussscount'] = $commentcount;
                    $arr = split("<img ", $classdiscuss[$i]['discussion_description']);
                    if (sizeof($arr) > 1) {
                        foreach ($arr as $val) {
                            if (strpos($val, 'src') === false) {
                                $val = strip_tags($val);
                                $classdiscuss[$i]['shortdicreption'] = $val;
                            }
                        }
                    } else {
                        $classdiscuss[$i]['shortdicreption'] = $classdiscuss[$i]['discussion_description'];
                    }
                    //  echo "<pre>";print_r($classdiscuss[$i]['shortdicreption']);
                    $i++;
                }//die();
                //echo "<pre>";print_r($classdiscuss);
                if ($classdiscuss) {
                    $this->view->classdiscuss = $classdiscuss;
                }
                $resultCountdiscussions = $objTeachingClassDiscussions->getdiscussionCount($classid);

                $this->view->resultCountdiscussions = $resultCountdiscussions;
//        print_r($classdiscuss);
//        die();
            }
            $objTeachingClassesModel = Application_Model_TeachingClasses::getinstance();
            $tags = $objTeachingClassesModel->getTeachClassesDetails($classid);

            $classassignemnt['title'] = $tags['assignment_project_title'];
            $classassignemnt['dec'] = $tags['assignment_project_description'];

            $objfiles = Application_Model_TechingClassFile::getinstance();
            $files = $objfiles->getTeachingClassfiles($classid);
            $i = 0;
            $filesize = "";
            $filename = "";
            if (isset($files)) {
                foreach ($files as $val) {
                    $filepath = $val['class_file_path'];
                    $file = split('/', $filepath);
                    foreach ($file as $res) {
                        $filename = $res;
                    }
                    $size = "";
                    $header = get_headers("http://version2.fashionlearn.com.br/" . $filepath);
                    foreach ($header as $head) {
                        if (preg_match('/Content-Length: /', $head)) {
                            $size = substr($head, 15);
                            $filesize = $size / 1000;
                        }
                    }

                    $filename = split("\.", $filename);
                    if (strlen($filename[0]) > 20) {
                        $filename123 = substr($filename[0], 0, 20) . "..." . $filename[1];
                    } else {
                        $filename123 = substr($filename[0], 0, 20) . "." . $filename[1];
                    }
                    if ($filesize != "") {
                        $files[$i]['filesize'] = $filesize;
                    } else {
                        $files[$i]['filesize'] = 0;
                    }
                    $files[$i]['filename'] = $filename123;
                    $i++;
                }
                $this->view->classfiles = $files;
            }

            if (isset($classassignemnt)) {
                $this->view->classassignemnt = $classassignemnt;
            }
            //---------------------------------------------------------
            $getshare = $objTeachingClassesModel->getClassById($classid);

            $getfbShareCount = $getshare['fb_share_count'];
            $gettwShareCount = $getshare['tw_share_count'];
            $this->view->fbshare = $getfbShareCount;
            $this->view->twcountshare = $gettwShareCount;
            $teacherdetails = $objTeachingClassesModel->getClassById($classid);

            $this->view->teacherdetails = $teacherdetails;
        } else {
            header("location:/allclasses");
            die();
        }


        /* ============ review================= */
        if (isset($this->view->session->storage->user_id)) {

            //$this->view->CheckEnrollClass = $getEnrollCLass;
            if (!$getEnrollCLass) {

//                header("location:/teachclass/" . $classid);
//                die();
            } else {

                $objClassReview = Application_Model_ClassReview::getinstance();
                $myreview = $objClassReview->getMyReview($userid, $classid);
                if ($myreview) {

                    $this->view->myreview = $myreview;
                }
            }
        }
    }

    public function createProjectsAction() {

        $teach = Application_Model_Projects::getinstance();
        $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
        $page = $this->getRequest()->getParam('page');

//             if($page){
//               $this->_helper->layout()->disableLayout();
//              $this->_helper->viewRenderer->setNoRender(false);
//           }
        if (!$page) {

            $page = 0;
        }
        //$count=count($result);
        //$this->view->pages=$pages;
        $result = $teach->getallprojectspage($page);
        $totalprojects = $teach->gettotalprojects();
        $pagecount = count($totalprojects);

        $this->view->pagecount = $pagecount;
        $recentprojects = $teach->mostrecentProjectsPage($page);
        $mostlikeprojects = $teach->mostlikeProjectsPage($page);
        $objClassProjectComments = Application_Model_ProjectComments::getinstance();
        if ($result) {

            $i = 0;
            foreach ($result as $val) {

                $project_id = $val['project_id'];
                $comment = $objClassProjectComments->getComments($project_id);
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);

                if (isset($this->view->session->storage->user_id)) {
                    $userid = $this->view->session->storage->user_id;
                    $this->view->user_id = $userid;
                    $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                    if ($userresultlike) {
                        $result[$i]['islike'] = 1;
                    } else {
                        $result[$i]['islike'] = 0;
                    }
                } else {
                    $result[$i]['islike'] = 0;
                }
                $result[$i]['commentsCount'] = count($comment);
                $result[$i]['discussslikecount'] = $resultlike;
                $i++;
            }
            ;
        }


        if ($recentprojects) {
            $i = 0;
            foreach ($recentprojects as $val) {

                $project_id = $val['project_id'];
                $comment = $objClassProjectComments->getComments($project_id);
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                if (isset($this->view->session->storage->user_id)) {
                    $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                    if ($userresultlike) {
                        $recentprojects[$i]['islike'] = 1;
                    } else {
                        $recentprojects[$i]['islike'] = 0;
                    }
                } else {
                    $recentprojects[$i]['islike'] = 0;
                }
                $recentprojects[$i]['commentsCount'] = count($comment);
                $recentprojects[$i]['discussslikecount'] = $resultlike;
                $i++;
            }
        }


        if ($mostlikeprojects) {
            $i = 0;
            foreach ($mostlikeprojects as $val) {

                $project_id = $val['project_id'];
                $comment = $objClassProjectComments->getComments($project_id);
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);

                if (isset($this->view->session->storage->user_id)) {
                    $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                    if ($userresultlike) {
                        $mostlikeprojects[$i]['islike'] = 1;
                    } else {
                        $mostlikeprojects[$i]['islike'] = 0;
                    }
                }
                $mostlikeprojects[$i]['commentsCount'] = count($comment);
                $mostlikeprojects[$i]['discussslikecount'] = $resultlike;
                $i++;
            }
            $tmp = array();
            foreach ($mostlikeprojects as $key => $row) {
                $tmp[$key] = $row['discussslikecount'];
            }
            array_multisort($tmp, SORT_DESC, $mostlikeprojects);
            $this->view->mostlikeprojects = $mostlikeprojects;
        }

        $this->view->res = $result;

        $this->view->recent = $recentprojects;
        if (isset($this->view->session->storage->user_id)) {
//            $notify = Application_Model_Savednotifications::getinstance();
//            if (isset($userid)) {
//                $notificationresult = $notify->getNotification($userid);
//                $notification_count = count($notificationresult);
//                $this->view->session->storage->notificationresult = $notificationresult;
//                $this->view->session->storage->notification_count = $notification_count;
//            }
            //$this->view->session->storage->notyfyvalue = $notyfyvalue;
        }
    }

    public function projectScrollAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $teach = Application_Model_Projects::getinstance();
        $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
        $page = $this->getRequest()->getParam('page');
        $method = $this->getRequest()->getParam('method');

        $result = $teach->getallprojectspage($page);

        $recentprojects = $teach->mostrecentProjectsPage($page);
        $mostlikeprojects = $teach->mostlikeProjectsPage($page);
        $objClassProjectComments = Application_Model_ProjectComments::getinstance();
        if ($result) {

            $i = 0;
            foreach ($result as $val) {

                $project_id = $val['project_id'];
                $comment = $objClassProjectComments->getComments($project_id);
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);

                if (isset($this->view->session->storage->user_id)) {
                    $userid = $this->view->session->storage->user_id;
                    $this->view->user_id = $userid;
                    $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                    if ($userresultlike) {
                        $result[$i]['islike'] = 1;
                    } else {
                        $result[$i]['islike'] = 0;
                    }
                } else {
                    $result[$i]['islike'] = 0;
                }
                $result[$i]['commentsCount'] = count($comment);
                $result[$i]['discussslikecount'] = $resultlike;
                $i++;
            }
            ;
        }

        $this->view->res = $result;
    }

    public function recentProjectScrollAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);

        $teach = Application_Model_Projects::getinstance();
        $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
        $page = $this->getRequest()->getParam('page');
        $method = $this->getRequest()->getParam('method');

        $result = $teach->getallprojectspage($page);

        $recentprojects = $teach->mostrecentProjectsPage($page);
        $mostlikeprojects = $teach->mostlikeProjectsPage($page);
        $objClassProjectComments = Application_Model_ProjectComments::getinstance();

        if ($recentprojects) {
            $i = 0;
            foreach ($recentprojects as $val) {
                $userid = $this->view->session->storage->user_id;
                $this->view->user_id = $userid;
                $project_id = $val['project_id'];
                $comment = $objClassProjectComments->getComments($project_id);
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                if (isset($this->view->session->storage->user_id)) {
                    $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                    if ($userresultlike) {
                        $recentprojects[$i]['islike'] = 1;
                    } else {
                        $recentprojects[$i]['islike'] = 0;
                    }
                } else {
                    $recentprojects[$i]['islike'] = 0;
                }
                $recentprojects[$i]['commentsCount'] = count($comment);
                $recentprojects[$i]['discussslikecount'] = $resultlike;
                $i++;
            }
        }
        $this->view->recent = $recentprojects;
    }

    public function mostlikedProjectScrollAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);

        $teach = Application_Model_Projects::getinstance();
        $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
        $page = $this->getRequest()->getParam('page');
        $method = $this->getRequest()->getParam('method');

        $result = $teach->getallprojectspage($page);

        $recentprojects = $teach->mostrecentProjectsPage($page);
        $mostlikeprojects = $teach->mostlikeProjectsPage($page);
        $objClassProjectComments = Application_Model_ProjectComments::getinstance();

        if ($mostlikeprojects) {
            $userid = $this->view->session->storage->user_id;
            $this->view->user_id = $userid;
            $i = 0;
            foreach ($mostlikeprojects as $val) {

                $project_id = $val['project_id'];
                $comment = $objClassProjectComments->getComments($project_id);
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);

                if (isset($this->view->session->storage->user_id)) {
                    $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                    if ($userresultlike) {
                        $mostlikeprojects[$i]['islike'] = 1;
                    } else {
                        $mostlikeprojects[$i]['islike'] = 0;
                    }
                }
                $mostlikeprojects[$i]['commentsCount'] = count($comment);
                $mostlikeprojects[$i]['discussslikecount'] = $resultlike;
                $i++;
            }
            $tmp = array();
            foreach ($mostlikeprojects as $key => $row) {
                $tmp[$key] = $row['discussslikecount'];
            }
            array_multisort($tmp, SORT_DESC, $mostlikeprojects);
            $this->view->mostlikeprojects = $mostlikeprojects;
        }
    }

    public function classDiscussionAction() {

        if ($this->getRequest()->getPost()) {
            $data = array();
            if (isset($this->view->session->storage->user_id)) {
                $user_id = $this->view->session->storage->user_id;
            }
            $response = new stdClass();
            $mailer = Engine_Mailer_Mailer::getInstance();
            $notificationcen = Application_Model_Notificationcenter::getinstance();
            $points = Application_Model_Points::getinstance();
            $objUsermetaModel = Application_Model_UsersMeta::getinstance();
            $userdata = $objUsermetaModel->getUserMetaDetail($user_id);
            $objlevel = Application_Model_Levels::getinstance();
            $nextlevel = $objlevel->getlevelsinfo(intval($userdata['level']) + 1);
            $objUserachievement = Application_Model_Userachievements::getinstance();
            $achievement = Application_Model_Achievements::getinstance();
            $usergamestats = Application_Model_Usergamestats::getinstance();
            $uachievement = Application_Model_Userachievements::getinstance();

            $messs = array();
            $date = gmdate('Y-m-d H:i:s', time());
            $classid = $this->getRequest()->getPost("classid");
            $discusstitle = $this->getRequest()->getPost("discusstitle");
            $discusslink = $this->getRequest()->getPost("discusslink");
            $discussdescription = $this->getRequest()->getPost("desp");

            $date123 = date_create();
            $time = date_timestamp_get($date123);
            $discussioncommentone = "";
            $projectdesc1 = $discussdescription;


            $projectdesc1 = split("<img data", $discussdescription);
            if (count($projectdesc1) == 1)
                $projectdesc1 = split("<img style", $discussdescription);
            if (count($projectdesc1) == 1)
                $projectdesc1 = split('<img src="data', $discussdescription);

            $discussdescription = $projectdesc1;

            $userid = $user_id;

            $i = 0;
            $saperatetext = array();
            foreach ($discussdescription as $value) {
                if ($i != 0) {
                    $saperatetext = split(";\">", $value);
                    $saperatetext[0] = split("style=", $saperatetext[0]);
                    $image = split(",", $saperatetext[0][0]);
                    $ifp = fopen("assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg", "wb");
                    $result = fwrite($ifp, base64_decode($image[1]));
                    fclose($ifp);
                    $path = "/assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg";
                    $saperatetext[0] = "<img src=" . $path . " alt='Smiley face'>";
                }
                if ($i == 0) {
                    $discussioncommentone = $value;
                } else {
                    if (isset($saperatetext[0])) {
                        $discussioncommentone = $discussioncommentone . $saperatetext[0];
                    }
                    if (isset($saperatetext[1])) {
                        $discussioncommentone = $discussioncommentone . $saperatetext[1];
                    }
                }$i++;
            }
            $discussdescription = $discussioncommentone;

            $teachhobj = Application_Model_TeachingClasses::getinstance();
            $teachhres = $teachhobj->getTeachingClassescre($classid);
            $objCore = Engine_Core_Core::getInstance();
            $realobj = $objCore->getAppSetting();
            $host = $realobj->hostLink;
            $data["time"] = $date;
            $data["classid"] = $classid;
            $data["creator_id"] = $teachhres["user_id"];
            $data["title"] = $discusstitle;
            $data["initiator_id"] = $user_id;
            $data["reciever_id"] = $teachhres["user_id"];
            $data["img"] = $this->getRequest()->getParam('img');
            $data["link"] = $host . "teachclass/" . $data["classid"];
            $data["type"] = 7;
            $data["seen_status"] = false;
            if ($data["creator_id"] != $user_id) {
                $response->rid = $data["creator_id"];

                $result = $notificationcen->insertnotifi($data);
                $p = $points->getpointsinfo(5);

                $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
                $messs[] = "you earned <span class='color-purple'>" . $p["points"] . "</span> points for starting a Discussion";

                $messs[] = "you earned <span class='color-green'>" . $p["gems"] . "</span> gems  for starting a Discussion ";
                while ($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                    $objUsermetaModel->updatelevel($user_id);

                    $messs[] = 'Congratulation, you are in <span class="color-blue">LEVEL ' . $nextlevel["level"] . '</span>';

                    $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                }
            }
            $data1["discussion"] = 1;
            $usergamestats->updatestats($data1, $user_id);

            $statss = $usergamestats->getstatsinfo($user_id);

            $badges = $uachievement->getachinfo($user_id);
            $achevementsid = array_column($badges, 'achevementsid');

            $newbadges = $achievement->checkbadge($statss, $achevementsid);

            foreach ($newbadges as $b1) {


                $uachievement->awardbadge($user_id, $b1);
            }


            $usergamestats->updatestats($data1, $user_id);

            $discussiondate = gmdate('Y-m-d H:i:s', time());

            $getClass = $teachhobj->getClassUnitID($data["classid"]);
            $data1 = array("user_id" => $user_id, "class_id" => $classid, "discussion_title" => $discusstitle, "discussion_description" => $discussdescription, "discussion_url" => $discusslink, "discussed_date" => $discussiondate);
            $objUsersModel = Application_Model_Users::getinstance();
            $getInitiator = $objUsersModel->getFbConnectedStatus($data["initiator_id"]);
            $getUser = $objUsersModel->getFbConnectedStatus($data["reciever_id"]);
            $notification = Application_Model_Notification::getinstance();
            $notifyAllowed = $notification->getUserNotificationData($data["reciever_id"]);
            if (($notifyAllowed['no_email'] == 0) && ($notifyAllowed['activity_your_discussion'] == 1)) {
                $template_name = 'create discussion';
                $email = $getUser['email'];
                $username = $getUser['first_name'];
                $subject = 'Discussion Created';
                $mergers = array(
                    array(
                        'name' => 'name',
                        'content' => $getUser['first_name']
                    ),
                    array(
                        'name' => 'name2',
                        'content' => $getInitiator['first_name']
                    ),
                    array(
                        'name' => 'classlink',
                        'content' => $data["link"]
                    ),
                    array(
                        'name' => 'classtitle',
                        'content' => $getClass['class_title']
                    ),
                    array(
                        'name' => 'discusstitle',
                        'content' => $data["title"]
                    )
                );
                try {
                    $projectCreated = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                } catch (Exception $ex) {
                    
                }
            }

            $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();
            $discussresult = $objTeachingClassDiscussions->insertDiscussions($data1);
            if ($discussresult) {

                $notification = Application_Model_Notification::getinstance();
                $notifyuserAllowed = $notification->getUserNotificationData($user_id);
                $response->newbadges = $newbadges;
                $messss = "";
                foreach ($messs as $temps) {
                    $messss.="<li class='border-bottom-light'><a>" . $temps . "</a></li>";
                }
                if (count($messs) != 0 && $notifyuserAllowed["no_gam_notifications"] == 0)
                    $response->mess = $messss;
                echo json_encode($response);
                die();
                //location.reload(); 
                // $this->_redirect("/teachclass/" . $classid);
            }
        }
        die();
    }

    public function viewDiscussionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        if ($this->getRequest()->getPost()) {
            if (isset($this->view->session->storage->user_id)) {
                $userid = $this->view->session->storage->user_id;
            }
            $discussionid = $this->getRequest()->getPost('discussionid');


            $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();

            $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();
            $objClassDiscussionCommentLikes = Application_Model_DiscussionCommentLikes::getinstance();
            $objClassDiscussionsComments = Application_Model_DiscussionComments::getinstance();
            $objUser = Application_Model_Users::getinstance();
            if (isset($this->view->session->storage->user_id)) {
                $result = $objTeachingClassDiscussions->getDiscussion($discussionid, $userid);
                if ($result) {
                    $i = 0;
                    foreach ($result as $val) {

                        $discussion_id = $val['discussion_id'];
                        $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                        $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                        if ($userresultlike) {
                            $result[$i]['islike'] = 1;
                        } else {
                            $result[$i]['islike'] = 0;
                        }
                        $result[$i]['discussslikecount'] = $resultlike['num'];
                        $i++;
                    }
                    $this->view->result = $result;
                }
            }
            $resultcomment = $objClassDiscussionsComments->getComments($discussionid);
            if ($resultcomment) {
                $i = 0;
                foreach ($resultcomment as $val) {
                    $comment_id = $val['comment_id'];
                    $resultlike = $objClassDiscussionCommentLikes->getdiscusscommentlikes($comment_id);
                    if (isset($this->view->session->storage->user_id)) {
                        $userresultlike = $objClassDiscussionCommentLikes->getuserdiscusscommentlikes($userid, $comment_id);
                        if ($userresultlike) {
                            $resultcomment[$i]['islike'] = 1;
                        } else {
                            $resultcomment[$i]['islike'] = 0;
                        }
                    }
                    $resultcomment[$i]['discussscommentlikecount'] = $resultlike['num'];
                    $i++;
                }


                $this->view->resultcomment = $resultcomment;
            }
            if (isset($this->view->session->storage->user_id)) {
                $resultuser = $objUser->getUserDetail($userid);
                $this->view->resultuser = $resultuser;
            }
        }
    }

    public function viewCommentAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        if ($this->getRequest()->getPost()) {
            if (isset($this->view->session->storage->user_id)) {
                $userid = $this->view->session->storage->user_id;
            }
            $discussionid = $this->getRequest()->getPost('discussionid');
            $classid = $this->getRequest()->getPost('classid');
            $discussioncomment = $this->getRequest()->getPost('discussioncomment');
            $parentid = $this->getRequest()->getPost('parentid');
            $action = $this->getRequest()->getPost('action');
            $commentid = $this->getRequest()->getPost('commentid');
            $discussioncommentone = $date = date_create();
            $time = date_timestamp_get($date);
            //$discussioncomment = split("<img data",$discussioncomment);
            $discussioncomment1 = $discussioncomment;

            $discussioncomment1 = split("<img data", $discussioncomment);
            if (count($discussioncomment1) == 1)
                $discussioncomment1 = split("<img style", $discussioncomment);
            if (count($discussioncomment1) == 1)
                $discussioncomment1 = split('<img src="data', $discussioncomment);

            $discussioncomment = $discussioncomment1;

            $i = 0;
            $saperatetext = array();
            foreach ($discussioncomment as $value) {
                if ($i != 0) {
                    $saperatetext = split(";\">", $value);
                    $saperatetext[0] = split("style=", $saperatetext[0]);
                    $image = split(",", $saperatetext[0][0]);
                    $ifp = fopen("assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg", "wb");
                    $result = fwrite($ifp, base64_decode($image[1]));
                    fclose($ifp);
                    $path = "/assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg";
                    $saperatetext[0] = "<img src=" . $path . " alt='Smiley face'>";
                }
                if ($i == 0) {
                    $discussioncommentone = $value;
                } else {
                    if (isset($saperatetext[0])) {
                        $discussioncommentone = $discussioncommentone . $saperatetext[0];
                    }
                    if (isset($saperatetext[1])) {
                        $discussioncommentone = $discussioncommentone . $saperatetext[1];
                    }
                }$i++;
            }
            $commentdate = gmdate('Y-m-d H:i:s', time());
            $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();
            $result = $objTeachingClassDiscussions->getDiscussion($discussionid, $userid);
            $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();
            $objClassDiscussionCommentLikes = Application_Model_DiscussionCommentLikes::getinstance();
            $objClassDiscussionsComments = Application_Model_DiscussionComments::getinstance();

            $objUser = Application_Model_Users::getinstance();
            if (isset($this->view->session->storage->user_id)) {
                $data = array("user_id" => $userid, "class_id" => $classid, "discussion_id" => $discussionid, "discussion_comment" => $discussioncommentone, "parent_id" => $parentid, "comment_date" => $commentdate);
                if ($action === "insert") {
                    $insertresult = $objClassDiscussionsComments->insertComments($data);
                } else if ($action == "deletereply") {

                    $deletereplyresult = $objClassDiscussionsComments->deletereplyComments($parentid);
                } else if ($action == "delete") {
                    $deleteresult = $objClassDiscussionsComments->deleteComments($commentid);
                } else {
                    $data = array("discussion_comment" => $discussioncommentone);
                    $updateresult = $objClassDiscussionsComments->updateComments($data, $commentid);
                }
            }

            $resultcomment = $objClassDiscussionsComments->getComments($discussionid);
            $resultuser = $objUser->getUserDetail($userid);
            // print_r($result);die;
            if ($result) {
                $i = 0;
                foreach ($result as $val) {
                    $discussion_id = $val['discussion_id'];
                    $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                    if (isset($this->view->session->storage->user_id)) {
                        $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                        if ($userresultlike) {
                            $result[$i]['islike'] = 1;
                        } else {
                            $result[$i]['islike'] = 0;
                        }
                    }


                    $result[$i]['discussslikecount'] = $resultlike['num'];

                    $i++;
                }
            }

            if ($resultcomment) {
                $i = 0;
                foreach ($resultcomment as $val) {

                    $comment_id = $val['comment_id'];
                    $resultlike = $objClassDiscussionCommentLikes->getdiscusscommentlikes($comment_id);
                    if (isset($this->view->session->storage->user_id)) {
                        $userresultlike = $objClassDiscussionCommentLikes->getuserdiscusscommentlikes($userid, $comment_id);
                        if ($userresultlike) {
                            $resultcomment[$i]['islike'] = 1;
                        } else {
                            $resultcomment[$i]['islike'] = 0;
                        }
                    }
                    $resultcomment[$i]['discussscommentlikecount'] = $resultlike['num'];
                    $commentresult = $objClassDiscussionsComments->getdiscssionreplyCount($comment_id);

                    $resultcomment[$i]['commentresult'] = $commentresult;
                    $i++;
                }
            }

            $this->view->resultuser = $resultuser;
            $this->view->result = $result;

            $this->view->resultcomment = $resultcomment;
        }//die();
    }

    public function viewProjectsAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);

        if ($this->getRequest()->getPost()) {
            if (isset($this->view->session->storage->user_id)) {
                $userid = $this->view->session->storage->user_id;
            }
            $classid = $this->getRequest()->getPost('classid');
            $classname = $this->getRequest()->getPost('classname');
            $discussioncomment = $this->getRequest()->getPost('discussioncomment');
            $this->view->classid = $this->getRequest()->getPost('classid');

            $objTeachingClassesModel = Application_Model_TeachingClasses::getinstance();
            $objUsersModel = Application_Model_Users::getinstance();
            $objUsersMetaModel = Application_Model_UsersMeta::getinstance();
            $objfollow = Application_Model_Followers::getInstance();
            $objproject = Application_Model_Projects::getInstance();
            $objProjectLikes = Application_Model_ProjectLikes::getinstance();

            $teachinclassresult = $objTeachingClassesModel->getClassById($classid);
            $classuserid = $teachinclassresult['user_id'];
            $userresult = $objUsersModel->getUserDetail($classuserid);
            $usermetaresult = $objUsersMetaModel->getUserMetaDetail($classuserid);
            if (isset($this->view->session->storage->user_id)) {
                $followresult = $objfollow->getFollowDetail($userid, $classuserid);

                if ($followresult != 0) {
                    $this->view->followresult = $followresult;
                }
            }
            $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
            /* Get project detail by trend */
            if ($classname == 1) {
                $projectresult = $objproject->getTrendProjectDetail($classid);
            }
            /* Get project detail by Recent */ else if ($classname == 2) {
                $projectresult = $objproject->getRecentProjectDetail($classid);
            }
            /* Get project detail by Popular */ else if ($classname == 3) {
                $projectresult = $objproject->getPopularProjectDetail($classid);
            }

//            echo "<pre>";print_r($projectresult);die;
            if ($projectresult) {
                $i = 0;
                foreach ($projectresult as $val) {

                    $project_id = $val['project_id'];
                    $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                    if (isset($this->view->session->storage->user_id)) {
                        $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                        if ($userresultlike) {
                            $projectresult[$i]['islike'] = 1;
                        } else {
                            $projectresult[$i]['islike'] = 0;
                        }
                    }
                    $projectresult[$i]['projectlikecount'] = $resultlike['num'];
                    $i++;
                }
                if ($classname == 3) {
                    $tmp = array();
                    foreach ($projectresult as $key => $row) {
                        $tmp[$key] = $row['projectlikecount'];
                    }
                    array_multisort($tmp, SORT_DESC, $projectresult);
                }
                $this->view->projectresult = $projectresult;
            }
            $objClassEnrollModel = Application_Model_ClassEnroll::getinstance();


            if (isset($this->view->session->storage->user_id)) {
                $myprojectresult = $objproject->getMyProject($userid, $classid);
                if ($myprojectresult) {
                    $this->view->myprojectresult = $myprojectresult;
                }
            }


            //echo"<pre>"; print_r($playervideo); echo"</pre>";die;

            if (isset($this->view->session->storage->user_id)) {
//                die('dasd');
                $getEnrollCLass = $objClassEnrollModel->getEnrollClass($userid, $classid);
                $this->view->CheckEnrollClass = $getEnrollCLass;
//                print_r($getEnrollCLass); die;
            }

            $this->view->teachingclassresult = $teachinclassresult;
            // $this->view->userresult = $userresult;
            //$this->view->usermetaresult = $usermetaresult;

            $this->view->currenttab = $classname;

            // echo "<pre>"; print_r($myprojectresult);die;
        }
    }

    public function displayDiscussionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);

        if ($this->getRequest()->getPost()) {
            if (isset($this->view->session->storage->user_id)) {
                $userid = $this->view->session->storage->user_id;
            }
            $classid = $this->getRequest()->getPost('classid');
            $classname = $this->getRequest()->getPost('classname');
            $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();

            $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();
            /* Get project detail by trend */
            if ($classname == 1) {
                $result = $objTeachingClassDiscussions->getTrendDetail($classid);
            }
            /* Get project detail by Recent */ else if ($classname == 2) {
                $result = $objTeachingClassDiscussions->getRecentDetail($classid);
            }
            /* Get project detail by Popular */ else if ($classname == 3) {
                $result = $objTeachingClassDiscussions->getTrendDetail($classid);
            }
            if ($result) {
                $i = 0;
                foreach ($result as $val) {

                    $discussion_id = $val['discussion_id'];
                    $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                    if (isset($this->view->session->storage->user_id)) {
                        $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                        if ($userresultlike) {
                            $result[$i]['islike'] = 1;
                        } else {
                            $result[$i]['islike'] = 0;
                        }
                    }
                    $result[$i]['discussslikecount'] = $resultlike['num'];
                    $i++;
                }
                if ($classname == 3) {
                    $tmp = array();
                    foreach ($result as $key => $row) {
                        $tmp[$key] = $row['discussslikecount'];
                    }
                    array_multisort($tmp, SORT_DESC, $result);
                }
                $this->view->result = $result;
            }

//                       print_r($result);die;




            $objClassEnrollModel = Application_Model_ClassEnroll::getinstance();
            if (isset($this->view->session->storage->user_id)) {

                $getEnrollCLass = $objClassEnrollModel->getEnrollClass($userid, $classid);
                $this->view->CheckEnrollClass = $getEnrollCLass;
            }

            $this->view->result = $result;

            $this->view->currenttab = $classname;
        }
    }

    public function showProjectFormAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $userid = $this->view->session->storage->user_id;
        if ($this->getRequest()->getPost()) {

            $classid = $this->getRequest()->getPost('classid');
            $objTeachingClassesModel = Application_Model_TeachingClasses::getinstance();
            $objUsersModel = Application_Model_Users::getinstance();
            $objUsersMetaModel = Application_Model_UsersMeta::getinstance();
            $objfollow = Application_Model_Followers::getInstance();
            $objproject = Application_Model_Projects::getInstance();
            $objTeachingClassDiscussions = Application_Model_ClassDiscussions::getinstance();
            $objProjectLikes = Application_Model_ProjectLikes::getinstance();

            $teachinclassresult = $objTeachingClassesModel->getClassById($classid);
            $classuserid = $teachinclassresult['user_id'];
            $userresult = $objUsersModel->getUserDetail($classuserid);
            $usermetaresult = $objUsersMetaModel->getUserMetaDetail($classuserid);
            $followresult = $objfollow->getFollowDetail($userid, $classuserid);
            $projectresult = $objproject->getProjectDetail($classid);

//        /echo "<pre>";print_r($projectresult);die;
            if ($projectresult) {
                $projectlikescount = array();
                $i = 0;
                foreach ($projectresult as $val) {

                    $project_id = $val['project_id'];
                    $resultproject = $objProjectLikes->getall($project_id);
                    $projectresult[$i]['likecount'] = $resultproject['num'];
                    $i++;
                }
                //echo "<pre>"; print_r($projectresult); die;
            }


            $myprojectresult = $objproject->getMyProject($userid, $classid);




            $this->view->teachingclassresult = $teachinclassresult;
            $this->view->userresult = $userresult;
            $this->view->usermetaresult = $usermetaresult;
            $this->view->projectresult = $projectresult;
            if ($followresult != 0) {
                $this->view->followresult = $followresult;
            }
            // echo "<pre>"; print_r($myprojectresult);die;
            if ($myprojectresult) {
                $this->view->myprojectresult = $myprojectresult;
            }

            // print_r($this->view->followresult);die;
        }
    }

    public function showProjectAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        if ($this->getRequest()->getPost()) {
            if (isset($this->view->session->storage->user_id)) {
                $userid = $this->view->session->storage->user_id;
            }
            $classid = $this->getRequest()->getPost('classid');
            $projectid = $this->getRequest()->getPost('projectid');


            $objTeachingClassProjects = Application_Model_Projects::getinstance();

            $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
            $objClassProjectCommentLikes = Application_Model_ProjectCommentLikes::getinstance();
            $objClassProjectComments = Application_Model_ProjectComments::getinstance();
            $objUser = Application_Model_Users::getinstance();
            $objproject = Application_Model_Projects::getInstance();

            if (isset($this->view->session->storage->user_id)) {
                $result = $objTeachingClassProjects->getProjectById($projectid, $userid);
                if ($result) {
                    $i = 0;
                    foreach ($result as $val) {
                        $project_id = $val['project_id'];
                        $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                        if (isset($this->view->session->storage->user_id)) {
                            $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                            if ($userresultlike) {
                                $result[$i]['islike'] = 1;
                            } else {
                                $result[$i]['islike'] = 0;
                            }
                        }
                        $result[$i]['discussslikecount'] = $resultlike['num'];
                        $i++;
                    }

                    $this->view->result = $result;
                }
            }
            $projectresult = $objproject->getTrendProjectDetail($classid);

//            echo "<pre>";print_r($projectresult);die;
            $resultcomment = $objClassProjectComments->getComments($projectid);
            if (isset($this->view->session->storage->user_id)) {
                $resultuser = $objUser->getUserDetail($userid);
                $this->view->resultuser = $resultuser;
            }

            //  print_r($result);
            if ($resultcomment) {
                $i = 0;
                foreach ($resultcomment as $val) {
                    $comment_id = $val['project_comment_id'];
                    $resultlike = $objClassProjectCommentLikes->getprojectcommentlikes($comment_id);
                    if (isset($this->view->session->storage->user_id)) {
                        $userresultlike = $objClassProjectCommentLikes->getuserprojectcommentlikes($userid, $comment_id);
                        if ($userresultlike) {
                            $resultcomment[$i]['islike'] = 1;
                        } else {
                            $resultcomment[$i]['islike'] = 0;
                        }
                    }
                    $resultcomment[$i]['projectcommentlikecount'] = $resultlike['num'];
                    $i++;
                }
            }



            $this->view->projectresult = $projectresult;
            $this->view->resultcomment = $resultcomment;
        }
    }

    public function projectCommentAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        if ($this->getRequest()->getPost()) {
            if (isset($this->view->session->storage->user_id)) {
                $userid = $this->view->session->storage->user_id;
            }
            $projectid = $this->getRequest()->getPost('projectid');
            $classid = $this->getRequest()->getPost('classid');
            $projectcomment = $this->getRequest()->getPost('projectcomment');
            $parentid = $this->getRequest()->getPost('parentid');
            $action = $this->getRequest()->getPost('action');
            $commentid = $this->getRequest()->getPost('commentid');
            // $projectcomment = strip_tags($projectcomment);
            // echo "<pre>";print_r($projectcomment);die();
            $date = date_create();
            $time = date_timestamp_get($date);
            $discussioncommentone = "";
            $projectcomment1 = $projectcomment;
            $projectcomment1 = split("<img data", $projectcomment);
            if (count($projectcomment1) == 1)
                $projectcomment1 = split("<img style", $projectcomment);
            if (count($projectcomment1) == 1)
                $projectcomment1 = split('<img src="data', $projectcomment);

            $projectcomment = $projectcomment1;
            //   echo "<pre>";print_r($projectcomment1);die();

            $i = 0;
            $saperatetext = array();
            foreach ($projectcomment as $value) {
                if ($i != 0) {
                    $saperatetext = split(";\">", $value);
                    $saperatetext[0] = split("style=", $saperatetext[0]);
                    $image = split(",", $saperatetext[0][0]);
                    $ifp = fopen("assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg", "wb");
                    $result = fwrite($ifp, base64_decode($image[1]));
                    fclose($ifp);
                    $path = "/assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg";
                    $saperatetext[0] = "<img src=" . $path . " alt='Smiley face'>";
                }
                if ($i == 0) {
                    $discussioncommentone = $value;
                } else {
                    if (isset($saperatetext[0])) {
                        $discussioncommentone = $discussioncommentone . $saperatetext[0];
                    }
                    if (isset($saperatetext[1])) {
                        $discussioncommentone = $discussioncommentone . $saperatetext[1];
                    }
                }$i++;
            }

            $projectcomment = $discussioncommentone;


            $commentdate = gmdate('Y-m-d H:i:s', time());
            $objTeachingClassProjects = Application_Model_Projects::getinstance();

            $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
            $objClassProjectCommentLikes = Application_Model_ProjectCommentLikes::getinstance();
            $objClassProjectComments = Application_Model_ProjectComments::getinstance();

            $objUser = Application_Model_Users::getinstance();
            $data = array("user_id" => $userid, "class_id" => $classid, "project_id" => $projectid, "project_comment" => $projectcomment, "parent_id" => $parentid, "project_comment_date" => $commentdate);



            if ($action === "insert") {
                $insertresult = $objClassProjectComments->insertComments($data);
            } else if ($action == "deletereply") {

                $deletereplyresult = $objClassProjectComments->deletereplyComments($commentid);
            } else if ($action == "delete") {
                //die("test");
                $deleteresult = $objClassProjectComments->deleteComments($parentid);
            } else {
                $data = array("project_comment" => $projectcomment);
                $updateresult = $objClassProjectComments->updateComments($data, $commentid);
            }

            if (isset($this->view->session->storage->user_id)) {
                $result = $objTeachingClassProjects->getProjectById($projectid, $userid);
                if ($result) {
                    $i = 0;
                    foreach ($result as $val) {

                        $project_id = $val['project_id'];
                        $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                        if (isset($this->view->session->storage->user_id)) {
                            $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                            if ($userresultlike) {
                                $result[$i]['islike'] = 1;
                            } else {
                                $result[$i]['islike'] = 0;
                            }
                        }
                        $result[$i]['discussslikecount'] = $resultlike['num'];
                        $i++;
                    }
                }

                $this->view->result = $result;
            }

            $resultcomment = $objClassProjectComments->getComments($projectid);

            if (isset($this->view->session->storage->user_id)) {
                $resultuser = $objUser->getUserDetail($userid);
                $this->view->resultuser = $resultuser;
            }

            // print_r($result);die;
            // print_r($result);die;
//            echo '<pre>'; print_r($resultcomment); die;
            if ($resultcomment) {
                $i = 0;
                foreach ($resultcomment as $val) {

                    $comment_id = $val['project_comment_id'];
                    $comment_count = $objClassProjectComments->getCommentReply($comment_id);
                    if (!isset($comment_count)) {
                        $comment_count = 0;
                    }

                    $resultlike = $objClassProjectCommentLikes->getprojectcommentlikes($comment_id);
                    if (isset($this->view->session->storage->user_id)) {
                        $userresultlike = $objClassProjectCommentLikes->getuserprojectcommentlikes($userid, $comment_id);
                        if ($userresultlike) {
                            $resultcomment[$i]['islike'] = 1;
                        } else {
                            $resultcomment[$i]['islike'] = 0;
                        }
                    }

                    $resultcomment[$i]['projectcommentlikecount'] = $resultlike['num'];
                    $resultcomment[$i]['comment_count'] = $comment_count;
                    $i++;
                }
            }
//    echo '<pre>'; print_r($resultcomment); die;

            $this->view->resultcomment = $resultcomment;
        }
    }

    public function leaveReviewAction() {
        $classid = $this->getRequest()->getParam('classid');

        $objClassEnrollModel = Application_Model_ClassEnroll::getinstance();
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $getEnrollCLass = $objClassEnrollModel->getEnrollClass($userid, $classid);
            //$this->view->CheckEnrollClass = $getEnrollCLass;
            if (!$getEnrollCLass) {
                header("location:/teachclass/" . $classid);
                die();
            } else {

                $objClassReview = Application_Model_ClassReview::getinstance();
                $myreview = $objClassReview->getMyReview($userid, $classid);
                if ($myreview) {

                    $this->view->myreview = $myreview;
                }
            }
        } else {
            header("location:/teachclass/" . $classid);
            die();
        }

        $objTeachingClassesModel = Application_Model_TeachingClasses::getinstance();

        $objUsersModel = Application_Model_Users::getinstance();
        $objUsersMetaModel = Application_Model_UsersMeta::getinstance();
        $objClassReviewModel = Application_Model_ClassReview::getinstance();
        $teachinclassresult = $objTeachingClassesModel->getClassById($classid);
        $classuserid = $teachinclassresult['user_id'];
        $userresult = $objUsersModel->getUserDetail($classuserid);

        $this->view->teachinclassresult = $teachinclassresult;
        $this->view->userresult = $userresult;

        if ($this->getRequest()->isPost()) {
            $recommend = $this->getRequest()->getPost('recommend');
            $review = $this->getRequest()->getPost('review');
            $experience = $this->getRequest()->getPost('experience');
            $reviewdate = gmdate('Y-m-d H:i:s', time());
            ;
            $data = array("user_id" => $userid, "class_id" => $classid, "recommend_class" => $recommend, "class_review" => $review, "learning_experience" => $experience, "review_date" => $reviewdate);

            $resultreview = $objClassReviewModel->insertReview($data);
            if ($resultreview) {
                echo 1;
                die();
                //$this->_redirect('/teachclass/' . $classid);
            }
            die();
        }
    }

    public function generateCertificateAction() {
        $this->_helper->layout()->disableLayout();
        $this->view->userName = $this->getRequest()->getParam('userName');
        $this->view->className = $this->getRequest()->getParam('class');
        $this->view->certificate_id = $this->getRequest()->getParam('certificateid');
        $this->view->date = date("d/m/Y");
    }

    public function certificateAction() {
        if (!isset($this->view->session->storage->user_id)) {
            $this->session->x = 12;
            $this->_redirect("/");
        }
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $host = $realobj->hostLink;
        $certificate_id = $this->getRequest()->getParam('id');
        $cid = $this->getRequest()->getParam('cid');

        $objTeachingCertificateModel = Application_Model_Certificate::getinstance();
        $objTeachingClassesModel = Application_Model_TeachingClasses::getinstance();
        $objTeachingVideoModel = Application_Model_TeachingClassVideo::getinstance();
        $objUserMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserDataModel = Application_Model_Users::getinstance();
        $fetchedResult = $objTeachingCertificateModel->getCertificateDetails($certificate_id);

        $user_id = $this->view->session->storage->user_id;
        $fetchedUserId = $fetchedResult['user_id'];
        $fetchedClassId = $fetchedResult['class_id'];


        if ((isset($this->view->session->storage->user_id)) && $cid && ($fetchedUserId == $this->view->session->storage->user_id) && ($fetchedClassId == $cid)) {

            $user_id = $this->view->session->storage->user_id;

            $classid = $cid;

            $classDetailss = $objTeachingVideoModel->getvideothumbnailss($classid);
            $classDetails = $objTeachingClassesModel->getClassById($classid);
            if ($classDetailss[0]['cover_image'] != "")
                $this->view->coverimage = $classDetailss[0]['cover_image'];
            else
                $this->view->coverimage = $classDetailss[0]['video_thumb_url'];


            $className = $classDetails['class_title'];

            $fullname = $this->view->session->storage->first_name . " " . $this->view->session->storage->last_name;

            try {
                $Url = $host . "/generate-certificate?userName=$fullname&class=$className&certificateid=$certificate_id";
                // $Url = "http://api.htm2pdf.co.uk/urltopdf?apikey=yourapikey&url=http://skillshare.globusapps.com/generate-certificate?user=1&class=2";
                $postdata = array(
                    'userName' => $this->view->session->storage->first_name . " " . $this->view->session->storage->last_name,
                    'class' => $className,
                    'certificateid' => $certificate_id
                );
                $ch = curl_init($Url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 4);
                curl_setopt($ch, CURLOPT_POST, true);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                $html2pdf = new HTML2PDF('L', 'A5', 'en', false, 'ISO-8859-15', array(16, 16, 16, 20));
                $output = curl_exec($ch);
                $html2pdf->writeHTML((curl_exec($ch)));
                curl_close($ch);

                if (!file_exists("certificates/$user_id/$classid")) {
                    mkdir("certificates/$user_id/$classid", 0777, true);
                }
                $file = $html2pdf->Output('certificates/' . $user_id . '/' . $classid . '/certificate.pdf', 'F');

                $im = new imagick();
                $im->setResolution(800, 400);
                $im->setSize(800, 400);
                $im->readImage('certificates/' . $user_id . '/' . $classid . '/certificate.pdf' . '[0]');
                $im->setImageFormat("jpg");
                $img_name = 'certificate' . '.jpg';

                $im->writeImage('certificates/' . $user_id . '/' . $classid . '/' . $img_name);
                $im->clear();
                $im->destroy();
                $image = $user_id . '/' . $classid . '/' . $img_name;

                $users = $objUserDataModel->getUserDetail($user_id);
                $this->view->fullname = $users['first_name'] . " " . $users['last_name'];
                $this->view->userpic = $users['user_profile_pic'];
                // $result = $objTeachingCertificateModel->insertCertificate($certificate_id, $user_id, $classid);
                $this->view->image = $image;
                $this->view->className = $className;
                $this->view->classDescription = $classDetails['class_description'];
                $this->view->imagepath = $user_id . '/' . $classid . '/' . $img_name;
                $this->view->filepath = $user_id . '/' . $classid . '/certificate.pdf';
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {

            $users = $objUserDataModel->getUserDetail($fetchedUserId);
            $this->view->fullname = $users['first_name'] . " " . $users['last_name'];
            $classDetails = $objTeachingClassesModel->getClassById($fetchedClassId);

            $classDetailss = $objTeachingVideoModel->getvideothumbnailss($fetchedClassId);
            $classDetails = $objTeachingClassesModel->getClassById($fetchedClassId);
            if ($classDetailss[0]['cover_image'] != "")
                $this->view->coverimage = $classDetailss[0]['cover_image'];
            else
                $this->view->coverimage = $classDetailss[0]['video_thumb_url'];
            $usermetaResult = $objUserMetaModel->getUserMetaDetail($fetchedUserId);
            $img_name = '/certificate' . '.jpg';
            $className = $classDetails['class_title'];
            $this->view->userpic = $users['user_profile_pic'];
            $this->view->image = $fetchedUserId . '/' . $fetchedClassId . '/' . $img_name;
            $this->view->className = $className;
            $this->view->classDescription = $classDetails['class_description'];
            $this->view->imagepath = $fetchedUserId . '/' . $fetchedClassId . '/' . $img_name;
            $this->view->filepath = $fetchedUserId . '/' . $fetchedClassId . '/certificate.pdf';
        }
    }

//    //dev:priyanka varanasi 
//    //desc: To update the defualt video thumbnail with actual thumbnail from vimeo 
//
//    public function loadingVimeourlAction() {
//        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender(true);
//        $objCore = Engine_Core_Core::getInstance();
//        $this->_appSetting = $objCore->getAppSetting();
//        $consumer_key = $this->_appSetting->vimeo->consumerKey;
//        $consumer_secret = $this->_appSetting->vimeo->consumerSecret;
//        $this->view->session->oauth_access_token = "d244b1932b206d65ff783f76fec01d41";
//        $this->view->session->oauth_access_token_secret = "7b5fa7da8b0c8f5f99c81dcfaf9e085edc06c958";
//        $vimeo = new Engine_Vimeo_Vimeo($consumer_key, $consumer_secret, $this->view->session->oauth_access_token, $this->view->session->oauth_access_token_secret);
//        $objclassvideoModel = Application_Model_TeachingClassVideo::getinstance();
//        $videosinresult = $objclassvideoModel->getVimeoVideoIds();
//
//        foreach ($videosinresult as $key => $val) {
//
//            if (!$val['video_thumb_url'] == NULL) {
//                $videodefault = explode('/', $val['video_thumb_url']);
//                if ($videodefault[4] == 'default_200x150') {
//                    try {
//                        $videoslist = $vimeo->call('vimeo.videos.getInfo', array('video_id' => $val['video_id']));
//                        $videos = json_decode(json_encode($videoslist), true);
//                        $thumb['video_thumb_url'] = $videos['video'][0]['thumbnails']['thumbnail'][1]['_content'];
//                        $response = $objclassvideoModel->updateDbWithNewThumb($thumb, $val['video_id']);
//                    } catch (Exception $e) {
//                        
//                    }
//                }
//            } else {
//                try {
//                    $videoslisting = $vimeo->call('vimeo.videos.getInfo', array('video_id' => $val['video_id']));
//                    $videosvimeo = json_decode(json_encode($videoslisting), true);
//                    $thumblist['video_thumb_url'] = $videosvimeo['video'][0]['thumbnails']['thumbnail'][1]['_content'];
//                    $response = $objclassvideoModel->updateDbWithNewThumb($thumblist, $val['video_id']);
//                } catch (Exception $e) {
//                    
//                }
//            }
//        }
//    }
    public function projectImageAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $crop = new Engine_Cropavatar_CropAvatar($_POST['avatar_src'], $_POST['avatar_data'], $_FILES['avatar_file']);
        $response = array(
            'state' => 200,
            'message' => $crop->getMsg(),
            'result' => "/" . $crop->getResult()
        );

        echo json_encode($response);

//        die('123');
    }

    public function allclassnextAction() {
        $this->_helper->layout()->disableLayout();

        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $objCategoryModel = Application_Model_Category::getinstance();
        $objClassEnroll = Application_Model_ClassEnroll::getinstance();
        $objsave = Application_Model_Myclasses::getInstance();
        $method = $this->getRequest()->getParam('method');
        $count123 = $this->getRequest()->getParam('count');
        $county = $this->getRequest()->getParam('county');
        $category = $this->getRequest()->getParam('filter');
        if ($county == 0) {

            $this->view->county = 0;
        }
        if ($count123 == "") {
            $count123 = 1;
        }
        $categoryid = "";
        $allCategories = $objCategoryModel->getAllCategories();
        if (isset($allCategories)) {
            foreach ($allCategories as $cat) {
                if ($category != 'all') {
                    if ($cat['category_name'] == $category) {
                        $categoryid = $cat['category_id'];
                    }
                }
            }
        }
        $this->view->allCategories = $allCategories;
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
        }
        $trending = array();
        if ($method == 'allClasses') {

            $trending = $teachingclass->gettrendingclasses($categoryid);

            $allclasscount = sizeof($trending);

            $allclasscount = $allclasscount / 9;
            $this->view->count123 = ceil($allclasscount);
        }if ($method == 'myclasses') {
            $this->view->method = $method;
            $resultenrole = $objClassEnroll->getEnrollUserClasses($userid);
            $getsaveresponse = array();
            $i = 0;
            if (isset($resultenrole)) {
                foreach ($resultenrole as $value) {
                    $res = $teachingclass->getsingleCLass($value['class_id'], $categoryid);
                    if (sizeof($res) != 0) {
                        $trending[$i] = $res;
                        $i++;
                    }
                }
            }
            $allclasscount = sizeof($trending);
            $allclasscount = $allclasscount / 9;
            $this->view->count123 = ceil($allclasscount);
        }
        if (isset($userid)) {
            $getsaveresponseUser = $objsave->getSaveDetail($userid);
        }

        $userSavedclassId = array();
        if (isset($getsaveresponseUser)) {
            foreach ($getsaveresponseUser as $value) {
                $userSavedclassId[] = $value['class_id'];
            }
        }
        $count = 0;
        if ($trending) {
            foreach ($trending as $val) {

                $objenrolled = Application_Model_ClassEnroll::getinstance();
                $resultenrolle = $objenrolled->getlastweekenrolleddetails($val['class_id']);
                $allreview = $objClassReview->getAllReview($val['class_id']);

                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $trending[$count]['review_per'] = $classreviewpercentage;
                $trending[$count]['thisweekstudents'] = $resultenrolle;
                $trending[$count]['stud_cnt'] = $val['stud_count'];
                $count++;
            }

            function my_sort($a, $b) {
                if ($a["thisweekstudents"] == $b["thisweekstudents"])
                    return 0;
                return ($a["thisweekstudents"] < $b["thisweekstudents"]) ? 1 : -1;
            }

            usort($trending, "my_sort");

            foreach ($trending as $key => $value) {
                $funnyarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                if ($funnyarray) {
                    foreach ($funnyarray as $value) {
                        $trending[$key]['class_video_title'] = $value['class_video_title'];
                        $trending[$key]['class_video_url'] = $value['class_video_url'];
                        $trending[$key]['class_video_id'] = $value['class_video_id'];
                        $trending[$key]['cover_image'] = $value['cover_image'];
                        $trending[$key]['video_thumb_url'] = $value['video_thumb_url'];
                    }
                }
            }
        }
        $count123 = $count123 - 1;
        $pagess = $count123 * 9;
        $pagese = $pagess + 8;
        $i = 0;

        $trending123 = array();
        if (sizeof($trending)) {
            foreach ($trending as $val) {
                if ($pagess <= $i && $pagese >= $i) {
                    $trending123[] = $val;
                }
                $i++;
            }
        }

        $this->view->getsaveresponse = $trending123;
        $this->view->userSavedclassId = $userSavedclassId;
    }

    public function allclassesAction() {
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $objCategoryModel = Application_Model_Category::getinstance();
        $objsave = Application_Model_Myclasses::getInstance();
        $count123 = $this->getRequest()->getParam('count');
        if ($count123 == "") {
            $count123 = 1;
        }
        $allCategories = $objCategoryModel->getAllCategories();
        $this->view->allCategories = $allCategories;
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
        }
        $allclasscount = $teachingclass->getAllCLassescount();
        $allclasscount = $allclasscount / 9;

        $this->view->count123 = ceil($allclasscount);
        $trending = $teachingclass->gettrendingclasses();
        if (isset($userid)) {
            $getsaveresponseUser = $objsave->getSaveDetail($userid);
        }
        $userSavedclassId = array();

        if (isset($getsaveresponseUser)) {
            foreach ($getsaveresponseUser as $value) {
                $userSavedclassId[] = $value['class_id'];
            }
        }
        $count = 0;
        if ($trending) {
            foreach ($trending as $val) {

                $objenrolled = Application_Model_ClassEnroll::getinstance();
                $resultenrolle = $objenrolled->getlastweekenrolleddetails($val['class_id']);
                $allreview = $objClassReview->getAllReview($val['class_id']);

                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $trending[$count]['review_per'] = $classreviewpercentage;
                $trending[$count]['thisweekstudents'] = $resultenrolle;
                $trending[$count]['stud_cnt'] = $val['stud_count'];
                $count++;
            }

            function my_sort($a, $b) {
                if ($a["thisweekstudents"] == $b["thisweekstudents"])
                    return 0;
                return ($a["thisweekstudents"] < $b["thisweekstudents"]) ? 1 : -1;
            }

            usort($trending, "my_sort");

            foreach ($trending as $key => $value) {
                $funnyarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                if ($funnyarray) {
                    foreach ($funnyarray as $value) {
                        $trending[$key]['class_video_title'] = $value['class_video_title'];
                        $trending[$key]['class_video_url'] = $value['class_video_url'];
                        $trending[$key]['class_video_id'] = $value['class_video_id'];
                        $trending[$key]['cover_image'] = $value['cover_image'];
                        $trending[$key]['video_thumb_url'] = $value['video_thumb_url'];
                    }
                }
            }
        }
        $count123 = $count123 - 1;
        $pagess = $count123 * 9;
        $pagese = $pagess + 8;
        $i = 0;
        $trending123 = array();
        if(isset($trending)){
        foreach ($trending as $val) {
            if ($pagess <= $i && $pagese >= $i) {
                $trending123[] = $val;
            }
            $i++;
        }
    }
        $this->view->getsaveresponse = $trending123;
        $this->view->userSavedclassId = $userSavedclassId;
    }

    public function projectsAction() {
        
    }

    public function classteachAction() {
        
    }

    public function tooltipAction() {


        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->_request->isxmlhttprequest()) {
            $userid = $this->view->session->storage->user_id;
            $uid = $this->getRequest()->getPost("uid");
            $preq = $this->getRequest()->getPost("preq");
            $objvideothumbsModel = Application_Model_TeachingClassVideo::getinstance();
            $objfollow = Application_Model_Followers::getInstance();
            $objproject = Application_Model_Projects::getInstance();


            $ifollowresult = $objfollow->getIFollow($uid);
            $followmeresult = $objfollow->getFollowMe($uid);


            if ($preq == 1)
                $getProjects = $objproject->getProjects($uid);
            else
                $vidthumbs = $objvideothumbsModel->getvideothumbnails($uid);
            if (isset($this->view->session->storage->user_id)) {
                $followresult = $objfollow->getFollowDetail($userid, $uid);
            }

            $newCalss = new stdClass();
            $newCalss->ifollowresult = count($ifollowresult);
            $newCalss->followmeresult = count($followmeresult);
            if (isset($followresult['follow_status']))
                $newCalss->followstatus = $followresult['follow_status'];
            else
                $newCalss->followstatus = 1;

            if ($preq == 1)
                $newCalss->getProjects = $getProjects;
            else
                $newCalss->getvidthumbs = $vidthumbs;

            if (!isset($followresult) || $followresult['follow_status'] == null)
                $newCalss->followstatus = 1;
            if (isset($userid)) {
                if ($userid == $uid)
                    $newCalss->followstatus = 3;
            }
            echo json_encode($newCalss);
            die;
        }
    }

    public function createProjectHandlerAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $userid = $this->view->session->storage->user_id;

        $objproject = Application_Model_Projects::getInstance();
        $projecttitle = $this->getRequest()->getParam('title');
        $projectdesc = $this->getRequest()->getParam('desc');
        $projectprivasy = $this->getRequest()->getParam('privasy');
        $classid = $this->getRequest()->getParam('classesid');
        $image = $this->getRequest()->getParam('manage_image');
        $projectlogo = $image;
        if ($projectlogo == "") {
            $projectlogo = "/assets/images/alternateicon.png";
        }
        $date = date_create();
        $time = date_timestamp_get($date);
        $discussioncommentone = "";
        $projectdesc1 = $projectdesc;


        $projectdesc1 = split("<img data", $projectdesc);
        if (count($projectdesc1) == 1)
            $projectdesc1 = split("<img style", $projectdesc);
        if (count($projectdesc1) == 1)
            $projectdesc1 = split('<img src="data', $projectdesc);

        $projectdesc = $projectdesc1;



        $i = 0;
        $saperatetext = array();
        foreach ($projectdesc as $value) {
            if ($i != 0) {
                $saperatetext = split(";\">", $value);
                $saperatetext[0] = split("style=", $saperatetext[0]);
                $image = split(",", $saperatetext[0][0]);
                $ifp = fopen("assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg", "wb");
                $result = fwrite($ifp, base64_decode($image[1]));
                fclose($ifp);
                $path = "/assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg";
                $saperatetext[0] = "<img src=" . $path . " alt='Smiley face'>";
            }
            if ($i == 0) {
                $discussioncommentone = $value;
            } else {
                if (isset($saperatetext[0])) {
                    $discussioncommentone = $discussioncommentone . $saperatetext[0];
                }
                if (isset($saperatetext[1])) {
                    $discussioncommentone = $discussioncommentone . $saperatetext[1];
                }
            }$i++;
        }
        $projectdesc = $discussioncommentone;

        $privacy = 0;
        if ($projectprivasy == "Only me") {
            $privacy = 0;
        } elseif ($projectprivasy == "Classmates") {
            $privacy = 1;
        } else {
            $privacy = 2;
        }
        $data = array("user_id" => $userid, "class_id" => $classid, "project_title" => $projecttitle, "project_cover_image" => $projectlogo, "project_workspace" => $projectdesc, "project_privacy" => $privacy, "project_created_date" => gmdate('Y-m-d H:i:s', time()));
        $saveresponse = $objproject->updateProject($userid, $classid, $data);
        echo $saveresponse;
        die();
    }

    public function editProjectHandlerAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $userid = $this->view->session->storage->user_id;
        $objproject = Application_Model_Projects::getInstance();
        $projecttitle = $this->getRequest()->getParam('title');
        $projectdesc = $this->getRequest()->getParam('desc');
        $projectprivasy = $this->getRequest()->getParam('privasy');

        $classid = $this->getRequest()->getParam('classesid');
        $image = $this->getRequest()->getParam('manage_image');
        $projectlogo = "";
        if ($image != "") {
            $projectlogo = $image;
        } else {
            $projectlogo = $this->getRequest()->getParam('oldurl');
            $projectlogo = substr($projectlogo, 0);
        }
        $date = date_create();
        $time = date_timestamp_get($date);
        $discussioncommentone = "";
        $projectdesc1 = $projectdesc;


        $projectdesc1 = split("<img data", $projectdesc);

        if (count($projectdesc1) == 1)
            $projectdesc1 = split('<img src="data', $projectdesc);

        $projectdesc = $projectdesc1;
        $i = 0;
        $saperatetext = array();
        foreach ($projectdesc as $value) {
            if ($i != 0) {
                $saperatetext = split(";\">", $value);
                $saperatetext[0] = split("style=", $saperatetext[0]);
                $image = split(",", $saperatetext[0][0]);
                $ifp = fopen("assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg", "wb");
                $result = fwrite($ifp, base64_decode($image[1]));
                fclose($ifp);
                $path = "/assets/discussioncommentimgs/" . $time . $userid . $i . ".jpg";
                $saperatetext[0] = "<img src=" . $path . " alt='Smiley face'>";
            }
            if ($i == 0) {
                $discussioncommentone = $value;
            } else {
                if (isset($saperatetext[0])) {
                    $discussioncommentone = $discussioncommentone . $saperatetext[0];
                }
                if (isset($saperatetext[1])) {
                    $discussioncommentone = $discussioncommentone . $saperatetext[1];
                }
            }$i++;
        }
        $projectdesc = $discussioncommentone;

        $privacy = $projectprivasy;

        $data = array("user_id" => $userid, "class_id" => $classid, "project_title" => $projecttitle, "project_cover_image" => $projectlogo, "project_workspace" => $projectdesc, "project_privacy" => $privacy, "project_created_date" => gmdate('Y-m-d H:i:s', time()));
        $saveresponse = $objproject->updateProject($userid, $classid, $data);
        echo 1;
        die();
    }

    public function applicationAction() {
        
    }

    public function updateSocialShareAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $teachingclassModel = Application_Model_TeachingClasses::getInstance();
        $pobj = Application_Model_Projects::getInstance();
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $host = $realobj->hostLink;
        if ($this->_request->isxmlhttprequest()) {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $method = $this->getRequest()->getParam('method');
            if ($this->getRequest()->getParam('classid'))
                $classid = $this->getRequest()->getParam('classid');
            if ($this->getRequest()->getParam('pid'))
                $pid = $this->getRequest()->getParam('pid');

            switch ($method) {
                case'fbshare':
                    $getshare = $teachingclassModel->getClassById($classid);
                    $getShareCount = $getshare['fb_share_count'];
                    $newShareCount = $getShareCount + 1;
                    $data = array('fb_share_count' => $newShareCount);
                    $updateshareValue = $teachingclassModel->insertingAlbumId($data, $classid);

                    if (isset($updateshareValue)) {
                        $getshare = $teachingclassModel->getClassById($classid);
                        echo json_encode($getshare);
                    }

                    break;

                case'twshare':

                    $getshare = $teachingclassModel->getClassById($classid);
                    $getShareCount = $getshare['tw_share_count'];
                    $newShareCount = $getShareCount + 1;
                    $data = array('tw_share_count' => $newShareCount);
                    $updateshareValue = $teachingclassModel->insertingAlbumId($data, $classid);

                    if (isset($updateshareValue)) {
                        $getshare = $teachingclassModel->getClassById($classid);
                        echo json_encode($getshare);
                    }

                    break;

                case'fpshare':

                    $res = $pobj->pfshare($pid);
                    if ($res)
                        echo json_encode(1);
                    else
                        echo json_encode(0);

                    break;

                case'tpshare':

                    $res = $pobj->ptshare($pid);
                    $link = $this->bitly_url_shorten($host . '/teachclass/' . $classid . '?actionname=project&projetid=' . $pid, '0ac6fd974647efb386cb6f3b509a4ae4f3100df4', 'fsln.me');
                    $data["link"] = $link;

                    echo json_encode($data["link"]);
                    break;

                case'ppshare':

                    $res = $pobj->ppshare($pid);
                    if ($res)
                        echo json_encode(1);
                    else
                        echo json_encode(0);

                    break;
            }
        }
    }

    public function commentreplyAction() {
        $objusermeta = Application_Model_UsersMeta::getInstance();
        $objClassProjectComments = Application_Model_ProjectComments::getInstance();
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->getPost()) {
            if (isset($this->view->session->storage->user_id)) {
                $userid = $this->view->session->storage->user_id;
            }
            $projectid = $this->getRequest()->getPost('projectid');
            $classid = $this->getRequest()->getPost('classid');
            $projectcomment = $this->getRequest()->getPost('projectcomment');
            $parentid = $this->getRequest()->getPost('parentid');
            $action = $this->getRequest()->getPost('action');
            $commentid = $this->getRequest()->getPost('commentid');

            $date = date_create();
            $time = date_timestamp_get($date);
            $result = $objusermeta->getUserMetaDetail($userid);

            $commentdate = gmdate('Y-m-d H:i:s', time());
            $data = array("user_id" => $userid, "class_id" => $classid, "project_id" => $projectid, "project_comment" => $projectcomment, "parent_id" => $parentid, "project_comment_date" => $commentdate);
            $insertresult = $objClassProjectComments->insertComments($data);
            if ($insertresult) {
                $time = $this->time_elapsed_string($commentdate);
                echo $result['user_profile_pic'] . '?' . $result['first_name'] . '?' . $result['user_headline'] . '?' . $time;
                die;
            }
        }
    }

    public function imageUploadAction() {
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $date = date_create();
            $time = date_timestamp_get($date);
            $objCore = Engine_Core_Core::getInstance();
            $realobj = $objCore->getAppSetting();
            $host = $realobj->hostLink;

            if ($_FILES['file']['name']) {
                if (!$_FILES['file']['error']) {
                    $name = md5(rand(100, 200));
                    $ext = explode('.', $_FILES['file']['name']);
                    $filename = $name . '.' . $ext[1];
                    $destination = 'assets/discussioncommentimgs/' . $time . $userid . $filename; //change this directory
                    $location = $_FILES["file"]["tmp_name"];
                    move_uploaded_file($location, $destination);
                    echo $host . $destination; //change this URL
                } else {
                    echo $message = 'Ooops!  Your upload triggered the following error:  ' . $_FILES['file']['error'];
                }
            }
        }



        die();
    }

    public function time_elapsed_string($created_time) {
        date_default_timezone_set('UTC'); //Change as per your default time
        $str = strtotime($created_time);
        $today = strtotime(date('Y-m-d H:i:s'));

        // It returns the time difference in Seconds...
        $time_differnce = $today - $str;

        // To Calculate the time difference in Years...
        $years = 60 * 60 * 24 * 365;

        // To Calculate the time difference in Months...
        $months = 60 * 60 * 24 * 30;

        // To Calculate the time difference in Days...
        $days = 60 * 60 * 24;

        // To Calculate the time difference in Hours...
        $hours = 60 * 60;

        // To Calculate the time difference in Minutes...
        $minutes = 60;

        if (intval($time_differnce / $years) > 1) {
            return intval($time_differnce / $years) . " years ago";
        } else if (intval($time_differnce / $years) > 0) {
            return intval($time_differnce / $years) . " year ago";
        } else if (intval($time_differnce / $months) > 1) {
            return intval($time_differnce / $months) . " months ago";
        } else if (intval(($time_differnce / $months)) > 0) {
            return intval(($time_differnce / $months)) . " month ago";
        } else if (intval(($time_differnce / $days)) > 1) {
            return intval(($time_differnce / $days)) . " days ago";
        } else if (intval(($time_differnce / $days)) > 0) {
            return intval(($time_differnce / $days)) . " day ago";
        } else if (intval(($time_differnce / $hours)) > 1) {
            return intval(($time_differnce / $hours)) . " hours ago";
        } else if (intval(($time_differnce / $hours)) > 0) {
            return intval(($time_differnce / $hours)) . " hour ago";
        } else if (intval(($time_differnce / $minutes)) > 1) {
            return intval(($time_differnce / $minutes)) . " minutes ago";
        } else if (intval(($time_differnce / $minutes)) > 0) {
            return intval(($time_differnce / $minutes)) . " minute ago";
        } else if (intval(($time_differnce)) > 1) {
            return intval(($time_differnce)) . " seconds ago";
        } else {
            return "few seconds ago";
        }
    }

    public function addvideocommentAction() {
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $video_id = $this->getRequest()->getPost('video_id');
            $comment = $this->getRequest()->getPost('comment');
            $img = $this->getRequest()->getPost('img');
            $name = $this->getRequest()->getPost('name');
            $video_timing = $this->getRequest()->getPost('video_timing');
            $videoprivacy = $this->getRequest()->getPost('videoprivacy');
           if($videoprivacy=="private"){
                
                $privacy=1;
            }
            else{
                $privacy=0;
                
            }
         
            $videocomment = Application_Model_videocomment::getInstance();
            $data = array(
                'user_id' => $userid,
                'video_id' => $video_id,
                //'class_unit_id' => $videoid,
                'comment_text' => $comment,
                'comment_point_time' => $video_timing,
                'users_pic' => $img,
                'user_name' => $name,
                'privacy'  =>$privacy
            );
            $status = $videocomment->insertvideocomment($data);
            echo json_encode($status);
        }
    }

    public function getvideocommentAction() {
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $video_id = $this->getRequest()->getPost('video_id');


            $videocomment = Application_Model_videocomment::getInstance();

            $allvideocommens = $videocomment->getvideocomments($video_id,$userid);

            echo json_encode($allvideocommens);
        }
    }

    public function editvideocommentAction() {
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $comment_id = $this->getRequest()->getPost('comment_id');
            $comment = $this->getRequest()->getPost('comment');
            $videoprivacy = $this->getRequest()->getPost('videoprivacy');
         
            if($videoprivacy=="Private"){
                
                $privacy=1;
            }
            else{
                $privacy=0;
                
            }

            $data = array(
                'comment_id' => $comment_id,
                'comment_text' => $comment,
                'privacy' => $privacy
            );
            $videocomment = Application_Model_videocomment::getInstance();
            $result=$videocomment->editvideocomment($data, $comment_id);
            echo $result; die;
        }
    }

    public function deletevideocommentAction() {
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $comment_id = $this->getRequest()->getPost('comment_id');


            $videocomment = Application_Model_videocomment::getInstance();
            $deleted = $videocomment->deletevideocomment($comment_id);
            echo json_encode($deleted);
        }
    }

    public function checklikestatusAction() {
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $comment_id = $this->getRequest()->getPost('comment_id');


            $videocommentlike = Application_Model_videocommentlike::getInstance();
            $result = $videocommentlike->checklike($comment_id, $userid);
            if ($result > 0) {
                echo $result;
            } else {
                echo "disliked";
            }
        }
    }

    public function allvideocommentsAction() {
        if (isset($this->view->session->storage->user_id)) {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(TRUE);
            $userid = $this->view->session->storage->user_id;
            $video_id = $this->getRequest()->getPost('video_id');
            

            $videocomment = Application_Model_videocomment::getInstance();
                
            $allvideocomments = $videocomment->getvideocomments($video_id,$userid);
           
             echo json_encode($allvideocomments);
        }
    }

}
