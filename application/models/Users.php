<?php

class Application_Model_Users extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'users';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Users();
        return self::$_instance;
    }

    /* Developer:Namrata Singh
      Desc : inserting data's in the users table
     * (being used)*
     */

    public function insertUser() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            try {
                $responseId = $this->insert($data);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($responseId) {

                return $responseId;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /**
      Developer: Namrata Singh
     * Desc: Updating data on Profile change action
     * */
    public function editUser() {

        if (func_num_args() > 0) {

            $data = func_get_arg(0);
            $where = func_get_arg(1);

            $update = $this->update($data, 'user_id =' . $where);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    }

    /**
      Developer: Namrata Singh
     *   Desc: Update email of user on account action
     * */
    public function editUserEmail($data, $user_id) {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $user_id = func_get_arg(1);
            $where = " user_id = " . $user_id;

            $result = $this->update($data, $where);
            if ($result) {
                return $result;
            }
        }
    }

    /*
     * Dev. Namrata Singh
     * Description: For validation of email while signup
     */

    public function validateEmailId() {

        if (func_num_args() > 0) {
            $email = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this)
                        ->where('email = ?', $email);

                $result = $this->getAdapter()->fetchRow($select);
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

    public function fetchuserbyEmailId() {

        if (func_num_args() > 0) {
            $email = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this)
                        ->where('email = ?', $email);

                $result = $this->getAdapter()->fetchRow($select);
                if ($result) {
                    return $result;
                } else {
                    return 0;
                }
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

    /*
     * Dev. Namrata Singh
     * Date:12/1/2015
     * Description: Get the value from Database based on UserId 
     */

    public function getUserDetail() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('l' => 'users'))
                        ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id')
                        ->where('l.user_id = ?', $user_id);

                $result = $this->getAdapter()->fetchRow($select);
//               echo "<pre>"; print_r($result); die;
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

    /**
      Developer: Namrata Singh
     *  Description: Selecting password based on userid for password authentication
     * */
    public function validatePassword() {

        if (func_num_args() > 0) {
            $userid = func_get_arg(0);

            try {

                $select = $this->select()
                        ->from($this, 'password')
                        ->where('user_id = ?', $userid);

                $result = $this->getAdapter()->fetchCol($select);
            } catch (Exception $e) {
                throw new Exception('Unable to access data :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /**
      Developer: Namrata Singh
     *  Description: Updating the table with the new password after checking teh current password 
     * */
    public function changePassword() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $userId = func_get_arg(1);
            $where = "user_id= " . $userId;
            try {
                $update = $this->update($data, $where);
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            if ($update) {
                return $update;
            }
        } else {
            throw new Exception('Argument not passed');
        }
    }

    //dev:priyanka varanasi  
    //desc: to validate email
    public function validateUserEmail() {

        if (func_num_args() > 0) {
            $userEmail = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this)
                        ->where('email = ?', $userEmail);

                $result = $this->getAdapter()->fetchRow($select);
            } catch (Exception $e) {
                throw new Exception('Unable to access data :' . $e);
            }

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

//dev:priyanka varanasi  
    //desc: TO update activation link in db
    public function updateActivationLink() {
        if (func_num_args() > 0) {
            $code = func_get_arg(0);
            $userId = func_get_arg(1);
            $date = date('Y-m-d H:i:s');
           
            $data = array('activation_code' => $code,
                          'passwordlink_exp'=>$date
                           );
            try {
                $update = $this->update($data, 'user_id =' . $userId);
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            if ($update) {
                return $update;
            }
        } else {
            throw new Exception('Argument not passed');
        }
    }

    //dev:priyanka varanasi  
    //desc: TO check the key whether exists in db or not
    public function checkActivationKey() {
        if (func_num_args() > 0) {
            $userId = func_get_arg(0);
            $key = func_get_arg(1);

            try {
                $select = $this->select()
                        ->from($this)
                        ->where('user_id = ?', $userId)
                        ->where('activation_code = ?', $key);
                $result = $this->getAdapter()->fetchRow($select);
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument not passed');
        }
    }

    public function twitterIdExist() {
        $twid = func_get_arg(0);
        $select = $this->select()->where('tw_id = ?', $twid);
        $result = $this->getAdapter()->fetchRow($select);
        if ($result) {
            return $result;
        }
    }

    /* Developer:partha neog
      Desc :getting user_id and fetching the details from users,teaching video and teaching classes table.
     */

    public function searchUsersResult() {
        $search = func_get_arg(0);

        $search = '%' . $search . '%';

        $select = $this->select()
                ->from(array('u' => 'users'), array('user_id', 'first_name', 'last_name'))
                ->setIntegrityCheck(false)
                ->join(array('um' => 'usersmeta'), 'u.user_id = um.user_id', array('um.user_profile_pic'))
                ->join(array('tc' => 'teachingclasses'), 'u.user_id = tc.user_id', array('tc.class_title', 'tc.class_id'))
                ->join(array('tcv' => 'teachingclassvideo'), 'tc.class_id = tcv.class_id', array('video_thumb_url', 'class_video_url'))
                ->where('tc.class_title LIKE ?', $search)
                ->orwhere('concat(u.first_name," ",u.last_name) LIKE ?', $search)
                ->orwhere('concat(u.last_name," ",u.first_name) LIKE ?', $search);
        //->orwhere('u.last_name LIKE ?', $search);
//               echo $select; die;

        $result = $this->getAdapter()->fetchAll($select);
        //echo '<pre>'; print_r($result); die;
        if ($result) {
            return $result;
        }
    }

    //dev: priyanka varanasi
    //desc: to check whether fb users exists in db or not
    public function checkFBUserExist() {
        if (func_num_args() > 0) {
            $email = func_get_arg(0);
            $fbId = func_get_arg(1);
            try {
                $select = $this->select()
                        ->from($this)
                        //dev:priyanka varanasi
                        //commented the fb id where condition , becoz it will return rows which contain the fb id
                        ->where('fb_id = ?', $fbId)
                        ->Where('email = ?', $email);
                $result = $this->getAdapter()->fetchRow($select);
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            if ($result) {
                return ($result);
            }
        } else {
            throw new Exception('Argument not passed');
        }
    }

    // dev: priyanka varanasi
    // desc: to validate user name
    public function validateUserName() {

        if (func_num_args() > 0) {
            $userName = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this)
                        ->where('first_name = ?', $userName);

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

    //dev: priyanka varanasi
    //desc: to update facebook id in the users list
    public function updateFBID() {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $userId = func_get_arg(1);

            try {
                $update = $this->update($data, 'user_id =' . $userId);
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            if ($update) {
                return $update;
            }
        } else {
            throw new Exception('Argument not passed');
        }
    }

    /*
      Developer: Rakesh Jha
      Date : 9Feb 14
      Desc:Teacher Dashboard for user who is teacher

     */

    public function publishedClass() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $select = $this->select()
                    ->from(array('u' => 'users'), array('u.user_id'))
                    ->setIntegrityCheck(false)
                    ->join(array('tc' => 'teachingclasses'), 'u.user_id = tc.user_id', array('tc.publish_status'))
                    ->where('tc.publish_status=?', 0)
                    ->where('u.user_id=?', $user_id);
            $result = $this->getAdapter()->fetchRow($select);
            if ($result) {

                return $result;
//               echo '<pre>'; print_r($result); echo '</pre>'; die;
            }
        }
    }

    //dev: priyanka varanasi
    //desc: to update password in db
    public function changePasswordsettings() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $userId = func_get_arg(1);
            $where = "user_id= " . $userId;
            try {
                $update = $this->update($data, $where);
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            if ($update) {
                return $update;
            }
        } else {
            throw new Exception('Argument not passed');
        }
    }

    /*
      Developer: Namrata Singh
      Date : 12 march'15
      Desc:selects all the users from database
     */

    public function selectAll() {
        try {
            $select = $this->select()
                    ->distinct()
                    ->from(array('u' => 'users'), array('u.user_id'))
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'payment'), 'p.user_id = u.user_id', array('p.user_id'));


            $result = $this->getAdapter()->fetchAll($select);
        } catch (Exception $exc) {
            throw new Exception('Unable to update, exception occured' . $exc);
        }
        //echo "<pre>"; print_r($result); die('----');
        if ($result) {
            return $result;
        }
    }

    /*
      Developer: Namrata Singh
      Date : 12 march'15
      Desc: update the database with the count of the referred members
     */

    public function updateCount() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from($this, array("count_referral" => "COUNT(*)"))
                        ->where('referrals = ?', $user_id)
                        ->where('premium_status=?', 1);

                $result = $this->getAdapter()->fetchRow($select);
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            if ($result) {
                try {
                    $count = $result['count_referral'];
                    $data = array('referral_counts' => $count);
                    $update = $this->update($data, 'user_id =' . $user_id);
                } catch (Exception $exc) {
                    throw new Exception('Unable to update, exception occured' . $exc);
                }
                //echo "<pre>"; print_r($update); die('---here itself----');
                if ($update) {
                    return $update;
                }
            } else {
                throw new Exception('Argument not passed');
            }
        }
    }

    /*
      Developer: Namrata Singh
      Date : 12 march'15
      Desc: checks for user having bonus month
     */

    public function bonusMonth() {
        try {
            $select = $this->select()
                    ->from($this)
                    ->where('referral_counts > 0');

            $result = $this->getAdapter()->fetchAll($select);
            //echo "<pre>"; print_r($result); die('----------');
        } catch (Exception $exc) {
            throw new Exception('Unable to update, exception occured' . $exc);
        }
        //  echo "<pre>"; print_r($result); die('----');
        if ($result) {
            return $result;
        }
    }

    /*
      Developer: Namrata Singh
      Date : 12 march'15
      Desc: updating the count of referrals after awarding the free months and extending the dates
     */

    public function userUpdateCount() {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where = func_get_arg(1);
            try {
                $update = $this->update($data, 'user_id =' . $where);

                if (isset($update)) {
                    return $update;
                } else {
                    throw new Exception('Argument Not Passed');
                }
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
        }
    }

    /* Developer:Rakesh Jha
      Dated:14-03-15
      Desc: Get all refered student by  a teacher
     */

    public function getReferedStudents() {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('u' => 'users'))
                ->joinLeft(array('p' => 'payment'), 'p.user_id = u.user_id', array('p.reffered', 'total_user' => 'COUNT(p.user_id)', 'p.subscription_id'))
                ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id=u.refferal_class_id', array('tc.user_id'))
                ->where('u.teacher_refferal=?', 1)
                ->where('p.status=?', 'paid')
//                ->where('p.subscription_id=?',54)  
                ->group('tc.user_id');
        // echo $select; die;
        $result = $this->getAdapter()->fetchAll($select);


        if ($result) {
            return $result;
        }
    }

    public function getReferStudents() {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('u' => 'users'))
                ->joinLeft(array('p' => 'payment'), 'p.user_id = u.user_id', array('p.reffered', 'monthly_user' => 'COUNT(p.user_id)', 'p.subscription_id'))
                ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id=u.refferal_class_id', array('tc.user_id'))
                ->where('u.teacher_refferal=?', 1)
                ->where('p.status=?', 'paid')
                ->where('p.subscription_id=?', 1)
                ->orwhere('p.subscription_id=?', 2)
                ->group('tc.user_id');
        $result = $this->getAdapter()->fetchRow($select);
        if ($result) {
            return $result;
        }
    }

    public function getTeachername() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('u' => 'users'), array('u.first_name', 'u.email'))
                    ->joinLeft(array('um' => 'usersmeta'), 'u.user_id=um.user_id', array('um.paypal_email'))
                    ->where('u.user_id=?', $user_id);

            $result = $this->getAdapter()->fetchRow($select);
            if ($result) {
                return $result;
            }
        }
    }

    //abhishekm
    public function updatecommission() {
        $data = array('commission_with_sat' => (Double) func_get_arg(0));
        $where = func_get_arg(1);
        //die( $where );
        $update = $this->update($data, 'user_id =' . $where);

        if (isset($update)) {
            return $update;
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    //abhishekm
    public function getcommission() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this)
                        ->where('user_id = ?', $user_id);

                $result = $this->getAdapter()->fetchRow($select);
            } catch (Exception $e) {
                throw new Exception($e);
            }

            if ($result) {
                return $result['commission_with_sat'];
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*
      Developer: Namrata Singh
      Date : 18 march'15
      Desc: Selects all the premiumstatus and fb_id all all user if its there
     */

    public function selectPremiumStatus() {
        if (func_num_args() > 0) {
            $userId = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from($this, array('premium_status', 'fb_id'))
                        ->where('user_id = ?', $userId);

                $result = $this->getAdapter()->fetchRow($select);

                if ($result) {
                    return $result;
                }
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            //  echo "<pre>"; print_r($result); die('----');
        }
    }

    /*
      Developer: Namrata Singh
      Date : 18 march'15
      Desc: updating the Premium Status of users when they cancel membership
     */

    public function updatePremiumStatus() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where = func_get_arg(1);
            // echo "<pre>"; print_r($where); die('45444');

            $update = $this->update($data, 'user_id =' . $where);
//       echo $update; die('update model');

            return $update;
        }
    }

    public function deleteUser() {
        if (func_num_args() > 0) {
            $uid = func_get_arg(0);
            try {

                $sql = 'DELETE users, usersmeta,userinvites,teachingclassvideo,teachingclassunit,teachingclassfile,teachingclasses,referfriends,projects,projectlikes,projectcomments,projectcommentlikes,notification,myclasses,discussionlikes,discussioncomments,discussioncommentlikes,classfiles,classesreview,classenroll,classdiscussions,certificate,user_video_status
 FROM users
 LEFT JOIN  usersmeta ON users.user_id = usersmeta.user_id
 LEFT JOIN  userinvites ON users.user_id = userinvites.user_id
 LEFT JOIN  teachingclassvideo ON users.user_id = teachingclassvideo.user_id
 LEFT JOIN  teachingclassunit ON users.user_id = teachingclassunit.user_id
 LEFT JOIN  teachingclassfile ON users.user_id = teachingclassfile.user_id
 LEFT JOIN  teachingclasses ON users.user_id = teachingclasses.user_id
 LEFT JOIN  referfriends ON users.user_id = referfriends.user_id
 LEFT JOIN  projects ON users.user_id = projects.user_id
 LEFT JOIN  projectlikes ON users.user_id = projectlikes.user_id
 LEFT JOIN  projectcomments ON users.user_id = projectcomments.user_id
 LEFT JOIN  projectcommentlikes ON users.user_id = projectcommentlikes.user_id
 LEFT JOIN  notification ON users.user_id = notification.user_id
 LEFT JOIN  myclasses ON users.user_id = myclasses.user_id
 LEFT JOIN  discussionlikes ON users.user_id = discussionlikes.user_id
 LEFT JOIN  discussioncomments ON users.user_id = discussioncomments.user_id
 LEFT JOIN  discussioncommentlikes ON users.user_id = discussioncommentlikes.user_id
 LEFT JOIN  classfiles ON users.user_id = classfiles.user_id
 LEFT JOIN  classesreview ON users.user_id = classesreview.user_id
 LEFT JOIN  classenroll ON users.user_id = classenroll.user_id
 LEFT JOIN  classdiscussions ON users.user_id = classdiscussions.user_id
 LEFT JOIN  certificate ON users.user_id = certificate.user_id
 LEFT JOIN  user_video_status ON users.user_id = user_video_status.user_id
 
 WHERE users.user_id = ' . $uid . '';

                $responseid = $this->getAdapter()->query($sql);
            } catch (Exception $e) {
                throw new Exception($e);
            }
            return $uid;
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*
      Developer:Rakesh Jha
      Desc:Connect with Social media facebook,tweeter
      Created :1/04/2015
     */

    public function insertSocialDetails() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where = func_get_arg(1);

//            print_r($data);
//            die;
            $update = $this->update($data, 'user_id =' . $where);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*
      Developer:Rakesh Jha
      Desc:Update If user is Disconnected
      Created :1/04/2015
     */

    public function fbSocialStatus() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where = func_get_arg(1);
//            print_r($data);
//            die;
            $update = $this->update($data, 'user_id =' . $where);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*
      Developer:Rakesh Jha
      Desc:Get Social media status
      Created :1/04/2015
     */

    public function getFbConnectedStatus() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this)
                        ->where('user_id = ?', $user_id);

                $result = $this->getAdapter()->fetchRow($select);
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

    //dev:priyanka varanasi 
    //desc: to  fetch row based on fb id
    public function getEmailBasedOnFBId() {

        if (func_num_args() > 0) {
            $fb_id = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this)
                        ->where('fb_id = ?', $fb_id);

                $result = $this->getAdapter()->fetchRow($select);
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

    public function getsocialaccountids() {

        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $select = $this->select()
                    ->from($this)
                    ->where('user_id = ?', $userid);
            $result = $this->getAdapter()->fetchRow($select);
            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
     /*
      Developer:Abhishek M
      Desc:Update set teacher status 1
     */

    public function maketeacher() {

        if (func_num_args() > 0) {
            $where = func_get_arg(0);
            $data["teacher_status"]=1;
            $update = $this->update($data, "user_id =$where");

            if ($update) {
                return $update;
            } 
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

}

?>