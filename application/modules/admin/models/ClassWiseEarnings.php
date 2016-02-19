<?php
//DEV: priyanka varanasi
//DESC: Referal Payment Table  modal created
//DATE: 13/10/2015
class Admin_Model_ClassWiseEarnings extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'class_wise_earnings';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_ClassWiseEarnings();
        return self::$_instance;
    }
 
 
 /*
 * dev:priyanka varanasi
 * desc: to insrert  referral details if the row is not existed for current month
 * date : 14/10/2015
 */

    public function insertClassWiseEarningsInfo() {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
          foreach ($data as $value) {
             try {
                $select = $this->select()
                        ->from($this)
                         ->where('MONTH(calculated_date) = ?', date('m'))
                         ->where('class_id=?',$value['class_id']);
               $result = $this->getAdapter()->fetchRow($select);
              
        if ($result){
              $res = $this->update($value,'earning_id = '. $result['earning_id']);
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

}

?>