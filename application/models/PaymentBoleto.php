<?php
//DEV: priyanka varanasi
//DESC: Modal Design
//DATED:28/78/2015
class Application_Model_PaymentBoleto extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'payment_boleto';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_PaymentBoleto();
        return self::$_instance;
    }
    
    
    
    public function insertUserBoletoInfo() {

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
    
    public function getRecentUserAddressByUserID(){
          if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from($this)
                        ->setIntegrityCheck(false)
                        ->where('user_id =?', $user_id)
                        ->order('add_date DESC')
                        ->limit(1);
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

}
?>