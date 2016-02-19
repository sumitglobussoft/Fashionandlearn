<?php

class Application_Model_ReferFriends extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'referfriends';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_ReferFriends();
        return self::$_instance;
    }

/* Developer:Namrata Singh
   Desc :inserting the data in the table when a user refer any of his friend
*/
    public function insertrefer() {
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
   Desc : selecting the row's of any particular user to check the reffered person's
*/
    public function selectrefer() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this, array("num"=>"COUNT(*)"))
                        ->where('user_id = ?', $userid);

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
	/* Developer:Namrata Singh
   Desc : selecting the row's of any particular user to check the reffered person's
*/
    public function selectReferredEmail() {

        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this, array('email'))
                        ->where('user_id = ?', $userid);

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
