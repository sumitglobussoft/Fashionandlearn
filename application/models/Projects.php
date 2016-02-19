<?php

class Application_Model_Projects extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'projects';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Projects();
        return self::$_instance;
    }

    /**
      Developer: Jeyakumar
     * Desc: Update follow status if already exists otherwise insert new row
     * */
    public function updateProject() {

        if (func_num_args() > 0) {

            $userid = func_get_arg(0);
            $classid = func_get_arg(1);
            $data = func_get_arg(2);
            $select = $this->select()
                    ->where("user_id = " . $userid)
                    ->where("class_id =" . $classid);
            $result = $this->getAdapter()->fetchRow($select);

            if ($result) {

                $where = array("user_id = " . $userid, "class_id = " . $classid);
                $updateresult = $this->update($data, $where);
                return $updateresult;
            } else {

                $response = $this->insert($data);
                return $response;
            }
        }
    }

    /**
      Developer: Jeyakumar
     *   Desc: Get Follow Details
     * */
    public function getProjectDetail() {
        if (func_num_args() > 0) {
            // $userid = func_get_arg(0);
            $classid = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'projects'))
//                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title'))
                    ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name', 'con.email'))
//                    ->where('l.user_id = ?',$userid)
                    ->where('l.class_id = ?', $classid);
            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
                $i = 0;
                foreach ($result as $val) {
                    $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('cr' => 'classenroll'), array("stud_count" => "COUNT(*)"))
                            ->where('cr.class_id = ?', $val['class_id']);
                    $resultcount = $this->getAdapter()->fetchRow($select);
                    $result[$i]['stud_count'] = $resultcount['stud_count'];
                    $i++;
                }
                return $result;
            }
        }
    }

    /**
      Developer: Jeyakumar
     *   Desc: Get Follow Details
     * */
    public function getMyProject() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $classid = func_get_arg(1);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'projects'))
//                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title'))
                    ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
                    ->where('l.user_id = ?', $userid)
                    ->where('l.class_id = ?', $classid);
            $result = $this->getAdapter()->fetchRow($select);
            if ($result) {
                return $result;
            }
        }
    }

    /**
      Developer: Rakesh Jha
     *   Desc: Get All the projects details with pagination
     * */
    public function getallprojectspage($page) {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => "projects"))
                ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = p.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                ->join(array('con' => 'users'), 'con.user_id = p.user_id', array('con.first_name', 'con.email', 'con.last_name', 'con.premium_status'))
                ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id = p.class_id', array('tc.category_id', 'tc.class_title'))
                ->joinLeft(array('cat' => 'category'), 'cat.category_id = tc.category_id', array('cat.category_name'))
               
                ->limitpage($page,10);
        
//                ->order('p.project_created_date DESC');
        $result = $this->getAdapter()->fetchAll($select);
    // echo '<pre>'; print_r(count($result)); echo '</pre>'; die;
        if ($result) {
            return $result;
        }
    }
    
     public function getallprojects() {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => "projects"))
                ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = p.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                ->join(array('con' => 'users'), 'con.user_id = p.user_id', array('con.first_name', 'con.email', 'con.last_name', 'con.premium_status'))
                ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id = p.class_id', array('tc.category_id', 'tc.class_title'))
                ->joinLeft(array('cat' => 'category'), 'cat.category_id = tc.category_id', array('cat.category_name'))
               
               ;
        
//                ->order('p.project_created_date DESC');
        $result = $this->getAdapter()->fetchAll($select);
    // echo '<pre>'; print_r(count($result)); echo '</pre>'; die;
        if ($result) {
            return $result;
        }
    }
    
       /*
      Developer: Rakesh jha
      Desc: Get All most recent projects details
     */
     public function gettotalprojects() {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => "projects"))
                ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = p.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                ->join(array('con' => 'users'), 'con.user_id = p.user_id', array('con.first_name', 'con.email', 'con.last_name', 'con.premium_status'))
                ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id = p.class_id', array('tc.category_id', 'tc.class_title'))
                ->joinLeft(array('cat' => 'category'), 'cat.category_id = tc.category_id', array('cat.category_name'))
                  ;
//                ->order('p.project_created_date DESC');
        $result = $this->getAdapter()->fetchAll($select);
