<?php

/*
 * Developer : Ankit Singh
 * Date : 30/12/2014
 */

class Application_Model_Subscription extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'subscription';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Subscription();
        return self::$_instance;
    }

    public function selectSubscription() {
         
        $select = $this->select();
                 
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }

     public function recurringSubscription($membership) {
        
        $select = $this->select()
                 ->where('subscription_type = ?',$membership);

        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }
	/* Dev: Namrata Singh
	   Desc: to select the plans which have trial period 
	*/
	
     public function selectTrialSubscription() {
         $val = 1;
        $select = $this->select()
		         ->where('description = ?', $val);
                 
        $result = $this->getAdapter()->fetchAll($select);
		
        if ($result) {
            return $result;
        }
    }	
    /* Dev: Namrata Singh
	   Desc: to select the plans which don't have trial period 
	*/
	
     public function selectNonTrialSubscription() {
         $val = 0;
        $select = $this->select()
		          ->where('description = ?', $val);
                 
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }	
}
