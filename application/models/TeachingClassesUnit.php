<?php
/*
 * Developer : Ankit Singh
 * Date : 30/12/2014
  */
class Application_Model_TeachingClassesUnit extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teachingclassunit';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_TeachingClassesUnit();
        return self::$_instance;
    }
    
    public function insertTeachingClassUnit() {

        if (func_num_args() > 0) {
            $imageData = func_get_arg(0);


            try {
                $responseId = $this->insert($imageData);
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
    
    public function updateunassignedClassunitsByUserid() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $classid = func_get_arg(1);
            $assignuserid = func_get_arg(2);

            try {
                $select = $this->select()
                        ->where("class_id=?", $classid);

                $result = $this->getAdapter()->fetchAll($select);
                if($result){
                     $data = array("user_id" => $assignuserid);
                    $where = "class_id =" . $classid;
                    $result123 = $this->update($data, $where);
                    if($result123){
                        return $result123;
                    }else{
                        return 0;
                    }
                }else{
                    return 1;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
   public function insertvideoinfo(){
               if (func_num_args() > 0) {
            $videoData = func_get_arg(0);
            


            try {
                $responseId = $this->insert($videoData);
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
    public function getclassunitvideoID() {
        if (func_num_args() > 0) {
            $class_id = func_get_arg(0);

            try {
                $select = $this->select()
                        ->from($this)
                        ->where('class_id = ?', $class_id);

                $result = $this->getAdapter()->fetchAll($select);
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
    
      public function getclassunitDetails() {
        if (func_num_args() > 0) {
            $class_id = func_get_arg(0);

            try {
                $select = $this->select()
                        ->from($this)
                        ->where('class_id = ?', $class_id);

                $result = $this->getAdapter()->fetchAll($select);
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

    public  function getUnitDetails(){
                if (func_num_args() > 0) {
                            $user_id = func_get_arg(0);


            try {
                $select = $this->select()
                         ->setIntegrityCheck(false)
                        ->from(array('tcu' => 'teachingclassunit'))
                        ->join(array('tc'=>'teachingclasses'),'tc.class_id=tcu.class_id',array('tc.class_title'))
                        ->where('tcu.user_id = ?', $user_id);
                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
                    
                }
                else {
            throw new Exception('Argument Not Passed');
        }
        
    }
    
    public function insertUnitName(){
         if (func_num_args() > 0) {
            $data= func_get_arg(0);
           
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
    public function updateUnitTitle(){
          if (func_num_args() > 0) {

            $data = func_get_arg(0);
            $where = func_get_arg(1);

            $update = $this->update($data, 'class_unit_id =' . $where);
            
            if (isset($update)) {
                
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
        
    }
       public  function getClassUnitID(){
         if(func_num_args() > 0) {
            $classid = func_get_arg(0);
//            print_r($classid); die;
            try {
                 $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('tcu'=>'teachingclassunit'))
                        ->join(array('tc'=>'teachingclassvideo'),'tcu.class_unit_id=tc.class_unit_id',array('tc.class_unit_id'))
                        ->where('tc.class_id=?',$classid)
                        ->group('tcu.class_unit_id');
//                 echo $select ;die;
               
                $result = $this->getAdapter()->fetchAll($select);
//               echo'<pre>';  print_r($result); die;
            }
            catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                
                return $result;
            }
        }
        else{
            
        }
    }
    
}
