<?php

/**
 * AdminController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';
require_once 'Engine/html2pdf_v4.03/html2pdf.class.php';

require_once "assets/dompdf/vendor/autoload.php";
define('DOMPDF_ENABLE_AUTOLOAD', false);
require_once 'assets/dompdf/dompdf_config.inc.php';

class Users_UsersController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function usersAction() {
        
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
            $this->view->userwebsite = $getmetaresult['user_website'];
            $Coursesinstructing = $teachingclass->getCountOfClasses($user_id);

            $this->view->session->storage->courses = count($Coursesinstructing);
            $objnotifiModel = Application_Model_Notificationcenter::getInstance();

            $notificount = $objnotifiModel->getnotificount($user_id);
            $notifi = $objnotifiModel->getnotifi($user_id);

            $this->view->notificount = $notificount;
            $this->view->notifi = $notifi;


            $objPayment = Application_Model_PaymentNew::getinstance();
            $getMemberss = $objPayment->getUserPaymentInfo($user_id);
            $this->view->session->storage->member = $getMemberss;
            $request = new Zend_Controller_Request_Http();
            $objss = Application_Model_Sitestatistics::getInstance();
            if ($request->getCookie('fashioncount') == 0) {

                $objss->removestatistics();
                setcookie("fashioncount", 1, time() + (86400 * 30), "/");
            }
            if ($request->getCookie('fashionsignup') == 0) {

                if ($getMemberss == 1) {
                    $objss->insertstatistics("freeuser");
                } else {
                    $objss->insertstatistics($getMemberss["status"]);
                }
                setcookie("fashionsignup", 1, 0, "/");
            }

//print_r($getMemberss["status"]);
//die();



            $uachievement = Application_Model_Userachievements::getinstance();
            $badges = $uachievement->getlachinfo($user_id);
            $this->view->badges = $badges;
            $this->view->cbadges = count($uachievement->getachinfo($user_id));
            $uachievement = Application_Model_Userachievements::getinstance();
            $badges = $uachievement->getlachinfo($user_id);
            $this->view->badges = $badges;
            $this->view->cbadges = count($uachievement->getachinfo($user_id));
        }
        $objCategoryModel = Application_Model_Category::getInstance();
        $allCategories = $objCategoryModel->getAllCategories();
        $this->view->AllCategories = $allCategories;
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $this->view->host = $realobj->hostLink;
    }

    /**
      Developer: partha neog
      Description: this method search the table for the query asked and returns user_id
      and based on user_id getting details of associated videos.
     * */
    public function searchAction() {

        $count = 0;
        $search = $this->getRequest()->getParam('query');
        //$this->view->session->storage->search = $search;
        //echo "<pre>"; print_r($this->view->session->storage->search); echo "</pre>"; die('123'); 
        $objUserModel = Application_Model_Users::getinstance();
        $objCategoryModel = Application_Model_Category::getinstance();
        $objClassTag = Application_Model_TeachingClasses::getinstance();

        // Search using firstname or last name 
        if ($count == 0) {

            $Result = $objUserModel->searchUsersResult(trim(preg_replace('!\s+!', ' ', $search)));         //getting user_id,name,video url, video thumb url,video title and profile pik
//            echo '<pre>';print_r($Result); die;
            if (isset($Result)) {
                $this->view->detail = $Result;
                $count = $count + 1;
            }
        }

        // Search using category     
//        if ($count == 0) {
//
//            $Result = $objCategoryModel->getCategoryDetail($search);
//
//            if (isset($Result)) {
//                $this->view->detail = $Result;
//                $count = $count + 1;
//            }
//        }
//
//        // Search using classtags    
//        if ($count == 0) {
//
//            $Result = $objClassTag->getClassTags($search);
////             echo "<pre>"; print_r($Result); echo "</pre>"; die('123');
//            if (isset($Result)) {
//                $this->view->detail = $Result;
//                $count = $count + 1;
//            }
//        }
        // when no match found
        if ($count == 0) {

            $this->_redirect('/noresult');
        }
    }

    // Name: Jeykumar
    public function userProfileAction() {

        $userid = $this->getRequest()->getParam('userid');
        $currentuserid;
        $this->view->userid = $userid;

        if (isset($this->view->session->storage->user_id)) {
            $currentuserid = $this->view->session->storage->user_id;
        }
        $this->view->currentuserid = $currentuserid;
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objfollow = Application_Model_Followers::getInstance();

        $usermetaresult = $objMetaModel->getUserMetaDetail($userid);

        $userresult = $objUserModel->getUserDetail($userid);

        if (isset($currentuserid)) {
            $followresult = $objfollow->getFollowDetail($currentuserid, $userid);
            if ($followresult) {
                $this->view->followresult = $followresult;
            }
        }
        // echo"<pre>";print_r($followresult);echo"</pre>";
        $ifollowresult = $objfollow->getIFollow($userid);
        //echo"<pre>";print_r($ifollowresult);echo"</pre>";
        $followmeresult = $objfollow->getFollowMe($userid);

        $this->view->usermetaresult = $usermetaresult;
        $this->view->userresult = $userresult;
//       echo '<pre>'; print_r($ifollowresult); echo '<pre>';     print_r($followmeresult); die;
        $this->view->ifollowresult = $ifollowresult;
        $this->view->followmeresult = $followmeresult;
        $objGetMyProjects = Application_Model_Projects::getInstance();
        $resultMyProject = $objGetMyProjects->myProjectDetails($userid);
        $this->view->resultmyproject = $resultMyProject;
//        echo "<pre>"; print_r($resultMyProject);die;

        $objClassReview = Application_Model_ClassReview::getinstance();
//-------------------for teaching----------------                                       // Name:Namrata
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $result = $teachingclass->getTeachClasses($userid);
        if ($result) {
            $count = 0;
            foreach ($result as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);
                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $result[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }
        }
        $this->view->teachresult = $result;

//-------------------for enrolled classes--------
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $teachingclassenroll = Application_Model_ClassEnroll::getinstance();
        $recentlyadded = $teachingclassenroll->getEnrollUserClasses($userid);

        if ($recentlyadded) {
            $count = 0;
            foreach ($recentlyadded as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);

                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $recentlyadded[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }

            foreach ($recentlyadded as $key => $value) {
                $funniestofarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                if ($funniestofarray) {
                    foreach ($funniestofarray as $var) {
                        $recentlyadded[$key]['class_video_title'] = $var['class_video_title'];
                        $recentlyadded[$key]['class_video_url'] = $var['class_video_url'];
                        $recentlyadded[$key]['class_video_id'] = $var['class_video_id'];
                        $recentlyadded[$key]['cover_image'] = $var['cover_image'];
                        $recentlyadded[$key]['video_thumb_url'] = $var['video_thumb_url'];
                    }
                }
            }
        }
        $this->view->enrolluserclass = $recentlyadded;
        // echo "<pre>"; print_r($recentlyadded); die; 
        //--------------- Social media status ------------------ //
        if (!empty($currentuserid)) {
            $fbstatus = $objUserModel->getFbConnectedStatus($currentuserid);
            $this->view->fbstatus = $fbstatus;
            //echo '<pre>'; print_r($fbstatus); die;
        }
        //--------------- Review -------------------------------- //
        $allRecomended = $teachingclass->getAllReview($userid);
        $negativeRecomended = $teachingclass->getReviews($userid);
        $percentageReview = 0;
        if ($allRecomended > 0) {
            $percentageReview = (($allRecomended - $negativeRecomended) / $allRecomended) * 100;
        }

        if (isset($percentageReview) > 0) {
            $this->view->percentReview = $percentageReview;
        }
        $this->view->showReview = $allRecomended;

        //-----------------projects-------------------------------------- //
        $project = Application_Model_Projects::getinstance();
        $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
        $objprojectcomments = Application_Model_ProjectComments::getInstance();
        $getmyprojects = $project->getrecentProjects($userid);
        $count = 0;
        if (isset($getmyprojects)) {
            foreach ($getmyprojects as $p) {
                $projectcomment = $objprojectcomments->getComments($p["project_id"]);
                $countss = $objClassProjectLikes->getprojectlikes($p["project_id"]);
                $getmyprojects[$count]['commentcount'] = sizeof($projectcomment);
                $getmyprojects[$count]["likescount"] = $countss;
                $count++;
            }
        }
        $this->view->getmyprojects = $getmyprojects;
        //------------------Saved-----------------------------//
        $objsave = Application_Model_Myclasses::getInstance();
        $getsaveresponse = $objsave->getSaveclassDetail($userid);
    
        $objClassEnroll = Application_Model_ClassEnroll::getinstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        if ($getsaveresponse) {
            $count = 0;
            foreach ($getsaveresponse as $val) {
                $result1 = $teachingclass->getTeachClassesDetails($val['class_id']);
                $result = $objClassEnroll->getStudentsCount($val['class_id']);
                $allreview = $objClassReview->getAllReview($val['class_id']);
                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $getsaveresponse[$count]['category_name'] = $result1['category_name'];
                $getsaveresponse[$count]['student_count'] = $result['stud_count'];
                $getsaveresponse[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }
        }
        $this->view->getsaveresponse = $getsaveresponse;

        $objbadges = Application_Model_Userachievements::getInstance();
        $badges = $objbadges->getuserbadges($userid);
        $this->view->badges = $badges;
    }

    public function classesAction() {
          if (isset($this->view->session->storage->user_id)) {
              header("location:/allclasses");
          }
          

        $categoryname = $this->getRequest()->getParam('category');
//        echo $categoryname; 
        $objCategoryModel = Application_Model_Category::getinstance();
        $allCategories = $objCategoryModel->getAllCategories();
        $this->view->categoryname = $categoryname;
        $this->view->allCategories = $allCategories;

        if ($categoryname === 'Trending') {
            $objMetaModel = Application_Model_UsersMeta::getinstance();
            $objUserModel = Application_Model_Users::getinstance();
            $objClassReview = Application_Model_ClassReview::getinstance();
            $teachingclass = Application_Model_TeachingClasses::getinstance();
            $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
            $objsave = Application_Model_Myclasses::getInstance();
            $trending = $teachingclass->gettrendingclasses();

            if ($trending) {
                foreach ($trending as $key => $row) {
                    $value[$key] = $row['stud_count'];
                }
                @array_multisort($value, SORT_DESC, $trending);
            }
            $count = 0;
            if ($trending) {
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
                            $trending[$key]['cover_image'] = $value['cover_image'];
                            $trending[$key]['video_thumb_url'] = $value['video_thumb_url'];
                        }
                    }
                }
            }