//     echo '<pre>'; print_r($result); echo '</pre>'; die;
        if ($result) {
            return $result;
        }
    }
    

    /*
      Developer: Rakesh jha
      Desc: Get All most recent projects details
     */

    public function mostrecentProjectsPage($page) {

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => "projects"))
                ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = p.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                ->join(array('con' => 'users'), 'con.user_id = p.user_id', array('con.first_name', 'con.last_name', 'con.premium_status'))
                ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id = p.class_id', array('tc.category_id', 'tc.class_title'))
                ->joinLeft(array('cat' => 'category'), 'cat.category_id = tc.category_id', array('cat.category_name'))
                ->order('p.project_created_date DESC')
                ->limitpage($page,10);
       
        $result = $this->getAdapter()->fetchAll($select);
     
        if ($result) {
            return $result;
        }
    }

     public function mostrecentProjects() {

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => "projects"))
                ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = p.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                ->join(array('con' => 'users'), 'con.user_id = p.user_id', array('con.first_name', 'con.last_name', 'con.premium_status'))
                ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id = p.class_id', array('tc.category_id', 'tc.class_title'))
                ->joinLeft(array('cat' => 'category'), 'cat.category_id = tc.category_id', array('cat.category_name'))
                ->order('p.project_created_date DESC')
               ;
       
        $result = $this->getAdapter()->fetchAll($select);
     
        if ($result) {
            return $result;
        }
    }
    public function mostlikeProjectsPage($page) {

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => "projects"))
                ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = p.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                ->join(array('con' => 'users'), 'con.user_id = p.user_id', array('con.first_name', 'con.last_name', 'con.premium_status'))
                ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id = p.class_id', array('tc.category_id', 'tc.class_title'))
                ->joinLeft(array('cat' => 'category'), 'cat.category_id = tc.category_id', array('cat.category_name'))
                ->order('p.project_created_date DESC')
                ->limitpage($page,10)
                ;
        $result = $this->getAdapter()->fetchAll($select);
//     echo '<pre>'; print_r($result); echo '</pre>'; die;
        if ($result) {
            return $result;
        }
    }
    
      public function mostlikeProjects() {

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('p' => "projects"))
                ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = p.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                ->join(array('con' => 'users'), 'con.user_id = p.user_id', array('con.first_name', 'con.last_name', 'con.premium_status'))
                ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id = p.class_id', array('tc.category_id', 'tc.class_title'))
                ->joinLeft(array('cat' => 'category'), 'cat.category_id = tc.category_id', array('cat.category_name'))
                ->order('p.project_created_date DESC')
                ;
        $result = $this->getAdapter()->fetchAll($select);
//     echo '<pre>'; print_r($result); echo '</pre>'; die;
        if ($result) {
            return $result;
        }
    }

    public function getTrendProjectDetail() {
        if (func_num_args() > 0) {
            // $userid = func_get_arg(0);
            $classid = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'projects'))
//                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title'))
                    ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
                    //  ->joinLeft(array('proj' => 'projectlikes'), 'proj.project_id = l.project_id',array("likescount"=>"COUNT(*)"))
//                    ->where('l.user_id = ?',$userid)
                    ->where('l.class_id = ?', $classid);
            $result = $this->getAdapter()->fetchAll($select);

            if ($result) {

                return $result;
            }
        }
    }

    public function getPopularProjectDetail() {
        if (func_num_args() > 0) {
            // $userid = func_get_arg(0);
            $classid = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'projects'))
//                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title'))
                    ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
                    //  ->joinLeft(array('proj' => 'projectlikes'), 'proj.project_id = l.project_id',array("likescount"=>"COUNT(*)"))
//                    ->where('l.user_id = ?',$userid)
                    ->where('l.class_id = ?', $classid);
            $result = $this->getAdapter()->fetchAll($select);

            if ($result) {

                return $result;
            }
        }
    }

    public function getRecentProjectDetail() {
        if (func_num_args() > 0) {
            // $userid = func_get_arg(0);
            $classid = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'projects'))
//                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title'))
                    ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
                    //  ->joinLeft(array('proj' => 'projectlikes'), 'proj.project_id = l.project_id',array("likescount"=>"COUNT(*)"))
//                    ->where('l.user_id = ?',$userid)
                    ->where('l.class_id = ?', $classid)
                    ->order('l.project_created_date DESC');
            $result = $this->getAdapter()->fetchAll($select);

            if ($result) {

                return $result;
            }
        }
    }

    public function getProjectById() {
        if (func_num_args() > 0) {
            // $userid = func_get_arg(0);
            $projectid = func_get_arg(0);
            $userid = func_get_arg(1);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'projects'))
