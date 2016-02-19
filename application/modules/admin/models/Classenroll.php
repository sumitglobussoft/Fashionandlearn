<?php

class Admin_Model_Classenroll extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'classenroll';

    private function __clone() {
        
    }

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Classenroll();
        return self::$_instance;
    }

    /* Developer:Rakesh Jha
      Dated:14-03-15
      Desc: Get all refered student by  a teacher
     */
//    public function getReferedStudents(){
//          $select = $this->select()
//                ->setIntegrityCheck(false)
//                ->from(array('ce' => 'classenroll'))
//                ->joinLeft(array('p' => 'payment'), 'p.user_id = ce.user_id', array('p.subscription_id'))
//                ->joinLeft(array('tc'=>'teachingclasses'),'tc.class_id=ce.class_id',array('tc.user_id'))
//                ->where('ce.reference=?',1)
//                ->where('p.status=?','paid')
//                ->group('tc.user_id');
//        $result = $this->getAdapter()->fetchAll($select);
//
//     
//
//        if ($result) {
//            return $result;
//        }
//        
//    }
    
    /* Developer:Rakesh Jha
      Dated:14-03-15
      Desc: Get all enrolled student on aclass
     */
    
 public function getAllStudentFromClass(){
     if (func_num_args() > 0){
             $clasid = func_get_arg(0); 
            
             
     try{
                    $select = $this->select()
                            
                              ->from(array('c' =>'classenroll'),array("student_count" => "COUNT(c.user_id)",'c.class_id'))
                              ->where('c.class_id=?',$clasid);
                    $result = $this->getAdapter()->fetchRow($select);
                   
         
     } catch (Exception $ex) {

     }
      if($result){
                   return $result;
                 }
   }else{
                 throw new Exception('Argument not passed');
           }
}

//dev:priyanka varanasi 
//desc: get project count based on user_id
   public function getClasEnrollCount() {
   if (func_num_args() > 0){
             $user_id = func_get_arg(0); 
             
     try{
        $select = $this->select()
                ->from(array('p' => 'projects'),array('p.user_id'))
                ->where('p.user_id =?',$user_id);
        $result = $this->getAdapter()->fetchAll($select);
     }catch (Exception $ex) {

     }if(isset($result)){
             return count($result);
        }
 }
  else {
          
             throw new Exception('Argument not passed');
        }
   }
   
   
   /////////////////////code in new version ///////////////////////


//dev: priyanka varanasi
//desc: TO get total no of projects in fashion learn site
//date :24/9/2015

public function getTotalNoOFStudentsInFAshionlearn(){
  try {
                  $select = $this->select()
                             ->from(array('ce'=>'classenroll'))
                             ->setIntegrityCheck(false)
                             ->joinLeft(array('us' => 'users'),'us.user_id = ce.user_id')
                             ->joinLeft(array('tc' => 'teachingclasses'),'ce.class_id = tc.class_id')
                             ->where('tc.publish_status=?',0);
                    $result = $this->getAdapter()->fetchAll($select);
                } catch (Exception $exc) {
                    throw new Exception('Unable to update, exception occured'.$exc);
               }
               if($result){
                   return count($result);
                }  
    
}
   
   

}

?>
