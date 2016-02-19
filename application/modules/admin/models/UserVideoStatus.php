<?php
class Admin_Model_UserVideoStatus extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'user_video_status';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_UserVideoStatus();
        return self::$_instance;
    }
     public function getSeenVideoCount() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
//            print_r($user_id); die;
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('uv' => 'user_video_status'), array("video seen count" => "COUNT(uv.class_id)", 'uv.class_id'))
                    ->joinLeft(array('tc' => 'teachingclasses'), 'tc.class_id=uv.class_id', array('tc.user_id'))
                    ->where('tc.user_id=?', $user_id);
          
            $result = $this->getAdapter()->fetchRow($select);
                   
            if ($result) {

                return $result;
            } else {
                
            }
        }
    }

    }
?>