//                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title'))
                    ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
                    ->joinLeft(array('f' => 'followers'), 'l.user_id = f.following_user_id and ' . $userid . ' = f.follower_user_id and f.follow_status=0', array('f.follow_status'))
                    //  ->joinLeft(array('proj' => 'projectlikes'), 'proj.project_id = l.project_id',array("likescount"=>"COUNT(*)"))
//                    ->where('l.user_id = ?',$userid)
                    ->where('l.project_id = ?', $projectid);
            $result = $this->getAdapter()->fetchAll($select);

            if ($result) {

                return $result;
            }
        }
    }

    public function getProjectBypId() {
        if (func_num_args() > 0) {
            // $userid = func_get_arg(0);
            $projectid = func_get_arg(0);
            // $userid = func_get_arg(1);        
            $select = $this->select()
                    ->where('project_id = ?', $projectid);
            $result = $this->getAdapter()->fetchAll($select);

            if ($result) {

                return $result;
            }
        }
    }

    /*
      Developer:Rakesh jha
      Desc:Projects Details on Teacher DashBoard
      Dated:10/02/15
     */

    public function teacherprojectDetails() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'projects'))
                    ->join(array('u' => 'users'), 'p.user_id = u.user_id', array('u.first_name', 'p.project_title', 'p.project_workspace'))
//                    ->where('l.user_id = ?',$userid)
                    ->where('p.user_id = ?', $user_id);
            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
//               echo '<pre>' ;               print_r($result); echo '</pre>'; die;
                return $result;
            }
        }
    }

    /* Developer: Rakesh jha
      Dated:02/11/2015
      Desc:New projects on Dashboard

     */

    public function newProjects() {
        if (func_num_args() > 0) {
            $class_id = func_get_arg(0);


            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => "projects"))
                    ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = p.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = p.user_id', array('con.first_name', 'con.last_name'))
                    //->joinLeft(array('tl' => 'teachingclasses'), 'tl.user_id =tl.user_id', array('tl.class_id','tl.user_id'))
                    ->order('p.project_created_date DESC');
            // ->where('tl.user_id=2');
            $result = $this->getAdapter()->fetchAll($select);
//     echo '<pre>'; print_r($result); echo '</pre>'; die;
            if ($result) {
                return $result;
            }
        }
    }

    public function myProjectDetails() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'projects'), '*')
                    ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = p.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->joinleft(array('con' => 'users'), 'con.user_id = p.user_id', array('con.first_name', 'con.last_name'))
                    ->where('p.user_id = ?', $user_id)
                    ->order('p.project_created_date DESC');
            $result = $this->getAdapter()->fetchAll($select);
            //   echo '<pre>' ;               print_r($result); echo '</pre>'; die;
            if ($result) {
                return $result;
            }
        }
    }

//    public function myProjectDetails() {
//           if (func_num_args() > 0) {
//               $user_id = func_get_arg(0);
//
//               $select = $this->select()
//                       ->setIntegrityCheck(false)
//                       ->from(array('p' => 'projects'), array('p.project_title', 'p.project_cover_image', 'p.project_privacy', 'p.project_id'))
//                       //->joinleft(array('pl' => 'projectlikes'),'p.project_id = pl.project_id',array("likecount" => "COUNT(*)"))
//                       //->join(array('u' => 'users'), 'p.user_id = u.user_id', array('u.first_name', 'p.project_title', 'p.project_workspace'))                 
//                       ->where('p.user_id = ?', $user_id);
//               $result = $this->getAdapter()->fetchAll($select);
//               //   echo '<pre>' ;               print_r($result); echo '</pre>'; die;
//               if ($result) {
//
//                   return $result;
//               }
//           }
//       }
    /*
      Developer:Namrata Singh
      Desc:Getting all prpject details based on classid
      Dated:17/02/15
     */

    public function projectsOnClassEnroll() {
        if (func_num_args() > 0) {
            $class_id = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'projects'), array('p.user_id', 'p.project_title', 'p.project_cover_image', 'p.project_privacy', 'p.project_id', 'p.project_workspace', 'p.project_created_date', 'p.project_updated_date', 'p.fshare', 'p.tshare', 'p.pshare'))
                    //->joinleft(array('pl' => 'projectlikes'),'p.project_id = pl.project_id',array("likecount" => "COUNT(*)"))
                    //->join(array('u' => 'users'), 'p.user_id = u.user_id', array('u.first_name', 'p.project_title', 'p.project_workspace'))                 
                    ->joinleft(array('u' => 'users'), 'p.user_id = u.user_id', array('u.first_name', 'u.last_name'))
                    ->joinleft(array('um' => 'usersmeta'), 'p.user_id = um.user_id', array('um.user_profile_pic'))
                    ->where('p.class_id IN(?)', $class_id);
            $result = $this->getAdapter()->fetchAll($select);
            //   echo '<pre>' ;               print_r($result); echo '</pre>'; die;
            if ($result) {

                return $result;
            }
        }
    }

    /*
      Developer:Rakesh Jha
      Desc:Notification on like
      Dated:18/02/15
     */

    public function likenotification() {
        if (func_num_args() > 0) {
            $project_id = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'projects'), array('p.user_id', 'p.project_title'))
                    ->join(array('n' => 'notification'), 'p.user_id = n.user_id', array('n.user_id', 'n.activity_your_project'))
                    ->where('p.project_id = ?', $project_id);
            $result = $this->getAdapter()->fetchAll($select);
