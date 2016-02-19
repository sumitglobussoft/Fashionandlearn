<?php

/**
 * AdminController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class Home_HomeController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function preDispatch() {
        $request = new Zend_Controller_Request_Http();
        

        //set cookie for unique visitor count
        //setcookie('cookieName', 'value', 'lifetime', 'path', 'domain'); 
        if ($request->getCookie('fashioncount')==null) {
            setcookie("fashioncount", 0, time() + (86400 * 30), "/");
            $objss = Application_Model_Sitestatistics::getInstance();
           $objss->insertstatistics("visit");
           
        }
        $objCategoryModel = Application_Model_Category::getInstance();
        $allCategories = $objCategoryModel->getAllCategories();
        $this->view->AllCategories = $allCategories;
    }

    public function homeAction() {

        if ($this->view->auth->hasIdentity()) {
            $this->_redirect('/dashboard');
        }
        //dev:priyanka varanasi
        //dev: to get facebook login url
        $objFacebookModel = Engine_Facebook_Facebookclass::getInstance();
        $url = $objFacebookModel->getLoginUrl();
        $this->view->fbLogin = $url;

        //For showing all Classes in Home-page
        $objAllClasses = Application_Model_TeachingClasses::getInstance();
        $classResult = $objAllClasses->getAllCLasses();
        $this->view->classes = $classResult;
//        echo "<pre>";print_r($classResult);die;
//        
//        
        //////priyanka varanasi
        //To show the trending classes in home page 
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $trending = $teachingclass->gettrendingclasses();
        $objUserModel = Application_Model_Users::getinstance();
        $objSecurity = Engine_Vault_Security::getInstance();
        $objUsermetaModel = Application_Model_UsersMeta::getinstance();
        $objNotificationModel = Application_Model_Notification::getinstance();
        $gameobj = Application_Model_Usergamestats::getinstance();
        $mailer = Engine_Mailer_Mailer::getInstance();

        if ($trending) {
            foreach ($trending as $key => $row) {
                $value[$key] = $row['stud_count'];
            }
            array_multisort($value, SORT_DESC, $trending);

            $count = 0;
            foreach ($trending as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);

                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $trending[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }
            foreach ($trending as $key => $value) {
                $funnyarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);

                if ($funnyarray) {
                    foreach ($funnyarray as $value) {
                        $trending[$key]['class_video_title'] = $value['class_video_title'];
                        $trending[$key]['class_video_url'] = $value['class_video_url'];
                        $trending[$key]['class_video_id'] = $value['class_video_id'];
                        $trending[$key]['video_id'] = $value['video_id'];
                        $trending[$key]['cover_image'] = $value['cover_image'];
                        $trending[$key]['video_thumb_url'] = $value['video_thumb_url'];
                    }
                }
            }

//            echo"<pre>";print_r($trending);echo"</pre>"; die;

            $this->view->trending = $trending;
        }


        //For showing all Projects in Home-Page
        $teach = Application_Model_Projects::getinstance();
        $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
        $objProjectComments = Application_Model_ProjectComments::getinstance();
        $result = $teach->getallprojects();
        $recentprojects = $teach->mostrecentProjects();
        $mostlikeprojects = $teach->mostlikeProjects();

        if ($result) {
            $i = 0;
            foreach ($result as $val) {

                $project_id = $val['project_id'];
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                $commentCount = $objProjectComments->getComments($project_id);
                if (isset($this->view->session->storage->user_id)) {
                    $userid = $this->view->session->storage->user_id;
                    $this->view->user_id = $userid;
                    $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                    if ($userresultlike) {
                        $result[$i]['islike'] = 1;
                    } else {
                        $result[$i]['islike'] = 0;
                    }
                }
                $result[$i]['discussslikecount'] = @$resultlike;
                $result[$i]['comment_count'] = @count($commentCount);
                $i++;
            }
        }
//        echo "<pre>";print_r($result);die();
        $this->view->projects = $result;
//        $this->view->recent = $recentprojects;
        
        
             if ($this->getRequest()->isPost()) {
           
            $firstname = $this->getRequest()->getPost('fname');
            $email = $this->getRequest()->getPost('E-mail');
            $password = $this->getRequest()->getPost('pwd');
            $data =array();
          
                if (isset($firstname)&& isset($email) && isset($password)) {

                    $data = array('first_name' => $firstname,
                        'password' => sha1(md5($password)),
                        'email' => $email,
                        'status' => '1',
                        'role' => '1',
                        'reg_date' => date('y/m/d')
                            
                    );
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
                
                $paydata = array('user_id' => $insertionResult);
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
                $username = $this->getRequest()->getPost('fname');
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
  
        
    }
    
     public function projectsbeforeloginAction() {


        $objFacebookModel = Engine_Facebook_Facebookclass::getInstance();
        $url = $objFacebookModel->getLoginUrl();
        $this->view->fbLogin = $url;
        
        $objCategoryModel = Application_Model_Category::getInstance();
        $allCategories = $objCategoryModel->getAllCategories();
        $this->view->AllCategories = $allCategories;

        $teach = Application_Model_Projects::getinstance();
        $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
        $result = $teach->getallprojects();
        $recentprojects = $teach->mostrecentProjects();
        $mostlikeprojects = $teach->mostlikeProjects();
        $objprojectcomments = Application_Model_ProjectComments::getInstance();

        if ($result) {
            $i = 0;
            foreach ($result as $val) {
                $project_id = $val['project_id'];
                $result1 = $objClassProjectLikes->getmostpoular($project_id);
                $projectcomment = $objprojectcomments->getComments($project_id);
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
                $result[$i]['popapularity'] = $result1;
                $result[$i]['likecount'] = $resultlike;
                $result[$i]['commentcount'] = sizeof($projectcomment);
                $i++;
            }
            foreach ($result as $key => $row) {
                $tmp1[$key] = $row['popapularity'];
            }
            array_multisort($tmp1, SORT_DESC, $result);
        }

        if ($recentprojects) {
            $i = 0;
            foreach ($recentprojects as $val) {

                $project_id = $val['project_id'];
                $projectcomment = $objprojectcomments->getComments($project_id);
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
                $recentprojects[$i]['likecount'] = $resultlike;
                $recentprojects[$i]['commentcount'] = sizeof($projectcomment);
                $i++;
            }
        }
        if ($mostlikeprojects) {
            $i = 0;
            foreach ($mostlikeprojects as $val) {

                $project_id = $val['project_id'];
                $projectcomment = $objprojectcomments->getComments($project_id);
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);

                if (isset($this->view->session->storage->user_id)) {
                    $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                    if ($userresultlike) {
                        $mostlikeprojects[$i]['islike'] = 1;
                    } else {
                        $mostlikeprojects[$i]['islike'] = 0;
                    }
                }
                $mostlikeprojects[$i]['likecount'] = $resultlike;
                $mostlikeprojects[$i]['commentcount'] = sizeof($projectcomment);
                $i++;
            }
            $tmp = array();
            foreach ($mostlikeprojects as $key => $row) {
                $tmp[$key] = $row['likecount'];
            }
            array_multisort($tmp, SORT_DESC, $mostlikeprojects);
        }
        $this->view->mostlikeprojects = $mostlikeprojects;

        $this->view->res = $result;

        $this->view->recent = $recentprojects;
        
        
    }
    
    

}
