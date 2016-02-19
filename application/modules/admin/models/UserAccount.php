<?php

class Admin_Model_UserAccount extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'user_account';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_UserAccount();
        return self::$_instance;
    }

    /**
     * Developer : Bhojraj Rawte
     * Date : 08/03/2014
     * Description : Get All User Details
     */
    public function getUserAccountsDeatils() {
        $select = $this->select()
                ->from(array('uc' => 'user_account'))
                ->setIntegrityCheck(false)
                ->joinLeft(array('u' => 'users'), 'u.user_id = uc.user_id', array("u.user_name"));

        $result = $this->getAdapter()->fetchAll($select);
        if ($result) :
            return $result;
        endif;
    }

   
    
}