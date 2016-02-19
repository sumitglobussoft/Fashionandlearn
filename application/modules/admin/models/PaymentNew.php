<?php
//DEV: priyanka varanasi
//DESC: PaymentNew modal created
//DATE: 21/8/2015
class Admin_Model_PaymentNew extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'payment_new';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new  Admin_Model_PaymentNew();
        return self::$_instance;
    }

    /*
     * dev: priyanka varanasi
     * desc : TO get all trial users 
     * 
     */
   
   public function getAllTrialUsers(){
     try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                         ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                         ->where('np.customer_status = ?',2);
                  $result = $this->getAdapter()->fetchAll($select);
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        
        
       /////////////////////code in new version ///////////////////////
        
    /*
     * dev: priyanka varanasi
     * desc : TO get count of monthly paid users
     * date :23/9/2015
     */
        
   public function getNoOfMonthlyPaidUsers(){
            try {
                $select = $this->select()
                        ->from('payment_new',array("monthlyusers"=>"COUNT(*)"))
                        ->setIntegrityCheck(false)
                        ->where('customer_status = ?',3)
                        ->where('plan_type = ?',1);
               
                  $result = $this->getAdapter()->fetchRow($select);
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
  
    /*
     * dev: priyanka varanasi
     * desc : TO get count of Yealry paid users
     * date :23/9/2015
     */
   public function getNoOfYearlyPaidUsers(){
       try {
                $select = $this->select()
                        ->from('payment_new',array("yearlyusers"=>"COUNT(*)"))
                        ->setIntegrityCheck(false)
                        ->where('customer_status = ?',3)
                        ->where('plan_type = ?',2);
                  $result = $this->getAdapter()->fetchRow($select);
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        
        
      /*
     * dev: priyanka varanasi
     * desc : TO get sum of amount of monthly paid users
     * date :23/9/2015
     */
  public function getMonthlySumOfAmount(){
      try {
                $select = $this->select()
                        ->from(array('np'=>'payment_new'),array())
                        ->setIntegrityCheck(false)
                        ->join(array('ft' => 'fashion_transactions'),'np.transaction_no = ft.transaction_id',array("monthlysum"=>"SUM(amount)"))     
                        ->where('np.customer_status = ?',3)
                        ->where('np.plan_type = ?',1);
               
                  $result = $this->getAdapter()->fetchRow($select);
                  
                  
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }   
      
      
  }
   /*
     * dev: priyanka varanasi
     * desc : TO get sum of amount of yearly paid users
     * date :23/9/2015
     */
  
    public function getYearlySumOfAmount(){
      
     try {
                $select = $this->select()
                        ->from(array('np'=>'payment_new'),array())
                        ->setIntegrityCheck(false)
                        ->join(array('ft' => 'fashion_transactions'),'np.transaction_no = ft.transaction_id',array("yearlysum"=>"SUM(amount)"))     
                        ->where('np.customer_status = ?',3)
                        ->where('np.plan_type = ?',2);
              
                  $result = $this->getAdapter()->fetchRow($select);
                  
                  
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }   
      
        
      
  }
  
     /*
     * dev: priyanka varanasi
     * desc : TO get total premium members
     * date :23/9/2015
     */
  
    public function getTotalPremiumMembers(){
      
     try {
                $select = $this->select()
                         ->from($this,array("premiumtotal"=>"COUNT(user_id)"))
                         ->where('customer_status = ?',3);
                  $result = $this->getAdapter()->fetchRow($select);
                  
                  
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }   
      
        
      
  }
  
      /*
     * dev: priyanka varanasi
     * desc : TO get total premium members
     * date :23/9/2015
     */
  
    public function getTotalFreeMembers(){
      
     try {
                $select = $this->select()
                         ->from($this,array("freetotal"=>"COUNT(user_id)"))
                         ->where('customer_status = ?',3)
                        ->where('customer_status = ?',2)
                        ->where('customer_status = ?',9);
                  $result = $this->getAdapter()->fetchRow($select);
                  
                  
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }   
      
        
      
  }
  /*
     * dev: priyanka varanasi
     * desc : TO get total premium members
     * date :28/9/2015
     */
  
  public function getPremiumUsers(){
      
          try {
                $select = $this->select()
                        ->from(array('np' => 'payment_new'))
                        ->setIntegrityCheck(false)
                         ->joinLeft(array('u' => 'users'),'u.user_id = np.user_id')
                         ->where('np.customer_status = ?',3);
                  $result = $this->getAdapter()->fetchAll($select);
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } 
      

}

?>