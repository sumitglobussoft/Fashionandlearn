<?php

/*
 * Developer : Ankit Singh
 * Date : 30/12/2014
 */

class Application_Model_TechingClassFile extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teachingclassfile';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_TechingClassFile();
        return self::$_instance;
    }

    /*
     * Developer : Ankit Singh
     * Date: 20 Jan 2015
     * Desc : Insert Teaching classed data 
     */

    public function insertTeachingClassesFile() {

        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $classid = func_get_arg(1);
            $dir = func_get_arg(2);
            $filedate = func_get_arg(3);

            $data=array("user_id"=>$userid,"class_id"=>$classid,"class_file_path"=>$dir,"file_uploaded_date"=>$filedate);
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
    
 public function deleteTeachingClassesFile() {

        if (func_num_args() > 0) {
            $dir = func_get_arg(0);
            try {
                $result = $this->delete('class_file_id = "' .$dir . '"');
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
    
    
     public function renameClassesFile() {

        if (func_num_args() > 0) {
            $id = func_get_arg(0);
            $name = func_get_arg(1);
            $data=array("class_file_name"=>$name);
            try {
                $result = $this->update($data,'class_file_id = "' .$id . '"');
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
    
    
    
     public function updateunassignedClassfilesByUserid() {

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
    
 
    public function getTeachingClassesFile() {

        if (func_num_args() > 0) {
            //$userid = func_get_arg(0);
            $classid = func_get_arg(1);
          
//            $data=array("user_id"=>$userid,"class_id"=>$classid,"class_file_path"=>$dir,"file_uploaded_date"=>$filedate);
            try {
                $select = $this->select()
                   // ->where("user_id=?",$userid)
                    ->where("class_id=?",$classid);
                    
                $result = $this->getAdapter()->fetchAll($select); 
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return $result;
                print_r($result);
                die();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    
     public function getTeachingClassescre() {

        if (func_num_args() > 0) {
        
            $classid = func_get_arg(0);
          

            try {
                $select = $this->select()
                   
                    ->where("class_id=?",$classid);
                    
                $result = $this->getAdapter()->fetchRow($select); 
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
    public function getTeachingClassfiles() {

        if (func_num_args() > 0) {
        
            $classid = func_get_arg(0);
          

            try {
                $select = $this->select()
                   
                    ->where("class_id=?",$classid);
                    
                $result = $this->getAdapter()->fetchAll($select); 
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
