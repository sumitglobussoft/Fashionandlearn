<?php

class Application_Model_Invitationtable extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'invitationtable';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Invitationtable();
        return self::$_instance;
    }

    
    
     public function insertinvetation() {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
                $response = $this->insert($data);
                if($response){
                    return $response;
                }else{
                    return 0;
                }
            }else{
            throw new Exception('Argument Not Passed');
        }
        }
        public function getallinvitations() {
            try {
                $select = $this->select();

                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return $result;
            }
        
    }

    
   

}

?>