//        echo "<pre>";print_r($trending);die;
            $this->view->category = $trending;
        } elseif ($categoryname === 'Highest Rated') {
            $objMetaModel = Application_Model_UsersMeta::getinstance();
            $objUserModel = Application_Model_Users::getinstance();
            $teachingclass = Application_Model_TeachingClasses::getinstance();
            $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
            $objClassReview = Application_Model_ClassReview::getinstance();
            $objsave = Application_Model_Myclasses::getInstance();
            $higlyrated = $teachingclass->gettrendingclasses();

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
                @array_multisort($value, SORT_DESC, $higlyrated);
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
//            echo "<pre>";print_r($higlyrated);die;
                $this->view->category = $higlyrated;
            }
        } elseif ($categoryname === 'Recently Added') {
            $objMetaModel = Application_Model_UsersMeta::getinstance();
            $objUserModel = Application_Model_Users::getinstance();
            $teachingclass = Application_Model_TeachingClasses::getinstance();
            $objClassReview = Application_Model_ClassReview::getinstance();
            $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
            $objsave = Application_Model_Myclasses::getInstance();
            $recentlyadded = $teachingclass->gettrendingclasses();


            if ($recentlyadded) {
                foreach ($recentlyadded as $key => $row) {
                    $value[$key] = $row['class_created_date'];
                }
                @array_multisort($value, SORT_DESC, $recentlyadded);

                $count = 0;
                foreach ($recentlyadded as $val) {
                    $allreview = $objClassReview->getAllReview($val['class_id']);

                    $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                    if (count($allreview) != 0) {
                        $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                    } else {
                        $classreviewpercentage = 0;
                    }
                    $recentlyadded[$count]['review_per'] = $classreviewpercentage;
                    $count++;
                }

                foreach ($recentlyadded as $key => $value) {
                    $funniestofarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                    if ($funniestofarray) {
                        foreach ($funniestofarray as $var) {
                            $recentlyadded[$key]['class_video_title'] = $var['class_video_title'];
                            $recentlyadded[$key]['class_video_url'] = $var['class_video_url'];
                            $recentlyadded[$key]['class_video_id'] = $var['class_video_id'];
                            $recentlyadded[$key]['cover_image'] = $var['cover_image'];
                            $recentlyadded[$key]['video_thumb_url'] = $var['video_thumb_url'];
                        }
                    }
                }
            }
//        echo "<pre>";print_r($recentlyadded);die;
            $this->view->category = $recentlyadded;
        } else {
            $response = $objCategoryModel->getDetail($categoryname);
            $cat_id = $response['category_id'];
            if ($cat_id == 0) {
                $cat_id = 0;
            }

            $objTeachingClassesModel = Application_Model_TeachingClasses::getinstance();
            $responsesubcat = $objTeachingClassesModel->getSubCategory($cat_id);
            $responsevideos = $objTeachingClassesModel->getcategoryvideos($cat_id);

            $this->view->responsevideos = $responsevideos;

//        echo "<pre>";print_r($responsesubcat);die;

            $objClassReview = Application_Model_ClassReview::getinstance();
            $objClassEnroll = Application_Model_ClassEnroll::getinstance();
            if ($responsesubcat) {
                $count = 0;
                foreach ($responsesubcat as $val) {
                    $allreview = $objClassReview->getAllReview($val['class_id']);
                    $calculatereview = $objClassReview->getCalculateReview($val['class_id']);
                    $studentCnt = $objClassEnroll->getStudentsCount($val['class_id']);

                    if (count($allreview) != 0) {
                        $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                    } else {
                        $classreviewpercentage = 0;
                    }
                    $responsesubcat[$count]['review_per'] = $classreviewpercentage;
                    $responsesubcat[$count]['stud_count'] = $studentCnt['stud_count'];
                    $count++;
                }
            }
            $this->view->category = $responsesubcat;
//        echo "<pre>";print_r($responsesubcat);die;
        }
    }

    public function myClassesActionb() {

        $userid = $this->view->session->storage->user_id;
//        echo $userid;die;
        $objsave = Application_Model_Myclasses::getInstance();
        $objteachinclasses = Application_Model_TeachingClasses::getInstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objCategoryModel = Application_Model_Category::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $objClassEnroll = Application_Model_ClassEnroll::getinstance();
        $allCategories = $objCategoryModel->getAllCategories();
        $userresponse = $objUserModel->getUserDetail($userid);
        $resultenrole = $objClassEnroll->getEnrollUserClasses($userid);
        $getsaveresponse = array();
        $i = 0;
        if (isset($resultenrole)) {
            foreach ($resultenrole as $value) {
                $res = $objteachinclasses->getsingleCLass($value['class_id']);
                if (sizeof($res) != 0) {
                    $getsaveresponse[$i] = $res;
                    $i++;
                }
            }
        }
        $allclasscount = sizeof($getsaveresponse);
        $allclasscount = $allclasscount / 9;
        $this->view->count123 = ceil($allclasscount);
        if ($getsaveresponse) {
            $count = 0;
            foreach ($getsaveresponse as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);
                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);
                $studentCnt = $objClassEnroll->getStudentsCount($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $getsaveresponse[$count]['review_per'] = $classreviewpercentage;
                $getsaveresponse[$count]['stud_cnt'] = $studentCnt['stud_count'];
                $count++;
            }
        }
//      echo "<pre>";print_r($getsaveresponse);echo "</pre>";die();
        $this->view->allCategories = $allCategories;
        $this->view->getsaveresponse = $getsaveresponse;
        $this->view->userresponse = $userresponse;
    }
    public function myClassesAction() {

        $userid = $this->view->session->storage->user_id;
//        echo $userid;die;
        $objsave = Application_Model_Myclasses::getInstance();
        $objteachinclasses = Application_Model_TeachingClasses::getInstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objCategoryModel = Application_Model_Category::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $objClassEnroll = Application_Model_ClassEnroll::getinstance();
        $allCategories = $objCategoryModel->getAllCategories();
        $userresponse = $objUserModel->getUserDetail($userid);
        $resultenrole = $objClassEnroll->getEnrollUserClasses($userid);
        $getsaveresponse = array();
        $i = 0;
        if (isset($resultenrole)) {
            foreach ($resultenrole as $value) {
                $res = $objteachinclasses->getsingleCLass($value['class_id']);
                if (sizeof($res) != 0) {
                    $getsaveresponse[$i] = $res;
                    $i++;
                }
            }
        }
        $allclasscount = sizeof($getsaveresponse);
        $allclasscount = $allclasscount / 9;
        $this->view->count123 = ceil($allclasscount);
        if ($getsaveresponse) {
            $count = 0;
            foreach ($getsaveresponse as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);
                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);
                $studentCnt = $objClassEnroll->getStudentsCount($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $getsaveresponse[$count]['review_per'] = $classreviewpercentage;
                $getsaveresponse[$count]['stud_cnt'] = $studentCnt['stud_count'];
                $count++;
            }
        }
