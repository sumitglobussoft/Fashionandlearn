<?php

class Application_Model_Usergamestats extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'usergamestats';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Usergamestats();
        return self::$_instance;
    }
    
    
    
    public function getstatsinfo() {
        
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

          $select = $this->select()
                    
                        ->where('user_id = ?', $user_id);

                $result = $this->getAdapter()->fetchRow($select);
                if($result)
        return $result;
                else{
                    $data["user_id"]=$user_id;
                    $this->insert($data);
                     $select = $this->select()
                    
                        ->where('user_id = ?', $user_id);

                $result = $this->getAdapter()->fetchRow($select);
                 return $result;
                }
        
        
        }
        
        
    }
        
    
    
    
     public function insertuser() {
        
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $data["user_id"]=$user_id;
          

                $result = $this->insert($data);
        
       if($result)
        return $result;
        
        
        }
        
        
    }
    
    
    
    
    
    
    
    
    
      public function updatestats() {

          if (func_num_args() > 0) {
            $data1 = func_get_arg(0);
            $user_id = func_get_arg(1);
            foreach ($data1 as $key => $value) {
             $data=array("$key"=>new Zend_Db_Expr("$key + ".$value));   
            }
           
          
           try{
             $result = $this->update($data, 'user_id =' . $user_id);
            
           }
 catch (Exception $e)
 {
     echo $e;
 }
            if ($result) {
                return $result;
            }
        } 
    
    
    }
    
    
    
    
    
    
    
    
    
}
?>