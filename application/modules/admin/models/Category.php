<?php

class Admin_Model_Category extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'category';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Category();
        return self::$_instance;
    }
     /* Developer:priyanka varanasi
       Desc : Getting all the data based on category  id
    */
    public function getcategorybyid() {
          
        if(func_num_args() > 0){
            $categoryid = func_get_arg(0);
            try{
                $select = $this->select()
                               ->from($this)
                               ->where('category_id = ?',$categoryid);
                
                $result = $this->getAdapter()->fetchRow($select);
              
            }catch(Exception $e){
                throw new Exception('Unable To Insert Exception Occured :'.$e);
            }
            
            if($result){
                return $result;
            }
        }else{
            throw new Exception('Argument Not Passed');
        }
    }

    //dev:priyanka varanasi
    //desc: to get categories from db
  public function getCategorys() {
          $select = $this->select()
                ->from(array('c' => 'category'))
                ->setIntegrityCheck(false);
        $result = $this->getAdapter()->fetchAll($select);
        //print_r($result);die('test');
        if ($result) :
            return $result;
        endif;
    }
    // dev:priyanka varanasi
    //desc: to delete categories from db
     public function deletecategory(){
          if (func_num_args() > 0):
            $catid = func_get_arg(0);
            try {
                $db = Zend_Db_Table::getDefaultAdapter();
                $where = (array('category_id = ?' => $catid));
                $db->delete('category', $where);
            } catch (Exception $e) {
                throw new Exception($e);
            }
            return $catid;
        else:
            throw new Exception('Argument Not Passed');
        endif; 
  }
  //dev: priyanka varanasi
  //desc: to add category in db
  public function addcategory(){
       
        if(func_num_args() > 0){
            $data['category_name'] = func_get_arg(0);
            $data['status'] = 1;
         try{
                $responseId = $this->insert($data);
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
        //dev:priyanka varanasi
    //desc: TO update category details by id
    
    public function updatecategoryDetails() {

        if (func_num_args() > 0):
            $catid = func_get_arg(0);
            $catdata = func_get_arg(1); 
            try {
                $result = $this->update($catdata, 'category_id = "' . $catid . '"');
                if ($result) {
                    return $result;
                } else {
                    return 0;
                }
            } catch (Exception $e) {
                throw new Exception($e);
            }
        else :
            throw new Exception('Argument Not Passed');
        endif;
    } 
}

?>