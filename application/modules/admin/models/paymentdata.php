<?php

class Admin_Model_paymentdata extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'paymentdata';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_paymentdata();
        return self::$_instance;
    }
    
    public  function getpercentage(){
//        die('dasdasd');
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this);
        $result = $this->getAdapter()->fetchRow($select);
        if ($result) {
            return $result;
        }
    }
    
  
    public  function updatepercentagedata(){
       
         if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where=  func_get_arg(1);      
//            print_r($data); die;
      
        $result = $this->update($data, $where);
        if ($result) {
            return $result;
        }
          
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    public  function updateavgstudents(){
       
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where=  func_get_arg(1);      
//            print_r($data); die;
      
        $result = $this->update($data, $where);
        if ($result) {
            return $result;
        }
          
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    public  function updateprojects(){ if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where=  func_get_arg(1);      
//            print_r($data); die;
      
        $result = $this->update($data, $where);
        if ($result) {
            return $result;
        }
          
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
      public  function updatevideoesviews(){
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where=  func_get_arg(1);      
//            print_r($data); die;
      
        $result = $this->update($data, $where);
        if ($result) {
            return $result;
        }
          
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
}