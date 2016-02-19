<?php

class Admin_Model_UsersMeta extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'usersmeta';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_UsersMeta();
        return self::$_instance;
    }

    /* Developer:Namrata Singh
      Desc : inserting data's in the UserMeta table
     */

    public function insertUsermeta($data) {

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

    /* Developer:Namrata Singh
      Desc : updating the usermeta table when user make any changes
     */

    public function editUsermeta() {


        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where = func_get_arg(1);
            try {
                $responseId = $this->update($data, 'user_id =' . $where);
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
          /* Developer:priyanka varanasi
       Desc : get image based on user id
    */
    public function getuserprofilepic(){
        if (func_num_args() > 0):
             $userid = func_get_arg(0);
         try {
              $select = $this->select()
                ->from(array('um' => 'usersmeta'))
                      ->where('um.user_id=?',$userid);
                 
        $result = $this->getAdapter()->fetchRow($select);
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


   public function updatePaymentUsermeta() {


        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
           $data=array("teacher_payment_status"=>0);
            try {
                $responseId = $this->update($data, 'user_id =' .$userid );
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
}