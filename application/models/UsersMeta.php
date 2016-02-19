<?php

class Application_Model_UsersMeta extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'usersmeta';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_UsersMeta();
        return self::$_instance;
    }

    /* Developer:Namrata Singh
      Desc : inserting data's in the UserMeta table
     */

    public function insertUsermeta($data) {

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

    /* Developer:Namrata Singh
      Desc : updating the usermeta table when user make any changes
     */

    public function editUsermeta() {


        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where = func_get_arg(1);

            try {
                $responseId = $this->update($data, 'user_id =' . $where);
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

    /*
     * Dev. Namrata Singh
     * Date:12/1/2015
     * Description: Get the value from Database based on UserId 
     * being used
     */

    public function getUserMetaDetail() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {

                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('um' => 'usersmeta'))
                        ->join(array('u' => 'users'), 'um.user_id = u.user_id', array('u.first_name', 'u.last_name'))
                        ->where('um.user_id = ?', $user_id);
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

    /**
      Developer: Namrata Singh
     * Date: 12/1/15
      Desc: Update the email,city,zip and timezone of user on account action using Join
     * */
    public function updateUsermeta($data, $user_id) {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $user_id = func_get_arg(1);
            $where = " user_id = " . $user_id;

            $result = $this->update($data, $where);
            if ($result) {
                return $result;
            }
        }
    }

    public function getUsermeta() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);

            //print_r($data);die;
            $user_id = func_get_arg(1);
            $where = "user_id= " . $user_id;

            try {
                $response = $this->update($data, $where);
                // print_r($responseId);die;
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($response) {
                return $response;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function updateEamil() {

        if (func_num_args() > 0) {
            $paypalemail = func_get_arg(0);
            $userid = func_get_arg(1);


            $where = "user_id= " . $userid;

            try {
                $response = $this->update(array("paypal_email" => $paypalemail), $where);
                // print_r($responseId);die;
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($response) {
                return $response;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    //dev:priyanka varanasi
    //desc: to get interested catgories 
    public function getinterestedcategories() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                        ->from($this)
                        ->where('user_id = ?', $user_id);
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

    /*
     * Dev. Namrata Singh
     * Date:9/4/2015
     * Description: Get the value for step 1 and step 2 if choose once 
     */

    public function getStepsDetail() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this, 'interested_categories')
                        ->where('user_id = ?', $user_id);

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

    /*
     * Dev. abhishek m
     * Date:15/5/2015
     * Description: get top six points holders
     * being used
     */

    public function gettopscores() {


        try {

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'usersmeta'))
                    ->join(array('u' => 'users'), 'u.user_id = l.user_id', array("u.first_name", "u.last_name"))
                    ->order("l.points desc")
                    ->limit(9);

            $result = $this->getAdapter()->fetchAll($select);
        } catch (Exception $e) {
            throw new Exception('Error :' . $e);
        }

        if ($result) {
            return $result;
        }
    }

    /*
     * Dev. abhishek m
     * Date:15/5/2015
     * Description: get top six points holders
     * being used
     */

    public function getalltopscores() {


        try {

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'usersmeta'))
                    ->join(array('u' => 'users'), 'u.user_id = l.user_id', array("u.first_name", "u.last_name"))
                    ->order("l.points desc")
                    ->limit(20);

            $result = $this->getAdapter()->fetchAll($select);
        } catch (Exception $e) {
            throw new Exception('Error :' . $e);
        }

        if ($result) {
            return $result;
        }
    }

    public function updatepoints() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $points = func_get_arg(1);
            $gems = func_get_arg(2);
            $data = array("points" => new Zend_Db_Expr('points + ' . $points), "gems" => new Zend_Db_Expr('gems + ' . $gems));

            try {
                $result = $this->update($data, 'user_id =' . $user_id);
            } catch (Exception $e) {
                echo $e;
            }
            if ($result) {
                return $result;
            }
        }
    }

    public function updatelevel() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            $data = array("level" => new Zend_Db_Expr('level + 1'));

            try {
                $result = $this->update($data, 'user_id =' . $user_id);
            } catch (Exception $e) {
                echo $e;
            }
            if ($result) {
                return $result;
            }
        }
    }

    public function getrank() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $select = "SELECT uo.user_id,user_profile_pic,points,\n"
                    . " (\n"
                    . " SELECT COUNT(*)\n"
                    . " FROM usersmeta ui\n"
                    . " WHERE ui.points >= uo.points\n"
                    . " ) AS rank\n"
                    . "FROM usersmeta uo\n"
                    . "WHERE user_id = " . $user_id;


            $result = $this->getAdapter()->fetchRow($select);

            if ($result)
                return $result;
        }
    }

    public function shop() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $gems = func_get_arg(1);
            $data = array("gems" => new Zend_Db_Expr('gems - ' . $gems));

            try {
                $result = $this->update($data, 'user_id =' . $user_id);
            } catch (Exception $e) {
                echo $e;
            }
            if ($result) {
                return $result;
            }
        }
    }

}

?>