//      echo "<pre>";print_r($getsaveresponse);echo "</pre>";die();
        $this->view->allCategories = $allCategories;
        $this->view->getsaveresponse = $getsaveresponse;
        $this->view->userresponse = $userresponse;
        
        
        //-------------------for enrolled classes--------
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $teachingclassenroll = Application_Model_ClassEnroll::getinstance();
        $recentlyadded = $teachingclassenroll->getEnrollUserClasses($userid);

        if ($recentlyadded) {
            $count = 0;
            foreach ($recentlyadded as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);

                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $recentlyadded[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }

            foreach ($recentlyadded as $key => $value) {
                $funniestofarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                if ($funniestofarray) {
                    foreach ($funniestofarray as $var) {
                        $recentlyadded[$key]['class_video_title'] = $var['class_video_title'];
                        $recentlyadded[$key]['class_video_url'] = $var['class_video_url'];
                        $recentlyadded[$key]['class_video_id'] = $var['class_video_id'];
                        $recentlyadded[$key]['cover_image'] = $var['cover_image'];
                        $recentlyadded[$key]['video_thumb_url'] = $var['video_thumb_url'];
                    }
                }
            }
        }
        $this->view->enrolluserclass = $recentlyadded;
        // echo "<pre>"; print_r($recentlyadded); die; 
        //-------------------for teaching----------------                                       // Name:Namrata
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $result = $teachingclass->getTeachClasses($userid);
        if ($result) {
            $count = 0;
            foreach ($result as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);
                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $result[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }
        }
        $this->view->teachresult = $result;
         //------------------Saved-----------------------------//
        $objsave = Application_Model_Myclasses::getInstance();
        $getsaveresponse = $objsave->getSaveclassDetail($userid);
    
        $objClassEnroll = Application_Model_ClassEnroll::getinstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        if ($getsaveresponse) {
            $count = 0;
            foreach ($getsaveresponse as $val) {
                $result1 = $teachingclass->getTeachClassesDetails($val['class_id']);
                $result = $objClassEnroll->getStudentsCount($val['class_id']);
                $allreview = $objClassReview->getAllReview($val['class_id']);
                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $getsaveresponse[$count]['category_name'] = $result1['category_name'];
                $getsaveresponse[$count]['student_count'] = $result['stud_count'];
                $getsaveresponse[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }
        }
        $this->view->getsaveresponse = $getsaveresponse;
        
        //-------------------for completed classes--------
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $teachingclassenroll = Application_Model_ClassEnroll::getinstance();
        $recentlyadded = $teachingclassenroll->getEnrollUsercClasses($userid);

        if ($recentlyadded) {
            $count = 0;
            foreach ($recentlyadded as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);

                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $recentlyadded[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }

            foreach ($recentlyadded as $key => $value) {
                $funniestofarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                if ($funniestofarray) {
                    foreach ($funniestofarray as $var) {
                        $recentlyadded[$key]['class_video_title'] = $var['class_video_title'];
                        $recentlyadded[$key]['class_video_url'] = $var['class_video_url'];
                        $recentlyadded[$key]['class_video_id'] = $var['class_video_id'];
                        $recentlyadded[$key]['cover_image'] = $var['cover_image'];
                        $recentlyadded[$key]['video_thumb_url'] = $var['video_thumb_url'];
                    }
                }
            }
        }
        $this->view->completed = $recentlyadded;
        // echo "<pre>"; print_r($recentlyadded); die; 
        
        
    }

    public function classesAjaxHandlerAction() {

        $objvideothumbsModel = Application_Model_TeachingClassVideo::getinstance();
        $objfollow = Application_Model_Followers::getInstance();
        $objproject = Application_Model_Projects::getInstance();






        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $uid = $this->getRequest()->getPost("uid");





        $ifollowresult = $objfollow->getIFollow($uid);
        $followmeresult = $objfollow->getFollowMe($uid);
        $preq = $this->getRequest()->getPost("preq");
        if ($preq == 1)
            $getProjects = $objproject->getProjects($uid);
        else
            $vidthumbs = $objvideothumbsModel->getvideothumbnails($uid);
//             if($getProjects){
//                 $this->view->getProjects=$getProjects;
//                 
//         
//    }



        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
            $followresult = $objfollow->getFollowDetail($userid, $uid);
            // print_r($followresult);
            //  die();
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
    }

    /**
      Developer: Namrata Singh
     * date: 30/1/2015
      Description: for profile changes/update functionality.
     * */
    public function dashboardAction() {
   
        $userid = $this->view->session->storage->user_id;

        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objfollow = Application_Model_Followers::getInstance();

        $userresult = $objUserModel->getUserDetail($userid);
        $ifollowresult = $objfollow->getIFollow($userid);
        $followmeresult = $objfollow->getFollowMe($userid);
        $this->view->userresult = $userresult;
        $this->view->ifollowresult = $ifollowresult;
        $this->view->followmeresult = $followmeresult;

        if ($this->getRequest()->isPost()) {
            $reactive = $this->getRequest()->getPost('id');
            $this->view->reactive = $reactive;
        } else {
            $reactive = $this->getRequest()->getParam('id');
            $this->view->reactive = $reactive;
        }

        $userid = $this->view->session->storage->user_id;
 
        $project = Application_Model_Projects::getinstance();
     
        $classenroll = Application_Model_ClassEnroll::getinstance();
        $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
        $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();
        $objreferModel = Application_Model_ReferFriends::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $usermetaresult = $objMetaModel->getUserMetaDetail($userid);
        unset($this->view->session->storage->signup);

        $this->view->metaresult = $usermetaresult;

         $paymentnewModal = Application_Model_PaymentNew::getinstance();
         $customerstatus = $paymentnewModal->getUserPaymentInfo($userid);
        $this->view->session->storage->premium_status = $customerstatus['customer_status'];
  
       $this->view->session->storage->member['status'] == 'paid';
      $this->view->currentStatus = $this->view->session->storage->member['customer_status'];

        $userid = $this->view->session->storage->user_id;
        $teachingclass = Application_Model_TeachingClasses::getinstance();


        $checkteacher = $teachingclass->checkTeacher($userid);
        if ($checkteacher['user_id'] > 0) {

            $this->view->session->storage->teacher = 'teacher123';
        }
        $objClassReview = Application_Model_ClassReview::getinstance();
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $teachingclassenroll = Application_Model_ClassEnroll::getinstance();
        $recentlyadded = $teachingclassenroll->getEnrollUserClasses($userid);

        if ($recentlyadded) {

    $count = 0;
            foreach ($recentlyadded as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);

                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $recentlyadded[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }

            foreach ($recentlyadded as $key => $value) {
                $funniestofarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                if ($funniestofarray) {
                    foreach ($funniestofarray as $var) {
                        $recentlyadded[$key]['class_video_title'] = $var['class_video_title'];
                        $recentlyadded[$key]['class_video_url'] = $var['class_video_url'];
                        $recentlyadded[$key]['class_video_id'] = $var['class_video_id'];
                        $recentlyadded[$key]['cover_image'] = $var['cover_image'];
                        $recentlyadded[$key]['video_thumb_url'] = $var['video_thumb_url'];
                    }
                }
            }
        }
        $enrolledc = $teachingclassenroll->getEnrollUserClasses($userid);
        $objuserenroled = Application_Model_ClassEnroll::getinstance();
        if (isset($enrolledc)) {
            $count = 0;
            foreach ($enrolledc as $value) {
                $userenroledtoclass = $objuserenroled->getStudentsCount($value['class_id']);
                $enrolledc[$count]['userenroled'] = $userenroledtoclass;
                $count++;
            }
        }

        $this->view->enrolluserclass = $recentlyadded;
        $objuserenroled = Application_Model_ClassEnroll::getinstance();
        if (isset($enrolledc)) {
            $count = 0;
            foreach ($enrolledc as $value) {
                $userenroledtoclass = $objuserenroled->getStudentsCount($value['class_id']);
                $enrolledc[$count]['userenroled'] = $userenroledtoclass;
                $count++;
            }
        }
        $this->view->enrolledc = $enrolledc;
       $enrollusertrendproject = $classenroll->getEnrollUserProject($userid);
        $enrolluserrecentproject = $classenroll->getEnrollUserRecentProject($userid);
        $enrolluserlikeproject = $classenroll->getEnrollUserLikeProject($userid);

        $enrollusertrenddiscussion = $classenroll->getEnrollUserDiscussion($userid);
        $enrolluserrecentdiscussion = $classenroll->getEnrollUserRecentDiscussion($userid);
        $enrolluserlikediscussion = $classenroll->getEnrollUserLikeDiscussion($userid);

        if ($enrollusertrendproject) {

            $i = 0;
            foreach ($enrollusertrendproject as $val) {

                $project_id = $val['project_id'];
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                if ($userresultlike) {
                    $enrollusertrendproject[$i]['islike'] = 1;
                } else {
                    $enrollusertrendproject[$i]['islike'] = 0;
                }
                $enrollusertrendproject[$i]['projectlikecount'] = $resultlike['num'];
                $i++;
            }

            $this->view->enrollusertrendproject = $enrollusertrendproject;
        }


        if ($enrolluserrecentproject) {

            $i = 0;
            foreach ($enrolluserrecentproject as $val) {

                $project_id = $val['project_id'];
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                if ($userresultlike) {
                    $enrolluserrecentproject[$i]['islike'] = 1;
                } else {
                    $enrolluserrecentproject[$i]['islike'] = 0;
                }
                $enrolluserrecentproject[$i]['projectlikecount'] = $resultlike['num'];
                $i++;
            }

            $this->view->enrolluserrecentproject = $enrolluserrecentproject;
        }
        if ($enrolluserlikeproject) {

            $i = 0;
            foreach ($enrolluserlikeproject as $val) {

                $project_id = $val['project_id'];
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                if ($userresultlike) {
                    $enrolluserlikeproject[$i]['islike'] = 1;
                } else {
                    $enrolluserlikeproject[$i]['islike'] = 0;
                }
                $enrolluserlikeproject[$i]['projectlikecount'] = $resultlike['num'];
                $i++;
            }
            $tmp = array();
            foreach ($enrolluserlikeproject as $key => $row) {
                $tmp[$key] = $row['projectlikecount'];
            }
            array_multisort($tmp, SORT_DESC, $enrolluserlikeproject);
            $this->view->enrolluserlikeproject = $enrolluserlikeproject;
        }

//        echo "<pre>";print_r($enrolluserlikeproject);die;
        if ($enrollusertrenddiscussion) {
            $i = 0;
            foreach ($enrollusertrenddiscussion as $val) {

                $discussion_id = $val['discussion_id'];
                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                if ($userresultlike) {
                    $enrollusertrenddiscussion[$i]['islike'] = 0;
                } else {
                    $enrollusertrenddiscussion[$i]['islike'] = 1;
                }
                $enrollusertrenddiscussion[$i]['discusslikecount'] = $resultlike['num'];
                $i++;
            }
            $this->view->enrollusertrenddiscussion = $enrollusertrenddiscussion;
        }

        if ($enrolluserrecentdiscussion) {
            $i = 0;
            foreach ($enrolluserrecentdiscussion as $val) {

                $discussion_id = $val['discussion_id'];
                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                if ($userresultlike) {
                    $enrolluserrecentdiscussion[$i]['islike'] = 1;
                } else {
                    $enrolluserrecentdiscussion[$i]['islike'] = 0;
                }
                $enrolluserrecentdiscussion[$i]['discusslikecount'] = $resultlike['num'];
                $i++;
            }
            $this->view->enrolluserrecentdiscussion = $enrolluserrecentdiscussion;
        }

        if ($enrolluserlikediscussion) {
            $i = 0;
            foreach ($enrolluserlikediscussion as $val) {

                $discussion_id = $val['discussion_id'];
                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);

                if ($userresultlike) {
                    $enrolluserlikediscussion[$i]['islike'] = 1;
                } else {
                    $enrolluserlikediscussion[$i]['islike'] = 0;
                }
                $enrolluserlikediscussion[$i]['discusslikecount'] = $resultlike['num'];
                $i++;
            }
            $tmp = array();
            foreach ($enrolluserlikediscussion as $key => $row) {
                $tmp[$key] = $row['discusslikecount'];
            }
            array_multisort($tmp, SORT_DESC, $enrolluserlikediscussion);
            $this->view->enrolluserlikediscussion = $enrolluserlikediscussion;
        }
