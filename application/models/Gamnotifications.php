<?php

class Application_Model_Gamnotifications extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'gamnotifications';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Gamnotifications();
        return self::$_instance;
    }

    public function storenotifi() {

        if (func_num_args() > 0) {
            $data["user_id"] = func_get_arg(0);
            $data["notification"] = func_get_arg(1);

            $result = $this->insert($data);
        }
    }
    
     public function getgamnotifi() {

        if (func_num_args() > 0) {
            $data["user_id"] = func_get_arg(0);
          

            $result = $this->select
                     ->where('user_id = ?', $userid)
                        ->order("gamnid desc")
                        ->limit(6);
        }
    }

}

?>