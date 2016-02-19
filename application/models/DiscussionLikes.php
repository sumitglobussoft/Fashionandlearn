<?php

class Application_Model_DiscussionLikes extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'discussionlikes';

    private function __clone() {
        
    }

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_DiscussionLikes();
        return self::$_instance;
    }

    public function discusslikes() {
      
          if(func_num_args() > 0){
            
            $userid = func_get_arg(0);
            $classid=func_get_arg(1);
            $discussionid=func_get_arg(2);
            $date=date('Y-m-d');
      // $result="UPDATE followers SET follow_status = 1 - follow_status where follow_id=2";
            $select=  $this->select()
                           ->where("user_id= ".$userid )
                           ->where("discussion_id =".$discussionid);
             $result = $this->getAdapter()->fetchRow($select);
             
             if($result){
                 $data=array("likes_status"=>1-$result['likes_status'],"likes_date"=>$date); 
                 $where=array("user_id = ".$userid,"discussion_id = ".$discussionid);
                 $updateresult = $this->update($data,$where); 
                 return 0;
          
            }
        else{
           
            $data=array("user_id"=>$userid,"class_id"=>$classid,"discussion_id"=>$discussionid,"likes_status"=>0,"likes_date"=>$date);
             $response = $this->insert($data);
            return 1;
        }
             
             
        
            

       
        
                //$responseId = $this->update($data, "user_id= ".$where);
               // print_r($update);die;
        }
   }
      public function getdiscusslikes() {
          
              if (func_num_args() > 0){
            $discussionid = func_get_arg(0);
           
         $select = $this->select()
                   ->from("discussionlikes", array("num"=>"COUNT(*)"))
                    ->where("discussion_id = ". $discussionid)
                    ->where("likes_status = 0");
                    
            $result = $this->getAdapter()->fetchRow($select);
          
            if($result){
                return $result;
            }
            
            
              }
      
                 
             

    }

         public function getuserdiscusslikes() {
          
              if (func_num_args() > 0){
            $userid = func_get_arg(0);
            $discussid= func_get_arg(1);
           
         $select = $this->select()
                    ->where("discussion_id = ". $discussid)
                    ->where("user_id = ".$userid)
                    ->where("likes_status = 0");
                    
            $result = $this->getAdapter()->fetchAll($select);
          
            if($result){
                return 1;
            }
            else{
                return 0;
            }
            
            
              }
      
                 
             

    }
   
    public function projectdislikes() {
          if (func_num_args() > 0):
            $user_id = func_get_arg(0);
                         $project_id = func_get_arg(1);

            try {
                $db = Zend_Db_Table::getDefaultAdapter();
                $where = (array('user_id = ?' => $user_id ,'project_id = ?' => $project_id));
                $db->delete('projectlikes', $where);
            } catch (Exception $e) {
                throw new Exception($e);
            }
            return $user_id;
        else:
            throw new Exception('Argument Not Passed');
        endif;
    }
     
      public function getall() {
          
              if (func_num_args() > 0){
            $projectid = func_get_arg(0);
           
         $select = $this->select()
                   ->from("projectlikes", array("num"=>"COUNT(*)"))
                    ->where("project_id = " . $projectid)
                    ->where("like_status=0");
                    
            $result = $this->getAdapter()->fetchRow($select);
          
            if($result){
                return $result;
            }
            
            
              }
      
                 
             

    }

}

?>