//        echo 'fashiontuts1'; die;
        $userresponse = $objUserModel->getUserDetail($userid);

        $this->view->result = $userresponse;

        $usermetaresult = $objMetaModel->getUserMetaDetail($userid);
        $this->view->metaresult = $usermetaresult;

        $newprojects = $project->newProjects();
        $this->view->newprojects = $newprojects;

        $allprojects = $project->getallprojects();

        $result = $objUserModel->publishedClass($userid);
        $this->view->session->storage->publish_status = $result;

        $enrollresult = $classenroll->getEnrollMember($userid);

        $this->view->enroll = $enrollresult;
        if (!empty($enrollresult)) {
            foreach ($enrollresult as $key) {
                $a[] = $key['class_id'];
            }

        } else {
            
        }




        /*
         * Abhishek M
         * 
         * 
         */
        $objprojectcomments = Application_Model_ProjectComments::getInstance();

        $getmyprojects = $project->getrecentProjects($userid);
        $count = 0;
        if (isset($getmyprojects)) {
            foreach ($getmyprojects as $p) {
                $countss = $objClassProjectLikes->getprojectlikes($p["project_id"]);
                $projectcomment = $objprojectcomments->getComments($p["project_id"]);
                $getmyprojects[$count]['commentcount'] = sizeof($projectcomment);
                $getmyprojects[$count]["likescount"] = $countss;
                $count++;
            }

            $this->view->getmyprojects = $getmyprojects;
        }
        $objsave = Application_Model_Myclasses::getInstance();
        $getsaveresponse = $objsave->getSaveclassDetail($userid);
        $objClassEnroll = Application_Model_ClassEnroll::getinstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        if ($getsaveresponse) {
            $count = 0;
            foreach ($getsaveresponse as $val) {
                $result1 = $teachingclass->getTeachClassesDetails($val['class_id']);
                $result = $objClassEnroll->getStudentsCount($val['class_id']);
                $allreview = $objClassReview->getAllReview($val['class_id']);
                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);
                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $getsaveresponse[$count]['category_name'] = $result1['category_name'];
                $getsaveresponse[$count]['student_count'] = $result['stud_count'];
                $getsaveresponse[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }
        }
        $this->view->getsaveresponse = $getsaveresponse;

        $getmetaresult = $objMetaModel->gettopscores();
        $this->view->topscores = $getmetaresult;
        //---------------Social Media status------------------------
        if (!empty($userid)) {
            $fbstatus = $objUserModel->getFbConnectedStatus($userid);
            $this->view->fbstatus = $fbstatus;
            //echo '<pre>'; print_r($fbstatus); die;
        }
        ///dev: priyanka varanasi
        //desc: added these lines of code for showing the button of upgrade for unpaid users
        //dated: 31/8/2015
        $user_id = $this->view->session->storage->user_id;
        $paymentnewModal  =  Application_Model_PaymentNew::getinstance();
        $userpayinfo = $paymentnewModal->getUserPaymentInfo($user_id);
        $this->view->upgradestatus =  $userpayinfo;
      

        
        
        
        
    }

    /**
      Developer: Namrata Singh
     * date: 31/1/2015
      Description: for showing all the details about the people who follows
     *             the classes.
     * */
    public function followingAction() {
        $userid = $this->view->session->storage->user_id;

        $objUserModel = Application_Model_Users::getinstance();
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $userresponse = $objUserModel->getUserDetail($userid);
        $usermetaresult = $objMetaModel->getUserMetaDetail($userid);
        $objClassReview = Application_Model_ClassReview::getinstance();
        $this->view->result = $userresponse;
        $this->view->metaresult = $usermetaresult;

        $objFollowModel = Application_Model_Followers::getinstance();
        $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
        $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();
        $followersresult = $objFollowModel->getPeopleFollowDetails($userid);

        if ($followersresult) {
            $count = 0;
            foreach ($followersresult as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);
                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);
                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $followersresult[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }

            $this->view->follow = $followersresult;
            $this->view->sucess = "sucess";
        }

        $followusertrendproject = $objFollowModel->getFollowUserProject($userid);
        $followuserrecentproject = $objFollowModel->getFollowUserRecentProject($userid);
        $followuserlikeproject = $objFollowModel->getFollowUserLikeProject($userid);
        $followusertrenddiscussion = $objFollowModel->getFollowUserDiscussion($userid);
        $followuserrecentdiscussion = $objFollowModel->getFollowUserRecentDiscussion($userid);
        $followuserlikediscussion = $objFollowModel->getFollowUserLikeDiscussion($userid);
        $objprojectcomments = Application_Model_ProjectComments::getinstance();

        // echo "<pre>"; print_r($followersresult); echo "</pre>"; die('123');
        //   $this->view->follow = $followersresult;

        $objprojectcomments = Application_Model_ProjectComments::getinstance();

        if ($followusertrendproject) {

            $i = 0;
            foreach ($followusertrendproject as $val) {


                $project_id = $val['project_id'];
                $projectcomments = $objprojectcomments->getComments($project_id);

                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                if ($userresultlike) {
                    $followusertrendproject[$i]['islike'] = 1;
                } else {
                    $followusertrendproject[$i]['islike'] = 0;
                }
                $followusertrendproject[$i]['projectlikecount'] = $resultlike;
                $followusertrendproject[$i]['commentcount'] = count($projectcomments);
                $i++;
            }

            $this->view->followusertrendproject = $followusertrendproject;
        }
        if ($followuserrecentproject) {

            $i = 0;
            foreach ($followuserrecentproject as $val) {

                $project_id = $val['project_id'];
                $projectcomments = $objprojectcomments->getComments($project_id);
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);

                $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);

                if ($userresultlike) {
                    $followuserrecentproject[$i]['islike'] = 0;
                } else {
                    $followuserrecentproject[$i]['islike'] = 1;
                }
                $followuserrecentproject[$i]['projectlikecount'] = $resultlike;
                $followuserrecentproject[$i]['commentcount'] = sizeof($projectcomments);
                $i++;
            }

            $this->view->followuserrecentproject = $followuserrecentproject;
        }
        if ($followuserlikeproject) {

            $i = 0;
            foreach ($followuserlikeproject as $val) {

                $project_id = $val['project_id'];
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                if ($userresultlike) {
                    $followuserlikeproject[$i]['islike'] = 1;
                } else {
                    $followuserlikeproject[$i]['islike'] = 0;
                }
                $followuserlikeproject[$i]['projectlikecount'] = $resultlike['num'];
                $i++;
            }
            $tmp = array();
            foreach ($followuserlikeproject as $key => $row) {
                $tmp[$key] = $row['projectlikecount'];
            }
            array_multisort($tmp, SORT_DESC, $followuserlikeproject);
            $this->view->followuserlikeproject = $followuserlikeproject;
        }


        if ($followusertrenddiscussion) {
            $i = 0;
            foreach ($followusertrenddiscussion as $val) {

                $discussion_id = $val['discussion_id'];
                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);

                if ($userresultlike) {
                    $followusertrenddiscussion[$i]['islike'] = 1;
                } else {
                    $followusertrenddiscussion[$i]['islike'] = 0;
                }
                $followusertrenddiscussion[$i]['discusslikecount'] = $resultlike['num'];
                $arr = split("<img ", $followusertrenddiscussion[$i]['discussion_description']);
                if (sizeof($arr) > 1) {
                    foreach ($arr as $val) {
                        if (strpos($val, 'src') === false) {
                            $val = strip_tags($val);
                            $followusertrenddiscussion[$i]['shortdicreption'] = $val;
                        }
                    }
                } else {
                    $followusertrenddiscussion[$i]['shortdicreption'] = $followusertrenddiscussion[$i]['discussion_description'];
                }

                $i++;
            }
            $this->view->followusertrenddiscussion = $followusertrenddiscussion;
        }

        if ($followuserrecentdiscussion) {
            $i = 0;
            foreach ($followuserrecentdiscussion as $val) {

                $discussion_id = $val['discussion_id'];
                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                if ($userresultlike) {
                    $followuserrecentdiscussion[$i]['islike'] = 1;
                } else {
                    $followuserrecentdiscussion[$i]['islike'] = 0;
                }
                $followuserrecentdiscussion[$i]['discusslikecount'] = $resultlike['num'];
                $arr = split("<img ", $followuserrecentdiscussion[$i]['discussion_description']);
                if (sizeof($arr) > 1) {
                    foreach ($arr as $val) {
                        if (strpos($val, 'src') === false) {
                            $val = strip_tags($val);
                            $followuserrecentdiscussion[$i]['shortdicreption'] = $val;
                        }
                    }
                } else {
                    $followuserrecentdiscussion[$i]['shortdicreption'] = $followuserrecentdiscussion[$i]['discussion_description'];
                }
                $i++;
            }
            $this->view->followuserrecentdiscussion = $followuserrecentdiscussion;
        }
        if ($followuserlikediscussion) {
            $i = 0;
            foreach ($followuserlikediscussion as $val) {

                $discussion_id = $val['discussion_id'];
                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($discussion_id);
                $userresultlike = $objClassDiscussionsLikes->getuserdiscusslikes($userid, $discussion_id);
                if ($userresultlike) {
                    $followuserlikediscussion[$i]['islike'] = 1;
                } else {
                    $followuserlikediscussion[$i]['islike'] = 0;
                }
                $followuserlikediscussion[$i]['discusslikecount'] = $resultlike['num'];
                $arr = split("<img ", $followuserlikediscussion[$i]['discussion_description']);
                if (sizeof($arr) > 1) {
                    foreach ($arr as $val) {
                        if (strpos($val, 'src') === false) {
                            $val = strip_tags($val);
                            $followuserlikediscussion[$i]['shortdicreption'] = $val;
                        }
                    }
                } else {
                    $followuserlikediscussion[$i]['shortdicreption'] = $followuserlikediscussion[$i]['discussion_description'];
                }
                $i++;
            }
            $tmp = array();
            foreach ($followuserlikediscussion as $key => $row) {
                $tmp[$key] = $row['discusslikecount'];
            }
            array_multisort($tmp, SORT_DESC, $followuserlikediscussion);
            $this->view->followuserlikediscussion = $followuserlikediscussion;
        }


        $teachingclassenroll = Application_Model_ClassEnroll::getinstance();
        $enroledclassdiscussion = Application_Model_ClassDiscussions::getinstance();
        $enroledclassdiscussionlike = Application_Model_DiscussionLikes::getinstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $followinguserdiscussions = array();
        $ifollowinginfo = Application_Model_Followers::getinstance();
        $ifollowedpeoplediscussion = $enroledclassdiscussion;
        $iamfollowing = $ifollowinginfo->getIFollow($userid);
        if (isset($iamfollowing)) {
            foreach ($iamfollowing as $value) {
                $userdiscussions = $ifollowedpeoplediscussion->getuserdiscussionDetail($value['following_user_id']);
                if (isset($userdiscussions)) {
                    foreach ($userdiscussions as $res) {
                        $followinguserdiscussions[] = $res;
                    }
                }
            }
        }
        $objdisccomments = Application_Model_DiscussionComments::getinstance();
        if (isset($followinguserdiscussions)) {
            $i = 0;
            foreach ($followinguserdiscussions as $result) {
                $clsscreaterid = $teachingclass->getClassUnitID($result['class_id']);
                $resultlike = $objClassDiscussionsLikes->getdiscusslikes($result['discussion_id']);
                $isdiked = $enroledclassdiscussionlike->getuserdiscusslikes($result['user_id'], $result['discussion_id']);
                $disccomments = $objdisccomments->getDiscussionscCount($result['discussion_id']);
                if ($isdiked) {
                    $followinguserdiscussions[$i]['islike'] = 1;
                } else {
                    $followinguserdiscussions[$i]['islike'] = 0;
                }
                $followinguserdiscussions[$i]['commentcount'] = $disccomments;
                $followinguserdiscussions[$i]['discusslikecount'] = $resultlike['num'];
                $followinguserdiscussions[$i]['teacherid'] = $clsscreaterid['user_id'];
                $arr = split("<img ", $followinguserdiscussions[$i]['discussion_description']);
                if (sizeof($arr) > 1) {
                    foreach ($arr as $val) {
                        if (strpos($val, 'src') === false) {
                            $val = strip_tags($val);
                            $followinguserdiscussions[$i]['shortdicreption'] = $val;
                        }
                    }
                } else {
                    $followinguserdiscussions[$i]['shortdicreption'] = $followinguserdiscussions[$i]['discussion_description'];
                }
                $i++;
            }
        }
        $this->view->followinguserdiscussion = $followinguserdiscussions;
    }

    public function teacherDashboardAction() {

//        $this->_helper->viewRenderer->setNoRender(true);
//        $this->_helper->layout()->disableLayout();
        $userid = $this->view->session->storage->user_id;
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $teachingclassunit = Application_Model_TeachingClassesUnit::getinstance();
        $user_details = Application_Model_Users::getinstance();
        $unitdetails = $teachingclassunit->getUnitDetails($userid);
        //echo '<pre>';print_r($unitdetails); die;

        $this->view->unitdetails = $unitdetails;
        $user = $user_details->getUserDetail($userid);
        $this->view->image = $user['user_profile_pic'];
        $this->view->fname = $user['first_name'];
        $this->view->lname = $user['last_name'];

        $unitcreate = $teachingvideoclass->getUnitsCreated($userid);

        $this->view->unitscreated = $unitcreate;
//              $test=  $this->view->session->storage->finalresult;
//              echo '<pre>';              print_r($test); die;
        $Coursesinstructing = $teachingclass->getCountOfClasses($userid);

        $this->view->courses = count($Coursesinstructing);

        $coursedetails = $teachingclass->getCourcesDetails($userid);


        //dev:priyanka varanasi
        //desc: to get total no of projects assignments created by the teacher
        $result = $teachingclass->getProjectsAssignments($userid);
        if ($result) {
            $procount = 0;
            foreach ($result as $value) {
                if ((!empty($value['assignment_project_title'])) && (!empty($value['assignment_project_description']))) {
                    $procount++;
                    //echo"<pre>"; print_r('if'.$count);   
                } else {
                    $procount;
                    // echo"<pre>";print_r('else'.$count);
                }
            }

            $this->view->projectasssignment = $procount;
        }
        //------------------------code ends--------------------------------
        //dev:priyanka varanasi
        //desc:to get total no of projects for the user
        $respone = $teachingclass->getProjectsByClassByUserid($userid);
        $this->view->projcount = count($respone);
        //------------------------code ends--------------------------------
        //dev:priyanka varanasi
        //desc: to get total no of units created for classes belongs to session user
        $repnse = $teachingclass->getUnitsCountForClasses($userid);
        $this->view->classunits = $repnse;
        //-------------------------code ends-------------------------------------
        $this->view->coursedetails = $coursedetails;
        $projdetails = Application_Model_Projects::getinstance();
//        $projectresult = $projdetails->teacherprojectDetails($userid);
        //dev: priyanka varanasi
        //desc: to get no of projects created in the class, which is created by the logged teacher
        $projectresult = $teachingclass->getProjectsByClassByUserid($userid);

        $this->view->proj = $projectresult;
        //abhishekm
        $comm = $user_details->getcommission($userid);
        $this->view->comm = round($comm, 2);
        $certificateobj = Application_Model_Certificate::getinstance();
        $this->view->certificates = $certificateobj->getcertificateCount($userid);

        //dev: priyanka varanasi
        //desc: TO show the project description in the modal in teacher dasboadr page
        if ($this->_request->isxmlhttprequest()) {
            $this->_helper->viewRenderer->setNoRender(true);
            $this->_helper->layout()->disableLayout();
            $projdesc = $this->getRequest()->getParam('projDesc');
            $result = $projdetails->getUserProject($projdesc);
            if ($result['project_workspace']) {
                echo json_encode($result['project_workspace']);
            } else {
                echo json_encode('no description available');
            }
        }
    }

    public function takingAction() {
        $userid = $this->view->session->storage->user_id;

        $objUserModel = Application_Model_Users::getinstance();
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $userresponse = $objUserModel->getUserDetail($userid);
        $usermetaresult = $objMetaModel->getUserMetaDetail($userid);

        $this->view->result = $userresponse;
        $this->view->metaresult = $usermetaresult;
    }

    public function discussionAction() {
        
    }

    public function profileDetailsAction() {
        
    }

    public function cartAction() {
        
    }

    public function noresultAction() {

        // print$this->view->session->storage['search']; 
    }

