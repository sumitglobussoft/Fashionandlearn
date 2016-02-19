<?php

class Application_Model_Savednotifications extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'savednotifications';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Savednotifications();
        return self::$_instance;
    }

    public function seenNotificationStatus() {
        if (func_num_args() > 0) {


            $userid = func_get_arg(0);
//            print_r($userid); die;
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('sn' => 'savednotifications', array('sn.seen_status')))
                    ->where('sn.seen_status = ?', 0)
                    ->where('sn.user_id = ?', $userid);
            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
                
                return $result;
            } else {
//                return 'seen';
            }
        }
    }

    public function getNotification() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('sn' => 'savednotifications', array('sn.notifier_user_id', 'sn.notified_title_name')))
                    ->join(array('u' => 'users'), 'u.user_id = sn.notifier_user_id', array('u.first_name'))
                    ->join(array('um' => 'usersmeta'), 'um.user_id =sn.notifier_user_id', array('um.user_profile_pic'))
                    ->where('sn.user_id = ?', $user_id)
                   ->order(array('sn.date_time DESC'));
            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
                return $result;
            }
        }
    }
 public function notificationStatus() {
        if (func_num_args() > 0) {


            $userid = func_get_arg(0);
//            print_r($userid); die;
            $data = array("seen_status" => 1);
            $update = $this->update($data, 'user_id =' . $userid);
           
            if ($update) {
                return $update;
            } else {
                return 'already seen';
//                throw new Exception('Argument Not Passed');
            }
        }
    }
    public function savedNotifications() {
//            die('test');

        if (func_num_args() > 0) {
            $id = func_get_arg(0);
            $notify_type = func_get_arg(1);
            $notify_topic = func_get_arg(2);
            $notify_id = func_get_arg(3);
            $user_id = func_get_arg(4);
            $notified_title_name = func_get_arg(5);
            $date = new DateTime();
         
            $date->setTimezone(new DateTimeZone('Asia/Kolkata'));
            $notifydate = $date->format('Y-m-d h:i:s');
            $url=func_get_arg(6);
            $data = array(
                'user_id' => $id,
                'notify_type' => $notify_type,
                'notify_topic' => $notify_topic,
                'notify_topic_id' => $notify_id,
                'notifier_user_id' => $user_id,
                'date_time' => $notifydate,
                'notified_title_name' => $notified_title_name,
                'url' => $url
            );
           
           $result= $this->insert($data);
           
         
          
        }
    }


}

?>
