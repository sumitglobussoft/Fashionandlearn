<?php

class Admin_Model_UserTransactions extends Zend_Db_Table_Abstract {
    
    private static $_instance = null;
    protected $_name = 'user_transactions';
    
    private function  __clone() { } //Prevent any copy of this object
	
    public static function getInstance(){
		if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
		self::$_instance = new Admin_Model_UserTransactions();
		return self::$_instance;
    }
  
    
     //Developer : priyanka varanasi
     // Date : 20/11/2014
     // Description : Get transaction Details
    
    public function gettransdetails() {         
          $select = $this->select()
                           ->from(array('ut' => 'user_transactions'))
                           ->setIntegrityCheck(false)
                           ->join(array('u' => 'users'),'u.user_id = ut.user_id',array("u.user_name"))                           
                           ->order('ut.transaction_id DESC');
          
            $result = $this->getAdapter()->fetchAll($select);                        
            if ($result) {
                return $result;
            }
    }     

}