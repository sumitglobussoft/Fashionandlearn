<?php


class Admin_Model_TeachingClassUnit extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teachingclassunit';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_TeachingClassUnit();
        return self::$_instance;
    }
 //dev:priyanka varanasi   
    //desc: insert the class uint data
    public function insertclassunitdata(){
              if (func_num_args() > 0) {
            $unitdata = func_get_arg(0);
            $classesunit = func_get_arg(1);
            try {
               $result = $this->update($unitdata, 'class_unit_id = "' . $classesunit . '"');
                             
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
    //dev:priyanka varanasi
    //desc: to get class units information
  public function getClassUnitsIDS(){
        if (func_num_args() > 0){
            $classunitid = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('tcu' => 'teachingclassunit', array('tcu.user_id', 'tcu.class_id','tcu.class_unit_id','tcu.class_unit_title')))
                        ->setIntegrityCheck(false)
                        ->joinleft(array('u' => 'users'),'u.user_id = tcu.user_id', array('u.first_name'))
                        ->where('tcu.class_id =?',$classunitid);
                $result = $this->getAdapter()->fetchAll($select);
                if ($result) {
                    return $result;
                }
            } catch (Exception $e) {
                throw new Exception($e);
            }
        }else {
            throw new Exception('Argument Not Passed');
        }
  }
  
public function getTheClassUnits(){
   if (func_num_args() > 0) {
            $classunit_id = func_get_arg(0);
         
          try {
            $select = $this->select()
                   ->setIntegrityCheck(false)
                  ->from(array('tcu'=>'teachingclassunit'),array('tcu.class_unit_id','tcu.class_id','tcu.class_unit_titile'))
                  //->joinleft(array('tcu' => 'teachingclassunit'),'tcu.class_unit_id = tcv.class_unit_id',array('tcu.user_id','tcu.class_unit_titile'))
                  ->joinleft(array('u' => 'users'),'tcu.user_id = u.user_id',array('u.first_name','u.last_name'))
                  ->where('tcu.class_unit_id = ?', $classunit_id);
            
                  $result = $this->getAdapter()->fetchRow($select);
                
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
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