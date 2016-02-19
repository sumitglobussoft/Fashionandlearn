<?php

class Application_Model_ProjectLikes extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'projectlikes';

    private function __clone() {
        
    }

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_ProjectLikes();
        return self::$_instance;
    }

    
    
    /*
     * being used
     * 
     * 
     */
    
    public function projectlikes() {
        if (func_num_args() > 0) {
//        die('test');
               $user_id = func_get_arg(0);
            $class_id = func_get_arg(1);
            $project_id = func_get_arg(2);
            $like_status = func_get_arg(3);
            
            $select = $this->select()
                   
                    ->where("user_id=".$user_id."&&project_id=". $project_id);
            
            $result = $this->getAdapter()->fetchRow($select);
           
            if($result)
            {  
                 $data = array(
                'user_id' => $user_id,
                'class_id' => $class_id,
                'project_id' => $project_id,
                'like_status' => $like_status
            );
//           
           
             $this->update($data,"user_id=".$user_id."&&project_id=". $project_id);    
             
             return 0;
            }   else
            {
                  $data = array(
                'user_id' => $user_id,
                'class_id' => $class_id,
                'project_id' => $project_id,
                'like_status' => $like_status
            );
//           
          
               
                $this->insert($data);
                 return 1;
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

  /*
   * 
   * being used
   */

   public function getuserprojectlikes() {

        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $projectid = func_get_arg(1);

            $select = $this->select()
                    ->where("project_id = " . $projectid)
                    ->where("user_id = " . $userid)
                    ->where("like_status = 0");

            $result = $this->getAdapter()->fetchRow($select);
          
          
            if ($result) {
               
                return 0;
            } else {
               
          
                return 1;
              
            }
        }
    }


    public function myprojectlikes() {

        if (func_num_args() > 0) {

            $userid = func_get_arg(0);
            $classid = func_get_arg(1);
            $projectid = func_get_arg(2);
            $date = date('Y-m-d');
          
            // $result="UPDATE followers SET follow_status = 1 - follow_status where follow_id=2";
            $select = $this->select()
                    ->where("user_id= " . $userid)
                    ->where("project_id =" . $projectid);
            $result = $this->getAdapter()->fetchRow($select);

            if ($result) {
                $data = array("like_status" => 1 - $result['like_status'], "like_date" => $date);
                $where = array("user_id = " . $userid, "project_id = " . $projectid);
                $updateresult = $this->update($data, $where);
               
                return 1 - $result['like_status'];
            } else {

                $data = array("user_id" => $userid, "class_id" => $classid, "project_id" => $projectid, "like_status" => 0, "like_date" => $date);
                $response = $this->insert($data);
                
                return 0;
            }







            //$responseId = $this->update($data, "user_id= ".$where);
            // print_r($update);die;
        }
    }
    
    /*
     * being used
     * 
     * 
     */
    
     public function getprojectlikes() {

        if (func_num_args() > 0) {
            $projectid = func_get_arg(0);
            $select = $this->select()
                    ->distinct("user_id")
                    ->where("project_id = " . $projectid)
                    ->where("like_status = 0");

            $result = $this->getAdapter()->fetchall($select);
            if ($result) {
//                echo '<pre>';  print_r($result); die;
                return count($result);
            }
        }
    }
     public  function getmostpoular(){
        if (func_num_args() > 0) {
            $project_id=  func_get_arg(0);
            
        }
        $select = $this->select()
                    ->where('project_id=?',$project_id) 
                    ->where('like_date <=?',date('d/m/Y', strtotime("-1 week")))
                    ->where('like_status =?', '0');
        //echo "<pre>";print($select);die();
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return count($result);
        }
    }
    
    
    
    
    
    
    
    

}

?>