//dev:priyanka varanasi
//desc: TO send email with activation link  to reset password

    public function resetpasswordAction() {

        $mailer = Engine_Mailer_Mailer::getInstance();
        $objUserModel = Application_Model_Users::getinstance();
        if ($this->getRequest()->isPost()) {
            $email = $this->getRequest()->getPost('Email');

            if ($email != "") {
                $result = $objUserModel->validateUserEmail($email);
                if (!empty($result)) {

                    $objCore = Engine_Core_Core::getInstance();
                    $this->_appSetting = $objCore->getAppSetting();
                    $userID = $result['user_id'];
                    $activationKey = base64_encode($result['user_id'] . '@' . $random = mt_rand(10000000, 99999999));
                    $link = 'http://' . $this->_appSetting->host . '/set-password/' . $activationKey;
                    $objUserModel->updateActivationLink($activationKey, $userID);
                    $template_name = 'ForgotPassword';
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
                 
                    if ($result) {
                        $this->view->success = 'send';
                    }
                } else {
                    $this->view->msg = "Email doesnt exist";
                }
            }
        }
    }

    public function coursesInstructingAction() {
        
    }

    /**
      Developer: Namrata Singh
      Date: 12/2/2015
      Description:Fetch the data from DB based on userid to show which
      classes are published or in draft or pending,
     *             Shows all the classes teched by the teacher.
     * */
    public function teachingAction() {
        $userid = $this->view->session->storage->user_id;
        $objUserModel = Application_Model_Users::getinstance();
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $userresponse = $objUserModel->getUserDetail($userid);
        $usermetaresult = $objMetaModel->getUserMetaDetail($userid);
        $this->view->result = $userresponse;
        $this->view->metaresult = $usermetaresult;
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        //$responsesubcat = $teachingclass->getSubCategory();
        $result = $teachingclass->getTeachClasses($userid);

        //echo "<pre>";print_r($result);die;
        $this->view->teachresult = $result;
        //  $this->view->session->storage->teachresult = $result;
    }

    public function userFollowAction() {
        $type = $this->getRequest()->getParam('type');
        $userid = $this->getRequest()->getParam('userid');
        // echo $userid;
        $currentuserid = $this->view->session->storage->user_id;

        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objfollow = Application_Model_Followers::getInstance();

        $usermetaresult = $objMetaModel->getUserMetaDetail($userid);
        $userresult = $objUserModel->getUserDetail($userid);
        $followresult = $objfollow->getFollowDetail($currentuserid, $userid);
        $ifollowresult = $objfollow->getIFollow($userid);
        $followmeresult = $objfollow->getFollowMe($userid);
        if ($followresult != 0) {
            $this->view->followresult = $followresult;
        }
        if ($ifollowresult) {
            $followcount = 0;
            foreach ($ifollowresult as $followval) {
                $isresult = $objfollow->getIsFollow($currentuserid, $followval['following_user_id']);
                $ifollowresult[$followcount]['isfollow'] = $isresult;
                $followcount++;
            }
        }
//        echo "<pre>";print_r($followmeresult);die;
        if ($followmeresult) {
            $followcount = 0;
            foreach ($followmeresult as $followmeval) {
                $isresult = $objfollow->getIFollow($currentuserid, $followmeval['follower_user_id']);
                $followmeresult[$followcount]['isfollow'] = $isresult;
                $followcount++;
            }
        }
        //echo "<pre>";print_r($ifollowresult);die;
        $this->view->usermetaresult = $usermetaresult;
        $this->view->userresult = $userresult;
        $this->view->ifollowresult = $ifollowresult;
        $this->view->followmeresult = $followmeresult;
        $this->view->type = $type;
        $this->view->checkid = $userid;




//        echo "<pre>";print_r($ifollowresult);die;
//       echo "<pre>";print_r($followmeresult);die;
    }

    public function classEnrolledAction() {
        $userid = $this->getRequest()->getParam('userid');
        $type = $this->getRequest()->getParam('type');
        switch ($type) {
            case 'my-projects':
                $project = Application_Model_Projects::getinstance();
                $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
                $objprojectcomments = Application_Model_ProjectComments::getInstance();
                $getmyprojects = $project->getrecentProjects($userid);
                $count = 0;
                if (isset($getmyprojects)) {
                    foreach ($getmyprojects as $p) {
                        $projectcomment = $objprojectcomments->getComments($p["project_id"]);
                        $countss = $objClassProjectLikes->getprojectlikes($p["project_id"]);
                        $getmyprojects[$count]['commentcount'] = sizeof($projectcomment);
                        $getmyprojects[$count]["likescount"] = $countss;
                        $count++;
                    }
                }

                $this->view->getmyprojects = $getmyprojects;
                break;
            case 'enroll':
                $objClassReview = Application_Model_ClassReview::getinstance();
                $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
                $teachingclassenroll = Application_Model_ClassEnroll::getinstance();
                $recentlyadded = $teachingclassenroll->getEnrollUserClasses($userid);

                if ($recentlyadded) {
                    $count = 0;
                    foreach ($recentlyadded as $val) {
                        $allreview = $objClassReview->getAllReview($val['class_id']);

                        $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                        if (count($allreview) != 0) {
                            $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                        } else {
                            $classreviewpercentage = 0;
                        }
                        $recentlyadded[$count]['review_per'] = $classreviewpercentage;
                        $count++;
                    }

                    foreach ($recentlyadded as $key => $value) {
                        $funniestofarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                        if ($funniestofarray) {
                            foreach ($funniestofarray as $var) {
                                $recentlyadded[$key]['class_video_title'] = $var['class_video_title'];
                                $recentlyadded[$key]['class_video_url'] = $var['class_video_url'];
                                $recentlyadded[$key]['class_video_id'] = $var['class_video_id'];
                                $recentlyadded[$key]['cover_image'] = $var['cover_image'];
                                $recentlyadded[$key]['video_thumb_url'] = $var['video_thumb_url'];
                            }
                        }
                    }
                }
                $this->view->enrolluserclass = $recentlyadded;
                break;
            case 'saved':
                $objClassReview = Application_Model_ClassReview::getinstance();
                $objsave = Application_Model_Myclasses::getInstance();
                $getsaveresponse = $objsave->getSaveDetail($userid);

                $objClassEnroll = Application_Model_ClassEnroll::getinstance();
                $teachingclass = Application_Model_TeachingClasses::getinstance();
                if ($getsaveresponse) {
                    $count = 0;
                    foreach ($getsaveresponse as $val) {
                        $result1 = $teachingclass->getTeachClassesDetails($val['class_id']);
                        $result = $objClassEnroll->getStudentsCount($val['class_id']);
                        $allreview = $objClassReview->getAllReview($val['class_id']);
                        $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                        if (count($allreview) != 0) {
                            $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                        } else {
                            $classreviewpercentage = 0;
                        }
                        $getsaveresponse[$count]['category_name'] = $result1['0']['category_name'];
                        $getsaveresponse[$count]['student_count'] = $result['stud_count'];
                        $getsaveresponse[$count]['review_per'] = $classreviewpercentage;
                        $count++;
                    }
                }
                $this->view->getsaveresponse = $getsaveresponse;
                break;
            case 'teaching':
                $objClassReview = Application_Model_ClassReview::getinstance();
                $teachingclass = Application_Model_TeachingClasses::getinstance();
                $result = $teachingclass->getTeachClasses($userid);
                if ($result) {
                    $count = 0;
                    foreach ($result as $val) {
                        $allreview = $objClassReview->getAllReview($val['class_id']);
                        $calculatereview = $objClassReview->getCalculateReview($val['class_id']);
                        if (count($allreview) != 0) {
                            $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                        } else {
                            $classreviewpercentage = 0;
                        }
                        $result[$count]['review_per'] = $classreviewpercentage;
                        $count++;
                    }
                }
                $this->view->teachresult = $result;
                break;
        }
    }

    public function projectCreatedAction() {
        $userid = $this->getRequest()->getParam('userid');
        $currentuserid = $this->view->session->storage->user_id;

        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objfollow = Application_Model_Followers::getInstance();

        $usermetaresult = $objMetaModel->getUserMetaDetail($userid);
        $userresult = $objUserModel->getUserDetail($userid);
        $followresult = $objfollow->getFollowDetail($currentuserid, $userid);
        $ifollowresult = $objfollow->getIFollow($userid);
        $followmeresult = $objfollow->getFollowMe($userid);
        if ($followresult != 0) {
            $this->view->followresult = $followresult;
        }
        $this->view->usermetaresult = $usermetaresult;
        $this->view->userresult = $userresult;
        $this->view->ifollowresult = $ifollowresult;
        $this->view->followmeresult = $followmeresult;
        $objGetMyProjects = Application_Model_Projects::getInstance();
        $resultMyProject = $objGetMyProjects->myProjectDetails($userid);
        $this->view->resultmyproject = $resultMyProject;
    }

    public function classSavedAction() {
        $userid = $this->getRequest()->getParam('userid');
        $currentuserid = $this->view->session->storage->user_id;

        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objfollow = Application_Model_Followers::getInstance();

        $usermetaresult = $objMetaModel->getUserMetaDetail($userid);
        $userresult = $objUserModel->getUserDetail($userid);
        $followresult = $objfollow->getFollowDetail($currentuserid, $userid);
        $ifollowresult = $objfollow->getIFollow($userid);
        $followmeresult = $objfollow->getFollowMe($userid);
        if ($followresult != 0) {
            $this->view->followresult = $followresult;
        }
        $this->view->usermetaresult = $usermetaresult;
        $this->view->userresult = $userresult;
        $this->view->ifollowresult = $ifollowresult;
        $this->view->followmeresult = $followmeresult;
        $objsave = Application_Model_Myclasses::getInstance();

        $getSave = $objsave->getSaveDetail($userid);

        $this->view->save = $getSave;
    }

    public function classTeachingAction() {
        $userid = $this->getRequest()->getParam('userid');
        $currentuserid = $this->view->session->storage->user_id;

        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objfollow = Application_Model_Followers::getInstance();

        $usermetaresult = $objMetaModel->getUserMetaDetail($userid);
        $userresult = $objUserModel->getUserDetail($userid);
        $followresult = $objfollow->getFollowDetail($currentuserid, $userid);
        $ifollowresult = $objfollow->getIFollow($userid);
        $followmeresult = $objfollow->getFollowMe($userid);
        if ($followresult != 0) {
            $this->view->followresult = $followresult;
        }
        $this->view->usermetaresult = $usermetaresult;
        $this->view->userresult = $userresult;
        $this->view->ifollowresult = $ifollowresult;
        $this->view->followmeresult = $followmeresult;
        $objGetMyProjects = Application_Model_Projects::getInstance();
        $resultMyProject = $objGetMyProjects->myProjectDetails($currentuserid);
        $this->view->resultmyproject = $resultMyProject;
//        echo "<pre>"; print_r($resultMyProject);die;
//-------------------for teaching----------------                                       // Name:Namrata
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $result = $teachingclass->getTeachClasses($userid);
        $this->view->teachresult = $result;
//        echo "<pre>";print_r($result);die;
    }

    public function notificationstatusAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $userid = $this->view->session->storage->user_id;
        //$notification = Application_Model_Savednotifications::getinstance();
        //$result = $notification->NotificationStatus($userid);
    }

//dev:priyanka varanasi
//desc: TO check activation link  and update the password

    public function setPasswordAction() {

        $objUserModel = Application_Model_Users::getInstance();

        $key = $this->getRequest()->getParam('code');
        if ($key) {
            $decodeKey = base64_decode($key);
            $userId = explode('@', $decodeKey);
            $result = $objUserModel->checkActivationKey($userId[0], $key);
            $date1 = strtotime(date('Y-m-d H:i:s'));
            $date2 = strtotime($result['passwordlink_exp']);
            $hour = round(abs($date1 - $date2)/(60*60));
            if($hour <= 24){
               $this->view->userData =  $result;
            }else{
               $this->view->userData =  198;
            }
            }
            if ($this->getRequest()->isPost()) {
                $newPassword = $this->getRequest()->getPost('newpassword');

                $confPassword = $this->getRequest()->getPost('confirmpassword');

                if ($newPassword === $confPassword) {

                $data['password'] = sha1(md5($newPassword));
                $resultData = $objUserModel->changePasswordsettings($data, $userId[0]);

                if ($resultData) {
                    
                   $this->view->success = $resultData;
                    }
                }
            }
        }
   

