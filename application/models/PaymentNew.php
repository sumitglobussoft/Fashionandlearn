<?php
//DEV: priyanka varanasi
//DESC: PaymentNew modal created
//DATE: 21/8/2015
class Application_Model_PaymentNew extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'payment_new';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_PaymentNew();
        return self::$_instance;
    }
/*
 * dev:priyanka varanasi
 * desc: to get all payment details
 * date : 21/8/2015 
 */
    public function getAllPaymentDetails() {

        try {
            $select = $this->select()
                    ->from($this);
          $result = $this->getAdapter()->fetchAll($select);
           
        } catch (Exception $exc) {
            throw new Exception('Unable to update, exception occured' . $exc);
        }
       
        if ($result) {
            return $result;
        }
    }
    /*
 * dev:priyanka varanasi
 * desc: to get  payment details of a session 
 * date : 22/8/2015 
 */
    
    public function getUserPaymentInfo(){
        
            if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                        ->where('np.user_id =?', $user_id);
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
    
 public function updateUserPaymentInfo(){
          if(func_num_args()>0){
            $userid = func_get_arg(0);
            $data = func_get_arg(1);
            
            try {
               
                $result = $this->update($data,'user_id = '. $userid);
              
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
 
 
 /* Developer:priyanka varanasi
      Desc : inserting userid 
      dated : 25/8/2014
  */

    public function insertUserPaymentInfo() {

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
    
 /* Developer:priyanka varanasi
      Desc : get all trail users
      dated : 25/8/2014
  */
   public function  getAllTrailUsers(){
       
        if (func_num_args() > 0) {
            $currentdate= func_get_arg(0);
            $warningdate = func_get_arg(1);
            try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('c' => 'payment_cards'),'c.user_id = np.user_id')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                         ->where('np.trail_end >= ?',$warningdate)
                         ->where('np.trail_end < ?',$currentdate)
                         ->where('np.customer_status = ?',2)
                         ->where('np.payment_type = ?',1)
                         ->where('c.primary = ?',1);
            
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
   
      
    /* Developer:priyanka varanasi
      Desc : get all trail users for boleto user
      dated : 28/8/2014
  */
   public function  getAllTrailBoletoUsers(){
       
        if (func_num_args() > 0) {
            $currentdate= func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                         ->where('np.trail_end < ?',$currentdate)
                         ->where('np.customer_status = ?',2)
                         ->where('np.payment_type = ?',2);
            
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
   
   
    /* Developer:priyanka varanasi
      Desc : get all subscribed users through card
      dated : 31/8/2014
  */
   public function  getAllSubscribedUsers(){
       
        if (func_num_args() > 0) {
            $currentdate= func_get_arg(0);
             $warningdate = func_get_arg(1);
      
             try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('c' => 'payment_cards'),'c.user_id = np.user_id')
                        ->joinLeft(array('pl' => 'plans'),'np.plan_type = pl.plan_type_id')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                         ->where('np.subscription_end >= ?',$warningdate)
                         ->where('np.subscription_end < ?',$currentdate)
                         ->where('np.customer_status = ?',3)
                         ->where('np.payment_type = ?',1)
                         ->where('c.primary = ?',1);
            
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
   
   
   
   
    /* Developer:priyanka varanasi
      Desc : get all warning trail users
      dated : 27/8/2014
  */
   public function  getAllWarnTrailUsers(){
       
        if (func_num_args() > 0) {
            $currentdate= func_get_arg(0);
            $warningdate = func_get_arg(1);
            try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('c' => 'payment_cards'),'c.user_id = np.user_id')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                        ->where('np.trail_end >?',$warningdate)
                       ->where('np.customer_status = ?',2);
                       
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

   
     /* Developer:priyanka varanasi
      Desc : get all subscribed users for boleto user
      dated : 31/8/2014
  */
   public function  getAllSubscribedBoletoUsers(){
       
        if (func_num_args() > 0) {
            $alertdate= func_get_arg(0);
           
           
            try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('pl' => 'plans'),'np.plan_type = pl.plan_type_id')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                         //->where('np.subscription_end >= ?',$warningdate)
                         ->where('np.subscription_end = ?',$alertdate)
                         ->where('np.customer_status = ?',3)
                         ->where('np.payment_type = ?',2);
                         
            
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
   /* Developer:priyanka varanasi
      Desc : get all warning trail users for boleto
      dated : 2/9/2014
  */
   public function  getAllWarnTrailUsersForBoleto(){
       
        if (func_num_args() > 0) {
            $warningdate = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                        ->where('trail_end <?',$warningdate)
                        ->where('np.customer_status = ?',4);
                       
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
   
    /* Developer:priyanka varanasi
      Desc : get logged user payment details including plan , active card
      dated : 3/9/2014
  */
   
    public function getUserTransPayDetails(){
        
             if (func_num_args() > 0) {
            $userid =  func_get_arg(0);
            
            try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('c' => 'payment_cards'),'c.user_id = np.user_id')
                        ->joinLeft(array('pl' => 'plans'),'pl.plan_type_id = np.plan_type')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                        ->where('c.primary = ?',1)
                        ->where('np.user_id = ?',$userid);
                       
            
                 $result = $this->getAdapter()->fetchRow($select);
                
         if ($result){
                    return $result;
                } else{
                              $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('c' => 'payment_cards'),'c.user_id = np.user_id')
                        ->joinLeft(array('pl' => 'plans'),'pl.plan_type_id = np.plan_type')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                        ->where('np.user_id = ?',$userid);
                       
            
                 $result = $this->getAdapter()->fetchRow($select);
                
         if ($result){
                    return $result;
                } 
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            throw new Exception("Argument not passed");
        }   
        
    }
     /* Developer:priyanka varanasi
      Desc : to cancel subscription of subscribed users by updating the autopayment to 0;
      dated : 7/9/2014
  */
    public function cancelThisSubscription(){
        
            if(func_num_args()>0){
            $userid = func_get_arg(0);
            $data = func_get_arg(1);
            try {
               
                $result = $this->update($data,'user_id =' . $userid);
              
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
   
    
        /* Developer:priyanka varanasi
      Desc : get all subscribed users through card
      dated : 31/8/2014
  */
   public function  getFreemiumEndUsers(){
        $currentDate = date('Y-m-d');
      
             try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('c' => 'payment_cards'),'c.user_id = np.user_id')
                        ->joinLeft(array('pl' => 'plans'),'np.plan_type = pl.plan_type_id')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                        ->where('np.subscription_end < ?',$currentDate)
                        ->where('np.customer_status = ?',9);
                 $result = $this->getAdapter()->fetchAll($select);
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
            
      
   }
   
      
   /* Developer:priyanka varanasi
      Desc : get all trail  users exceeded the trail end date
      dated : 15/9/2014
  */
    public function getAllCreditCardTrialWarnUsers(){
       
          if (func_num_args() > 0) {
            $warningdate = func_get_arg(0);
        
         try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('c' => 'payment_cards'),'c.user_id = np.user_id')
                        ->joinLeft(array('pl' => 'plans'),'np.plan_type = pl.plan_type_id')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                        ->where('np.trail_end <=?',$warningdate)
                        ->where('np.customer_status = ?',2)
                        ->where('np.payment_type= ?',1)
                        ->where('c.primary= ?',1);
                 $result = $this->getAdapter()->fetchAll($select);
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
          } else{
            throw new Exception("Argument not passed");
        }
        
    }
    /* Developer:priyanka varanasi
      Desc : get all subscribed  users exceeded the subscription end date
      dated : 15/9/2014
  */
    
       public function getAllCreditCardSubscribedWarnUsers(){
       
          if (func_num_args() > 0) {
            $warningdate = func_get_arg(0);
        
         try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('c' => 'payment_cards'),'c.user_id = np.user_id')
                        ->joinLeft(array('pl' => 'plans'),'np.plan_type = pl.plan_type_id')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                        ->where('np.subscription_end <=?',$warningdate)
                        ->where('np.customer_status = ?',3)
                        ->where('np.payment_type= ?',1)
                         ->where('c.primary= ?',1);
                 $result = $this->getAdapter()->fetchAll($select);
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
          } else{
            throw new Exception("Argument not passed");
        }
        
    }
    
    
   
    public function getAllWarnTrialUsers(){
        if (func_num_args() > 0) {
            $warningdate = func_get_arg(0);
        
              try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('c' => 'payment_cards'),'c.user_id = np.user_id')
                        ->joinLeft(array('pl' => 'plans'),'np.plan_type = pl.plan_type_id')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                        ->where('np.customer_status = ?',2)
                        ->where('np.payment_type =?',1)
                        ->where('np.trail_end < ?',$warningdate);
                $result = $this->getAdapter()->fetchAll($select);
              
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            } 
            } else{
            throw new Exception("Argument not passed");
        }
        
        
    }
    
    
    public function getAllWarnSubscribeUsers(){
         if (func_num_args() > 0) {
            $warningdate = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('cp' => 'coupons'),'cp.coupon_code = np.couponcode')
                        ->joinLeft(array('c' => 'payment_cards'),'c.user_id = np.user_id')
                        ->joinLeft(array('pl' => 'plans'),'np.plan_type = pl.plan_type_id')
                        ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                        ->where('np.customer_status = ?',3)
                        ->where('np.payment_type =?',1)
                        ->where('np.subscription_end < ?',$warningdate);
                 $result = $this->getAdapter()->fetchAll($select);
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
               } else{
            throw new Exception("Argument not passed");
        }
        
        
    }
/*
 * dev:priyanka varanasi
 * desc: to get all payment details of premium users
 * date : 14/10/2015 
 */
    public function getAllPaymentDetailsOFPremiumUsers() {

        try {
            $select = $this->select()
                     ->from($this)
                     ->where('customer_status=?',3);
          $result = $this->getAdapter()->fetchAll($select);
           
        } catch (Exception $exc) {
            throw new Exception('Unable to update, exception occured' . $exc);
        }
       
        if ($result) {
            return $result;
        }
    }
    
}

?>