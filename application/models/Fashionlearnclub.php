<?php

class Application_Model_Fashionlearnclub extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'fashionlearnclub';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Fashionlearnclub();
        return self::$_instance;
    }

  
     public function getall() {
          $select = $this->select();
                 
                $result = $this->getAdapter()->fetchAll($select);
        return $result;
    }
      public function getalll() {
          $select = $this->select()
                  ->where("avl_count>0");
                $result = $this->getAdapter()->fetchAll($select);
        return $result;
    }
     public function getorderdetails() {
          if(func_num_args() > 0){
          $orderid = func_get_arg(0);
          $select = $this->select()
                  ->where("fsid=?",$orderid);
                $result = $this->getAdapter()->fetchRow($select);
        return $result;
          }
    }
     public function updatefashionlearnclub() {
         if (func_num_args() > 0) {
            $id = func_get_arg(0);
            $data = func_get_arg(1);
           if($id != 0){
            $select = $this->select()
                    ->where("fsid = " . $id);
            $result = $this->getAdapter()->fetchRow($select);
                $where = array("fsid = " . $id);
                $result = $this->update($data,$where);
                return $result;
                
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
     public function deletefashionlearnclub() {
        if(func_num_args() > 0){
          $id = func_get_arg(0);
            $where = "fsid= " . $id;
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
   
       public function shop() {

          if (func_num_args() > 0) {
            $fsid = func_get_arg(0);
          
            $data=array("avl_count"=>new Zend_Db_Expr('avl_count - 1'));
          
           try{
             $result = $this->update($data, 'fsid =' . $fsid);
             
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