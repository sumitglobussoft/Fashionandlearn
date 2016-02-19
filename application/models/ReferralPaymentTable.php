<?php
//DEV: priyanka varanasi
//DESC: Referal Payment Table  modal created
//DATE: 13/10/2015
class Application_Model_ReferralPaymentTable extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'referral_payment_table';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_ReferralPaymentTable();
        return self::$_instance;
    }


/*
 * dev:priyanka varanasi
 * desc: to teacher row by user id
 * date : 14/10/2015 
 */
    public function getReferalRowByRefferredId(){
         if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from($this)
                        ->where('user_id =?', $user_id)
                        ->where('Month(payment_date) =?',date('m'));
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
 
    
    
  /*
 * dev:priyanka varanasi
 * desc: to update  payment details of a session 
 * date : 22/8/2015 
 */
    
 public function updateReferralPaymentInfo(){
          if(func_num_args()>0){
            $userid = func_get_arg(0);
            $refid = func_get_arg(1);
            $data = func_get_arg(2);
            
            $dat = array( 'students_monthly' => new Zend_Db_Expr('students_monthly + '.$data['students_monthly']),
                          'students_annually' => new Zend_Db_Expr('students_annually + '.$data['students_annually']),
                          'amount_monthly' => new Zend_Db_Expr('amount_monthly + '.$data['amount_monthly']),
                          'amount_annually' =>  new Zend_Db_Expr('amount_annually + '.$data['amount_annually']),
                          'total_earned' =>  new Zend_Db_Expr('total_earned + '.$data['total_earned']),
                          'pay_status' => 0);
            
      
            try {
          $where =('user_id = '.$userid.' AND ref_id = '.$refid.'');
           $result = $this->update($dat,$where);
              
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
 * desc: to insrert  referral details if the row is not existed for current month
 * date : 14/10/2015
 */

    public function insertReferralPaymentInfo() {

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


    
}

?>