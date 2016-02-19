<?php

class Application_Model_Timezone extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'timezone';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Timezone();
        return self::$_instance;
    }
    
    /* Developer:Namrata Singh
       Desc : Selecting all the timezones from the DataBase  
    */
         public function getTimeZone() {
         $select = $this->select()
                       ->from($this);
//                       ->where('status = ?','0');
                    
        $result = $this->getAdapter()->fetchAll($select);
//           echo"<pre>";var_dump($result);echo"</pre>";die;
        if($result){
            return $result;
    }
         }
}
?>
