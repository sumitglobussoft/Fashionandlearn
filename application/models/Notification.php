<?php

class Application_Model_Notification extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'notification';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Notification();
        return self::$_instance;
    }

    /* Developer:Namrata Singh
      Desc : Updation of notification table at checking and unchecking the checkbox
     */

    public function updateNotification($val, $uid, $dataname) {
        $uid1 = (int) $uid;
        $val1 = (int) $val;
        $where = "user_id = $uid1";
        $data = array($dataname => $val1);
        $result = $this->update($data, $where);

        return $result;
    }

    /* Developer:Namrata Singh
      Desc : inserting the userid in the notification table for every guest
     *       immediately after signup
     */

    public function insertNotification($data) {
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

    /* Developer:Jeyakumar N
      Desc : Getting all notification data from db for specific user
     *       
     */

    public function getUserNotificationData() {
//        echo $email;
//        echo $password;
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $select = $this->select()
                    ->where("user_id ='" . $userid . "'");

            
            $result = $this->getAdapter()->fetchRow($select);
           
            if ($result) {

                return $result;
            }
        }
    }

    public function notificationStatus() {
        if (func_num_args() > 0) {


            $userid = func_get_arg(0);
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
        public function seenNotificationStatus() {
        if (func_num_args() > 0) {


            $userid = func_get_arg(0);
            $data = array("seen_status" => 0);
            $update = $this->update($data, 'user_id =' . $userid);

            if ($update) {
//                print_r($update); die;
                return $update;
            } else {
//                print_r($update); die;
                return 'already seen';
//                throw new Exception('Argument Not Passed');
            }
        }
    }



}

?>