<?php
//DEV: priyanka varanasi
//DESC: Teacher Payment modal created
//DATE: 29/9/2015
class Admin_Model_TeacherPayment extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teacher_payment';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_TeacherPayment();
        return self::$_instance;
    }

 
 
 /* Developer:priyanka varanasi
      Desc : inserting userid 
      dated : 29/9/2015
  */

    public function insertPaymentValuesInTOTable() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
          foreach ($data as $value) {
             try {
                $select = $this->select()
                        ->from($this)
                         ->where('MONTH(pay_date) = ?', date('m'))
                         ->where('user_id=?',$value['user_id']);
               $result = $this->getAdapter()->fetchRow($select);
              
        if ($result){
              $res = $this->update($value,'pay_id = '. $result['pay_id']);
             }else{
              $responseId = $this->insert($value);
             }
               } catch (Exception $e) {
                print $e->getMessage();
            }
        } 
           } else {
            
            throw new Exception('Argument Not Passed');
        }
        
    }
    
      /*
     * dev: priyanka varanasi
     * desc : TO fetch all rows 
     * date :29/9/2015
     */
  
    public function selectAllTeachersToBePaid(){
      
     try {
                $select = $this->select()
                        ->from(array('tp'=>'teacher_payment'))
                        ->setIntegrityCheck(false)
                        ->join(array('us' => 'users'),'us.user_id = tp.user_id',array('us.email','us.first_name','us.last_name'));   
                   $result = $this->getAdapter()->fetchAll($select);
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }   
      
        
      
  }
      /*
     * dev: priyanka varanasi
     * desc : TO fetch all rows 
     * date :29/9/2015
     */
  
    public function selectTeachersTOBePaidByDate(){
      
        if (func_num_args() > 0) {
            $month = func_get_arg(0);
            $year = func_get_arg(1);
        
     try {
                $select = $this->select()
                        ->from(array('tp'=>'teacher_payment'))
                        ->setIntegrityCheck(false)
                        ->join(array('us' => 'users'),'us.user_id = tp.user_id',array('us.email','us.first_name','us.last_name'))
                        ->where('MONTH(pay_date)=?',$month)
                        ->where('YEAR(pay_date)=?',$year);
               
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
    
    
    
         /*
     * dev: priyanka varanasi
     * desc : TO fetch all data for all given users 
     * date :3/10/2015
     */
  
    public function selectTeachersTOBePaidByUserid(){
      
        if (func_num_args() > 0) {
            $userspayids = func_get_arg(0);
          
  
      try {
                $select = $this->select()
                        ->from(array('tp'=>'teacher_payment'))
                        ->setIntegrityCheck(false)
                        ->joinLeft(array('us' => 'users'),'tp.user_id = us.user_id',array('us.email','us.first_name','us.last_name'))
                        ->joinLeft(array('pb' => 'pagarbank'),'tp.user_id = pb.user_id')
                       ->where("tp.pay_id IN ($userspayids)");
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
        
        /*
 * dev:priyanka varanasi
 * desc: to update  payment details 
 * date : 3/10/2015 
 */
    
 public function updateTeacherPaymentInfo(){
          if(func_num_args()>0){
            $pay_id = func_get_arg(0);
            $data = func_get_arg(1);
            
            try {
               
                $result = $this->update($data,'pay_id = '. $pay_id);
              
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
     * dev: priyanka varanasi
     * desc : TO fetch all teachers whose status is unpaid and pending
     * date :5/10/2015
     */
  
    public function selectAllStatusUpdateTeachers(){
      
     try {
                $select = $this->select()
                        ->from(array('tp'=>'teacher_payment'))
                        ->setIntegrityCheck(false)
                        ->join(array('us' => 'users'),'us.user_id = tp.user_id',array('us.email','us.first_name','us.last_name'))
                        ->where('tp.payment_status!=1');
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
 * desc: to get  last month statistics 
 * date : 10/5/2015 
 */
    
    public function getLastMonthCurrentBal(){
            if(func_num_args()>0){
            $user_id = func_get_arg(0);
           
        try {
                $select = $this->select()
                        ->from($this)
                         ->where('MONTH(pay_date) = ?', date('n', strtotime('last month')))
                        ->where('user_id = ?',$user_id);
               $result = $this->getAdapter()->fetchRow($select);
      
                if ($result){
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
 * desc: to get already divided sum this month
 * date : 9/10/2015 
 */
        
     public function getsalaryPaidTeachersMonhtly(){
       
         try {
                $select = $this->select()
                        ->from($this,array("withsatisfaction"=>"SUM(with_satisfaction)","leftover"=>"SUM(leftover)",'pay_date'))
                        ->where('year(pay_date)=?',date('Y'))
                         ->where("payment_status IN (1,4,6)")
                        ->group('Month(pay_date)');
                $result = $this->getAdapter()->fetchAll($select);
          if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        
         
     }
     
      /*
 * dev: Priyanka Varanasi
 * desc: to get already divided sum this month
 * date : 9/10/2015 
 */
        
     public function getsalaryPaidTeachersMonhtlyByYear(){
            if(func_num_args()>0){
            $year = func_get_arg(0);
         try {
                $select = $this->select()
                        ->from($this,array('pay_date',"withsatisfaction"=>"SUM(with_satisfaction)","leftover"=>"SUM(leftover)"))
                        ->where('year(pay_date)=?',$year)
                       ->where("payment_status IN (1,4,6)")
                        ->group('Month(pay_date)'); 
                $result = $this->getAdapter()->fetchAll($select);
                
          if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
               }else{
            
           throw new Exception("Argument not passed");  
        } 
        }  
      public function  getunpaidTeacherCount(){
          
            try { 
            $select = $this->select()
      
                        ->from($this,array("unpaidsteachers"=>"COUNT(payment_status)",'pay_date'))
                         ->where('payment_status=?',0)
                         ->group('Month(pay_date)');
                $res = $this->getAdapter()->fetchAll($select);
                 if ($res){
                    return $res;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
}    


      public function  getunpaidTeacherCountByYear(){
            if(func_num_args()>0){
            $year = func_get_arg(0);
            try { 
            $select = $this->select()
      
                        ->from($this,array("unpaidsteachers"=>"COUNT(payment_status)",'pay_date'))
                         ->where('payment_status=?',0)
                         ->where('year(pay_date)=?',$year)
                         ->group('Month(pay_date)');
                $res = $this->getAdapter()->fetchAll($select);
                 if ($res){
                    return $res;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
                 }else{
            
           throw new Exception("Argument not passed");  
        } 
} 


  
      /*
 * Dev:Priyanka Varanasi
 * Desc: TO get the total divided by admin in last year
 * Date : 16/10/2015 
 */      
        
     public function getAdminDividedLastYear(){
            try { 
            $select = $this->select()
      
                        ->from($this,array("alreadydivided"=>"SUM(with_satisfaction)"))
                         ->where('payment_status=?',1)
                         ->where('YEAR(pay_date)=?',date('Y', strtotime('-1 year')));
          
                $res = $this->getAdapter()->fetchRow($select);
                 if ($res){
                    return $res;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }    
         
     }
    
       
      /*
 * Dev:Priyanka Varanasi
 * Desc: TO get the total divided by admin in last month
 * Date : 16/10/2015 
 */      
        
     public function getAdminDividedLastMonth(){
            try { 
            $select = $this->select()
      
                        ->from($this,array("alreadydivided"=>"SUM(with_satisfaction)"))
                         ->where("payment_status IN (1,4)")
                         ->where('Month(pay_date)=?',date('n', strtotime('last month')));
         
                $res = $this->getAdapter()->fetchRow($select);
                 if ($res){
                    return $res;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }    
         
     }
     
  /*
 * Dev:Priyanka Varanasi
 * Desc: TO get the best teacher earned in last month 
 * Date : 16/10/2015 
 */  
    public function teacherMoreEarnedLastMonth(){
        
        try { 
            $select = $this->select()
                        ->from($this,array("bestearn"=>"MAX(with_satisfaction)"))
                        ->where('Month(pay_date)=?',date('n', strtotime('last month')));
           
            $res = $this->getAdapter()->fetchRow($select);
                 if ($res){
                    return $res;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }    
        
    } 
    
      /*
 * Dev:Priyanka Varanasi
 * Desc: TO get the  teacher  less earned in last month 
 * Date : 16/10/2015 
 */  
    
    public function teacherLessEarnedLastMonth(){
        
            try { 
            $select = $this->select()
                        ->from($this,array("lessearnteacher"=>"MIN(with_satisfaction)"))
                        ->where('Month(pay_date)=?',date('n', strtotime('last month')));
            $res = $this->getAdapter()->fetchRow($select);
                 if ($res){
                    return $res;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }    
        
        
    }
    public function getTeachersYearsNames(){
        
        
         try { 
            $select = $this->select()
      
                        ->from($this,array("years"=>"Year(pay_date)"))
                         ->group('Year(pay_date)')
                         ->order('pay_date ASC');
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
 
        public function getListOfMonthsByYearForTeachers(){
      
         if (func_num_args() > 0) {
            $year = func_get_arg(0);
           
       try {
            $select = $this->select()
                     ->from($this,array('month'=>'Month(pay_date)'))
                           ->order('pay_date ASC')
                           ->where('Year(pay_date)=?',$year)
                           ->distinct('Month(pay_date)');
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