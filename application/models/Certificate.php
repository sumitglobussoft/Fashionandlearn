<?php

class Application_Model_Certificate extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'certificate';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Certificate();
        return self::$_instance;
    }

    public function insertCertificate($data) {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $classid = func_get_arg(1);
            $data = array("user_id"=>$user_id, "class_id"=>$classid);
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
    
    public function getCertificateDetails() {

        if (func_num_args() > 0) {
            $certid = func_get_arg(0);
          
            try {
                $select = $this->select()
                    ->where("certificate_id=?",$certid);
                    
                $result = $this->getAdapter()->fetchrow($select); 
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
    
    //abhishekm
    public function getcertificateCount() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
          
            try {
                $select = $this->select()
                         ->where("user_id=?",$user_id);
                        
              //  die($select);
                  
                    
                $result = $this->getAdapter()->fetchAll($select); 
//                var_dump($result);
             
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
               
                return count($result);
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
    
      public function getCertificateDetailss() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $class_id=func_get_arg(1);
          
            try {
                $select = $this->select()
                    ->where("user_id=?",$user_id)
                ->where("class_id=?",$class_id);
                    
                $result = $this->getAdapter()->fetchrow($select); 
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}

?>
