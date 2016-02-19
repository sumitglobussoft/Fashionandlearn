<?php

class Application_Model_ClassEnroll extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'classenroll';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_ClassEnroll();
        return self::$_instance;
    }

    public function getEnrollMember() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
//            print_r($userid); die;
            try {
                $select = $this->select()
                  ->from(array('ce' => 'classenroll'), array('ce.class_id'))
                  ->setIntegrityCheck(false)
                  ->joinleft(array('tc' => 'teachingclasses'), 'tc.class_id = ce.class_id', array('tc.class_title', 'tc.category_id', 'tc.user_id'))
//                        ->joinleft(array('tcv' => 'teachingclassvideo'), 'tc.class_id = tcv.class_id', array('video_thumb_url', 'class_video_url'))
                  ->joinleft(array('c' => 'category'), 'tc.category_id = c.category_id', array('c.category_name'))
                  ->joinleft(array('u' => 'users'), 'tc.user_id = u.user_id', array('u.first_name', 'u.last_name'))
                  ->joinleft(array('um' => 'usersmeta'), 'tc.user_id = um.user_id', array('um.user_profile_pic', 'um.user_headline'))
                  ->where('ce.user_id = ?', $userid);

                $result = $this->getAdapter()->fetchAll($select);
               
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
               
                $i = 0;
                foreach ($result as $val) {
                    if(isset($val["category_id"])){

                    $select = $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('tcv' => 'teachingclassvideo'))
                      ->joinLeft(array('cat' => 'category'), 'cat.category_id = ' . $val["category_id"], array('category_name'))
                      ->where('tcv.class_id= ?', $val['class_id']);
                    $resultvideo = $this->getAdapter()->fetchRow($select);

                    $result[$i]['video_thumb_url'] = $resultvideo['video_thumb_url'];
                    $result[$i]['category_name'] = $resultvideo['category_name'];

                    $i++;
                    }else{
                     $select = $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('tcv' => 'teachingclassvideo'))
                      //->joinLeft(array('cat' => 'category'), 'cat.category_id = ' . $val["category_id"], array('category_name'))
                      ->where('tcv.class_id= ?', $val['class_id']);
                    $resultvideo = $this->getAdapter()->fetchRow($select);

                    $result[$i]['video_thumb_url'] = $resultvideo['video_thumb_url'];
                    $result[$i]['category_name'] = $resultvideo['category_name'];

                    $i++;   
                    }
                }

                $i = 0;
                foreach ($result as $val) {
                    $select = $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('cr' => 'classenroll'), array("stud_count" => "COUNT(*)"))
                      ->where('cr.class_id = ?', $val['class_id']);
                    $resultcount = $this->getAdapter()->fetchRow($select);
                    $result[$i]['stud_count'] = $resultcount['stud_count'];
                    $i++;
                }


                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getEnrollUserClasses() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                  ->setIntegrityCheck(false)
                  ->from(array('cr' => 'classenroll'), array('cr.class_id','cr.percentage'))
                  ->join(array('cl' => 'teachingclasses'), 'cl.class_id = cr.class_id')
                  ->join(array('c' => 'category'),'cl.category_id = c.category_id',array('c.category_name'))
                  ->join(array('um' => 'usersmeta'), 'um.user_id = cl.user_id', array('um.user_profile_pic', 'um.user_id','um.user_headline'))
                  ->join(array('u' => 'users'), 'u.user_id = cl.user_id', array('u.first_name', 'u.last_name','u.premium_status'))
                 
                  ->order('cl.class_created_date DESC')
                  ->where('cr.user_id = ?', $user_id);
                  
                $result = $this->getAdapter()->fetchAll($select);
               
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                $count = 0;
                foreach ($result as $val) {
                    $select1 = $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('tcv' => 'teachingclassvideo'))
                      ->where('tcv.class_id=?', $val['class_id']);
                    $result1 = $this->getAdapter()->fetchRow($select1);

                    $result[$count]['video_thumb_url'] = $result1['video_thumb_url'];
                    $result[$count]['cover_image'] = $result1['cover_image'];
//                    echo '<pre>';     print_r($result); die;
                    $select2 = $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('cr' => 'classenroll'), array("stud_count" => "COUNT(*)"))
                      ->where('cr.class_id = ?', $val['class_id']);
                    $resultcount = $this->getAdapter()->fetchRow($select2);
                    $result[$count]['stud_count'] = $resultcount['stud_count'];
                    $count++;
                }
