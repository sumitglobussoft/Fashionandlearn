<?php

class Application_Model_videocommentlike extends Zend_Db_Table_Abstract {
    
    private static $_instance = null;
    protected $_name = 'video_comment_like';
    
    private function  __clone() { } //Prevent any copy of this object
	
    public static function getInstance(){
		if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
		self::$_instance = new Application_Model_videocommentlike();
		return self::$_instance;
    }

   
     /** 
        Developer: Rakesh jha
      * Desc: Update follow status if already exists otherwise insert new row
    **/
 
           public function checklike($commentid,$user_id) {
   
     
            try {
             
           $select = $this->select()
                        ->from($this)
                        ->where('comment_id = ?', $commentid)
                        ->where('user_id = ?', $user_id);
                $result = $this->getAdapter()->fetchAll($select);
                 
             
               
                return count($result);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }      
        
   }
}
?>