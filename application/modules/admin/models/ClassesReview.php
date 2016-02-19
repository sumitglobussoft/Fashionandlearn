<?php

class Admin_Model_ClassesReview extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'classesreview';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_ClassesReview();
        return self::$_instance;
    }

    public function getSatisfactionPercentage() {
        if (func_num_args() > 0) {
            $user_id=  func_get_arg(0);
//            echo "UserID:".$user_id;
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('cr' => 'classesreview'),array("review_count" => "COUNT(cr.recommend_class)"))
                    ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id = cr.class_id')
                    ->where('tc.user_id=?',$user_id);
            $result = $this->getAdapter()->fetchRow($select);
            
                    
            if ($result) {
               $totalcount= $result['review_count'];
                 $select1 = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('cr' => 'classesreview'),array("like_review_count" => "COUNT(cr.recommend_class)"))
                    ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id = cr.class_id')
                    ->where('tc.user_id=?',$user_id)
                    ->where('cr.recommend_class=0');
            $result1 = $this->getAdapter()->fetchRow($select1);
            if($result1){
                $like_review_count=$result1['like_review_count'];
                
                if($totalcount>0){
                $satisfied_percentage=round(($like_review_count*100)/$totalcount);
                }  
 else {
     $satisfied_percentage=0;
 }
                return $satisfied_percentage;
                
                
                }
            }
        }
        
    }

}

?>