//                echo '<pre>';   print_r($result); die;
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    public function getEnrollUsercClasses() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                  ->setIntegrityCheck(false)
                  ->from(array('cr' => 'classenroll'), array('cr.class_id','cr.percentage'))
                  ->join(array('cl' => 'teachingclasses'), 'cl.class_id = cr.class_id')
                  ->join(array('c' => 'category'),'cl.category_id = c.category_id',array('c.category_name'))
                  ->join(array('um' => 'usersmeta'), 'um.user_id = cl.user_id', array('um.user_profile_pic', 'um.user_id','um.user_headline'))
                  ->join(array('u' => 'users'), 'u.user_id = cl.user_id', array('u.first_name', 'u.last_name','u.premium_status'))
                 
                  ->order('cl.class_created_date DESC')
                  ->where('cr.user_id = ?', $user_id)
                       ->where('cr.percentage = 100');
                $result = $this->getAdapter()->fetchAll($select);
               
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                $count = 0;
                foreach ($result as $val) {
                    $select1 = $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('tcv' => 'teachingclassvideo'))
                      ->where('tcv.class_id=?', $val['class_id']);
                    $result1 = $this->getAdapter()->fetchRow($select1);

                    $result[$count]['video_thumb_url'] = $result1['video_thumb_url'];
                    $result[$count]['cover_image'] = $result1['cover_image'];
//                    echo '<pre>';     print_r($result); die;
                    $select2 = $this->select()
                      ->setIntegrityCheck(false)
                      ->from(array('cr' => 'classenroll'), array("stud_count" => "COUNT(*)"))
                      ->where('cr.class_id = ?', $val['class_id']);
                    $resultcount = $this->getAdapter()->fetchRow($select2);
                    $result[$count]['stud_count'] = $resultcount['stud_count'];
                    $count++;
                }
//                echo '<pre>';   print_r($result); die;
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getEnrollUserProject() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                  ->setIntegrityCheck(false)
                  ->from(array('cr' => 'classenroll'), array('cr.class_id', 'cr.user_id'))
                 
                  ->join(array('p' => 'projects'), 'p.class_id = cr.class_id')
                  ->join(array('um' => 'usersmeta'), 'p.user_id = um.user_id', array('um.user_profile_pic'))
                  ->join(array('u' => 'users'), 'u.user_id = p.user_id', array('u.first_name', 'u.last_name','u.premium_status'))
                  ->where('cr.user_id = ?', $user_id);


                $result = $this->getAdapter()->fetchAll($select);
            //  echo "<pre>"; print_r($result); die;
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

    public function getEnrollUserRecentProject() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                  ->setIntegrityCheck(false)
                  ->from(array('cr' => 'classenroll'))
                  ->join(array('p' => 'projects'), 'p.class_id = cr.class_id')
                  ->join(array('um' => 'usersmeta'), 'p.user_id = um.user_id', array('um.user_profile_pic'))
                  ->join(array('u' => 'users'), 'u.user_id = p.user_id', array('u.first_name', 'u.last_name'))
                  ->where('cr.user_id = ?', $user_id)
                  ->order('p.project_created_date DESC');
                $result = $this->getAdapter()->fetchAll($select);
