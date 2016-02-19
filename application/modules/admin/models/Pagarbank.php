<?php

class Admin_Model_Pagarbank extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'pagarbank';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Pagarbank();
        return self::$_instance;
    }
    
    
    
    public function getbankdetails() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            $select = $this->select()
                    ->where('user_id = ?', $user_id);

            $result = $this->getAdapter()->fetchRow($select);

            if ($result)
                return $result;
        }
    }
    
      public function bankupsert() {

        if (func_num_args() > 1) {
            $data = func_get_arg(0);
              $user_id = func_get_arg(1);

            $select = $this->select()
                    ->where('user_id = ?', $user_id);

            $result = $this->getAdapter()->fetchRow($select);
            
            $res;
            if ($result)
            {
               $res=$this->update($data,"user_id=$user_id");
            }
            else
            {
                $data["user_id"]=$user_id;
                $res=$this->insert($data);
            }
            if($res)
                return $res;
              
        }
    }

}
?>