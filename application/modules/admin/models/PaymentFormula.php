<?php
//DEV: priyanka varanasi
//DESC: Paymentformula modal created
//DATE: 25/9/2015
class Admin_Model_PaymentFormula extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'payment_formula';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_PaymentFormula();
        return self::$_instance;
    }
/*
 * dev:priyanka varanasi
 * desc: to get all payment details
 * date : 25/9/2015 
 */
    public function getPaymentFormulaValues() {
      try {
                $select = $this->select()
                            ->from($this);
                 $result = $this->getAdapter()->fetchRow($select);
                
         if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
    }
    
    
    
    /*
 * dev:priyanka varanasi
 * desc: to get all payment details
 * date : 25/9/2015 
 */
    public function UpdatePaymentFormulaValues() {
          if(func_num_args()>0){
            $forid = func_get_arg(0);
            $data = func_get_arg(1);
            try {
                $result = $this->update($data, 'formula =' . $forid);
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
}
?>