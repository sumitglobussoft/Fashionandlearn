<?php

class Admin_Model_Projects extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'projects';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Projects();
        return self::$_instance;
    }
     /* Developer:priyanka varanasi
       Desc : Getting all the projects related details
    */
    public function getprojectdetails(){
           $select = $this->select()
                  ->setIntegrityCheck(false)
                    ->from(array('p' => 'projects'),array('p.class_id','project_title','p.project_id','p.project_cover_image','p.project_workspace'))
                    ->joinLeft(array('tc' => 'teachingclasses'),'tc.class_id = p.class_id',array('tc.category_id','tc.class_title','tc.user_id','tc.class_id'))
                    ->joinLeft(array('con' => 'users'), 'con.user_id = tc.user_id', array('con.first_name', 'con.last_name'))  
                    ->joinLeft(array('c' => 'category'), 'c.category_id = tc.category_id ', array('c.category_name')) ;   
                  $result = $this->getAdapter()->fetchAll($select);
 if ($result) {
            return $result;
        }
    }
    /* Developer:priyanka varanasi
       Desc : Getting all the projects related details based on project id
    */ 
    public function getprojectsbyid(){
    if (func_num_args() > 0) {
            $project = func_get_arg(0);
            
            try {
                $select = $this->select()
                   ->setIntegrityCheck(false)
                   ->from(array('p' => 'projects'))
                   ->joinLeft(array('tc' => 'teachingclasses'),'tc.class_id = p.class_id',array('tc.class_title','tc.category_id'))
                   ->joinLeft(array('u' => 'users'),'p.user_id = u.user_id',array('u.first_name'))
                   ->joinLeft(array('c' => 'category'),'tc.category_id = c.category_id',array('c.category_name'))
                   ->where('p.project_id = ?', $project);
                
                 $result = $this->getAdapter()->fetchRow($select);
                             
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
    /* Developer:priyanka varanasi
       Desc : update project details based on project id
    */ 
public function updateProjectdet(){
   if (func_num_args() > 0):
             $userid = func_get_arg(0);
            $classid = func_get_arg(1);
            $projectid = func_get_arg(2);
            $data = func_get_arg(3);
           
            try {
                $result = $this->update($data, 'project_id = "' . $projectid . '"');
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
      /* Developer:priyanka varanasi
       Desc : get image based on project id
    */
    public function selectimage(){
        if (func_num_args() > 0):
             $projetid = func_get_arg(0);
         try {
              $select = $this->select()
                ->from(array('p' => 'projects'))
                      ->where('p.project_id=?',$projetid);
                 
        $result = $this->getAdapter()->fetchRow($select);
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

  /* Developer:priyanka varanasi
       Desc : To delete project related info from every table based on project id
    */
public function deleteprojects(){
    
     if (func_num_args() > 0):
            $projectid = func_get_arg(0);
            try {
               
           $sql = 'DELETE projects, projectlikes,projectcomments,projectcommentlikes
 FROM projects
 LEFT JOIN  projectlikes ON projects.project_id = projectlikes.project_id
 LEFT JOIN  projectcomments ON projects.project_id = projectcomments.project_id
 LEFT JOIN  projectcommentlikes ON projects.project_id = projectcommentlikes.project_id
WHERE projects.project_id = '.$projectid.'';
              $responseid = $this->getAdapter()->query($sql);
             } catch (Exception $e) {
                throw new Exception($e);
            }
            return $projectid;
        else:
            throw new Exception('Argument Not Passed');
        endif; 

    
}
   
  public function countTotalProject(){
            if(func_num_args()>0){
                try {
                    $select = $this->select();
                    $result = $this->getAdapter()->fetchAll($select);
                } catch (Exception $exc) {
                    throw new Exception('Unable to update, exception occured'.$exc);
                }
                if($result){
                    return $result;
                }
            }else{
                 throw new Exception('Argument not passed');
            }
        }
      //dev: priyanka varanasi
//dev: TO get the total no of students 
 public function countTotalNoOfStudents(){
            if(func_num_args()>0){
               try {
                  $select = $this->select()
                              ->distinct()
                         ->from(array('p' =>'projects'),array('p.user_id'));
                    $result = $this->getAdapter()->fetchAll($select);
                } catch (Exception $exc) {
                    throw new Exception('Unable to update, exception occured'.$exc);
               }
               if($result){
                   return $result;
                }
          }else{
                 throw new Exception('Argument not passed');
           }
        }
     //dev: priyanka varanasi
//dev: TO get the total no of students for a respective class
        
   public function getAllStudentFromClass(){
     if (func_num_args() > 0){
             $clasid = func_get_arg(0); 
             
     try{
                    $select = $this->select()
                              ->distinct()
                              ->from(array('p' =>'projects'),array('p.user_id'))
                              ->where('p.class_id=?',$clasid);
                    $result = $this->getAdapter()->fetchAll($select);
                   
         
     } catch (Exception $ex) {

     }
      if($result){
                   return $result;
                 }
   }else{
                 throw new Exception('Argument not passed');
           }
}

//dev: priyanka varanasi
//dev: TO get the total no of projects for a respective class
   public function getAllClassProjects(){
     if (func_num_args() > 0){
             $clasid = func_get_arg(0); 
             
     try{
                    $select = $this->select()
                              ->distinct()
                              ->from(array('p' =>'projects'),array('p.project_id'))
                              ->where('p.class_id=?',$clasid);
                    $result = $this->getAdapter()->fetchAll($select);
                   
         
     } catch (Exception $ex) {

     }
      if($result){
                   return $result;
                 }
   }else{
                 throw new Exception('Argument not passed');
           }
}
}