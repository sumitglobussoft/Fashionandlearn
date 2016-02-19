<?php

class Application_Model_ClassReview extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'classesreview';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_ClassReview();
        return self::$_instance;
    }

    public function insertReview() {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $classid=$data["class_id"];
            $userid=$data["user_id"];
          $select = $this->select()
                  ->where("class_id=".$classid."&&user_id=".$userid);
           $result = $this->getAdapter()->fetchAll($select);
           if($result)
           {
                unset($data["class_id"]);
               unset($data["user_id"]);
             
               $result = $this->update($data,"class_id=".$classid."&&user_id=".$userid);
               
           }
               else{
                 $result = $this->insert($data);  
               }
          

            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
    public function getAllpReview() {
        if (func_num_args() > 0) {
            $classid = func_get_arg(0);
          try {
              $select = $this->select()
                      ->where("class_id=".$classid."&&`recommend_class`=0");
                $result = $this->getAdapter()->fetchAll($select);
                      
                                      
          }
            catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }
             if ($result) {
                return $result;
            }
    }
    }
    
        public function getAllReview() {
        if (func_num_args() > 0) {
            $classid = func_get_arg(0);
//            echo $classid;die;
            try {
              $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('cr' => 'classesreview'))
                                ->join(array('um' => 'usersmeta'),'cr.user_id = um.user_id'  ,array('um.user_profile_pic'))
                                ->join(array('u' => 'users'), 'u.user_id = cr.user_id', array('u.first_name', 'u.last_name'))
                               
                        ->where("cr.class_id=?",$classid);
                       
                 $result = $this->getAdapter()->fetchAll($select);
                 
                
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
        public function getMyReview() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
             $classid = func_get_arg(1);
            
            try {
               $select = $this->select()
                       ->where("user_id=?",$userid)
                       ->where("class_id=?",$classid);
                 $result = $this->getAdapter()->fetchRow($select);
                //echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
    
    

    
         public function getCalculateReview() {
        if (func_num_args() > 0) {
            $classid = func_get_arg(0);
            
            try {
              $select = $this->select()
                      ->where("class_id=?",$classid)
                      ->where("recommend_class=?",0);
                       
                 $result = $this->getAdapter()->fetchAll($select);
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
    
    
    
     public function  getEnrollUserProject()
  {
              if(func_num_args() > 0){
            $user_id = func_get_arg(0);
          
            try{
          
                $select = $this->select()
                                ->setIntegrityCheck(false)
                                ->from(array('cr' => 'classenroll'))
                                ->join(array('um' => 'usersmeta'),'cr.user_id = um.user_id'  ,array('um.user_profile_pic'))
                                ->join(array('u' => 'users'), 'u.user_id = cr.user_id', array('u.first_name', 'u.last_name'))
                                ->join(array('p' => 'projects'), 'p.class_id = cr.class_id', '*')
                                ->where('cr.user_id = ?',$user_id);
                                
                 
                $result = $this->getAdapter()->fetchAll($select);
//              echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
   
    public function  getEnrollUserRecentProject()
  {
              if(func_num_args() > 0){
            $user_id = func_get_arg(0);
          
            try{
          
                $select = $this->select()
                                ->setIntegrityCheck(false)
                               ->from(array('cr' => 'classenroll'))
                                ->join(array('um' => 'usersmeta'),'cr.user_id = um.user_id'  ,array('um.user_profile_pic'))
                                ->join(array('u' => 'users'), 'u.user_id = cr.user_id', array('u.first_name', 'u.last_name'))
                                ->join(array('p' => 'projects'), 'p.class_id = cr.class_id', '*')
                                ->where('cr.user_id = ?',$user_id)
                                ->order('p.project_created_date DESC');
                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
        public function  getEnrollUserLikeProject()
  {
              if(func_num_args() > 0){
            $user_id = func_get_arg(0);
          
            try{
          
                $select = $this->select()
                                ->setIntegrityCheck(false)
                                ->from(array('cr' => 'classenroll'))
                                ->join(array('um' => 'usersmeta'),'cr.user_id = um.user_id'  ,array('um.user_profile_pic'))
                                ->join(array('u' => 'users'), 'u.user_id = cr.user_id', array('u.first_name', 'u.last_name'))
                                ->join(array('p' => 'projects'), 'p.class_id = cr.class_id', '*')
                                ->where('cr.user_id = ?',$user_id);
                                
                 
                $result = $this->getAdapter()->fetchAll($select);
//              echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
  public function getEnrollUserDiscussion()
   {
                     if(func_num_args() > 0){
            $user_id = func_get_arg(0);
          
            try{
          
                $select = $this->select()
                                ->setIntegrityCheck(false)
                                ->from(array('cr' => 'classenroll'))
                                ->join(array('um' => 'usersmeta'),'cr.user_id = um.user_id'  ,array('um.user_profile_pic'))
                                ->join(array('u' => 'users'), 'u.user_id = cr.user_id', array('u.first_name', 'u.last_name'))
                                ->join(array('d' => 'classdiscussions'), 'd.class_id = cr.class_id', '*')
                                ->where('cr.user_id = ?',$user_id);
                               
                 
                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
      public function getEnrollUserRecentDiscussion()
   {
                     if(func_num_args() > 0){
            $user_id = func_get_arg(0);
          
            try{
          
                $select = $this->select()
                                ->setIntegrityCheck(false)
                                ->from(array('cr' => 'classenroll'))
                                ->join(array('um' => 'usersmeta'),'cr.user_id = um.user_id'  ,array('um.user_profile_pic'))
                                ->join(array('u' => 'users'), 'u.user_id = cr.user_id', array('u.first_name', 'u.last_name'))
                                ->join(array('d' => 'classdiscussions'), 'd.class_id = cr.class_id', '*')
                                ->where('cr.user_id = ?',$user_id)
                                ->order('d.discussed_date DESC');
                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
      public function getEnrollUserLikeDiscussion()
   {
                     if(func_num_args() > 0){
            $user_id = func_get_arg(0);
          
            try{
          
                $select = $this->select()
                                ->setIntegrityCheck(false)
                                ->from(array('cr' => 'classenroll'))
                                ->join(array('um' => 'usersmeta'),'cr.user_id = um.user_id'  ,array('um.user_profile_pic'))
                                ->join(array('u' => 'users'), 'u.user_id = cr.user_id', array('u.first_name', 'u.last_name'))
                                ->join(array('d' => 'classdiscussions'), 'd.class_id = cr.class_id', '*')
                                ->where('cr.user_id = ?',$user_id);
                               
                 
                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
    /* Dev.:Namrata Singh
     * Desc:get total number of reviews for 
     */
         public function getCountAllReview() {
        if (func_num_args() > 0) {
            $classid = func_get_arg(0);
//            echo $classid;die;
            try {
              $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('cr' => 'classesreview'))
                                ->join(array('um' => 'usersmeta'),'cr.user_id = um.user_id'  ,array('um.user_profile_pic'))
                                ->join(array('u' => 'users'), 'u.user_id = cr.user_id', array('u.first_name', 'u.last_name'))
                               
                        ->where("cr.class_id=?",$classid);
                       
                 $result = $this->getAdapter()->fetchAll($select);
                 
                
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
}
