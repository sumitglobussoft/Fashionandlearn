<?php

class Application_Model_Teacherpaymentdetails extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teacherpaymentdetails';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Teacherpaymentdetails();
        return self::$_instance;
    }

    public function teacehrpaymentdata() {
//die('dasd');
        if (func_num_args() > 0) {
            $data = func_get_arg(0);

//            print_r($data); die;
            try {
                $responseId = $this->insert($data);
            } catch (Exception $e) {
                die($e->getMessage());
            }
            if ($responseId) {
                return $responseId;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function bestsalarymonth() {
        if (func_num_args() > 0) {
            $teacher_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from($this, array(new Zend_Db_Expr('max(salary) as maxsalry'), 'month'))
                    ->where('teacher_id=?', $teacher_id);

            $result = $this->getAdapter()->fetchRow($select);
            if ($result) {
                return $result;
            }
        }
    }

    public function worstmonthsalary() {
        if (func_num_args() > 0) {
            $teacher_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from($this, array(new Zend_Db_Expr('min(salary) as minsalry'), 'month'))
                    ->where('teacher_id=?', $teacher_id);

            $result = $this->getAdapter()->fetchRow($select);
            if ($result) {
                return $result;
            }
        }
    }

    public function totalsalarydata() {
        if (func_num_args() > 0) {
            $teacher_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from($this)
                    ->where('teacher_id=?', $teacher_id);

            $result = $this->getAdapter()->fetchAll($select);
            
            if ($result) {
                return $result;
            }
        }
    }

    public function currentmonthsalary() {
        if (func_num_args() > 0) {
            $month = func_get_arg(0);
            $year = func_get_arg(1);
            $teacher_id = func_get_arg(2);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from($this)
                    ->where('teacher_id=?', $teacher_id)
                    ->where('month=?', $month)
                    ->where('year=?', $year);
            
            $result = $this->getAdapter()->fetchRow($select);
            if($result){
                return $result['referal_money']+$result['enroll_money'];
            }
        }
    }
    
    public function allTeacherSalary(){
      
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from($this,'salary');
                    

            $result = $this->getAdapter()->fetchAll($select);
            
            if ($result) {
                return $result;
            }
        
    }
    
    public function getmonthsstatistics() {
        
        
         if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

        $datet = gmdate('Y-m-d', time());
 $month= date("m", strtotime($datet));
 $year=date("Y", strtotime($datet));


//           $date = strtotime($date . ' +1 week');
//           $date=date('Y-m-d', $date);
//           



        $data["labels"] = array();
        $data["referal"] = array();
        $data["patnersip"] = array();

      
        for ($i = 1; $i <= 6; $i++) {
        
            if($month==0)
            {
                $date = "$year-01-01";
                $month=12;
                $year--;
                $date1 = "$year-12-01";
                 
               
            }
            else
            {
               $month++;
                 $date1 = "$year-$month-01";
                  $month--;
                  $date = "$year-$month-01";
                
            }    
      

             $select = $this->select()
                    ->where('month='.$month.' and year='.$year.' and teacher_id='.$user_id);
                     $result = $this->getAdapter()->fetchRow($select);
               
            $stamp = date("M", strtotime($date));

            array_push($data["labels"], $stamp);
            array_push($data["referal"], $result["referal_money"]);
            array_push($data["patnersip"], $result["enroll_money"]);
      
            
            
                         $month--; 
        }
    // die();
        $data["labels"] = array_reverse($data["labels"]);
        $data["referal"] = array_reverse($data["referal"]);
        $data["patnersip"] = array_reverse($data["patnersip"]);
    

        if ($data) {
            return $data;
        }
        
        
         }
    }
    
    
     public function invoice() {
        if (func_num_args() > 0) {
            $data["invoice"] = func_get_arg(0);
             $id = func_get_arg(1);
            $result= $this->update($data,"datat_no=".$id);
          

         
            
            if ($result) {
                return $result;
            }
        }
    }
    
    
    
    
    

}
