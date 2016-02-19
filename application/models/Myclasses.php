<?php

class Application_Model_Myclasses extends Zend_Db_Table_Abstract {
    
    private static $_instance = null;
    protected $_name = 'myclasses';
    
    private function  __clone() { } //Prevent any copy of this object
	
    public static function getInstance(){
		if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
		self::$_instance = new Application_Model_Myclasses();
		return self::$_instance;
    }

   
     /** 
        Developer: Jeyakumar
      * Desc: Update follow status if already exists otherwise insert new row
    **/
      public function updateSave() {
          
          if(func_num_args() > 0){
            
            $userid = func_get_arg(0);
            $classid = func_get_arg(1);
            $date=date('Y-m-d');
            $select=  $this->select()
                    ->where("user_id = ".$userid )
                    ->where("class_id =".$classid);
            $result = $this->getAdapter()->fetchRow($select);
          
            if($result){
                $data=array("save"=>1-$result['save'],"saved_date"=>$date); 
                 $where=array("user_id = ".$userid,"class_id = ".$classid);
                $updateresult = $this->update($data,$where); 
                return $updateresult;
            }
            else{
                $data=array("user_id"=>$userid,"class_id"=>$classid,"save"=>0,"saved_date"=>$date);
               $response = $this->insert($data);
               return $response;
            }

        }
   }
   /** 
        Developer: Jeyakumar
    *   Desc: Get Follow Details
    **/
     
    public function getSaveDetail() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            }
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'myclasses'))
                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.class_title','tc.class_description','tc.user_id as tcid'))
//                    ->join(array('tcv' => 'teachingclassvideo'),'tcv.class_id = tc.class_id',array('tcv.video_thumb_url','tcv.cover_image'))
                    ->join(array('ul' => 'usersmeta'),'ul.user_id = tc.user_id',array('ul.user_profile_pic','ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = tc.user_id', array('con.first_name', 'con.last_name', 'con.premium_status'));
             if(!empty($userid)){
                    $select = $select->where('l.user_id=?', $userid)
                                     ->where('l.save = 0') ;
                    }
            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
                $count = 0;
                foreach ($result as $val) {
                    $select1 = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('tcv' => 'teachingclassvideo'))
                            ->where('tcv.class_id=?', $val['class_id']);
                    $result1 = $this->getAdapter()->fetchRow($select1);
                    $result[$count]['video_thumb_url'] = $result1['video_thumb_url'];
                    $result[$count]['cover_image'] = $result1['cover_image'];

                    $count++;
                }
                //echo "<pre>";print_r($result);die;
                return $result;
            }
            
            
//            if ($result) {
//                return $result;
//            }
        
    }
    
    public function getSaveclassDetail() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            }
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'myclasses'))
                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.class_title','tc.class_description','tc.user_id as tcid'))
//                    ->join(array('tcv' => 'teachingclassvideo'),'tcv.class_id = tc.class_id',array('tcv.video_thumb_url','tcv.cover_image'))
                    ->join(array('ul' => 'usersmeta'),'ul.user_id = tc.user_id',array('ul.user_profile_pic','ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = tc.user_id', array('con.first_name', 'con.last_name', 'con.premium_status'));
             if(!empty($userid)){
                    $select = $select->where('l.user_id=?', $userid)
                                     ->where('l.save = 0') ;
                    }
            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
                $count = 0;
                foreach ($result as $val) {
                    $select1 = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('tcv' => 'teachingclassvideo'))
                            ->where('tcv.class_id=?', $val['class_id']);
                    $result1 = $this->getAdapter()->fetchRow($select1);
                    $result[$count]['video_thumb_url'] = $result1['video_thumb_url'];
                    $result[$count]['cover_image'] = $result1['cover_image'];

                    $count++;
                }
                //echo "<pre>";print_r($result);die;
                return $result;
            }
            
            
//            if ($result) {
//                return $result;
//            }
        
    }
  
   
    public function getSave()
    {
         if(func_num_args() > 0){
            $user_id = func_get_arg(0);
            $classid = func_get_arg(1);
            try{
                
                   $select = $this->select()
                               ->from($this)
                               ->where('user_id = ?',$user_id)
                               ->where('class_id = ?',$classid);
                
                $result = $this->getAdapter()->fetchRow($select);
            }
             
             catch(Exception $e){
                throw new Exception('Unable To Insert Exception Occured :'.$e);
            }
            
             if($result){
                return $result;
            }
         }
    }
   
    public function getFollwerDetail() {
        
        if(func_num_args() > 0){
            $user_id = func_get_arg(0);
            try{
               
                $select = $this->select()
                               ->from($this)
                               ->where('user_id = ?',$user_id);
                
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
   /* Name: Namrata Singh
    * Date: 13/2/15
    * Desc: Get all the saved classes details
    */
   public function getSavedClasses(){
         if(func_num_args() > 0){
            $user_id = func_get_arg(0);
            try{
               
                $select = $this->select()
                               ->setIntegrityCheck(false)
                               ->from(array('m' => 'myclasses'),array('m.class_id','m.save'))
                               ->joinleft(array('tc' => 'teachingclasses'),'m.class_id = tc.class_id',array('tc.class_title','tc.user_id'))
                               ->joinleft(array('u' => 'users'),'tc.user_id = u.user_id',array('u.first_name','u.last_name'))
                               ->joinleft(array('um' => 'usersmeta'),'u.user_id = um.user_id',array('um.user_profile_pic','um.user_headline'))
                               ->where('m.user_id = ?',$user_id)
                               ->where('save = ?',0);
                
                $result = $this->getAdapter()->fetchAll($select);
               // echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
   //desc: to insert class in my class where user saves in step2 form
   public function insertInMyclasses(){
       
     if (func_num_args() > 0) {
            $data = func_get_arg(0);
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
   
    /** 
        Developer: Ram
    *   Desc: Get Follow Details
    **/
     
    public function getUserSaveDetailbyCategory() {
        if (func_num_args() > 0) {
            $categoryid = func_get_arg(0);
            if(!empty(@func_get_arg(1))){
            $userid = @func_get_arg(1);
            }
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'myclasses'))
                    ->join(array('tc' => 'teachingclasses'),'tc.class_id = l.class_id',array('tc.user_id','tc.class_title','tc.class_description'))
//                    ->join(array('tcv' => 'teachingclassvideo'),'tcv.class_id = tc.class_id',array('tcv.video_thumb_url','tcv.cover_image'))
                    ->join(array('ul' => 'usersmeta'),'ul.user_id = tc.user_id',array('ul.user_profile_pic','ul.user_headline'))
                    ->join(array('con' => 'users'), 'con.user_id = tc.user_id', array('con.first_name', 'con.last_name'))
                    ->where('l.save = 0')
                    ->where('tc.category_id = ?', $categoryid);
             if(!empty($userid)){
                  $select = $select->where('l.user_id=?', $userid);
             }
            $result = $this->getAdapter()->fetchAll($select);
            //echo "<pre>";print_r($result);die;
            if ($result) {
                $count = 0;
                foreach ($result as $val) {
                    $select1 = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('tcv' => 'teachingclassvideo'))
                            ->where('tcv.class_id=?', $val['class_id']);
                    $result1 = $this->getAdapter()->fetchRow($select1);
                    $result[$count]['video_thumb_url'] = $result1['video_thumb_url'];
                    $result[$count]['cover_image'] = $result1['cover_image'];

                    $count++;
                }
                //echo "<pre>";print_r($result);die;
                return $result;
            }
            
            
//            if ($result) {
//                return $result;
//            }
        }
    }
   
           
}
?>