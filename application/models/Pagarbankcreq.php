<?php

class Application_Model_Pagarbankcreq extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'pagarbankcreq';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Pagarbankcreq();
        return self::$_instance;
    }
    
    
    
    public function insertbankreqc() {

        if (func_num_args() > 0) {
      
        $data = func_get_arg(0);
             $result  = $this->insert($data);
            if ($result)
            {
                $select=$this->select()
                        ->where("bankcr_id=?",$result);
                
             $res = $this->getAdapter()->fetchRow($select);
               return $res; 
                
            }
        }
    }
    
      public function checkbank() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            $select = $this->select()
                    ->where('user_id = ? and status=0', $user_id);

            $result = $this->getAdapter()->fetchRow($select);

            if ($result)
                return $result;
        }
    }
    
    public function checkreq() {

        if (func_num_args() > 0) {
            $request_id = func_get_arg(0);

            $select = $this->select()
                    ->where('requestid =? and status=0', $request_id);

            $result = $this->getAdapter()->fetchRow($select);

            if ($result)
                return $result;
        }
    }
     public function updatereq() {

        if (func_num_args() > 0) {
            $request_id = func_get_arg(0);
           
            $req["status"]=1;
            $result = $this->update($req,"requestid ='$request_id'");
                  

      

            if ($result)
                return $result;
        }
    }

}
?>