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


}

?>
