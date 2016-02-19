<?php

class Application_Model_Plans extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'plans';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Plans();
        return self::$_instance;
    }

    public function getAllPlanDetails() {
  try {
            $select = $this->select()
                    ->from($this);
          $result = $this->getAdapter()->fetchAll($select);
           
        } catch (Exception $exc) {
            throw new Exception('Unable to update, exception occured' . $exc);
        }
       
        if ($result) {
            return $result;
        }

}
}
?>