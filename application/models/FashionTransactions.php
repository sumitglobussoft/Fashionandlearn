<?php

//DEV:priyanka varanasi
//DESC: Modal Design
//Date : 26/8/2015

class Application_Model_FashionTransactions extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'fashion_transactions';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_FashionTransactions();
        return self::$_instance;
    }
    
       /* Developer:varanas priyanka 
          Desc : Inserting transaction data in table
        * dated:26/8/2015
       */

    public function insertUserTransactionsInfo() {

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
    
    
  /*
 * dev:priyanka varanasi
 * desc: to update the transaction details 
 * date : 27/8/2015 
 */
    
 public function updateUserTransInfo(){
          if(func_num_args()>0){
            $transid = func_get_arg(0);
            $data = func_get_arg(1);
       
            try {
                $result = $this->update($data, 'transaction_id =' . $transid);
                if ($result) {
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }else{
            throw new Exception("Argument not passed");
        } 
  
     
 }
 
   /*
 * dev:priyanka varanasi
 * desc: to get all transaction details of user
 * date : 2/9/2015 
 */
 public function getUserPayTransactionDetails(){
     
          if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from($this)
                        ->where('user_id =?', $user_id)
                        ->order('transaction_date DESC');
                $result = $this->getAdapter()->fetchAll($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            throw new Exception("Argument not passed");
        }  
     
     
 }
 
 
 
   /*
 * dev:priyanka varanasi
 * desc: to user particular transaction id
 * date : 14/9/2015 
 */
 public function getTransInfoById(){
     
          if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
             $transid = func_get_arg(1);
             
            try {
                $select = $this->select()
                        ->from($this)
                        ->where('user_id =?', $user_id)
                        ->where('transaction_id =?', $transid);
                        
                $result = $this->getAdapter()->fetchRow($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            throw new Exception("Argument not passed");
        }  
     
     
 }
 
}
?>