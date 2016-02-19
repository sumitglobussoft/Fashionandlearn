<?php

class Application_Model_DiscussionComments extends Zend_Db_Table_Abstract {
    
    private static $_instance = null;
    protected $_name = 'discussioncomments';
    
    private function  __clone() { } //Prevent any copy of this object
	
    public static function getInstance(){
		if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
		self::$_instance = new Application_Model_DiscussionComments();
		return self::$_instance;
    }
    /* Developer:Namrata Singh
       Desc : inserting data's in the users table   
    */
    public function insertComments() {
        
        if(func_num_args() > 0){
            $data = func_get_arg(0);
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
       public function updateComments() {
        
        if(func_num_args() > 0){
            $data = func_get_arg(0);
            $commentid = func_get_arg(1);
            $where = "comment_id= " . $commentid;
          try{
                $responseId = $this->update($data,$where);
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
          public function deletereplyComments() {
        
        if(func_num_args() > 0){
           
            $commentid = func_get_arg(0);
            $where = "comment_id= " . $commentid;
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
             public function deleteComments() {
        
        if(func_num_args() > 0){
           
            $commentid = func_get_arg(0);
            $where1 = array('comment_id = ' . $commentid);
            $where2 = array('parent_id = ' . $commentid);
          try{
                $responseId1 = $this->delete($where1);
                $responseId2 = $this->delete($where2);
            }catch(Exception $e){             
                 return $e->getMessage(); 
            } 
          if($responseId1 && $responseId2){
                return $responseId1;
            }
        }else{
            throw new Exception('Argument Not Passed');
        }
        
        
   }
         public function getComments() {
          
          if(func_num_args() > 0){
            
            $discussionid = func_get_arg(0);
           
            
    try{
               
                $select = $this->select()
                               ->setIntegrityCheck(false)
                                ->from(array('l' => 'discussioncomments'))
                                ->join(array('ul' => 'users'),'ul.user_id = l.user_id',array('ul.first_name','ul.last_name'))
                               ->join(array('um' => 'usersmeta'),'ul.user_id = um.user_id',array('um.user_profile_pic','um.user_headline'))
                               ->where('l.discussion_id = ?',$discussionid)
                               ->order('l.comment_date');
                $result = $this->getAdapter()->fetchAll($select);
                
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
 
      public function getTrendDetail() {
          
          if(func_num_args() > 0){
            
            $classid = func_get_arg(0);
            
    try{
               
                $select = $this->select()
                               ->setIntegrityCheck(false)
                                ->from(array('l' => 'classdiscussions'))
                                ->join(array('ul' => 'users'),'ul.user_id = l.user_id',array('ul.first_name','ul.last_name'))
                               ->where('l.class_id = ?',$classid);
                
                $result = $this->getAdapter()->fetchAll($select);
                
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
   
   
   public function getRecentDetail() {
        if (func_num_args() > 0) {
            // $userid = func_get_arg(0);
             $classid = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'classdiscussions'))
//                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title'))
                    ->join(array('ul' => 'usersmeta'),'ul.user_id = l.user_id',array('ul.user_profile_pic','ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
//                    ->where('l.user_id = ?',$userid)
                    ->where('l.class_id = ?',$classid)
                    ->order('l.discussed_date DESC');
             $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
                return $result;
            }
            
        }
    } 
   
   
   
   
   


    public function getDiscussionCreatorDetails() {
        if (func_num_args() > 0) {
            $discussioncomment_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('cdc' =>'discussioncomments'),array('cdc.user_id'))
                    ->join(array('cd' => 'classdiscussions'),'cd.discussion_id=cdc.discussion_id', array('cd.discussion_title'))
                    ->join(array('u' => 'users'), 'u.user_id=cdc.user_id', array('u.user_id', 'u.first_name', 'u.email'))
                    ->where('cdc.comment_id=?', $discussioncomment_id);
            $result = $this->getAdapter()->fetchRow($select);
//            print_r($result); die;
            if($result){
                return $result;
            }
        }
    }
    
    /*  Developer :Bhabani 
        Desc : For counting discussions of a Perticular user
    */
    public  function getDiscussionsCount(){
        if (func_num_args() > 0) {
            $class_id = func_num_args(0); 
            $select = $this->select()
                    ->where('class_id = ?',$class_id);
        }
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return count($result);
        }
    }
    
    
     /*  Developer :abhishekm 
        Desc : get userid
    */
    public  function getuserbyparentid(){
        if (func_num_args() > 0) {
            $parent_id = func_num_args(0); 
            $select = $this->select()
                    ->where('parent_id = ?',$parent_id);
        }
        $result = $this->getAdapter()->fetchRow($select);
        if ($result) {
            return $result;
        }
    }
    
    
    
      /*  Developer :abhishek m
        Desc : For counting commentcount for a particular discussion
    */
    public  function getDiscussionscCount(){
       
        if (func_num_args() > 0) {
           
            $discussion_id = func_get_arg(0);
           
            $select = $this->select()
                    ->where('discussion_id = ?',$discussion_id);
           
        }
      
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return count($result);
        }
        else
            return 0;
    }
    public  function getdiscssionreplyCount(){
         if (func_num_args() > 0) {
           
            $comment_id = func_get_arg(0);
           
            $select = $this->select()
                    ->where('parent_id = ?',$comment_id);
           
        }
      
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return count($result);
        }
        else
            return 0;
        
    }
    
     public function getCommentsdetail() {

        if (func_num_args() > 0) {

            $id = func_get_arg(0);
         


            try {

                $select = $this->select()
                         ->from($this)
                        ->where('comment_id = ?', $id)                  
                        ->limit(1);
                
                $result = $this->getAdapter()->fetchRow($select);
               if ($result) {
                return $result;
            }
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

           
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    public function getCommentsid() {

        if (func_num_args() > 0) {

            $discussion_id = func_get_arg(0);
            $class_id = func_get_arg(1);
            $user_id = func_get_arg(2);


            try {

                $select = $this->select()
                         ->from($this)
                        ->where('discussion_id = ?', $discussion_id)
                        ->where('class_id = ?', $class_id)
                        ->where('user_id = ?', $user_id)
                        ->order('comment_id desc')
                        ->limit(1);
                
                $result = $this->getAdapter()->fetchRow($select);
               if ($result) {
                return $result;
            }
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

           
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
}
?>