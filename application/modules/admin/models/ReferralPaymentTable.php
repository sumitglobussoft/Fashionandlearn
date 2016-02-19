<?php
//DEV: priyanka varanasi
//DESC: Referal Payment Table  modal created
//DATE: 13/10/2015
class Admin_Model_ReferralPaymentTable extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'referral_payment_table';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_ReferralPaymentTable();
        return self::$_instance;
    }


/*
 * dev:priyanka varanasi
 * desc: to teacher row by user id
 * date : 14/10/2015 
 */
    public function getReferals(){
      
            try {
                $select = $this->select()
                          ->from(array('rft' =>'referral_payment_table'))
                          ->setIntegrityCheck(false)
                          ->joinLeft(array('us' => 'users'),'rft.user_id = us.user_id',array('us.email','us.first_name','us.last_name')); 
              
                $result = $this->getAdapter()->fetchAll($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
       
        
    }
    
    
    /*
 * dev:priyanka varanasi
 * desc: to teacher row by user id
 * date : 14/10/2015 
 */
    public function getReferalsByYearAndMonth(){
       
        if (func_num_args() > 0) {
            $yr = func_get_arg(0);
             $month = func_get_arg(1);
            try {
                $select = $this->select()
                          ->from(array('rft' =>'referral_payment_table'))
                          ->setIntegrityCheck(false)
                          ->joinLeft(array('us' => 'users'),'rft.user_id = us.user_id',array('us.email','us.first_name','us.last_name')) 
                          ->where('Month(payment_date)=?',$month)
                          ->where('Year(payment_date)=?',$yr);
             
                $result = $this->getAdapter()->fetchAll($select);
               
         if ($result){
                    return $result;
                    }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }else{
              throw new Exception('Argument Not Passed');
        }
        
    }

    /*
 * dev:priyanka varanasi
 * desc: get teacher rows by user ids
 * date : 14/10/2015 
 */
    public function getReferalsByIds(){
      if(func_num_args()>0){
         $ref_ids = func_get_arg(0);
      
            try {
                $select = $this->select()
                          ->from(array('rft' =>'referral_payment_table'))
                          ->setIntegrityCheck(false)
                          ->joinLeft(array('us' => 'users'),'rft.user_id = us.user_id',array('us.email','us.first_name','us.last_name'))
                          ->joinLeft(array('pb' => 'pagarbank'),'rft.user_id = pb.user_id') 
                          ->where('ref_id IN ('.$ref_ids.')');
                $result = $this->getAdapter()->fetchAll($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
      }else{
          
          throw new Exception('Argument not passed');
      }
       
        
    }
    
    /*
 * dev:priyanka varanasi
 * desc: to update referral payment info 
 * date : 15/10/2015 
 */
    public function updateReferralPaymentInfo(){
             if(func_num_args()>0){
            $ref_id = func_get_arg(0);
            $data = func_get_arg(1);
            
            try {
               
                $result = $this->update($data,'ref_id = '. $ref_id);
              
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
    
    
    public function selectAllPaidUsers(){
      try {
                $select = $this->select()
                        ->from(array('rft'=>'referral_payment_table'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('us' => 'users'),'us.user_id = rft.user_id',array('us.email','us.first_name','us.last_name'))
                        ->where('rft.pay_status!=1');
                   $result = $this->getAdapter()->fetchAll($select);
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }   
      
    }
    
    public function getReferralYearsNames(){
        
      
         try { 
            $select = $this->select()
      
                        ->from($this,array("years"=>"Year(payment_date)"))
                         ->group('Year(payment_date)')
                         ->order('payment_date ASC');
        $res = $this->getAdapter()->fetchAll($select);
        
     
                 if ($res){
                    return $res;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }     
        
    }
    
    
            /*
 * dev:priyanka varanasi
 * desc: to  list of month related to that year
 * date : 17/10/2015 
 */
 
        public function getListOfMonthsByYearForReferrals(){
      
         if (func_num_args() > 0) {
            $year = func_get_arg(0);
           
       try {
            $select = $this->select()
                     ->from($this,array('month'=>'Month(payment_date)'))
                           ->order('payment_date ASC')
                           ->where('Year(payment_date)=?',$year)
                           ->distinct('Month(payment_date)');
                $result = $this->getAdapter()->fetchAll($select);
          if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
            
             }else {
            
            throw new Exception('Argument Not Passed');
        }
        } 
    
}

?>