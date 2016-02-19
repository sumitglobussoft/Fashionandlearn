<?php
//DEV: priyanka varanasi
//DESC: teacher payment Monthly statistics modal
//DATE: 5/10/2015
class Admin_Model_TeacherpayMonthlystatistics extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teacherpay_monthlystatistics';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_TeacherpayMonthlystatistics();
        return self::$_instance;
    }

    /*
 * dev:priyanka varanasi
 * desc: to get  last month statistics 
 * date : 10/5/2015 
 */
    
    public function getStatsofLastMonth(){
            try {
                $select = $this->select()
                        ->from($this)
                         ->where('MONTH(insert_date) = ?', date('n', strtotime('last month')));
               $result = $this->getAdapter()->fetchRow($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
   

 
 /* Developer:priyanka varanasi
      Desc : insert the info of teacher statistics
      dated : 05/10/2014
  */

    public function insertTeacherCurrentPaymentStatics() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
             try {
                $select = $this->select()
                        ->from($this)
                         ->where('MONTH(insert_date) = ?', date('m'));
               $result = $this->getAdapter()->fetchRow($select);
             
        if ($result){
            $res = $this->update($data,'statistics_id = '. $result['statistics_id']);
           
          }else{
       
                $responseId = $this->insert($data);
                 if ($responseId) {
              return $responseId;
            }
                }
            
            }  catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
         } else {
            throw new Exception('Argument Not Passed');
        }
    }
    /*
 * dev:priyanka varanasi
 * desc: to  statistics based on month and year
 * date : 10/5/2015 
 */
    
    public function getStatsofAdminstatistics(){
           
          if (func_num_args() > 0) {
            $month = func_get_arg(0);
            $year = func_get_arg(1);
        
        try {
                $select = $this->select()
                        ->from($this)
                        ->where('MONTH(insert_date)=?',$month)
                        ->where('YEAR(insert_date)=?',$year);
               $result = $this->getAdapter()->fetchRow($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }else {
            
            throw new Exception('Argument Not Passed');
        }
    
}    /*
 * dev:priyanka varanasi
 * desc: to  get statistics group by month in year
 * date : 09/01/2015 
 */

public function getAdminstaticsOrderByMonthly(){
   
       try {
                $select = $this->select()
                        ->from($this)
                        ->where('year(insert_date)=?',date('Y'));
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
 * desc: to  get statistics group by month in year
 * date : 09/01/2015 
 */
 
        public function getAdminstaticsByMonthlyByYear(){
      
         if (func_num_args() > 0) {
            $year = func_get_arg(0);
           
       try {
                $select = $this->select()
                        ->from($this)
                        ->where('year(insert_date)=?',$year);
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
 * Dev:Priyanka Varanasi
 * Desc: TO get the total earned by admin in last month
 * Date : 16/10/2015 
 */      
        
     public function getAdminEarnedLastYear(){
          try { 
            $select = $this->select()
      
                        ->from($this,array("adminincome"=>"SUM(admin_weightage_amount)",'insert_date',"admininleftover"=>"SUM(current_leftover)"))
                         ->where('YEAR(insert_date)=?',date('Y', strtotime('-1 year')));
           
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
 * Desc: TO get the total leftover last year
 * Date : 16/10/2015 
 */      
   public function  getTotalLeftoverInLastYear(){
       
       try { 
            $select = $this->select()
      
                        ->from($this,array("lastyearleftover"=>"SUM(current_leftover)"))
                         ->where('YEAR(insert_date)=?',date('Y', strtotime('-1 year')));
           
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
 * Desc: TO get the total leftover last year
 * Date : 16/10/2015 
 */      
   public function  getTotalLeftoverInLastMonth(){
       
       try { 
            $select = $this->select()
      
                        ->from($this,array("lastmonthleftover"=>"SUM(current_leftover)"))
                         ->where('Month(insert_date)=?',date('n', strtotime('last month')));
           
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
 * Desc: TO get the list of years present in db
 * Date : 17/10/2015 
 */ 
   public function getTheYearsFromDb(){
       
         try { 
            $select = $this->select()
      
                        ->from($this,array("years"=>"Year(insert_date)"))
                         ->group('Year(insert_date)')
                         ->order('insert_date ASC');
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
 
        public function getListOfMonthsByYear(){
      
         if (func_num_args() > 0) {
            $year = func_get_arg(0);
           
       try {
            $select = $this->select()
                     ->from($this,array('month'=>'Month(insert_date)'))
                           ->order('insert_date ASC')
                           ->where('Year(insert_date)=?',$year);
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