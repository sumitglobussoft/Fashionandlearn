<?php

class Application_Model_ProjectCommentLikes extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'projectcommentlikes';

    private function __clone() {
        
    }

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_ProjectCommentLikes();
        return self::$_instance;
    }

    public function projectcommentlikes() {

        if (func_num_args() > 0) {

            $userid = func_get_arg(0);
            $classid = func_get_arg(1);
            $projectid = func_get_arg(2);
            $commentid = func_get_arg(3);
            $date = new DateTime();

         

            $date = gmdate('Y-m-d h:i:s',  time());
            // $result="UPDATE followers SET follow_status = 1 - follow_status where follow_id=2";
            $select = $this->select()
                    ->where("user_id= " . $userid)
                    ->where("project_comment_id =" . $commentid);
            $result = $this->getAdapter()->fetchRow($select);
           

            if ($result) {
                $data = array("like_status" => 1 - $result['like_status'], "like_date" => $date);
                $where = array("user_id = " . $userid, "project_comment_id = " . $commentid);
                $updateresult[0] = $this->update($data, $where);
                $updateresult[1] = "updated";
                return 0;
            } else {

                $data = array("user_id" => $userid, "class_id" => $classid, "project_id" => $projectid, "project_comment_id" => $commentid, "like_status" => 0, "like_date" => $date);
                $response[0] = $this->insert($data);
                $response[1] = "inserted";
                return 1;
            }







            //$responseId = $this->update($data, "user_id= ".$where);
            // print_r($update);die;
        }
    }

    public function getprojectcommentlikes() {

        if (func_num_args() > 0) {
            $commentid = func_get_arg(0);

            $select = $this->select()
                    ->from("projectcommentlikes", array("num" => "COUNT(*)"))
                    ->where("project_comment_id = " . $commentid)
                    ->where("like_status = 0");

            $result = $this->getAdapter()->fetchRow($select);

            if ($result) {
                return $result;
            }
        }
    }

    public function getuserprojectcommentlikes() {

        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $commentid = func_get_arg(1);

            $select = $this->select()
                    ->where("project_comment_id = " . $commentid)
                    ->where("user_id = " . $userid)
                    ->where("like_status = 0");

            $result = $this->getAdapter()->fetchAll($select);

            if ($result) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    public function projectdislikes() {
        if (func_num_args() > 0):
            $user_id = func_get_arg(0);
            $project_id = func_get_arg(1);

            try {
                $db = Zend_Db_Table::getDefaultAdapter();
                $where = (array('user_id = ?' => $user_id, 'project_id = ?' => $project_id));
                $db->delete('projectlikes', $where);
            } catch (Exception $e) {
                throw new Exception($e);
            }
            return $user_id;
        else:
            throw new Exception('Argument Not Passed');
        endif;
    }

    public function getall() {

        if (func_num_args() > 0) {
            $projectid = func_get_arg(0);

            $select = $this->select()
                    ->from("projectlikes", array("num" => "COUNT(*)"))
                    ->where("project_id = " . $projectid)
                    ->where("like_status=0");

            $result = $this->getAdapter()->fetchRow($select);

            if ($result) {
                return $result;
            }
        }
    }
    
    
  
    

}

?>