//dev:priyanka varanasi
//desc: TO display classes based on trending

    public function trendingAction() {
        $this->_helper->_layout->disableLayout();
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
        $category = $this->getRequest()->getParam('filter');
        $county = $this->getRequest()->getParam('county');
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
        $userid = $this->view->session->storage->user_id;
        $trending = array();
        if ($method == 'allClasses') {
            $trending = $teachingclass->gettrendingclasses($categoryid);
            $allclasscount = sizeof($trending);
            $allclasscount = $allclasscount / 9;
            $this->view->count123 = ceil($allclasscount);
        }if ($method == 'myclasses') {
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
            $allclasscount = sizeof($resultenrole);
            $allclasscount = $allclasscount / 9;
            $this->view->count123 = ceil($allclasscount);
        }
        $getsaveresponseUser = 0;
        if (isset($userid)) {
            $getsaveresponseUser = $objsave->getSaveDetail($userid);
        }
        $userSavedclassId = array();
        if ($getsaveresponseUser) {
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
        if (isset($trending)) {
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

//dev:priyanka varanasi
//desc: TO display classes based on trending

    public function highlyRatedAction() {
        $this->_helper->_layout->disableLayout();
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $objCategoryModel = Application_Model_Category::getinstance();
        $objsave = Application_Model_Myclasses::getInstance();
        $objClassEnroll = Application_Model_ClassEnroll::getinstance();
        $method = $this->getRequest()->getParam('method');
        $count123 = $this->getRequest()->getParam('count');
        $county = $this->getRequest()->getParam('county');

        $category = $this->getRequest()->getParam('filter');
          if($county==0){
          
            $this->view->county=0;
            
        }
        if ($count123 == "") {
            $count123 = 1;
        }
        $allCategories = $objCategoryModel->getAllCategories();
        $categoryid = "";
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
        $higlyrated = array();
        if ($method == 'allClasses') {
            $higlyrated = $teachingclass->gettrendingclasses($categoryid);
            $allclasscount = sizeof($higlyrated);
            $allclasscount = $allclasscount / 9;
            $this->view->count123 = ceil($allclasscount);
        }if ($method == 'myclasses') {
            $resultenrole = $objClassEnroll->getEnrollUserClasses($userid);
            $getsaveresponse = array();
            $i = 0;
            if (isset($resultenrole)) {
                foreach ($resultenrole as $value) {
                    $res = $teachingclass->getsingleCLass($value['class_id'], $categoryid);
                    if (sizeof($res) != 0) {
                        $higlyrated[$i] = $res;
                        $i++;
                    }
                }
            }
            $allclasscount = sizeof($resultenrole);
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

            function my_sort($a, $b) {
                if ($a["review_per"] == $b["review_per"])
                    return 0;
                return ($a["review_per"] < $b["review_per"]) ? 1 : -1;
            }

            usort($higlyrated, "my_sort");
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
            $count123 = $count123 - 1;
            $pagess = $count123 * 9;
            $pagese = $pagess + 8;
            $i = 0;
            $higlyrated123 = array();
            foreach ($higlyrated as $val) {
                if ($pagess <= $i && $pagese >= $i) {
                    $higlyrated123[] = $val;
                }
                $i++;
            }
            $this->view->getsaveresponse = $higlyrated123;
            $this->view->userSavedclassId = $userSavedclassId;
        }
    }

//dev:priyanka varanasi
//desc: TO display classes based on trending

    public function recentlyAddedAction() {
        $this->_helper->_layout->disableLayout();
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $objCategoryModel = Application_Model_Category::getinstance();
        $objsave = Application_Model_Myclasses::getInstance();
        $objClassEnroll = Application_Model_ClassEnroll::getinstance();
        $method = $this->getRequest()->getParam('method');
        $category = $this->getRequest()->getParam('filter');
        $county = $this->getRequest()->getParam('county');

        $allCategories = $objCategoryModel->getAllCategories();
        $categoryid = "";
        if ($county == 0) {

            $this->view->county = 0;
        }
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
        $userid = $this->view->session->storage->user_id;

//        if (isset($userid) && $method != 'allClasses') {
//            $recentlyadded = $teachingclass->gettrendingclasses($userid);
//        } else {
//            $recentlyadded = $teachingclass->gettrendingclasses();
//        }
//        if (isset($userid)) {
//            $getsaveresponseUser = $objsave->getSaveDetail($userid);
//        }
//        $userSavedclassId = array();
//        foreach ($getsaveresponseUser as $value) {
//            $userSavedclassId[] = $value['class_id'];
//        }
//        if ($recentlyadded) {
//            foreach ($recentlyadded as $key => $row) {
//                $value[$key] = $row['class_created_date'];
//            }
//            @array_multisort($value, SORT_DESC, $recentlyadded);
//
//            $count = 0;
//            foreach ($recentlyadded as $val) {
//                $allreview = $objClassReview->getAllReview($val['class_id']);
//
//                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);
//
//                if (count($allreview) != 0) {
//                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
//                } else {
//                    $classreviewpercentage = 0;
//                }
//                $recentlyadded[$count]['review_per'] = $classreviewpercentage;
//                $count++;
//            }
//
//            foreach ($recentlyadded as $key => $value) {
//                $funniestofarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
//                if ($funniestofarray) {
//                    foreach ($funniestofarray as $var) {
//                        $recentlyadded[$key]['class_video_title'] = $var['class_video_title'];
//                        $recentlyadded[$key]['class_video_url'] = $var['class_video_url'];
//                        $recentlyadded[$key]['class_video_id'] = $var['class_video_id'];
//                        $recentlyadded[$key]['cover_image'] = $var['cover_image'];
//                        $recentlyadded[$key]['video_thumb_url'] = $var['video_thumb_url'];
//                    }
//                }
//            }
//        }
//        $this->view->getsaveresponse = $recentlyadded;
//        $this->view->userSavedclassId = $userSavedclassId;
////    }else{
        $type = $this->getRequest()->getParam('type');
        $count123 = $this->getRequest()->getParam('count');
        if ($count123 == "") {
            $count123 = 1;
        }
        $resentlyaddedclass = array();
        if ($method == "allClasses") {
            $resentlyaddedclass = $teachingclass->getAllRecentlyCLasses($count123, $categoryid);
            $allclasscount = sizeof($resentlyaddedclass);
            $allclasscount = $allclasscount / 9;
            $this->view->count123 = ceil($allclasscount);
        }if ($method == 'myclasses') {
            $resultenrole = $objClassEnroll->getEnrollUserClasses($userid);
            $getsaveresponse = array();
            $i = 0;
            if (isset($resultenrole)) {
                foreach ($resultenrole as $value) {
                    $res = $teachingclass->getsingleCLass($value['class_id'], $categoryid);
                    if (sizeof($res) != 0) {
                        $resentlyaddedclass[$i] = $res;
                        $i++;
                    }
                }
            }
//         function my_sort($a,$b)
//            {
//                if ($a["class_created_date"]==$b["class_created_date"]) return 0;
//                return ($a["class_created_date"]<$b["class_created_date"])?1:-1;
//            }
//            usort($resentlyaddedclass,"my_sort");
            $allclasscount = sizeof($resultenrole);
            $allclasscount = $allclasscount / 9;
            $this->view->count123 = ceil($allclasscount);
            $count123 = $count123 - 1;
            $pagess = $count123 * 9;
            $pagese = $pagess + 8;
            $i = 0;
            $higlyrated123 = array();
            if (isset($resentlyaddedclass)) {
                foreach ($resentlyaddedclass as $val) {
                    if ($pagess <= $i && $pagese >= $i) {
                        $higlyrated123[] = $val;
                    }
                    $i++;
                }
            }
            $higlyrated123 = $resentlyaddedclass;
        }

        $objsave = Application_Model_Myclasses::getInstance();
        $getsaveresponseUser = $objsave->getSaveDetail($userid);
        $userSavedclassId = array();
        if (isset($getsaveresponseUser)) {
            foreach ($getsaveresponseUser as $value) {
                $userSavedclassId[] = $value['class_id'];
            }
        }
        if ($resentlyaddedclass) {
            $count = 0;
            foreach ($resentlyaddedclass as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);

                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $resentlyaddedclass[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }

            foreach ($resentlyaddedclass as $key => $value) {
                $funniestofarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                if ($funniestofarray) {
                    foreach ($funniestofarray as $var) {
                        $resentlyaddedclass[$key]['class_video_title'] = $var['class_video_title'];
                        $resentlyaddedclass[$key]['class_video_url'] = $var['class_video_url'];
                        $resentlyaddedclass[$key]['class_video_id'] = $var['class_video_id'];
                        $resentlyaddedclass[$key]['cover_image'] = $var['cover_image'];
                        $resentlyaddedclass[$key]['video_thumb_url'] = $var['video_thumb_url'];
                    }
                }
            }
        }

        $this->view->getsaveresponse = $resentlyaddedclass;
        $this->view->userSavedclassId = $userSavedclassId;
    }

    public function deleteProjectsAction() {
        $prodelete = Application_Model_Projects::getinstance();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        if ($this->getRequest()->isPost()) {
            $project_id = $this->getRequest()->getPost('project_id');
            $result = $prodelete->deleteProject($project_id);
            if ($result) {
                echo $result;
            }
        }
    }

    /* Abhishek M
     * 
     * 
     */

    public function classestakingAction() {
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
        }


        $teachingclass = Application_Model_TeachingClasses::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $teachingvideoclass = Application_Model_TeachingClassVideo::getinstance();
        $teachingclassenroll = Application_Model_ClassEnroll::getinstance();
        $recentlyadded = $teachingclassenroll->getEnrollUserClasses($userid);


        $teachingclassenroll = Application_Model_ClassEnroll::getinstance();
        $enroledclassdiscussion = Application_Model_ClassDiscussions::getinstance();
        $enroledclassdiscussionlike = Application_Model_DiscussionLikes::getinstance();
        $objdisccomments = Application_Model_DiscussionComments::getinstance();
        $recentlyadded = $teachingclassenroll->getEnrollUserClasses($userid);
        $userenroleddiscussion = array();
        $enroledclasses = array();
        if (isset($recentlyadded)) {
            foreach ($recentlyadded as $value) {
                $enroledclasses[] = $value['class_id'];
                $dise = $enroledclassdiscussion->getRecentDetail($value['class_id']);
                if (isset($dise)) {
                    foreach ($dise as $single) {
                        $userenroleddiscussion[] = $single;
                    }
                }
            }
        }
        if (isset($userenroleddiscussion)) {
            $i = 0;
            foreach ($userenroleddiscussion as $discussion) {
                $clsscreaterid = $teachingclass->getClassUnitID($discussion['class_id']);
                $isdiked = $enroledclassdiscussionlike->getuserdiscusslikes($userid, $discussion['discussion_id']);
                $likecount = $enroledclassdiscussionlike->getdiscusslikes($discussion['discussion_id']);
                $disccomments = $objdisccomments->getDiscussionscCount($discussion['discussion_id']);
                if ($isdiked) {
                    $userenroleddiscussion[$i]['islike'] = 1;
                } else {
                    $userenroleddiscussion[$i]['islike'] = 0;
                }
                $userenroleddiscussion[$i]['likecount'] = $likecount;
                $userenroleddiscussion[$i]['commentcount'] = $disccomments;
                $userenroleddiscussion[$i]['teacherid'] = $clsscreaterid['user_id'];
                $arr = split("<img ", $userenroleddiscussion[$i]['discussion_description']);
                if (sizeof($arr) > 1) {
                    foreach ($arr as $val) {
                        if (strpos($val, 'src') === false) {
                            $val = strip_tags($val);
                            $userenroleddiscussion[$i]['shortdicreption'] = $val;
                        }
                    }
                } else {
                    $userenroleddiscussion[$i]['shortdicreption'] = $userenroleddiscussion[$i]['discussion_description'];
                }
                $i++;
            }
        }



        $this->view->enroledclassdiscussion = $userenroleddiscussion;

        if ($recentlyadded) {
            $count = 0;
            foreach ($recentlyadded as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);

                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $recentlyadded[$count]['review_per'] = $classreviewpercentage;
                $count++;
            }

            foreach ($recentlyadded as $key => $value) {
                $funniestofarray = $teachingvideoclass->getterndingclassvideos($value['class_id']);
                if ($funniestofarray) {
                    foreach ($funniestofarray as $var) {
                        $recentlyadded[$key]['class_video_title'] = $var['class_video_title'];
                        $recentlyadded[$key]['class_video_url'] = $var['class_video_url'];
                        $recentlyadded[$key]['class_video_id'] = $var['class_video_id'];
                        $recentlyadded[$key]['cover_image'] = $var['cover_image'];
                        $recentlyadded[$key]['video_thumb_url'] = $var['video_thumb_url'];
                    }
                }
            }
        }

        $this->view->enrolluserclass = $recentlyadded;

        $objsave = Application_Model_Myclasses::getInstance();
        $getsaveresponse = $objsave->getSaveDetail($userid);
        $this->view->getsaveresponse = $getsaveresponse;
        //-----------------------------------------------
        $objClassProjectLikes = Application_Model_ProjectLikes::getinstance();
        $objprojectcomments = Application_Model_ProjectComments::getinstance();
        $enrollusertrendproject = $teachingclassenroll->getEnrollUserProject($userid);
        // echo "<pre>"; print_r($enrollusertrendproject); die;
        if ($enrollusertrendproject) {

            $i = 0;
            foreach ($enrollusertrendproject as $val) {

                $project_id = $val['project_id'];
                $projectcomments = $objprojectcomments->getComments($project_id);
                $resultlike = $objClassProjectLikes->getprojectlikes($project_id);
                $userresultlike = $objClassProjectLikes->getuserprojectlikes($userid, $project_id);
                if ($userresultlike) {
                    $enrollusertrendproject[$i]['islike'] = 0;
                } else {
                    $enrollusertrendproject[$i]['islike'] = 1;
                }
                $enrollusertrendproject[$i]['projectlikecount'] = $resultlike;
                $enrollusertrendproject[$i]['commentcount'] = sizeof($projectcomments);
                $i++;
            }
            $this->view->enrollusertrendproject = $enrollusertrendproject;
        }
    }

    /**
      Developer: Ram
     * date: 14/5/2015
      Description: for user saved category based classes
     * */
    public function categoryjshandlerAction() {
        $this->_helper->_layout->disableLayout();
        $userid = $this->view->session->storage->user_id;
        $categoryname = $this->getRequest()->getParam('categoryName');
        $method = $this->getRequest()->getParam('method');
        $objCategoryModel = Application_Model_Category::getinstance();
        $response = $objCategoryModel->getDetail($categoryname);
        $cat_id = $response['category_id'];
        if ($cat_id == 0) {
            $cat_id = 0;
        }
        if ($categoryname == 'See All') {
            $categoryname = 'All Category';
        }
        $this->view->categoryname = $categoryname;

        $objsave = Application_Model_Myclasses::getInstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objCategoryModel = Application_Model_Category::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        $objClassEnroll = Application_Model_ClassEnroll::getinstance();
        $allCategories = $objCategoryModel->getAllCategories();
        if ($categoryname === 'All Category') {
            if ($method === 'allClasses') {
                $getsaveresponse = $objsave->getSaveDetail();
            } else {
                $getsaveresponse = $objsave->getSaveDetail($userid);
            }
        } else {
            if ($method === 'allClasses') {
                $getsaveresponse = $objsave->getUserSaveDetailbyCategory($cat_id);
            } else {
                $getsaveresponse = $objsave->getUserSaveDetailbyCategory($cat_id, $userid);
            }
        }
        $userresponse = $objUserModel->getUserDetail($userid);
        if (isset($userid)) {
            $getsaveresponseUser = $objsave->getSaveDetail($userid);
        }
        $userSavedclassId = array();
        foreach ($getsaveresponseUser as $value) {
            $userSavedclassId[] = $value['class_id'];
        }
        if ($getsaveresponse) {
            $count = 0;
            foreach ($getsaveresponse as $val) {
                $allreview = $objClassReview->getAllReview($val['class_id']);
                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);
                $studentCnt = $objClassEnroll->getStudentsCount($val['class_id']);

                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $getsaveresponse[$count]['review_per'] = $classreviewpercentage;
                $getsaveresponse[$count]['stud_cnt'] = $studentCnt['stud_count'];
                $count++;
            }
        }
//      echo "<pre>";print_r($getsaveresponse);echo "</pre>";die;
        $this->view->allCategories = $allCategories;
        $this->view->getsaveresponse = $getsaveresponse;
        $this->view->userresponse = $userresponse;
        $this->view->userSavedclassId = $userSavedclassId;
    }

    public function leaderboardAction() {

        $userid = $this->view->session->storage->user_id;
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $getmetaresult = $objMetaModel->getalltopscores();
        $this->view->topscores = $getmetaresult;
        $myrank = $objMetaModel->getrank($userid);
        $this->view->myrank = $myrank;
    }

    public function removesavedclassAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $userid = $this->getRequest()->getParam('userid');
        $classid = $this->getRequest()->getParam('classid');
        $objsavedchange = Application_Model_Myclasses::getinstance();
        $result1 = $objsavedchange->updateSave($userid, $classid);
        if ($result1) {
            echo 1;
            die();
        } else {
            echo 0;
            die();
        }
    }

    public function badgesAction() {
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
        }
        $userid = $this->getRequest()->getParam('userid');
        $uachievement = Application_Model_Userachievements::getinstance();
        $badges = $uachievement->getuserbadgeinfo($userid);
        //echo "<pre>";print_r($badges);die();
        $this->view->badges = $badges;
    }

    /**
      Developer: Ram
     * date: 1/6/2015
      Description: for student convertion dashboard
     * */
    public function studentConversionAction() {
        $objClassEnroll = Application_Model_ClassEnroll::getinstance();
        $objProject = Application_Model_Projects::getinstance();
        $objTeachClasses = Application_Model_TeachingClasses::getinstance();
        $objvideoStatus = Application_Model_uservideostatus::getinstance();
        $objClassReview = Application_Model_ClassReview::getinstance();
        if (isset($this->view->session->storage->user_id)) {
            $userid = $this->view->session->storage->user_id;
        }
        // $currentYear = date("Y");
        // $enrollclassdata = $objClassEnroll->userEnrollClasses($currentYear, $userid);
        // echo "<pre>"; print_r($enrollclassdata); die;
        ///  if (!empty($enrollclassdata)) {
        ///    $usertotal = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
//            foreach ($enrollclassdata as $val) {
//                if ($val['month'] === 'JAN') {
//                    $usertotal[0] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'FEB') {
//                    $usertotal[1] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'MAR') {
//                    $usertotal[2] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'APR') {
//                    $usertotal[3] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'MAY') {
//                    $usertotal[4] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'JUN') {
//                    $usertotal[5] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'JUL') {
//                    $usertotal[6] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'AUG') {
//                    $usertotal[7] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'SEP') {
//                    $usertotal[8] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'OCT') {
//                    $usertotal[9] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'NOV') {
//                    $usertotal[10] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//                if ($val['month'] === 'DEC') {
//                    $usertotal[11] = ($val['total'] != 0) ? $val['total'] : NULL;
//                }
//            }
//            $this->view->enrollData = $usertotal;
        // }
        $projectCount = $objProject->getProjects($userid);
        if (!empty($projectCount)) {
            $this->view->projectCount = count($projectCount);
        }
        $userteachClasses = $objTeachClasses->getClassByUser($userid);
        if (!empty($userteachClasses)) {
            $this->view->userteachClasses = count($userteachClasses);

            foreach ($userteachClasses as $key => $val) {


                $classIds[] = $val['class_id'];
                $prjtCnt = $objProject->getProjectsCount($val['class_id']);
                $userteachClasses[$key]['project_count'] = count($prjtCnt);
                $enrollCount = $objClassEnroll->getStudentsCount($val['class_id']);
                $userteachClasses[$key]['enroll_count'] = $enrollCount['stud_count'];
                $classVisits = $objvideoStatus->getVisitbyClassId($val['class_id']);
                $userteachClasses[$key]['visit_count'] = $classVisits['visit_count'];
                $allreview = $objClassReview->getAllReview($val['class_id']);
                $calculatereview = $objClassReview->getCalculateReview($val['class_id']);
                if (count($allreview) != 0) {
                    $classreviewpercentage = round((count($calculatereview) / count($allreview)) * 100);
                } else {
                    $classreviewpercentage = 0;
                }
                $userteachClasses[$key]['review_per'] = $classreviewpercentage;
            }
            $this->view->classesTotalData = $userteachClasses;
//        echo "<pre>";print_r($userteachClasses);die();
            foreach ($userteachClasses as $key => $val) {
                $classIds[] = $val['class_id'];
            }
            $allStudentCount = $objClassEnroll->getStudentCountByClassIds($classIds);
            if (!empty($allStudentCount)) {
                $this->view->totalStudents = $allStudentCount['stud_count'];
            }
            // $totalVisits = $objvideoStatus->userVisitsbyClassIds($classIds, $currentYear);
//            if (!empty($totalVisits)) {
//                $visitCount = 0;
//                foreach ($totalVisits as $val) {
//                    $visitCount = $visitCount + $val['total'];
//                }
//                $this->view->totalVisitCount = $visitCount;
//                $visittotal = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
//                foreach ($totalVisits as $val) {
//                    if ($val['month'] === 'JAN') {
//                        $visittotal[0] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'FEB') {
//                        $visittotal[1] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'MAR') {
//                        $visittotal[2] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'APR') {
//                        $visittotal[3] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'MAY') {
//                        $visittotal[4] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'JUN') {
//                        $visittotal[5] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'JUL') {
//                        $visittotal[6] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'AUG') {
//                        $visittotal[7] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'SEP') {
//                        $visittotal[8] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'OCT') {
//                        $visittotal[9] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'NOV') {
//                        $visittotal[10] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                    if ($val['month'] === 'DEC') {
//                        $visittotal[11] = ($val['total'] != 0) ? $val['total'] : NULL;
//                    }
//                }
//                $this->view->VisitCountData = $visittotal;
//            }
        }
        $daysreport = $objClassEnroll->getdaysstatistics($userid);
        $weeksreport = $objClassEnroll->getweekssstatistics($userid);
        $monthsreport = $objClassEnroll->getmonthsstatistics($userid);
        $this->view->daysreport = $daysreport;
        $this->view->weeksreport = $weeksreport;
        $this->view->monthsreport = $monthsreport;
//        echo "<pre>";print_r($visitCount);die();
    }

  

    public function fashionlearnclubAction() {


        $objachievements = Application_Model_Fashionlearnclub::getInstance();
        $result = $objachievements->getalll();
        $this->view->club = $result;
    }

    public function cluborderAction() {
        $objachievements = Application_Model_Fashionlearnclub::getInstance();
        if ($this->getRequest()->getParam('method')) {

            $orderid = $this->getRequest()->getParam('orderid');
            $title = $this->getRequest()->getParam('title');
            $orderdetails = $objachievements->getorderdetails($orderid);
            if ($orderdetails["avl_count"] <= 0) {
                echo "stock is over";
                die();
            }
            $user_id = $this->view->session->storage->user_id;

            $objUsermetaModel = Application_Model_UsersMeta::getinstance();
            $getmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);

            if ($getmetaresult["gems"] < $orderdetails["gems"]) {
                echo "you have insufficient gems";
                die();
            }
            $res = $objUsermetaModel->shop($user_id, $orderdetails["gems"]);
            if ($res) {
                $ress = $objachievements->shop($orderid);
                if ($ress) {

                    //  Mandrill implementation                           
                    $template_name = 'fashionlearnclub';
                    $email = "clube@fashionlearn.com.br";
                    $username = $this->view->session->storage->first_name;
                    $email2 = $this->view->session->storage->email;
                    $subject = 'fashionlearnclub';
                    $objCore = Engine_Core_Core::getInstance();
                    $realobj = $objCore->getAppSetting();
                    $host = $realobj->hostLink;
                    $img = $host . $orderdetails["pic"];
                    $mergers = array(
                        array(
                            'name' => 'name',
                            'content' => $username
                        ),
                        array(
                            'name' => 'orderid',
                            'content' => $orderid
                        ),
                        array(
                            'name' => 'imagesrc',
                            'content' => $img
                        ),
                        array(
                            'name' => 'email',
                            'content' => $email2
                        )
                    );
                    $mailer = Engine_Mailer_Mailer::getInstance();
                    $shoped = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);

                    //------------------------
                    if ($shoped) {
                        $data['order_id'] = $orderid;
                        $data['order_name'] = $title;
                        $data['user_id'] = $user_id;
                        $data['user_name'] = $username;
                        $data['order_cost'] = $orderdetails["gems"];
                        $data['useer_mail'] = $email2;
                        $objclub = Application_Model_Fashioncluborders::getInstance();
                        $result = $objclub->insertorder($data);
                        echo "ordered place sucessfully";
                        die();
                    }
                } else {
                    echo "there was an error";
                    die();
                }
            }



            echo "there was an error";
            die();
        }
        die();
    }

    public function ifollowingAction() {

        $userid = $this->getRequest()->getParam('userid');
        $this->view->searchinguseid = $userid;
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objfollow = Application_Model_Followers::getInstance();

        $ifollowresult = $objfollow->getIFollow($userid);
        $getUpdate = $objUserModel->getsocialaccountids($userid);
        $this->view->currentid = $userid;
        $this->view->nameofuserrequested = $getUpdate['first_name'] . " " . $getUpdate['last_name'];
        $i = 0;
        $followinginfo = array();
        if (isset($ifollowresult)) {
            foreach ($ifollowresult as $value) {
                $usermetaresult = $objMetaModel->getUserMetaDetail($value['following_user_id']);
                $followmeresult1 = $objfollow->getFollowMe($value['following_user_id']);
                $getUpdateResult = $objUserModel->getsocialaccountids($value['following_user_id']);
                $followinginfo[$i] = $usermetaresult;
                $followinginfo[$i]['follinginfo'] = sizeof($followmeresult1);
                if (isset($followmeresult1)) {
                    foreach ($followmeresult1 as $val) {
                        if ($val['follower_user_id'] == $this->view->session->storage->user_id) {
                            $followinginfo[$i]['followingstatus'] = 1;
                            break;
                        } else {
                            $followinginfo[$i]['followingstatus'] = 0;
                        }
                    }
                }
                if (!(isset($followinginfo[$i]['followingstatus']))) {
                    $followinginfo[$i]['followingstatus'] = 0;
                }
                $followinginfo[$i]['fb_id'] = $getUpdateResult['fb_id'];
                $followinginfo[$i]['tw_id'] = $getUpdateResult['tw_id'];
                $followinginfo[$i]['first_name'] = $getUpdateResult['first_name'];
                $followinginfo[$i]['last_name'] = $getUpdateResult['last_name'];
                $followinginfo[$i]['screen_name'] = $getUpdateResult['screen_name'];
                $i++;
            }
        }
        $this->view->ifollowinginfo = $followinginfo;
    }

    public function myfollowersAction() {
        $userid = $this->getRequest()->getParam('userid');
        $objMetaModel = Application_Model_UsersMeta::getinstance();
        $objUserModel = Application_Model_Users::getinstance();
        $objfollow = Application_Model_Followers::getInstance();

        $followmeresult = $objfollow->getFollowMe($userid);
        $getUpdate = $objUserModel->getsocialaccountids($userid);
        $this->view->currentid = $userid;
        $this->view->nameofuserrequested = $getUpdate['first_name'] . " " . $getUpdate['last_name'];
        $i = 0;
        $followinginfo = array();
        if (isset($followmeresult)) {
            foreach ($followmeresult as $value) {
                $usermetaresult = $objMetaModel->getUserMetaDetail($value['follower_user_id']);
                $followmeresult1 = $objfollow->getFollowMe($value['follower_user_id']);
                $getUpdateResult = $objUserModel->getsocialaccountids($value['follower_user_id']);
                $followinginfo[$i] = $usermetaresult;
                $followinginfo[$i]['follinginfo'] = sizeof($followmeresult1);

                if (isset($followmeresult1)) {
                    foreach ($followmeresult1 as $val) {
                        if ($val['follower_user_id'] == $this->view->session->storage->user_id) {
                            $followinginfo[$i]['followingstatus'] = 1;
                            break;
                        } else {
                            $followinginfo[$i]['followingstatus'] = 0;
                        }
                    }
                }
                if (!(isset($followinginfo[$i]['followingstatus']))) {
                    $followinginfo[$i]['followingstatus'] = 0;
                }
                $followinginfo[$i]['fb_id'] = $getUpdateResult['fb_id'];
                $followinginfo[$i]['tw_id'] = $getUpdateResult['tw_id'];
                $followinginfo[$i]['first_name'] = $getUpdateResult['first_name'];
                $followinginfo[$i]['last_name'] = $getUpdateResult['last_name'];
                $followinginfo[$i]['screen_name'] = $getUpdateResult['screen_name'];
                $i++;
            }
        }
        $this->view->myfollowerinfo = $followinginfo;
    }

    public function followAjaxHandlerAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $follower = $this->getRequest()->getParam('follower');
        $following = $this->getRequest()->getParam('following');
        $status = $this->getRequest()->getParam('status');
        $objfollowers = Application_Model_Followers::getInstance();
        $result = $objfollowers->updateFollow($follower, $following, $status);
        if ($result) {
            echo $result;
            die();
        } else {
            echo 0;
            die();
        }
    }
    
    
    
      /**
      Developer: Ram
     * date: 1/6/2015
      Description: teacher finance dashboard
     * */
    public function financeDashboardAction() {
//        die('dasd');
        $user_id = $this->view->session->storage->user_id;
        ;
//        print_r($user_id); die;
        $teacherpaymentdetails = Application_Model_Teacherpaymentdetails::getinstance();
        $bestmonthdata = $teacherpaymentdetails->bestsalarymonth($user_id);

        $worstmonthsalary = $teacherpaymentdetails->worstmonthsalary($user_id);
        $totalsalarydata = $teacherpaymentdetails->totalsalarydata($user_id);

        $totalsalry = 0;
        if (isset($totalsalarydata)) {
            $i = 0;
            foreach ($totalsalarydata as $allsalary) {

                try {
                    $objCore = Engine_Core_Core::getInstance();
                    $realobj = $objCore->getAppSetting();
                    $host = $realobj->hostLink;
                    $Url = $host . "/invoicegen.php";
                    // $Url = "http://api.htm2pdf.co.uk/urltopdf?apikey=yourapikey&url=http://skillshare.globusapps.com/generate-certificate?user=1&class=2";
                    $postdata = array(
                        'Pattership' => $allsalary["enroll_money"],
                        'Referencial' => $allsalary["referal_money"],
                        'rid' => $allsalary["datat_no"],
                        'pass' => "nootherscangenerate",
                        'link' => $host . "/assets/images/Fashionlearn(Logo-1-png).png"
                    );
                    $ch = curl_init($Url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    //$html2pdf = new HTML2PDF('P','A4','fr');
                    $output = curl_exec($ch);



                    $dompdf = new DOMPDF();
                    $dompdf->load_html($output);
                    $dompdf->render();
                    $output = $dompdf->output();
                    file_put_contents('invoicee/invoice' . $allsalary["datat_no"] . ".pdf", $output);
//$dompdf->stream('invoicee/invoice'.$allsalary["datat_no"].".pdf");
//                $html2pdf->writeHTML($output);

                    curl_close($ch);


                    // $file = $html2pdf->Output('invoicee/invoice'.$allsalary["datat_no"] .'.pdf', 'F');
                    //    var_dump($file);
                    $totalsalarydata[$i]["invoice"] = 'invoicee/invoice' . $allsalary["datat_no"] . '.pdf';
                    $teacherpaymentdetails->invoice($totalsalarydata[$i]["invoice"], $allsalary["datat_no"]);
                    $i++;
                } catch (Exception $e) {
                    echo $e->getMessage();
                }






                $salary = $allsalary['enroll_money'] + $allsalary['referal_money'];
                $totalsalry = $totalsalry + $salary;
            }
        } else {
            $totalsalry = 0;
        }
        $date = date("Y/m/d");
        $date = split('/', $date);
        $year = $date[0];
        $month = $date[1];
        if ($date[2] <= 15) {
            $month = $date[1] - 1;
        }

        $thismonthsalary = $teacherpaymentdetails->currentmonthsalary($month, $year, $user_id);

        $this->view->currentmonthsalary = $thismonthsalary;
        $this->view->bestmonthdata = $bestmonthdata;
        $this->view->worstmonthdata = $worstmonthsalary;



        $this->view->teacherallata = $totalsalarydata;
        $this->view->totalsalary = $totalsalry;





        $monthsreport = $teacherpaymentdetails->getmonthsstatistics($user_id);



        $this->view->monthsreport = $monthsreport;
        
        // Dev :Priyanka Varanasi
        //Desc: TO modify  the statisctics and data of teacher displaying in this page 
        //date:20/10/2015
        
       $paymentnewtable =  Application_Model_PaymentNew::getInstance();
       $teachingclass = Application_Model_TeachingClasses::getInstance();
       $teachersstudents = Application_Model_ClassEnroll::getInstance();
       $classreviewscount = Application_Model_ClassReview::getInstance();
       $paymentformulamodal = Application_Model_PaymentFormula::getInstance();
       $projects = Application_Model_Projects::getInstance();
       $classwiseearningsmodal = Application_Model_ClassWiseEarnings::getInstance(); 
       $user_id = $this->view->session->storage->user_id;
       $months  = $classwiseearningsmodal->getClassearningsMonths();
       
       if($months){
           $this->view->teachermonths = $months;
       }
       
       $teacherclassearnings     =  $classwiseearningsmodal->getEarningsByClass($user_id);
       
       $month = date('m');
       
       $result = $classwiseearningsmodal->GetTheClassEarnsOfTeacherByMonth($month,$user_id); 
       if($result){
           $this->view->result = $result;
           
       }
        if($teacherclassearnings){
            
            $this->view->classearnings = $teacherclassearnings;
        }
    //////////////////////code ends /////////////////////////
        
    }
    
       /**
      Developer: Priyanka Varanasi
     * date: 20/10/2015
      Description: teacher class statistics filter by month
     * */
    public function classfilterByMonthAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $classwiseearningsmodal = Application_Model_ClassWiseEarnings::getInstance(); 
        $user_id = $this->view->session->storage->user_id;
        if ($this->getRequest()->isPost()) {
            $month = $this->getRequest()->getPost('month');
            if($month =='currentmonth'){
                $month= date('m');
               $result = $classwiseearningsmodal->GetTheClassEarnsOfTeacher($month,$user_id); 
            }else{
              $result = $classwiseearningsmodal->GetTheClassEarnsOfTeacher($month,$user_id);   
            }
           
           if ($result) {
               $res= array('code'=> 200,
                           'data'=>$result);
               echo json_encode($res);
               die();
            }else{
               $res= array('code'=>198,
                           'data'=>'No result Found');
               echo json_encode($res);
               die();  
                
            }
        
        
    }

}
}
