<?php

class Application_Model_uservideostatus extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'user_video_status';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_uservideostatus();
        return self::$_instance;
    }

    public function insertUserVideoStatus($data) {

if (func_num_args() > 0) {
            $data= func_get_arg(0);
           
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

   
    public function UserVideoStatusisexist($data) {

        $user_id = $data['user_id'];
        $class_id = $data['class_id'];
        $video_id = $data['video_id'];
        try {
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('tcv' => 'user_video_status'), array('tcv.class_id', 'tcv.user_id'))
                    ->where('tcv.class_id = ?', $class_id)
                    ->where('tcv.user_id = ?', $user_id)
                    ->where('tcv.video_id = ?', $video_id)
                    ->where('tcv.view_status = ?', 0);
            $result = $this->getAdapter()->fetchAll($select);
            $result = count($result);
        } catch (Exception $e) {
            throw new Exception('Unable To Insert Exception Occured :' . $e);
        }

        if ($result) {
            return $result;
        }
    }

    public function getvideoscount() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $class_id = func_get_arg(1);


            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('tu' => 'teachingclassvideo'), array('tu.class_id', 'tu.user_id'))
                        ->where('tu.class_id = ?', $class_id);
                $result = $this->getAdapter()->fetchAll($select);
                $result = count($result);
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

    public function getviewedvideoscount() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $class_id = func_get_arg(1);


            try {
                $select = $this->select()
                        ->where('class_id = ?', $class_id)
                        ->where('user_id = ?', $user_id)
                        ->where('view_status = ?', 0);
                $result = $this->getAdapter()->fetchAll($select);
                //print_r($result);die;
                $result = count($result);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            
                return $result;
            
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function userVideoSeen() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $class_id = func_get_arg(1);
            $video_id = func_get_arg(2);
            //$class_unit_id = func_get_arg(3);
            try {
                $select = $this->select()
                        ->where('class_id = ?', $class_id)
                        ->where('user_id = ?', $user_id)
                        ->where('video_id = ?', $video_id);
                 
                        
                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return $result;
            }
        }
    }
     /**
     * Developer : Ram
     * Date : 2/06/2015
     * Description : Show user visits statics/     
     */
     public function userVisitsbyClassIds()
    { 
         if (func_num_args() > 0) {
            $classIds = func_get_arg(0);
            $year = func_get_arg(1);
            $ids = join(',', $classIds);
     $select = "SELECT COUNT(e.status_id) AS total, m.month
                FROM (
                      SELECT 'JAN' AS MONTH
                      UNION SELECT 'FEB' AS MONTH
                      UNION SELECT 'MAR' AS MONTH
                      UNION SELECT 'APR' AS MONTH
                      UNION SELECT 'MAY' AS MONTH
                      UNION SELECT 'JUN' AS MONTH
                      UNION SELECT 'JUL' AS MONTH
                      UNION SELECT 'AUG' AS MONTH
                      UNION SELECT 'SEP' AS MONTH
                      UNION SELECT 'OCT' AS MONTH
                      UNION SELECT 'NOV' AS MONTH
                      UNION SELECT 'DEC' AS MONTH
                     ) AS m
               LEFT JOIN user_video_status e ON MONTH(STR_TO_DATE(CONCAT(m.month, ' $year'),'%M %Y')) = MONTH(e.watched_date) AND YEAR(e.watched_date) = '$year' WHERE e.class_id IN ($ids) AND (e.view_status != 2)
               GROUP BY m.month
               ORDER BY 1+1";

        $result = $this->getAdapter()->fetchAll($select);        
        return $result;
    }
    }
    /**
     * Developer : Ram
     * Date : 3/06/2015
     * Description : Show class visit statics/     
     */
    public function getVisitbyClassId(){
            if (func_num_args() > 0) {
            $classid = func_get_arg(0);
            try {
                 
               $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('ur' => 'user_video_status'), array("visit_count" => "COUNT(*)"))
                            ->where('ur.class_id = ?',$classid)
                            ->where('ur.view_status != 2');
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
    

}