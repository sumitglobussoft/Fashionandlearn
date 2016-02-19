<?php
//DEV: priyanka varanasi
//DESC: Referal Payment Table  modal created
//DATE: 13/10/2015
class Application_Model_ClassWiseEarnings extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'class_wise_earnings';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_ClassWiseEarnings();
        return self::$_instance;
    }
 
 
   /*
 * dev:priyanka varanasi
 * desc: to get  earnings earned by the class ofa respective teacher
 * date : 20/10/2015 
 */
    
    public function getEarningsByClass(){
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {
                $select = $this->select()
                         ->from($this)
                          ->where('user_id=?',$user_id);
                        
               $result = $this->getAdapter()->fetchAll($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }


    
}
   /*
 * dev:priyanka varanasi
 * desc: to get month in  these table
 * date : 20/10/2015 
 */
    
    public function getClassearningsMonths(){
      
            try { 
                 $select = $this->select()
                           ->from($this,array('month'=>'Month(calculated_date)'))
                           ->order('calculated_date ASC')
                           ->distinct('Month(calculated_date)');
                        
               $result = $this->getAdapter()->fetchAll($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
   
    
}

public function GetTheClassEarnsOfTeacher(){
    
    if (func_num_args() > 0) {
            $month = func_get_arg(0);
            $user_id = func_get_arg(1);
            try {
                $select = $this->select()
                         ->from($this)
                         ->where('user_id=?',$user_id)
                         ->where('Month(calculated_date)=?',$month);
               $result = $this->getAdapter()->fetchAll($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    
    
    
}
public function GetTheClassEarnsOfTeacherByMonth(){
    
     if (func_num_args() > 0) {
            $month = func_get_arg(0);
            $user_id = func_get_arg(1);
            try {
                $select = $this->select()
                         ->from($this)
                         ->where('user_id=?',$user_id)
                         ->where('Month(calculated_date)=?',$month);
               $result = $this->getAdapter()->fetchAll($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    
    
    
}

}

?>