<?php

/**
 * AdminController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';
require_once 'Engine/Core/Pusher.php';

class Notifications_NotificationsController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function preDispatch() {
        // Display the recent updated profile picture  

        if (isset($this->view->session->storage->user_id)) {
            $user_id = $this->view->session->storage->user_id;
            $objUsermetaModel = Application_Model_UsersMeta::getinstance();
            $getmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);
            $this->view->profilepic = $getmetaresult['user_profile_pic'];
        }
        $objCategoryModel = Application_Model_Category::getInstance();
        $allCategories = $objCategoryModel->getAllCategories();
        $this->view->AllCategories = $allCategories;
    }

    public function notificationsAction() {
        
    }

    public function notifistoreAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $user_id = $this->view->session->storage->user_id;
        $notificationcen = Application_Model_Notificationcenter::getinstance();
        $notification = Application_Model_Notification::getinstance();
        $points = Application_Model_Points::getinstance();
        $objUsermetaModel = Application_Model_UsersMeta::getinstance();
        $objTeachingClassModel = Application_Model_TeachingClasses::getinstance();
        $objUsersModel = Application_Model_Users::getinstance();
        $userdata = $objUsermetaModel->getUserMetaDetail($user_id);
        $method = $this->getRequest()->getPost('method');
        $objlevel = Application_Model_Levels::getinstance();
        $nextlevel = $objlevel->getlevelsinfo(intval($userdata['level']) + 1);
        $objUserachievement = Application_Model_Userachievements::getinstance();
        $achievement = Application_Model_Achievements::getinstance();
        $usergamestats = Application_Model_Usergamestats::getinstance();
        $getProjects = Application_Model_Projects::getinstance();
        $uachievement = Application_Model_Userachievements::getinstance();
        $gamnotifications = Application_Model_Gamnotifications::getinstance();
        $objCore = Engine_Core_Core::getInstance();
        $mailer = Engine_Mailer_Mailer::getInstance();
        $realobj = $objCore->getAppSetting();
        $host = $realobj->hostLink;
        $newbadges = array();
        $messs = array();
        $response = new stdClass();
        $date = gmdate('Y-m-d H:i:s', time());

        switch ($method) {

            case 'projectlike':

                $data = array();
                $data["time"] = $date;
                $data["classid"] = $this->getRequest()->getParam('classid');
                $data["projectid"] = $this->getRequest()->getParam('projectid');
                $data["creator_id"] = $this->getRequest()->getParam('creator');
                $data["title"] = $this->getRequest()->getParam('title');
                $data["initiator_id"] = $user_id;
                $data["reciever_id"] = $this->getRequest()->getParam('creator');
                $data["img"] = $this->getRequest()->getParam('img');
                $data["link"] = $url = $host . "teachclass/" . $data["classid"] . "?actionname=project&projectid=" . $data["projectid"];
                $userLink = $url = $host . "profile/" . $data["initiator_id"];
                $data["type"] = 1;
                $data["seen_status"] = false;
                $imglink = $host . $data["img"];
                $notifyAllowed = $notification->getUserNotificationData($data["reciever_id"]);   // checks for notification settings
                $getUser = $objUsersModel->getFbConnectedStatus($data["reciever_id"]);     //get details of receiver
                $getInitiator = $objUsersModel->getFbConnectedStatus($data["initiator_id"]);     //get details of initiator
                $status = $this->getRequest()->getParam('status');
                $getProjectInfo = $getProjects->getProjectInfo($data["projectid"]);
                $proCoverImg = $host . $getProjectInfo['project_cover_image'];
                if (!$status && $data["creator_id"] != $user_id) { {
                        $result = $notificationcen->insertnotifi($data);
                    }
                }

                $projectli = Application_Model_ProjectLikes::getinstance();
                $res = $projectli->projectlikes($user_id, $data["classid"], $data["projectid"], $status);
//              echo "<pre>"; print_r($res); die;
                if ($res == 1 && $data["creator_id"] != $user_id) {

                    $p = $points->getpointsinfo(1);
                    $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points by liking a project ";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems by liking a project ";
                   
                    
                    
                    // Mandrill implementation       

                    if (($notifyAllowed['no_email'] == 0) && ($notifyAllowed['activity_your_project'] == 1)) {              //Only if email is activated
                        $template_name = 'like project';
                        $email = $getUser['email'];
                        $username = $getUser['first_name'];
                        $subject = 'Project liked';
                        $mergers = array(
                            array(
                                'name' => 'name',
                                'content' => $getUser['first_name']
                            ),
                            array(
                                'name' => 'img',
                                'content' => $proCoverImg
                            ),
                            array(
                                'name' => 'imglink',
                                'content' => $data["link"]
                            ),
                            array(
                                'name' => 'name2',
                                'content' => $getInitiator['first_name']
                            ),
                            array(
                                'name' => 'name2link',
                                'content' => $userLink
                            ),
                            array(
                                'name' => 'projectlink',
                                'content' => $data["link"]
                            ),
                            array(
                                'name' => 'projecttitle',
                                'content' => $data["title"]
                            )
                        );
                        try {
                            $sendMailresult = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                        } catch (Exception $ex) {
                            
                        }
                    }
                    //-----------------------------------------------
                    $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);

                    while($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                        $objUsermetaModel->updatelevel($user_id);
                    
                    $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                      $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                        
                    }

                    $data1["likes_count"] = 1;

                    $usergamestats->updatestats($data1, $user_id);

                    $statss = $usergamestats->getstatsinfo($user_id);

                    $badges = $uachievement->getachinfo($user_id);

                    $achevementsid = array_column($badges, 'achevementsid');

                    $newbadges = $achievement->checkbadge($statss, $achevementsid);

                    foreach ($newbadges as $b1) {
                        $uachievement->awardbadge($user_id, $b1);
                    }
                }


                break;



            case 'classcomplete':

                $data = array();
                $data["time"] = $date;
                $cl=$this->getRequest()->getParam('classid');
                 $certificate = Application_Model_Certificate::getinstance();
                 $certificateno=$certificate->insertCertificate($user_id,$cl);
                 $response->certificateno=$certificateno;
                
                
                $p = $points->getpointsinfo(2);
                $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
                 $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points by completing a class ";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems by completing a class ";
                   
                
                
                
                while ($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                    $objUsermetaModel->updatelevel($user_id);
                    
                     $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                       $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                }
                $data1["classes_completed"] = 1;

                $usergamestats->updatestats($data1, $user_id);

                $statss = $usergamestats->getstatsinfo($user_id);

                $badges = $uachievement->getachinfo($user_id);
                $achevementsid = array_column($badges, 'achevementsid');

                $newbadges = $achievement->checkbadge($statss, $achevementsid);

                foreach ($newbadges as $b1) {


                    $uachievement->awardbadge($user_id, $b1);
                }


                break;


            case 'createproject':

                $data = array();
                $data["time"] = $date;
                $data["classid"] = $this->getRequest()->getParam('classid');
                $data["projectid"] = $this->getRequest()->getParam('projectid');
                $data["creator_id"] = $this->getRequest()->getParam('teacherid');
                $data["title"] = $this->getRequest()->getParam('title');
                $data["initiator_id"] = $user_id;
                $data["reciever_id"] = $this->getRequest()->getParam('teacherid');
                $data["img"] = $this->getRequest()->getParam('img');
                $data["link"] = $host . "teachclass/" . $data["classid"]."?actionname=project&projectid=".$data["projectid"];
                $data["type"] = 2;
                $data["seen_status"] = false;
                $userLink = $url = $host . "profile/" . $data["initiator_id"];
                $getUser = $objUsersModel->getFbConnectedStatus($data["reciever_id"]);     //get details of receiver
                $getInitiator = $objUsersModel->getFbConnectedStatus($data["initiator_id"]);     //get details of initiator
                $getClass = $objTeachingClassModel->getClassUnitID($data["classid"]);
                $notifyAllowed = $notification->getUserNotificationData($data["reciever_id"]);   // checks for notification settings

                if ($data["creator_id"] != $user_id) {
                    $result = $notificationcen->insertnotifi($data);
                    //  Mandrill implementation     
                    if (($notifyAllowed['no_email'] == 0) && ($notifyAllowed['updates_teaching_class'] == 1)) {
                        $template_name = 'create project';
                        $email = $getUser['email'];
                        $username = $getUser['first_name'];
                        $subject = 'Project Created';
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
                                'name' => 'name2link',
                                'content' => $userLink
                            ),
                            array(
                                'name' => 'regdate',
                                'content' => $userLink
                            )
                        );
                        try {
                            $projectCreated = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                        } catch (Exception $ex) {
                            
                        }
                    }
                }
                $p = $points->getpointsinfo(3);
                 $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points for creating a project ";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems for creating a project";
                   
                
                
                $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
               while ($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                    $objUsermetaModel->updatelevel($user_id);
                     $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                       $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                }
                $data1["projects_created"] = 1;

                $usergamestats->updatestats($data1, $user_id);

                $statss = $usergamestats->getstatsinfo($user_id);

                $badges = $uachievement->getachinfo($user_id);
                $achevementsid = array_column($badges, 'achevementsid');

                $newbadges = $achievement->checkbadge($statss, $achevementsid);

                foreach ($newbadges as $b1) {
                    $uachievement->awardbadge($user_id, $b1);
                }
                break;


            case 'creatediscussion':
                $data = array();
                $data["time"] = $date;

                break;



            case 'discussionlike':
                $data = array();
                $data["time"] = $date;
                if ($this->getRequest()->getPost()) {

                    $classid = $this->getRequest()->getPost('classid');
                    $discussionid = $this->getRequest()->getPost('discussionid');
                    $userid = $this->view->session->storage->user_id;
                    //echo $classid . $discussionid . $userid;
                    $objClassDiscussionsLikes = Application_Model_DiscussionLikes::getinstance();
                    $result = $objClassDiscussionsLikes->discusslikes($userid, $classid, $discussionid);
                    if ($result == 1) {
                        $data["classid"] = $classid;
                        $data["creator_id"] = $this->getRequest()->getParam('cid');
                        $data["title"] = $this->getRequest()->getParam('title');
                        $data["initiator_id"] = $user_id;
                        $data["reciever_id"] = $this->getRequest()->getParam('cid');
                        $data["img"] = $this->getRequest()->getParam('img');
                        $data["link"] = $host . "teachclass/" . $data["classid"] . "/?discussionid=" . $discussionid . "&actionname=discussion";
                        $data["type"] = 3;
                        $data["seen_status"] = false;
                        $classLink = $host . "teachclass/" . $data["classid"];
                        $img = $host . $data["img"];
                        $imgLink = $host . "profile/" . $data["initiator_id"];
                        $getUser = $objUsersModel->getFbConnectedStatus($data["reciever_id"]);     //get details of receiver
                        $getInitiator = $objUsersModel->getFbConnectedStatus($data["initiator_id"]);     //get details of initiator
                        $getClass = $objTeachingClassModel->getClassUnitID($data["classid"]);

                        if ($data["creator_id"] != $user_id) {
                            $result = $notificationcen->insertnotifi($data);
                            //  Mandrill implementation                           
                            $template_name = 'discussion like';
                            $email = $getUser['email'];
                            $username = $getUser['first_name'];
                            $subject = 'Discussion liked';
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
                                    'name' => 'classname',
                                    'content' => $getClass['class_title']
                                ),
                                array(
                                    'name' => 'classlink',
                                    'content' => $classLink
                                ),
                                array(
                                    'name' => 'imglink',
                                    'content' => $imgLink
                                ),
                                array(
                                    'name' => 'img',
                                    'content' => $img
                                ),
                                array(
                                    'name' => 'name2link',
                                    'content' => $imgLink
                                ),
                                array(
                                    'name' => 'discussionlink',
                                    'content' => $data["link"]
                                )
                            );
                            $projectCreated = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                            //------------------------
                        }
                        $p = $points->getpointsinfo(1);
                         $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points by liking a discussion ";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems by liking a discussion ";
                   
                        
                        
                        $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
                        while ($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                            $objUsermetaModel->updatelevel($user_id);
                            
                              $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                      $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                        }
                        $data1["likes_count"] = 1;

                        $usergamestats->updatestats($data1, $user_id);

                        $statss = $usergamestats->getstatsinfo($user_id);

                        $badges = $uachievement->getachinfo($user_id);
                        $achevementsid = array_column($badges, 'achevementsid');

                        $newbadges = $achievement->checkbadge($statss, $achevementsid);

                        foreach ($newbadges as $b1) {
                            $uachievement->awardbadge($user_id, $b1);
                        }
                    }// }                 
                }

                break;

            case 'discussioncomment':
                $data = array();
                $data["time"] = $date;
                $discussionid = $this->getRequest()->getParam('discussionid');
                $disobj = Application_Model_ClassDiscussions::getinstance();
                $dres = $disobj->getdiscussionbyid($discussionid);
                 $data["classid"] = $this->getRequest()->getParam('classid');
                
                $discobj = Application_Model_DiscussionComments::getinstance();
                $discress=$discobj->getCommentsid($discussionid,$data["classid"],$user_id);
                
                
                
               
                $data["creator_id"] = $dres["user_id"];
                $data["title"] = $dres["discussion_title"];
                $data["initiator_id"] = $user_id;
                $data["reciever_id"] = $dres["user_id"];
                $data["img"] = $this->getRequest()->getParam('img');
               $data["link"] = $host . "teachclass/" . $data["classid"]."/?discussionid=".$discussionid."&actionname=discussion&disid=".$discress["comment_id"];
                $data["type"] = 4;
                $data["seen_status"] = false;
                if ($data["creator_id"] != $user_id)
                    $result = $notificationcen->insertnotifi($data);
                $p = $points->getpointsinfo(4);
                 $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points by commenting on a Discussion ";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems by commenting on a Discussion ";
                   
                
                
                $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
                while ($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                    $objUsermetaModel->updatelevel($user_id);
                    
                       $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                      $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                }
                $data1["comments"] = 1;

                $usergamestats->updatestats($data1, $user_id);

                $statss = $usergamestats->getstatsinfo($user_id);

                $badges = $uachievement->getachinfo($user_id);
                $achevementsid = array_column($badges, 'achevementsid');

                $newbadges = $achievement->checkbadge($statss, $achevementsid);

                foreach ($newbadges as $b1) {


                    $uachievement->awardbadge($user_id, $b1);
                }
                $response->rid = $data["creator_id"];
                break;

            case 'discussioncommentreply':
                $data = array();
                $data["time"] = $date;
                $discussionid = $this->getRequest()->getParam('discussionid');
                $parentid = $this->getRequest()->getParam('parentid');
                $disobj = Application_Model_ClassDiscussions::getinstance();
                $dres = $disobj->getdiscussionbyid($discussionid);
                $dissobj = Application_Model_DiscussionComments::getinstance();
                $drres = $dissobj->getuserbyparentid($parentid);
               
                $data["classid"] = $this->getRequest()->getParam('classid');
                $data["creator_id"] = $drres["user_id"];
                $data["title"] = $dres["discussion_title"];
                $data["initiator_id"] = $user_id;
                $data["reciever_id"] = $drres["user_id"];
                $data["img"] = $this->getRequest()->getParam('img');
                $data["link"] = $host . "teachclass/" . $data["classid"]."/?discussionid=".$discussionid."&actionname=discussion&disid=".$parentid;
                $data["type"] = 5;
                $data["seen_status"] = false;
                if ($data["creator_id"] != $user_id)
                {
                    $result = $notificationcen->insertnotifi($data);
                $p = $points->getpointsinfo(4);
                
                   $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points by replying in discussion ";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems by replying in discussion ";
                   
                
                
                $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
               while($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                    $objUsermetaModel->updatelevel($user_id);
                    
                     $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                      $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                }
                }
                $data1["comments"] = 1;

                $usergamestats->updatestats($data1, $user_id);

                $statss = $usergamestats->getstatsinfo($user_id);

                $badges = $uachievement->getachinfo($user_id);
                $achevementsid = array_column($badges, 'achevementsid');

                $newbadges = $achievement->checkbadge($statss, $achevementsid);

                foreach ($newbadges as $b1) {


                    $uachievement->awardbadge($user_id, $b1);
                }
                $response->rid =  $data["creator_id"];
                break;

            case 'discussioncommentlike':
                $data = array();
                $data["time"] = $date;
                $classid = $this->getRequest()->getPost('classid');
                $discussionid = $this->getRequest()->getPost('discussionid');
                $commentid = $this->getRequest()->getPost('commentid');
                $userid = $this->view->session->storage->user_id;
                //echo $classid . $discussionid . $userid;



                $objClassDiscussionCommentLikes = Application_Model_DiscussionCommentLikes::getinstance();
                $result = $objClassDiscussionCommentLikes->discusscommentlikes($userid, $classid, $discussionid, $commentid);


                if ($result == 1) {
                     $disobj = Application_Model_DiscussionComments::getinstance();
                    $dres = $disobj->getCommentsdetail($commentid);
                      $disobj = Application_Model_ClassDiscussions::getinstance();
                $dress = $disobj->getdiscussionbyid($discussionid);

                    $data["classid"] = $classid;
                    $data["creator_id"] = $dres["user_id"];
                    $data["title"] = $dress["discussion_title"];
                    $data["initiator_id"] = $user_id;
                    $data["reciever_id"] = $dres["user_id"];
                    $data["img"] = $this->getRequest()->getParam('img');
                    $data["link"] = $host . "teachclass/" . $data["classid"]."/?discussionid=".$discussionid."&actionname=discussion&disid=".$commentid;
                    $data["type"] = 6;
                    $data["seen_status"] = false;
                    $response->rid = $data["creator_id"];
                     $p = $points->getpointsinfo(1);
                    if ($data["creator_id"] != $user_id)
                    {
                        $result = $notificationcen->insertnotifi($data);
                   
                       $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points by liking a comment ";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems by liking a comment ";
                   
                    
                    
                    $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
                    while ($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                        $objUsermetaModel->updatelevel($user_id);
                          $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                      $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                    }
                    }
                    $data1["likes_count"] = 1;

                    $usergamestats->updatestats($data1, $user_id);

                    $statss = $usergamestats->getstatsinfo($user_id);

                    $badges = $uachievement->getachinfo($user_id);
                    $achevementsid = array_column($badges, 'achevementsid');

                    $newbadges = $achievement->checkbadge($statss, $achevementsid);

                    foreach ($newbadges as $b1) {


                        $uachievement->awardbadge($user_id, $b1);
                    }
                }






                break;

            case 'creatediscussion':
                $data = array();
                $data["time"] = $date;
                $classid = $this->getRequest()->getPost("classid");
                $discusstitle = $this->getRequest()->getPost("discusstitle");
                $discusslink = $this->getRequest()->getPost("discusslink");
                $discussdescription = $this->getRequest()->getPost("desp");
                $teachhobj = Application_Model_TechingClassFile::getinstance();
                $teachhres->getTeachingClassescre($classid);
                $date = new DateTime();
                $date->setTimezone(new DateTimeZone('Asia/Kolkata'));
                $discussiondate = $date->format('Y-m-d h:i:s');
                $data["classid"] = $this->getRequest()->getParam('classid');
                $data["creator_id"] = $teachhres["user_id"];
                $data["title"] = $discusstitle;
                $data["initiator_id"] = $user_id;
                $data["reciever_id"] = $teachhres["user_id"];
                $data["img"] = $this->getRequest()->getParam('img');
                $data["link"] = $host . "teachclass/" . $data["classid"];
                $data["type"] = 7;
                $data["seen_status"] = false;
                if ($data["creator_id"] != $user_id)
                    $result = $notificationcen->insertnotifi($data);
                $p = $points->getpointsinfo(5);
                   $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points for creating a discussion ";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems for creating a discussion  ";
                   
                
                
                $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
               while ($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                    $objUsermetaModel->updatelevel($user_id);
                    
                     $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                      $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
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
                $response->rid = $parentid;
                break;


            case 'projectcomment':
                $data = array();
                $data["time"] = $date;
                $projectid = $this->getRequest()->getParam('projectid');
                $disobj = Application_Model_Projects::getinstance();
                $projobj = Application_Model_ProjectComments::getinstance();
                $dres = $disobj->getprojectbypid($projectid);
                $dres = $dres[0];
                $data["classid"] = $this->getRequest()->getParam('classid');
                $data["creator_id"] = $dres["user_id"];
                $data["title"] = $dres["project_title"];
                $data["initiator_id"] = $user_id;
                $data["reciever_id"] = $dres["user_id"];
                $data["img"] = null;
                $proress=$projobj->getCommentsid($projectid,$data["classid"],$user_id);
         
                
                
                $data["link"] = $host . "teachclass/" . $data["classid"] . "?actionname=project&projectid=". $projectid."&comid=".$proress["project_comment_id"];
                $data["type"] = 8;
                $data["seen_status"] = false;
                
                if ($data["creator_id"] != $user_id)
                    $result = $notificationcen->insertnotifi($data);
                $p = $points->getpointsinfo(4);
                          $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points by commenting on a project ";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems by commenting on a project ";
                   
                
                $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
                while($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                    $objUsermetaModel->updatelevel($user_id);
                     $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                      $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                }
                $data1["comments"] = 1;

                $usergamestats->updatestats($data1, $user_id);

                $statss = $usergamestats->getstatsinfo($user_id);

                $badges = $uachievement->getachinfo($user_id);
                $achevementsid = array_column($badges, 'achevementsid');

                $newbadges = $achievement->checkbadge($statss, $achevementsid);

                foreach ($newbadges as $b1) {


                    $uachievement->awardbadge($user_id, $b1);
                }
                $response->rid = $data["creator_id"];
                break;


            case 'projectcommentlike':
                $data = array();
                $data["time"] = $date;
                $commentid=$this->getRequest()->getParam('commentid');
                $projobj = Application_Model_ProjectComments::getinstance();
                $resul=$projobj->getCommentsdetail($commentid);
                $data["classid"] = $this->getRequest()->getParam('classid');
                $data["projectid"] = $this->getRequest()->getParam('projectid');
                $data["creator_id"] = $resul["user_id"];
                          $title = $this->getRequest()->getParam('title');
//                       $arr = split("<img ",$title);
//                 if(sizeof($arr) > 1){
//                     foreach($arr as $val){
//                         if(strpos($val,'src') === false){
//                            $val = strip_tags($val);
//                           $title = $val;
//                         } 
//                     }
//                 }
//                 else{
//                     $title = $title;
//                 }

                $data["title"] =  substr(strip_tags($title), 0, 20);
                $data["initiator_id"] = $user_id;
                $data["reciever_id"] = $resul["user_id"];
                $data["img"] = $this->getRequest()->getParam('img');
                $data["link"] = $url = $host . "teachclass/" . $data["classid"] . "?actionname=project&projectid=" . $data["projectid"]."&comid=".$commentid;
                $userLink = $url = $host . "profile/" . $data["initiator_id"];
                $data["type"] = 9;
                $data["seen_status"] = false;
                $imglink = $host . $data["img"];
                $notifyAllowed = $notification->getUserNotificationData($data["reciever_id"]);   // checks for notification settings
                $getUser = $objUsersModel->getFbConnectedStatus($data["reciever_id"]);     //get details of receiver
                $getInitiator = $objUsersModel->getFbConnectedStatus($data["initiator_id"]);     //get details of initiator
                $status = $this->getRequest()->getParam('status');
                
                $getProjectInfo = $getProjects->getProjectInfo($data["projectid"]);
                $proCoverImg = $host . $getProjectInfo['project_cover_image'];
                if ($status && $data["creator_id"] != $user_id) { {
                        $result = $notificationcen->insertnotifi($data);
                    
                    }
                }

                $projectli = Application_Model_ProjectCommentLikes::getinstance();
                $res = $projectli->projectcommentlikes($user_id, $data["classid"], $data["projectid"], $commentid);
//echo "<pre>"; print_r($res); die;
                if ($res == 1 && $data["creator_id"] != $user_id) {

                    $p = $points->getpointsinfo(1);
                    
                              $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points by liking a project comment";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems by liking a project comment";
                   
                    // Mandrill implementation       

                    if (($notifyAllowed['no_email'] == 0) && ($notifyAllowed['activity_your_project'] == 1)) {              //Only if email is activated
                        $template_name = 'like project';
                        $email = $getUser['email'];
                        $username = $getUser['first_name'];
                        $subject = 'Project liked';
                        $mergers = array(
                            array(
                                'name' => 'name',
                                'content' => $getUser['first_name']
                            ),
                            array(
                                'name' => 'img',
                                'content' => $proCoverImg
                            ),
                            array(
                                'name' => 'imglink',
                                'content' => $data["link"]
                            ),
                            array(
                                'name' => 'name2',
                                'content' => $getInitiator['first_name']
                            ),
                            array(
                                'name' => 'name2link',
                                'content' => $userLink
                            ),
                            array(
                                'name' => 'projectlink',
                                'content' => $data["link"]
                            ),
                            array(
                                'name' => 'projecttitle',
                                'content' => $data["title"]
                            )
                        );
                        try {
                            $sendMailresult = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                        } catch (Exception $e) {
                            
                        }
                    }
                    //-----------------------------------------------
                    $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);

                    while ($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                        $objUsermetaModel->updatelevel($user_id);
                        
                         $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                      $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                    }

                    $data1["likes_count"] = 1;

                    $usergamestats->updatestats($data1, $user_id);

                    $statss = $usergamestats->getstatsinfo($user_id);

                    $badges = $uachievement->getachinfo($user_id);

                    $achevementsid = array_column($badges, 'achevementsid');

                    $newbadges = $achievement->checkbadge($statss, $achevementsid);

                    foreach ($newbadges as $b1) {
                        $uachievement->awardbadge($user_id, $b1);
                    }
                }
                $response->rid = $data["creator_id"];
                break;



            case 'followuser':
                 $user = Application_Model_Users::getinstance();
                $data = array();
                $data["time"] = $date;

                $data["initiator_id"] = $user_id;
                $data["reciever_id"] = $this->getRequest()->getParam('userid');
                $data["creator_id"]=$data["reciever_id"];

                $data["link"] = $url = $host . "profile/" . $user_id;
                $userLink = $url = $host . "profile/" . $data["initiator_id"];
                $data["type"] = 10;
                $data["seen_status"] = false;

                $notifyAllowed = $notification->getUserNotificationData($data["reciever_id"]);   // checks for notification settings

                $status = $this->getRequest()->getParam('status');
                $subject = "Follow";
                $userid2 = $data["reciever_id"];

                $followModel = Application_Model_Followers::getinstance();
                $res = $followModel->getFollowDetail($userid2, $user_id);
                $followStatus = $res["follow_status"];



                $followers = $followModel->getnooffollowers($user_id);
                $following = $followModel->getnooffollowing($user_id);



                if ($followStatus == 0) {
                    $template_name = 'MeFollow';
                } else {
                    $template_name = 'MeNotFollow';
                }
                $objUsermetaModel = Application_Model_UsersMeta::getinstance();
                $getmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);
                $pic = $host . $getmetaresult['user_profile_pic'];


                $result = $user->getTeachername($userid2);
//                print_r($result); die;
                $email = $result['email'];

                $name = $result['first_name'];
                $link = $host . 'profile/' . $user_id;
                $plink=$link;
                if ($status && $data["creator_id"] != $user_id) { {
                        $result = $notificationcen->insertnotifi($data);
                    }
                }

               
                if ( $data["reciever_id"] != $user_id) {

                    $p = $points->getpointsinfo(1);
                    // Mandrill implementation       

                    if (($notifyAllowed['no_email'] == 0) && ($notifyAllowed['follower'] == 1)) {              //Only if email is activated
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
                                'name' => 'name2link',
                                'content' => $plink
                            ),
                            array(
                                'name' => 'topic',
                                'content' => $topic
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
                                'name' => 'pic',
                                'content' => $pic
                            )
                        );
                        try {
                            $sendMailresult = $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                        } catch (Exception $e) {
                            
                        }
                    }
                }
                $newbadges = "";
                $response->rid = $data["creator_id"];
                break;
                
                 case 'projectcommentreply':
                $data = array();
                $data["time"] = $date;
                $projectid = $this->getRequest()->getParam('projectid');
                $parentid = $this->getRequest()->getParam('parentid');
$data["projectid"]=$projectid;
                
              $projobj = Application_Model_ProjectComments::getinstance();
                $resul=$projobj->getCommentsdetail($parentid);
                $getProjectInfo = $getProjects->getProjectInfo($data["projectid"]);
                
                
                $data["classid"] = $this->getRequest()->getParam('classid');
                $data["creator_id"] = $resul["user_id"];
                $data["title"] =$getProjectInfo["project_title"];
                $data["initiator_id"] = $user_id;
                $data["reciever_id"] = $resul["user_id"];
                $data["img"] = $this->getRequest()->getParam('img');
                $data["link"] = $host . "teachclass/" . $data["classid"] . "?actionname=project&projectid=" . $projectid."&comid=".$parentid;
                $data["type"] = 11;
                $data["seen_status"] = false;
                if ($data["creator_id"] != $user_id)
                {
                    $result = $notificationcen->insertnotifi($data);
                $p = $points->getpointsinfo(4);
                
                   $messs[]="you earned <span class='color-purple'>".$p["points"]."</span> points by replying for a comment ";
                   
                    $messs[]="you earned <span class='color-green'>".$p["gems"]."</span> gems by replying for a comment ";
                   
                
                
                $objUsermetaModel->updatepoints($user_id, $p['points'], $p['gems']);
               while($nextlevel && (intval($userdata['points']) + $p['points']) >= $nextlevel['pointsrequired']) {
                    $objUsermetaModel->updatelevel($user_id);
                    
                     $messs[]='Congratulation, you are in <span class="color-blue">LEVEL '.$nextlevel["level"].'</span>';
                     
                      $nextlevel = $objlevel->getlevelsinfo(intval($nextlevel["level"]) + 1);
                }
                }
                $data1["comments"] = 1;

                $usergamestats->updatestats($data1, $user_id);

                $statss = $usergamestats->getstatsinfo($user_id);

                $badges = $uachievement->getachinfo($user_id);
                $achevementsid = array_column($badges, 'achevementsid');

                $newbadges = $achievement->checkbadge($statss, $achevementsid);

                foreach ($newbadges as $b1) {


                    $uachievement->awardbadge($user_id, $b1);
                }
                $response->rid = $data["creator_id"];
              
                break;
        }
        $notifyuserAllowed = $notification->getUserNotificationData($user_id); 
        $response->newbadges = $newbadges;
        $messss="";
        foreach($messs as $temps)
        {
            $messss.="<li class='border-bottom-light'><a>".$temps."</a></li>";
        }
        if(count($messs)!=0&& $notifyuserAllowed["no_gam_notifications"]==0)
        $response->mess = $messss;
        echo(json_encode($response));
        die();
    }

    public function getnotificationAction() {

        $user_id = $this->view->session->storage->user_id;
        $method = $this->getRequest()->getParam('method');

        $objnotifiModel = Application_Model_Notificationcenter::getInstance();

        $notifi = $objnotifiModel->getnotifi($user_id);
         $notificount = $objnotifiModel->getnotificount($user_id);
        if ($method == "refresh") {
            $res = $objnotifiModel->notifiseen($user_id);
            $notificount = 0;
        }
       
        $response = new stdClass();
        $noti = '<li><a href="#" style="padding-left:12em">Notifications</a></li>';
        if (isset($notifi)) {
            foreach ($notifi as $value) {
                $datetime = $value["time"];
                $datetime1 = $this->time_elapsed_string($datetime);

                if ($value['type'] == 1) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' liked your project </span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                } elseif ($value['type'] == 2) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' created project in your class </span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                } elseif ($value['type'] == 3) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' liked your discussion </span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                } elseif ($value['type'] == 4) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' commented on your discussion </span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                } elseif ($value['type'] == 5) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' replied to your comment </span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                } elseif ($value['type'] == 6) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' liked your comment </span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                } elseif ($value['type'] == 7) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' Created discussion </span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                } elseif ($value['type'] == 8) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' commented on your project</span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                } elseif ($value['type'] == 9) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' liked your comment </span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                }
                elseif ($value['type'] == 10) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' is following you </span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                }
                elseif ($value['type'] == 11) {
                    $noti.='<li><a href=' . $value["link"] . ' ><span class=""> <div class="activity-user-profile" style="width: 40px; margin-top: 10px;"> <img alt="" src="' . $value["user_profile_pic"] . '" style="margin-top: -16px; margin-left: -5px;"> </div> </span><span class="m-left-xs">' . $value["first_name"] . " " . ' replied to your comment </span><span class="time text-muted">' . $datetime1 . '</span></a></li>';
                }
            }
        }
        $noti.='<li><a href="/notifications">View all notifications</a></li>';


        $response->notificount = $notificount;

        $response->notifications = $noti;

        echo(json_encode($response));
        die();
    }

    public function clickAction() {
        if ($this->getRequest()->getPost('notid')) {
            $notid = $this->getRequest()->getPost('notid');

            $objnotifiModel = Application_Model_Notificationcenter::getInstance();
            $res = $objnotifiModel->notificlick($notid);

            if ($res)
                echo "success";
            die();
        }
    }

    public function getmoreAction() {
        $user_id = $this->view->session->storage->user_id;
        if ($user_id) {
            $response = new stdClass();
            $count = $this->getRequest()->getPost('count');

            $objnotifiModel = Application_Model_Notificationcenter::getInstance();

            $notifi = $objnotifiModel->getmorenotifi($user_id, $count);
            $noti = "";


            if (isset($notifi)) {
                foreach ($notifi as $value) {
                    $datetime = $value["time"];
                    $datetime1 = $this->time_elapsed_string($datetime);
                    if ($value['type'] == 1) {
                        // $noti.='<li><div class="activity-user-profile"><img alt="" src="/assets/images/profile/profile3.jpg"></div><div class="activity-detail"><span class="font-semi-bold">Karen Martin</span> started following <span class="font-semi-bold">Jame Smith</span>.<small class="text-muted block">36 mins ago</small> </div></li>'
                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span> liked your project  <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    } elseif ($value['type'] == 2) {

                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span> created project in your class  <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    } elseif ($value['type'] == 3) {

                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span> liked your discussion  <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    } elseif ($value['type'] == 4) {

                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span> commented on your discussion  <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    } elseif ($value['type'] == 5) {

                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span> replied to your comment  <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    } elseif ($value['type'] == 6) {

                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span> liked your comment <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    } elseif ($value['type'] == 7) {

                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span>  Created discussion <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    } elseif ($value['type'] == 8) {

                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span> commented on your project <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    } elseif ($value['type'] == 9) {

                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span>  liked your comment <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    }
                    elseif ($value['type'] == 10) {

                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span>  is following you <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    }
                    elseif ($value['type'] == 11) {

                        $noti.='<li class="col-md-12"><a href=' . $value["link"] . '><div class="activity-user-profile"><img alt="" src=' . $value["user_profile_pic"] . '></div><div class="activity-detail"><span class="font-semi-bold">' . $value["first_name"] . " " . '</span>  replied to your comment <span class="font-semi-bold"></span>.<small class="text-muted block">' . $datetime1 . '</small> </div><a></li>';
                    }
                }
            }


            if (count($notifi) == 5) {

                $response->more = 1;
            } else if (count($notifi) == 0) {
                $response->more = 2;
            } else {
                $response->more = 0;
            }


            $response->notifications = $noti;

            echo(json_encode($response));
            die();
        }
    }

    public function timezoneAction() {
        
        $appconfig=Zend_Registry::get('appconfig')->appSettings;
        $link=$appconfig->hostLink;
        die($link);
        
    }
    
    
    
    public function refreshAction() {
        
         $user_id = $this->view->session->storage->user_id;
         if($user_id)
         {
            $objUsermetaModel = Application_Model_UsersMeta::getinstance();
            $getmetaresult = $objUsermetaModel->getUserMetaDetail($user_id);
         $response = new stdClass();
         $response->points=$getmetaresult["points"];
          $response->gems=$getmetaresult["gems"];
         
         }
         
        
         echo(json_encode($response));
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

}
