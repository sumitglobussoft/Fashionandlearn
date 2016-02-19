<?php

class Application_Model_Levels extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'levels';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Levels();
        return self::$_instance;
    }
    
    
    
    public function getlevelsinfo() {
        
        if (func_num_args() > 0) {
            $typeid = func_get_arg(0);

          $select = $this->select()
                    
                        ->where('level = ?', $typeid);

                $result = $this->getAdapter()->fetchRow($select);
        
       if($result)
        return $result;
        
        
        }
        
        
        
        
    }
    public function getalllevelsinfo() {
          $select = $this->select();
                $result = $this->getAdapter()->fetchAll($select);
        return $result;
    }
     public function updatelevels() {

        if (func_num_args() > 0) {
            $requiredpoints = func_get_arg(0);
            $levels = func_get_arg(1);
            $levelid = func_get_arg(2);
            $data = array("level" =>$levels,"pointsrequired"=>$requiredpoints);
           if($levelid){
            $select = $this->select()
                    ->where("levelid = " . $levelid);
            $result = $this->getAdapter()->fetchRow($select);
            if($result){
                $where = array("levelid = " . $levelid);
                $result = $this->update($data, $where);
            return $result;}else{
               $response[0] = $this->insert($data);
                if($response[0])
                {
                $response[1]=$this->getAdapter()->lastInsertId();
                return $response;
                }
            }
            } else {
                $response[0] = $this->insert($data);
                if($response[0])
                {
                $response[1]=$this->getAdapter()->lastInsertId();
                return $response;
                }
            }
        }
    }
     public function deletelevel() {
        if(func_num_args() > 0){
            $levelid = func_get_arg(0);
            $where = "levelid= " . $levelid;
          try{
                $responseId = $this->delete($where);
            }catch(Exception $e){             
                 return $e->getMessage(); 
            } 
            
            
          if($responseId){
                return $responseId;
            }
        }else{
            throw new Exception('Argument Not Passed');
        }
        
        
   }
    
    
    
    
    
    
    
    
    
}
?>