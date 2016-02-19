<?php

class Application_Model_Category extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'category';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Category();
        return self::$_instance;
    }
    /* Developer:Namrata Singh
       Desc : Getting all the data based on category name
    */
    public function getDetail() {
          
        if(func_num_args() > 0){
            $categoryname = func_get_arg(0);
            try{
                $select = $this->select()
                               ->from($this)
                               ->where('category_name = ?',$categoryname);
                
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

    /* Developer:Namrata Singh
      Desc : Getting all the data based on category name
    */

    public function getCategoryDetail() {

        if (func_num_args() > 0) {
            $categoryname = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('c' => 'category'), 'category_id')
                        ->setIntegrityCheck(false)
                        ->join(array('tc' => 'teachingclasses'), 'c.category_id = tc.category_id', array('tc.user_id','tc.class_id','tc.class_title'))
                        ->join(array('tcv'=>'teachingclassvideo'),'tc.class_id = tcv.class_id',array('tcv.video_thumb_url', 'tcv.class_video_url'))
                        ->join(array('u'=>'users'),'u.user_id = tc.user_id',array('u.first_name', 'u.last_name'))
                        ->join(array('um' => 'usersmeta'), 'u.user_id = um.user_id', array('um.user_profile_pic'))
                        ->where('category_name = ?', $categoryname);

                $result = $this->getAdapter()->fetchAll($select);
//                echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
  
    public function getAllCategories() {
       
        try{
           
            $select = $this->select()
                    ->from($this);
//                    ->where('status = ?', 1);

            $result = $this->getAdapter()->fetchAll($select);

        }catch(Exception $e){
            throw new Exception('Unable To Insert Exception Occured :'.$e);
        }

        if($result){
            return $result;
        }
    }
//desc: priyanka varanasi
//desc: get categories where categories status is one 
    public function getAllCats() {
        try{
            $select = $this->select()
                    ->from($this)
                    ->where('status = ?', 1);

            $result = $this->getAdapter()->fetchAll($select);

        }catch(Exception $e){
            throw new Exception('Unable To Insert Exception Occured :'.$e);
        }

        if($result){
            return $result;
        }
    }
	
	
}

?>