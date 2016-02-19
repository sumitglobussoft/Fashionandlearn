<?php

class Application_Model_videocomment extends Zend_Db_Table_Abstract {
    
    private static $_instance = null;
    protected $_name = 'video_comment';
    
    private function  __clone() { } //Prevent any copy of this object
	
    public static function getInstance(){
		if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
		self::$_instance = new Application_Model_videocomment();
		return self::$_instance;
    }

   
     /** 
        Developer: Rakesh Jha
      * Desc:Insert video comment
    **/
      public function insertvideocomment($data) {
          
        if (func_num_args() > 0) {
            $data= func_get_arg(0);
           
            try {
                $responseId = $this->insert($data);
                if($responseId){
                    
                    return $responseId ;
                }
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
   public function getvideocomments($video_id,$userid) {
         
            try {
    $sql="select * from video_comment as vc where vc.privacy= 0 and vc.video_id=$video_id UNION select * from video_comment as vcc where vcc.privacy=1 and vcc.user_id=$userid and vcc.video_id=$video_id ";
    
                $result = $this->getAdapter()->fetchAll($sql);
              //  print_r($result); die;
                return $result;
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }      
        
   }
  
   public function editvideocomment($data,$commentid) {
         try {
           

            $update = $this->update($data, 'comment_id =' . $commentid);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }
   }
   public function deletevideocomment($commentid) {
         try {
           

            $sql="delete from video_comment where comment_id='$commentid'";
         
             $deleted = $this->getAdapter()->query($sql);

            if (isset($deleted)) {
                return $deleted;
            } else {
                throw new Exception('Argument Not Passed');
            }
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }
   }
     
   
     
   
          
}
?>