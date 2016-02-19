<?php

//DEV:priyanka varanasi
//DESC: Modal Design
//Date : 26/8/2015

class Admin_Model_FashionTransactions extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'fashion_transactions';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_FashionTransactions();
        return self::$_instance;
    }
       /**
     * Developer : priyanka varanasi
     * Date : 26/8/2014
     * Description : get all transaction details in db
     */
     public function getAllFashionTransactionDetails() {         
          $select = $this->select()
                           ->from(array('ft' => 'fashion_transactions'))
                           ->setIntegrityCheck(false)
                           ->joinLeft(array('u' => 'users'),'u.user_id = ft.user_id',array("u.first_name","u.last_name","u.email"))  
                           ->order('ft.transaction_date DESC')
                           ->where('ft.pay_type=?',1);
          
            $result = $this->getAdapter()->fetchAll($select);                        
            if ($result) :
                return $result;
            endif;
    } 
    
   
    
           /**
     * Developer : priyanka varanasi
     * Date : 26/8/2014
     * Description : get all  boleto transaction details in db
     */
     public function getAllBoletoFashionTransactionDetails() {         
          $select = $this->select()
                           ->from(array('ft' => 'fashion_transactions'))
                           ->setIntegrityCheck(false)
                           ->joinLeft(array('u' => 'users'),'u.user_id = ft.user_id',array("u.first_name","u.last_name","u.email"))  
                           ->order('ft.transaction_date DESC')
                           ->where('ft.pay_type=?',2)
                            ->where('ft.status!=?',"canceled");
          
            $result = $this->getAdapter()->fetchAll($select);                        
            if ($result) :
                return $result;
            endif;
    } 
    
    
               /**
     * Developer : priyanka varanasi
     * Date : 26/8/2014
     * Description : get all  boleto transaction details in db
     */
     public function getAllBoletoCanceledTransactionDetails() {         
          $select = $this->select()
                           ->from(array('ft' => 'fashion_transactions'))
                           ->setIntegrityCheck(false)
                           ->joinLeft(array('u' => 'users'),'u.user_id = ft.user_id',array("u.first_name","u.last_name","u.email"))  
                           ->order('ft.transaction_date DESC')
                           ->where('ft.pay_type=?',2)
                           ->where('ft.status=?',"canceled");
          
            $result = $this->getAdapter()->fetchAll($select);                        
            if ($result) :
                return $result;
            endif;
    }
    
    
}
?>