<?php

class Application_Model_ClassDiscussions extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'classdiscussions';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_ClassDiscussions();
        return self::$_instance;
    }

 
    public function insertDiscussions() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
        
            try {
                $responseId = $this->insert($data);
                 if ($responseId) {
                return $responseId;
            }
            } catch (Exception $e) {
                print $e->getMessage();
            }
           
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getDiscussion() {

        if (func_num_args() > 0) {

            $discussionid = func_get_arg(0);
            $userid = func_get_arg(1);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('l' => 'classdiscussions'))
                        ->join(array('ul' => 'users'), 'ul.user_id = l.user_id', array('ul.first_name', 'ul.last_name'))
                        ->join(array('um' => 'usersmeta'), 'ul.user_id = um.user_id', array('um.user_profile_pic', 'um.user_headline'))
                        ->joinLeft(array('f' => 'followers'), 'l.user_id = f.following_user_id and ' . $userid . ' = f.follower_user_id and f.follow_status=0', array('f.follow_status'))
                        ->where('l.discussion_id = ?', $discussionid);

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

    public function getTrendDetail() {

        if (func_num_args() > 0) {

            $classid = func_get_arg(0);

            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('l' => 'classdiscussions'))
                        ->join(array('ul' => 'users'), 'ul.user_id = l.user_id', array('ul.first_name', 'ul.last_name'))
                        ->where('l.class_id = ?', $classid);

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

    public function getRecentDetail() {
        if (func_num_args() > 0) {
            // $userid = func_get_arg(0);
            $classid = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'classdiscussions'))
//                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title'))
                    ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
//                    ->where('l.user_id = ?',$userid)
                    ->where('l.class_id = ?', $classid)
                    ->order('l.discussed_date DESC');
            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
                return $result;
            }
        }
    }

   

  
   
    
    /* Developer:Rakesh Jha
      Desc :Get the count of discussion on a class
     */
    public  function getdiscussionCount(){
        if (func_num_args() > 0) {
            $class_id=  func_get_arg(0);
            $select = $this->select()
                      ->where('class_id=?',$class_id)  ;
        }
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return count($result);
        }
    }
    
   /* Developer:pradeep D
      Desc :get user discusion
     */ 
    
 public function getuserdiscussionDetail() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'classdiscussions'))
//                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title'))
                    ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
//                    ->where('l.user_id = ?',$userid)
                    ->where('l.user_id = ?', $userid)
                    ->order('l.discussed_date DESC');
            try {
            $result = $this->getAdapter()->fetchAll($select);
            }catch(Exception $e)
            {
                die($e);
            }
            if ($result) {
                return $result;
            }
        }
    }
    
    
    /*
     * Abhishekm
     * 
     * 
     */
    
    
     public function getdiscussionbyid() {

        if (func_num_args() > 0) {

            $discussionid = func_get_arg(0);
          

            try {

                $select = $this->select()
                   
                  
                        ->where('discussion_id = ?', $discussionid);

                $result = $this->getAdapter()->fetchRow($select);
            } catch (Exception $e) {
               
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