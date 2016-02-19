<?php

class Application_Model_Followers extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'followers';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Followers();
        return self::$_instance;
    }

    /**
      Developer: Jeyakumar
     * Desc: Update follow status if already exists otherwise insert new row
     * */
    public function updateFollow() {

        if (func_num_args() > 0) {

            $followerid = func_get_arg(0);
            $followingid = func_get_arg(1);
            $status = func_get_arg(2);
            $date = date('Y-m-d');
            // $result="UPDATE followers SET follow_status = 1 - follow_status where follow_id=2";
            $select = $this->select()
                    ->where("follower_user_id= " . $followerid)
                    ->where("following_user_id =" . $followingid);
            $result = $this->getAdapter()->fetchRow($select);

            if ($result) {
                $data = array("follow_status" => 1 - $result['follow_status'], "follow_date" => $date);
                $where = array("follower_user_id = " . $followerid, "following_user_id = " . $followingid);
                $updateresult = $this->update($data, $where);
                 if($status==0){
                    return 1; die;
                }
                else{
                    return 0; die;
                }
  //              return $updateresult;
            } else {

                $data = array("follower_user_id" => $followerid, "following_user_id" => $followingid, "follow_status" => 0, "follow_date" => $date);
                $response = $this->insert($data);
//             print_r($response);die;
                return $response;
            }







            //$responseId = $this->update($data, "user_id= ".$where);
            // print_r($update);die;
        }
    }

    /**
      Developer: Jeyakumar
     *   Desc: Get Follow Details
     * */
    public function getFollowDetail() {
        if (func_num_args() > 0) {
            $followerid = func_get_arg(0);

            $followingid = func_get_arg(1);

            //$where = array("follower_user_id"=>$followerid,"following_user_id"=>$followingid);

            $select = $this->select()
                    ->where('follower_user_id = ?', $followerid)
                    ->where('following_user_id = ?', $followingid);

            $result = $this->getAdapter()->fetchRow($select);
        }
        if ($result) {
            return $result;
        } else {
            return 0;
        }
    }

    public function getFollwerDetail() {

        if (func_num_args() > 0) {
            $uid = func_get_arg(0);

            try {
                $select = $this->select()
                        ->from($this)
                        ->where('follower_user_id = ?', $uid);

                $result = $this->getAdapter()->fetchAll($select);
                //echo "<pre>";print_r($result); echo "</pre>"; die('123');
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getIFollow() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('l' => 'followers'))
                        ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.following_user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                        ->join(array('con' => 'users'), 'con.user_id = l.following_user_id', array('con.user_id', 'con.first_name', 'con.last_name'))
                        ->where('l.follower_user_id = ?', $user_id)
                        ->where('l.follow_status = 0');

                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getFollowMe() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('l' => 'followers'))
                        ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.follower_user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                        ->join(array('con' => 'users'), 'con.user_id = l.follower_user_id', array('con.user_id', 'con.first_name', 'con.last_name'))
                        ->where('l.following_user_id = ?', $user_id)
                        ->where('l.follow_status = 0');

                $result = $this->getAdapter()->fetchAll($select);
               
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*  Dev: Namrata Singh
     *  Date: 31/1/2015
      Desc: To fetch details of all the followed classes by any user
     * ***(being USED)***
     */

    public function getPeopleFollowDetails() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('f' => 'followers'))                              
                        ->join(array('um' => 'usersmeta'), 'f.following_user_id = um.user_id', array('um.user_profile_pic', 'um.user_headline'))
                        ->join(array('u' => 'users'), 'u.user_id = f.following_user_id', array('u.first_name', 'u.last_name','u.premium_status'))
                        ->join(array('tc' => 'teachingclasses'), 'tc.user_id = f.following_user_id', '*')
//                        ->join(array('tcv' => 'teachingclassvideo'),'tcv.class_id = tc.class_id', array('tcv.video_thumb_url'))
                         ->join(array('cat' => 'category'),'cat.category_id = tc.category_id', array('category_name'))
                        ->where('f.follower_user_id = ?', $user_id)
                        ->where('f.follow_status = 0')
                        ->limit(4);
                       


                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }
          
            if ($result) {


                $i = 0;
                foreach ($result as $val) {

                    $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('tcv' => 'teachingclassvideo'))
                           // ->join(array('cat' => 'category'), 'cat.category_id = ' . $val["category_id"], array('cat.category_name'))
                            ->where('tcv.class_id= ?', $val['class_id'])
                            ->limit(4);
                    $resultvideo = $this->getAdapter()->fetchRow($select);

                    $result[$i]['video_thumb_url'] = $resultvideo['video_thumb_url'];
                   // $result[$i]['category_name'] = $resultvideo['category_name'];
                    $result[$i]['cover_image'] = $resultvideo['cover_image'];
                    ;
                    $i++;
                }

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

                //echo "<pre>"; print_r($result);die;
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getFollowUserProject() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('f' => 'followers'))
                        ->join(array('um' => 'usersmeta'), 'f.following_user_id = um.user_id', array('um.user_profile_pic'))
                        ->join(array('u' => 'users'), 'u.user_id = f.following_user_id', array('u.first_name', 'u.last_name', 'u.premium_status'))
                        ->join(array('p' => 'projects'), 'p.user_id = f.following_user_id', '*')
                        ->where('f.follower_user_id = ?', $user_id)
                        ->where('f.follow_status = 0');

                $result = $this->getAdapter()->fetchAll($select);