//            print_r($result); die;
            if ($result) {
                return $result;
            }
        }
    }

    public function getUserProject() {
        if (func_num_args() > 0) {
            $projectid = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'projects'))
//                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title'))
                    ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->joinLeft(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name', 'con.email'))
                    ->where('l.project_id = ?', $projectid);
            $result = $this->getAdapter()->fetchRow($select);


            if ($result) {
                return $result;
            }
        }
    }

    /* Developer:Abhisek M 
      Desc:Delete The project from teacher-dashboard
      Dated:22-3-2015 */

    public function deleteProject() {
        if (func_num_args() > 0) {
            $project_id = func_get_arg(0);
            $result = $this->delete('project_id = "' . $project_id . '"');


            if ($result) {
                return $project_id;
            }
        }
    }

    public function getrecentProjects() {
        if (func_num_args() > 0) {

            $user_id = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'projects'))
                    ->join(array('c' => 'teachingclasses'), 'l.class_id=c.class_id', array('class_title'))
                    ->join(array('u' => 'users'), 'l.user_id = u.user_id', array('u.first_name', 'u.last_name', 'u.premium_status'))
                    ->joinleft(array('p' => 'projectlikes'), '(l.project_id=p.project_id)&&(l.user_id=p.user_id)', array('like_status'))
                    ->where('l.user_id=?', $user_id)
                    ->order('l.project_id DESC');
        }
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }

    public function getProjects() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'projects'))
                    ->where('l.user_id=?', $user_id);
        }
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }

    public function getProjectsCount() {
        if (func_num_args() > 0) {
            $class_id = func_get_arg(0);
            $select = $this->select()
                    ->where('class_id=?', $class_id);
        }
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return count($result);
        }
    }

    public function getProjectInfo() {
        if (func_num_args() > 0) {
            $project_id = func_get_arg(0);
            $select = $this->select()
                    ->where('project_id=?', $project_id);
        }
        $result = $this->getAdapter()->fetchRow($select);
        if ($result) {
            return $result;
        }
    }

    /*
     * 
     * abhishek m
     * 
     */

    public function pfshare() {
        if (func_num_args() > 0) {
            $project_id = func_get_arg(0);
            $data = array("fshare" => new Zend_Db_Expr("fshare + 1"));
            $result = $this->update($data, "project_id=$project_id");

            if ($result) {
                return $result;
            }
        }
    }

    public function ptshare() {
        if (func_num_args() > 0) {
            $project_id = func_get_arg(0);
            $data = array("tshare" => new Zend_Db_Expr("tshare + 1"));
            $result = $this->update($data, "project_id=$project_id");

            if ($result) {
                return $result;
            }
        }
    }

    public function ppshare() {
        if (func_num_args() > 0) {
            $project_id = func_get_arg(0);
            $data = array("pshare" => new Zend_Db_Expr("pshare + 1"));
            $result = $this->update($data, "project_id=$project_id");

            if ($result) {
                return $result;
            }
        }
    }

    public function gettotalproj() {

        $select = $this->select();
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return count($result);
        }
    }

}

?>