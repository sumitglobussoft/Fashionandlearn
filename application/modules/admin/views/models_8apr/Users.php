<?php

class Admin_Model_Users extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'users';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Users();
        return self::$_instance;
    }

    /**
     * Developer : priyanka varanasi
     * Date : 30/1/2015
     * Description : Get All User Details
     */
    public function getUsersDetails() {
        $select = $this->select()
                ->from(array('u' => 'users'))
                ->setIntegrityCheck(false);
        $result = $this->getAdapter()->fetchAll($select);
       if ($result){
            return $result;
        
       }    
    }

 
   //dev:priyanka varanasi
    //desc:deactivation of the user
    //date:30/1/2015
    public function getstatustodeactivate(){
          if (func_num_args() > 0):
            $userid = func_get_arg(0);
            try {
                $data = array('status' => new Zend_DB_Expr('IF(status=1, 0, 1)'));
                $result = $this->update($data, 'user_id = "' . $userid . '"');
            } catch (Exception $e) {
                throw new Exception($e);
            }
            if ($result):
                return $result;
            else:
                return 0;
            endif;
        else:
            throw new Exception('Argument Not Passed');
        endif;
        
    }

    /**
     * Developer : priyanka varanasi
     * date:31/1/2015
      * Description : User Delete
     */
    public function userdelete() {
        if (func_num_args() > 0):
            $uid = func_get_arg(0);
            try {
                $db = Zend_Db_Table::getDefaultAdapter();
                $where = (array('user_id = ?' => $uid));
                $db->delete('users', $where);
            } catch (Exception $e) {
                throw new Exception($e);
            }
            return $uid;
        else:
            throw new Exception('Argument Not Passed');
        endif;
    }
    //dev:priyanka varanasi
    //desc: TO insert admin created users
        public function insertUser() {
        
        if(func_num_args() > 0){
            $data = func_get_arg(0);
          try{
                $responseId = $this->insert($data);
            }catch(Exception $e){             
                 return $e->getMessage(); 
            } 
          if($responseId){
                return $responseId;
            }
        }else{
            throw new Exception('Argument Not Passed');
        }
  }

      //dev:priyanka varanasi
    //desc: TO usersdetails by id
  
    public function getUsersDeatilsByID() {
        if (func_num_args() > 0):
            $uid = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('u' => 'users',array('u.user_id','u.first_name','u.last_name','u.password',)))
                        ->setIntegrityCheck(false)
                        ->joinleft(array('um'=>'usersmeta'),'um.user_id = u.user_id',array('user_profile_pic'))
                        ->where('u.user_id =?', $uid);

                $result = $this->getAdapter()->fetchRow($select);
                if ($result) :
                    return $result;
                endif;
            } catch (Exception $e) {
                throw new Exception($e);
            }
        else :
            throw new Exception('Argument Not Passed');
        endif;
    }

 
      //dev:priyanka varanasi
    //desc: TO update user details by id
    
    public function updateUserDetails() {

        if (func_num_args() > 0):
            $uid = func_get_arg(0);
            $userdata = func_get_arg(1); 
            try {
                $result = $this->update($userdata, 'user_id = "' . $uid . '"');
                if ($result) {
                    return $result;
                } else {
                    return 0;
                }
            } catch (Exception $e) {
                throw new Exception($e);
            }
        else :
            throw new Exception('Argument Not Passed');
        endif;
    }  
    
    
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
    
        /**
      Developer: priyankav
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
      Developer: priyankav
     *  Description: To validate email whether exists in db
     * */
    public function validateUserEmail() {

        if (func_num_args() > 0) {
            $userEmail = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this)
                        ->where('email = ?', $userEmail)
                        ->where('role = 2');

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
       /**
      Developer: priyankav
     *  Description:To update the activation link  for reset password
        * 
     * */
        public function updateActivationLink(){
            if(func_num_args()>0){
                $data = func_get_arg(0);
                $userId = func_get_arg(1);
                
                $data = array('activation_code'=>$data);                   
                try {
                    $update = $this->update($data,'user_id =' . $userId);
                } catch (Exception $exc) {
                    throw new Exception('Unable to update, exception occured'.$exc);
                }
                if($update){
                    return $update;
                }
            }else{
                 throw new Exception('Argument not passed');
            }
        }
           /**
      Developer: priyankav
     *  Description:To update the activation link  for reset password
        **/
           public function checkActivationKey(){
            if(func_num_args()>0){
                $userId = func_get_arg(0);
                $key = func_get_arg(1);
                            
                try {
                    $select = $this->select()
                               ->from($this)
                               ->where('user_id = ?',$userId)
                               ->where('activation_code = ?',$key);
                    $result = $this->getAdapter()->fetchRow($select);
                } catch (Exception $exc) {
                    throw new Exception('Unable to update, exception occured'.$exc);
                }
                if($result){
                    return $result;
                }
            }else{
                 throw new Exception('Argument not passed');
            }
        }
        
     public function getUsersByID(){
            if (func_num_args() > 0){
            $uid = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('u' => 'users',array('u.status')))
                        ->setIntegrityCheck(false)
                        ->where('u.user_id =?', $uid);

                $result = $this->getAdapter()->fetchRow($select);
               
                if ($result) :
                    return $result;
                endif;
            } catch (Exception $e) {
                throw new Exception($e);
            }
            }
        else{
            throw new Exception('Argument Not Passed');
        } 
           
     }  
}