//              echo "<pre>"; print_r($result); echo "</pre>"; die('123');
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getFollowUserRecentProject() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('f' => 'followers'))
                        ->join(array('um' => 'usersmeta'), 'f.following_user_id = um.user_id', array('um.user_profile_pic'))
                        ->join(array('u' => 'users'), 'u.user_id = f.following_user_id', array('u.first_name', 'u.last_name','u.premium_status'))
                        ->join(array('p' => 'projects'), 'p.user_id = f.following_user_id', '*')
                        ->where('f.follower_user_id = ?', $user_id)
                        ->where('f.follow_status = 0')
                        ->order('p.project_created_date DESC');
                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die('123');
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getFollowUserLikeProject() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('f' => 'followers'))
                        ->join(array('um' => 'usersmeta'), 'f.following_user_id = um.user_id', array('um.user_profile_pic'))
                        ->join(array('u' => 'users'), 'u.user_id = f.following_user_id', array('u.first_name', 'u.last_name'))
                        ->join(array('p' => 'projects'), 'p.user_id = f.following_user_id', '*')
                        ->where('f.follower_user_id = ?', $user_id)
                        ->where('f.follow_status = 0');

                $result = $this->getAdapter()->fetchAll($select);
//              echo "<pre>"; print_r($result); echo "</pre>"; die('123');
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getFollowUserDiscussion() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('f' => 'followers'))
                        ->join(array('um' => 'usersmeta'), 'f.following_user_id = um.user_id', array('um.user_profile_pic'))
                        ->join(array('u' => 'users'), 'u.user_id = f.following_user_id', array('u.first_name', 'u.last_name'))
                        ->join(array('d' => 'classdiscussions'), 'd.user_id = f.following_user_id', '*')
                        ->where('f.follower_user_id = ?', $user_id)
                        ->where('f.follow_status = 0');

                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die('123');
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getFollowUserRecentDiscussion() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('f' => 'followers'))
                        ->join(array('um' => 'usersmeta'), 'f.following_user_id = um.user_id', array('um.user_profile_pic'))
                        ->join(array('u' => 'users'), 'u.user_id = f.following_user_id', array('u.first_name', 'u.last_name'))
                        ->join(array('d' => 'classdiscussions'), 'd.user_id = f.following_user_id', '*')
                        ->where('f.follower_user_id = ?', $user_id)
                        ->where('f.follow_status = 0')
                        ->order('d.discussed_date DESC');
                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die('123');
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getFollowUserLikeDiscussion() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('f' => 'followers'))
                        ->join(array('um' => 'usersmeta'), 'f.following_user_id = um.user_id', array('um.user_profile_pic'))
                        ->join(array('u' => 'users'), 'u.user_id = f.following_user_id', array('u.first_name', 'u.last_name'))
                        ->join(array('d' => 'classdiscussions'), 'd.user_id = f.following_user_id', '*')
                        ->where('f.follower_user_id = ?', $user_id)
                        ->where('f.follow_status = 0');

                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die('123');
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getnooffollowers() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {

                $select = $this->select()
                        ->where('follower_user_id = ?', $user_id)
                        ->where('follow_status = 0');


                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            return count($result);
        }
    }

    public function getnooffollowing() {


        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {

                $select = $this->select()
                        ->where('following_user_id = ?', $user_id)
                        ->where('follow_status = 0');


                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            return count($result);
        }
    }

    public function getIsFollow() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $following_user_id = func_get_arg(1);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('f' => 'followers'))
                        ->where('f.follower_user_id = ?', $user_id)
                        ->where('f.following_user_id = ?', $following_user_id)
                        ->where('f.follow_status = 0');

                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }
            if ($result) {
                return 1;
            } else {
                return 0;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getTopIFollow() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('l' => 'followers'))
                        ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.following_user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                        ->join(array('con' => 'users'), 'con.user_id = l.following_user_id', array('con.user_id', 'con.first_name', 'con.last_name', 'con.premium_status'))
                        //->join(array('tcv' => 'teachingclassvideo'),'l.following_user_id = tcv.user_id',array('tcv.video_thumb_url','tcv.class_id'))
                        // ->join(array('tc' => 'teachingclasses'), 'tcv.class_id = tc.class_id', array('tc.category_id','tc.class_title'))
                        //->join(array('c' => 'category'),'c.category_id = tc.category_id',array('c.category_name'))
                        ->where('l.follower_user_id = ?', $user_id)
                        ->where('l.follow_status = 0');


                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getTopIFollowVideos() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->join(array('tcv' => 'teachingclassvideo'), 'l.following_user_id = tcv.user_id', array('tcv.video_thumb_url', 'tcv.class_id'))
                        ->join(array('tc' => 'teachingclasses'), 'tcv.class_id = tc.class_id', array('tc.category_id', 'tc.class_title'))
                        ->join(array('c' => 'category'), 'c.category_id = tc.category_id', array('c.category_name'))
                        //->where('l.follower_user_id = ?',$user_id)
                        //->where('l.follow_status = 0')
                        ->where('user_id IN(?)', $user_id);

                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

}

?>