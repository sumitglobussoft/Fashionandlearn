<?php 
class Admin_Model_Teacherpaymentdetails extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teacherpaymentdetails';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Teacherpaymentdetails();
        return self::$_instance;
}

  public function teacehrpaymentdata() {
//die('dasd');
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $month=  func_get_arg(1);
            $year=  func_get_arg(2);
            $teacher_id=$data['teacher_id'];
            $where=array('month'=>$month,'year'=>$year,'teacher_id'=>$teacher_id);
            $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this)
               ->Where('month=?', $month)
               ->Where('year=?', $year)
               ->Where('teacher_id=?',$teacher_id);
    
        $result = $this->getAdapter()->fetchRow($select);
      
        if ($result) {
            
           $this->update($data, "month=$month&&year=$year&&teacher_id=$teacher_id");
              } 
        else{
            try {
             
                $responseId = $this->insert($data);
                
            } catch (Exception $e) {
               die($e->getMessage());
            }
            if ($responseId) {
                return $responseId;
            }
         else {
            throw new Exception('Argument Not Passed');
        }
        }
  }

         }

    public  function bestsalarymonth(){
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this, array(new Zend_Db_Expr('max(salary) as maxsalary'),'month'))    ;   
        $result = $this->getAdapter()->fetchRow($select);
  
        if ($result) {
            return $result;
        }
        
    }
      public  function worstsalarymonth(){
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this, array(new Zend_Db_Expr('min(salary) as minsalary'),'month'))    ;   
        $result = $this->getAdapter()->fetchRow($select);
  
        if ($result) {
            return $result;
        }
        
    }
    
    public  function updatepaymentstatus(){
             if (func_num_args() > 0) {
                 
                
                 $data=  func_get_arg(0);
                
                 $where=  func_get_arg(1);
                 
                   $this->update($data,"teacher_id='$where'");
             }
    }

    }