//              echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
    
    public function getEnrollUserLikeProject() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {

                $select = $this->select()
                  ->setIntegrityCheck(false)
                  ->from(array('cr' => 'classenroll'))
                  ->join(array('p' => 'projects'), 'p.class_id = cr.class_id')
                  ->join(array('um' => 'usersmeta'), 'p.user_id = um.user_id', array('um.user_profile_pic'))
                  ->join(array('u' => 'users'), 'u.user_id = p.user_id', array('u.first_name', 'u.last_name'))
                 
                  ->where('cr.user_id = ?', $user_id);


                $result = $this->getAdapter()->fetchAll($select);
//              echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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
    
    public function getEnrollUserDiscussion() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                  ->setIntegrityCheck(false)
                  ->from(array('cr' => 'classenroll'))
                  ->join(array('d' => 'classdiscussions'), 'd.class_id = cr.class_id')
                  ->join(array('um' => 'usersmeta'), 'd.user_id = um.user_id', array('um.user_profile_pic'))
                  ->join(array('u' => 'users'), 'u.user_id = d.user_id', array('u.first_name', 'u.last_name'))
                  
                  ->where('cr.user_id = ?', $user_id)
                  ->order('enrolled_date DESC');


                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die;
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

    public function getEnrollUserRecentDiscussion() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                  ->setIntegrityCheck(false)
                  ->from(array('cr' => 'classenroll'))
                  ->join(array('d' => 'classdiscussions'), 'd.class_id = cr.class_id')
                  ->join(array('um' => 'usersmeta'), 'd.user_id = um.user_id', array('um.user_profile_pic'))
                  ->join(array('u' => 'users'), 'u.user_id = d.user_id', array('u.first_name', 'u.last_name'))
                  
                  ->where('cr.user_id = ?', $user_id)
                  ->order('d.discussed_date DESC');
                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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

    public function getEnrollUserLikeDiscussion() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {

                $select = $this->select()
                  ->setIntegrityCheck(false)
                  ->from(array('cr' => 'classenroll'))
                  ->join(array('d' => 'classdiscussions'), 'd.class_id = cr.class_id')
                  ->join(array('um' => 'usersmeta'), 'd.user_id = um.user_id', array('um.user_profile_pic'))
                  ->join(array('u' => 'users'), 'u.user_id = d.user_id', array('u.first_name', 'u.last_name'))
                  ->where('cr.user_id = ?', $user_id);


                $result = $this->getAdapter()->fetchAll($select);
//               echo "<pre>"; print_r($result); echo "</pre>"; die('123');
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

    public function insertClassEnroll() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $class_id = func_get_arg(1);

            $enrolldate = $date=gmdate('Y-m-d H:i:s', time());
            $data = array('user_id' => $user_id, 'class_id' => $class_id, 'enrolled_date' => $enrolldate);
            try {
                $result = $this->insert($data);
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

    public function getEnrollClass() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $classid = func_get_arg(1);
            try {
                $select = $this->select()
                  ->where('user_id = ?', $userid)
                  ->where('class_id = ?', $classid);

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
    
    public function getStudentsCount(){
            if (func_num_args() > 0) {
            $classid = func_get_arg(0);
            try {
                 
               $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('cr' => 'classenroll'), array("stud_count" => "COUNT(*)"))
                            ->where('cr.class_id = ?',$classid);
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
     * Developer : Ram
     * Date : 27/05/2015
     * Description : Show user statics/     
     */
     public function userEnrollClasses()
    { 
         if (func_num_args() > 0) {
            $year = func_get_arg(0);
            $user_id = func_get_arg(1);
     $select = "SELECT COUNT(e.enroll_id) AS total, m.month
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
               LEFT JOIN classenroll e ON MONTH(STR_TO_DATE(CONCAT(m.month, ' $year'),'%M %Y')) = MONTH(e.enrolled_date) AND YEAR(e.enrolled_date) = '$year' WHERE e.user_id = '$user_id'
               GROUP BY m.month
               ORDER BY 1+1";

        $result = $this->getAdapter()->fetchAll($select);        
        return $result;
    }
    }
    /**
     * Developer : Ram
     * Date : 27/05/2015
     * Description : Show satudent count of given classes/     
     */
     public function getStudentCountByClassIds(){
            if (func_num_args() > 0) {
            $classid = func_get_arg(0);
            try {
                 
               $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('cr' => 'classenroll'), array("stud_count" => "COUNT(*)"))
                            ->where('cr.class_id IN (?)',$classid);
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
    public function getlastweekenrolleddetails(){
            if (func_num_args() > 0) {
            $classid = func_get_arg(0);
            try {
                   $date1 = gmdate('Y-m-d H:i:s', time());
                    $date = strtotime($date1 . '-1 week');
                    $date = date('Y-m-d H:i:s', $date);

        $select = $this->select()
                ->where("enrolled_date>'$date'")
                ->where("enrolled_date<='$date1'")
                ->where("class_id ='$classid'");
                  $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {
                return count($result);
            }
        } else {
            throw new Exception('Argument Not Passed');
        }   
    }

    public function updateClassViewedPercentage(){
        
         if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where = func_get_arg(1);
            try {
                $update = $this->update($data, 'class_id =' . $where);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($update) {
                return $update;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
    
    
   /*
    * abhishek m
    * 
    * 
    */ 
    
    
    
    
    public function getdaysstatistics() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

        $date1 = gmdate('Y-m-d', time());
      
        $date = strtotime($date1 . '-1 week');
          $date = date('Y-m-d', $date);
       

        $select = $this->select()
                   ->setIntegrityCheck(false)
                  ->from(array('s' => 'sitestatistics'))
                ->where("date>='$date'")
                ->where("date<='$date1'")
                ->order("date asc");

        $result = $this->getAdapter()->fetchAll($select);

         $select = $this->select()
                    ->setIntegrityCheck(false)
                  ->from(array('t' => 'teachingclasses'),array("class_id"))
                   ->where('t.user_id = ?', $user_id);
            $results = $this->getAdapter()->fetchAll($select);
$classes=  array_column($results, "class_id");

        $data = array();
        $data["labels"] = array();
        $data["enrolled"] = array();
        $data["total"] = array();
        foreach ($result as $value) {

            $stamp = date("D", strtotime($value["date"]));
                     
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('e' => 'classenroll'),array('count(*) as count'))
                    ->where('cast(e.enrolled_date as DATE) = "'.$value["date"].' "')
                    ->where('e.class_id in (?)',$classes);
       
                     $results = $this->getAdapter()->fetchRow($select);
//            $select = $this->select()
//                    ->setIntegrityCheck(false)
//                     ->from(array('e' => 'classenroll'),array('count(*) as count'))
//                    ->where('p.status="trialing" and p.current_period_start="'.$value["date"].'"');
//                     $resultss = $this->getAdapter()->fetchRow($select);         
                    
       
            
            array_push($data["labels"], $stamp);
            array_push($data["enrolled"], $results["count"]);
            array_push($data["total"], 0);
        }
     
      
        if ($data) {
            
            return $data;
          
        }
        }
    }

    public function getweekssstatistics() {

        $date = gmdate('Y-m-d', time());

//          $date = strtotime($date . ' +1 week');
//           $date=date('Y-m-d', $date);
//           



        $data["labels"] = array();
        $data["enrolled"] = array();
       
        $data["total"] = array();

        for ($i = 1; $i <= 5; $i++) {
            $date = strtotime($date . '-' . $i . ' week');
            $date = date('Y-m-d', $date);

            $date1 = strtotime($date . ' +1 week');
            $date1 = date('Y-m-d', $date1);


            
             $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('e' => 'classenroll'),array('count(*) as count'))
                    ->where('cast(e.enrolled_date as DATE)>"'.$date.'" and cast(e.enrolled_date as DATE)<="'.$date1.'"');
                     $results = $this->getAdapter()->fetchRow($select);
     

            array_push($data["labels"], "week " . $i);
            array_push($data["enrolled"], $results["count"]);
            array_push($data["total"], (0));
        }

        $data["enrolled"] = array_reverse($data["enrolled"]);  
        $data["total"] = array_reverse($data["total"]);

        if ($data) {
           
            return $data;
        }
    }
//
    public function getmonthsstatistics() {

        $datet = gmdate('Y-m-d', time());
 $month= date("m", strtotime($datet));
 $year=date("Y", strtotime($datet));


//           $date = strtotime($date . ' +1 week');
//           $date=date('Y-m-d', $date);
//           



        $data["labels"] = array();
        $data["enrolled"] = array();
        $data["total"] = array();
      
        for ($i = 1; $i <= 6; $i++) {
        
            if($month==0)
            {
                $date = "$year-01-01";
                $month=12;
                $year--;
                $date1 = "$year-12-01";
                 
               
            }
            else
            {
               $month++;
                 $date1 = "$year-$month-01";
                  $month--;
                  $date = "$year-$month-01";
                
            }    
                   $month--; 


             $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('e' => 'classenroll'),array('count(*) as count'))
                    ->where('cast(e.enrolled_date as DATE)>="'.$date.'" and cast(e.enrolled_date as DATE)<"'.$date1.'"');
                     $results = $this->getAdapter()->fetchRow($select);
    
            $stamp = date("M", strtotime($date));

            array_push($data["labels"], $stamp);
            array_push($data["enrolled"], $results["count"]);
            array_push($data["total"], 0);
        }
    // die();
        $data["labels"] = array_reverse($data["labels"]);
        $data["enrolled"] = array_reverse($data["enrolled"]);
        $data["total"] = array_reverse($data["total"]);

        if ($data) {
            return $data;
        }
    }


    
    
    
    
    
    
    
    

}
