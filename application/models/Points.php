<?php

class Application_Model_Points extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'points';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Points();
        return self::$_instance;
    }
    
    
    
    public function getpointsinfo() {
        
        if (func_num_args() > 0) {
            $typeid = func_get_arg(0);

          $select = $this->select()
                    
                        ->where('typeid = ?', $typeid);

                $result = $this->getAdapter()->fetchRow($select);
        
       
        return $result;
        
        
        }
        
        
        
        
    }
    public function getallpointsinfo() {
          $select = $this->select();
                $result = $this->getAdapter()->fetchAll($select);
        return $result;
    }
     public function updatepoints() {

        if (func_num_args() > 0) {

            $pointid = func_get_arg(0);
            $points = func_get_arg(1);
            $gems = func_get_arg(2);
            $select = $this->select()
                    ->where("pointid = " . $pointid);
            $result = $this->getAdapter()->fetchRow($select);
            if ($result) {
                $data = array("gems" =>$gems,"points"=>$points);
                $where = array("pointid = " . $pointid);
                $result = $this->update($data, $where);
                return $result;
            } else {
                $response = $this->insert($data);
                return $response;
            }
        }
    }
}
?>