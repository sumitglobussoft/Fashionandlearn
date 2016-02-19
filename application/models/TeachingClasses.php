<?php

class Application_Model_TeachingClasses extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teachingclasses';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_TeachingClasses();
        return self::$_instance;
    }

    /* Developer:Namrata Singh
      Desc : Getting all the sub-categories information based on category id
     */

    public function getSubCategory() {
        if (func_num_args() > 0) {
            $category_id = func_get_arg(0);

            if ($category_id == 0) {
                try {
                    $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('l' => 'teachingclasses'))
                            ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                            ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'));
                    $result = $this->getAdapter()->fetchAll($select);
                } catch (Exception $e) {
                    throw new Exception('Unable To Insert Exception Occured :' . $e);
                }
            } else {
                try {
                    $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('l' => 'teachingclasses'))
                            ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                            ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
                            ->where('l.category_id = ?', $category_id);
                    $result = $this->getAdapter()->fetchAll($select);
                } catch (Exception $e) {
                    throw new Exception('Unable To Insert Exception Occured :' . $e);
                }
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

                    $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('cr' => 'classenroll'), array("stud_count" => "COUNT(*)"))
                            ->where('cr.class_id = ?', $val['class_id']);
                    $resultcount = $this->getAdapter()->fetchRow($select);
                    $result[$count]['stud_count'] = $resultcount['stud_count'];
                    $count++;
                }
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getClassById() {
        if (func_num_args() > 0) {
            $class_id = func_get_arg(0);

            try {
                  $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('l' => 'teachingclasses'))
                        ->joinLeft(array('u' => 'users'), 'u.user_id = l.user_id', array('u.first_name','u.last_name'))
                        ->joinLeft(array('umeta' => 'usersmeta'), 'umeta.user_id = l.user_id', array('umeta.user_profile_pic'))
                        ->where('l.class_id = ?', $class_id);

                $result = $this->getAdapter()->fetchRow($select);
             
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }
//echo "<pre>"; print_r($result); die('00');
            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getClassByUserid() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {
                $select = $this->select()
                        ->where("user_id=?", $user_id);

                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return count($result);
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
    public function getunassignedClassByUserid() {
            try {
                $select = $this->select();

                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return $result;
            }
        
    }
    public function updateunassignedClassByUserid() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $classid = func_get_arg(1);
            $assignuserid = func_get_arg(2);
                
            try {
                $select = $this->select()
                        ->where("class_id=?", $classid);

                $result = $this->getAdapter()->fetchAll($select);
                if($result){
                     $data = array("user_id" => $assignuserid);
                    $where = "class_id =" . $classid;
                    $result123 = $this->update($data, $where);
                    if($result123){
                        return $result123;
                    }else{
                        return 0;
                    }
                }else{
                    return 0;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /* Developer:Namrata Singh
      Desc : Getting all the data based on classtags

      Modified: added Class unit id
      Developer:rakesh jha
      Dated:25/03/2015

     */

    public function getClassTags() {
        if (func_num_args() > 0) {
            $classtag = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('tc' => 'teachingclasses'), array('tc.class_tags', 'tc.class_title', 'tc.class_id', 'tc.user_id'))
                        ->setIntegrityCheck(false)
                        ->join(array('tcv' => 'teachingclassvideo'), 'tc.class_id = tcv.class_id', array('tcv.video_thumb_url', 'tcv.class_video_url'))
                        ->join(array('u' => 'users'), 'u.user_id = tc.user_id', array('u.first_name', 'u.last_name'))
                        ->join(array('um' => 'usersmeta'), 'u.user_id = um.user_id', array('um.user_profile_pic'))
                        ->where('class_tags LIKE ?', '%' . $classtag . '%');

                $result = $this->getAdapter()->fetchAll($select);
                //    echo "<pre>"; print_r($result); echo "</pre>"; die(' 123');
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

    //dev:priyanka varanasi
    //desc:to display category related videos
    public function getcategoryvideos() {
        if (func_num_args() > 0) {
            $category_id = func_get_arg(0);
            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('l' => 'teachingclasses'), array('l.class_id', 'class_title'))
//                        ->join(array('tcv' => 'teachingclassvideo'), 'l.class_id = tcv.class_id', array('tcv.video_id', 'tcv.video_thumb_url', 'tcv.class_video_url', 'tcv.user_id', 'tcv.class_unit_id'))
                        ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                        ->joinLeft(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
                        ->where('l.category_id = ?', $category_id);
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
                    $count++;
                }
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*
      Developer:Rakesh Jha
      Date created : 10/2/15
      Disc:get the number of classes teaches by teacher

     */

    Public function getCountOfClasses() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'teachingclasses'), array('class_id','class_title'))
                    ->where('l.user_id = ?', $user_id);
            $result = $this->getAdapter()->fetchRow($select);
            if ($result) {
//                print_r($result); die;
                return $result;
            }
        }
    }

    Public function getCourcesDetails() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('tc' => 'teachingclasses'), array('tc.class_title'))
                    ->joinLeft(array('c' => 'category'), 'tc.category_id = c.category_id', array('c.category_name'))
                    ->where('user_id = ?', $user_id);
            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
                return $result;
            }
        }
    }

    /* Developer:Namrata Singh
      Desc : Getting all the details of classes teached
     */

    public function getTeachClasses() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);

            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('tc' => 'teachingclasses'))
                        ->join(array('ul' => 'usersmeta'), 'ul.user_id = tc.user_id', array('ul.user_profile_pic', 'ul.user_headline'))
                        ->join(array('con' => 'users'), 'con.user_id = tc.user_id', array('con.first_name', 'con.last_name'))
//                      ->joinleft(array('tcv' => 'teachingclassvideo'), 'tc.class_id = tcv.class_id', array('video_thumb_url', 'class_video_url'))
                        //->join(array('c' => 'category'), 'c.category_id = tc.category_id', array('c.category_name'))
                        ->where('tc.user_id = ?', $userid);
                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {

                $i = 0;
                foreach ($result as $val) {

                    $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('tcv' => 'teachingclassvideo'))
                            ->join(array('cat' => 'category'), 'cat.category_id = ' . $val["category_id"], array('category_name'))
                            ->where('tcv.class_id= ?', $val['class_id']);
                    $resultvideo = $this->getAdapter()->fetchRow($select);

                    $result[$i]['video_thumb_url'] = $resultvideo['video_thumb_url'];
                    $result[$i]['category_name'] = $resultvideo['category_name'];
                    $i++;
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


    
    
    
    
    

    /* Developer:Rakesh Jha
     * Date: 22-03-15
      Desc : Getting all the details of classes videos teached
     */

    public function getTeachClassesDetails() {
        if (func_num_args() > 0) {
            $classid = func_get_arg(0);
//            print_r($classid); die;
            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('tc' => 'teachingclasses'), array('tc.publish_status', 'tc.category_id', 'tc.class_title', 'tc.category_id', 'tc.class_id', 'tc.class_tags', 'tc.class_description', 'tc.assignment_project_title', 'tc.assignment_project_description'))
                        ->joinLeft(array('c' => 'category'), 'c.category_id = tc.category_id', array('c.category_name'))
                        ->joinLeft(array('tcv' => 'teachingclassvideo'), 'tcv.class_id = tc.class_id', array('tcv.class_video_title', 'tcv.cover_image', 'tcv.class_video_url', 'tcv.class_video_id', 'tcv.video_id'))
                        ->where('tc.class_id = ?', $classid);
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

    public function getClassUnitID() {
        if (func_num_args() > 0) {
            $classid = func_get_arg(0);
//            print_r($classid); die;
            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('tc' => 'teachingclasses'))
                        ->where('tc.class_id=?', $classid);
                $result = $this->getAdapter()->fetchRow($select);
                
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($result) {

                return $result;
            }
        } else {
            
        }
    }

    public function getClassTitle() {
        if (func_num_args() > 0) {
            $classid = func_get_arg(0);
//            print_r($classid); die;
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('tc' => 'teachingclasses'), array('tc.class_title', 'tc.random_url'))
                    ->joinleft(array('u' => 'users'), 'u.user_id=tc.user_id', array('u.email', 'u.user_id', 'u.first_name'))
                    ->where('tc.class_id = ?', $classid);
            $result = $this->getAdapter()->fetchAll($select);
//            echo '<pre>';            print_r($result); die;
            if ($result) {
                return $result;
            } else {
                
            }
        }
    }

    public function getClassByUser() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {
                $select = $this->select()
                        ->from($this)
                        ->where('user_id = ?', $user_id);

                $result = $this->getAdapter()->fetchAll($select);
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

    public function getTeachClass() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('l' => 'teachingclasses'))
                        ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                        ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
                        ->join(array('p' => 'projects'), 'p.class_id = l.class_id', '*')
                        ->where('l.user_id = ?', $user_id);
//                $select = $this->select()
//                        ->from($this)
//                        ->where('category_id = ?', $category_id);

                $result = $this->getAdapter()->fetchAll($select);
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

    public function getAllCLasses() {
        try {
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'teachingclasses'))
                    //  ->join(array('tcv' => 'teachingclassvideo'), 'l.class_id = tcv.class_id', array('tcv.video_id', 'tcv.video_thumb_url', 'tcv.class_video_url', 'tcv.user_id', 'tcv.class_unit_id'))
                    ->join(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                    ->join(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
                    ->join(array('cat' =>'category'),'cat.category_id=l.category_id',array('cat.category_name','cat.category_id'));
//            echo $select; die;
            $result = $this->getAdapter()->fetchAll($select);
         
        }   catch (Exception $e) {
            throw new Exception('Unable To Insert Exception Occured :' . $e);
        }
        if ($result) {
            $i = 0;
            foreach ($result as $val) {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('tcv' => 'teachingclassvideo'))
                        ->where('tcv.class_id= ?', $val['class_id']);
                $resultvideo = $this->getAdapter()->fetchRow($select);

                $result[$i]['video_thumb_url'] = $resultvideo['video_thumb_url'];
                $result[$i]['cover_image'] = $resultvideo['cover_image'];
                $i++;
            }
            return $result;
        }
    }

//dev:priyanka varanasi
//desc: to insert video album id db

    public function insertingAlbumId() {
        if (func_num_args() > 0) {

            $data = func_get_arg(0);
            $where = func_get_arg(1);

            $update = $this->update($data, 'class_id =' . $where);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    }

//dev:Rakesh Jha
//desc: Edit Class
    public function editClass() {
        if (func_num_args() > 0) {

            $class_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('tc' => 'teachingclasses'), array('tc.class_title', 'tc.class_description', 'tc.assignment_project_title', 'tc.assignment_project_description', 'tc.class_tags'))
                    //  ->join(array('tcv' => 'teachingclassvideo'), 'l.class_id = tcv.class_id', array('tcv.video_id', 'tcv.video_thumb_url', 'tcv.class_video_url', 'tcv.user_id', 'tcv.class_unit_id'))
                    ->join(array('tcv' => 'teachingclassvideo'), 'tcv.class_id = tc.class_id', array('tcv.class_video_title', 'tcv.class_video_url'))
                    ->where('tc.class_id=?', $class_id);

            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
                return $result;
            }
        }
    }

    public function updateClass() {
        if (func_num_args() > 0) {
            $class_id = func_get_arg(0);
            $category_id = func_get_arg(1);
            $class_tags = func_get_arg(2);
            $class_title = func_get_arg(3);
            $class_description = func_get_arg(4);
            $project_title = func_get_arg(5);
            $project_desc = func_get_arg(6);
            $publishstatus = func_get_arg(7);
            
//        print_r($class_id); die;
            $data = array("category_id" => $category_id, "class_tags" => $class_tags, "class_title" => $class_title, "class_description" => $class_description, "assignment_project_title" => $project_title, "assignment_project_description" => $project_desc, "publish_status" => $publishstatus);
          
            $where = "class_id =" . $class_id;
            $result = $this->update($data, $where);
            if ($result) {
                // die($result);
                return $result;
            }
        }
    }

    //dev:priyanka varanasi
    //desc: to get classes based on students to display

    public function gettrendingclasses() {
        try {
            $category = "";
            if (func_num_args() > 0) {
                $category = func_get_arg(0);
            }
               
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'teachingclasses'), array('l.class_id', 'l.class_title', 'l.publish_status', 'l.category_id', 'l.class_created_date'))
                    // ->joinLeft(array('tcv' => 'teachingclassvideo'), 'l.class_id = tcv.class_id', array('tcv.video_id',   'tcv.video_thumb_url', 'tcv.class_video_url', 'tcv.user_id', 'tcv.class_unit_id'))
                    ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                    ->joinLeft(array('ct' => 'category'), 'ct.category_id = l.category_id', array('ct.category_name'))
                    ->joinLeft(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name', 'con.user_id'))
                    ->where('publish_status=?', 0);
            
                    if(!empty($category)){
                    $select = $select->where('l.category_id=?', $category);
                    }
            $result = $this->getAdapter()->fetchAll($select);
        } catch (Exception $e) {
            throw new Exception('Unable To Insert Exception Occured :' . $e);
        }

        if ($result) {
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
        } else {
            
        }
    }
    
      public function getmyCLassescount() {
         try{
             if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            }
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'teachingclasses'), array('l.class_id', 'l.class_title', 'l.publish_status', 'l.category_id', 'l.class_created_date'))
                    // ->joinLeft(array('tcv' => 'teachingclassvideo'), 'l.class_id = tcv.class_id', array('tcv.video_id',   'tcv.video_thumb_url', 'tcv.class_video_url', 'tcv.user_id', 'tcv.class_unit_id'))
                    ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                    ->joinLeft(array('ct' => 'category'), 'ct.category_id = l.category_id', array('ct.category_name'))
                    ->joinLeft(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name', 'con.user_id'))
                    ->joinLeft(array('cen' => 'classenroll'), 'cen.user_id = l.user_id', array('con.first_name', 'con.last_name', 'con.user_id'))
                    ->where('publish_status=?', 0)
                    ->where('l.user_id=?', $user_id);
            $result = $this->getAdapter()->fetchAll($select);
            if($result){
                return count($result);
            }
         
        }   catch (Exception $e) {
            throw new Exception('Unable To Insert Exception Occured :' . $e);
         }
    }
    
   

    //dev:priyanka varanasi
    //desc: to get the classes based on the recomended categories

    public function getClassesonIntclass() {
        if (func_num_args() > 0) {

            $category_id = func_get_arg(0);

            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('l' => 'teachingclasses'), array('l.class_id', 'l.class_title', 'l.publish_status', 'l.category_id', 'l.class_created_date'))
                        // ->joinLeft(array('tcv' => 'teachingclassvideo'), 'l.class_id = tcv.class_id', array('tcv.video_id',   'tcv.video_thumb_url', 'tcv.class_video_url', 'tcv.user_id', 'tcv.class_unit_id'))
                        ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                        ->joinLeft(array('ct' => 'category'), 'ct.category_id = l.category_id', array('ct.category_name'))
                        ->joinLeft(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name'))
                        ->where('l.category_id IN(?)', $category_id);

                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }
            if ($result) {

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
            } else {
                return 0;
            }
        }
    }

    public function updateClassUrl() {
        if (func_num_args() > 0) {

            $class_id = func_get_arg(0);
            $class_url = func_get_arg(1);
            $data = array("class_url" => $class_url);
            $update = $this->update($data, 'class_id =' . $class_id);
            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    }

    public function updateRefferalUrl() {
        if (func_num_args() > 0) {

            $class_id = func_get_arg(0);
            $refferal_url = func_get_arg(1);
            $data = array("student_refferal" => $refferal_url);
            $update = $this->update($data, 'class_id =' . $class_id);
            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    }

    //dev:priyanka varanasi
    //desc: to get the projects assignments count

    public function getProjectsAssignments() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {
                $select = $this->select()
                        ->from($this)
                        ->where('user_id = ?', $user_id);
                //->where('publish_status = ?',0);

                $result = $this->getAdapter()->fetchAll($select);
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

///abhishekm
    public function authUserid() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $class_id = func_get_arg(1);

            try {
                $select = $this->select()
                        ->where("user_id=?", $user_id)
                        ->where("class_id=?", $class_id);
//                   die($select); 
                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return true;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

//dev:priyanka varanasi
//desc:to get projects count by cuurent userid
    public function getProjectsByClassByUserid() {


        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('tc' => 'teachingclasses'), array('tc.user_id', 'tc.class_id'))
                        ->join(array('p' => 'projects'), 'p.class_id = tc.class_id', array('p.project_id', 'p.project_title', 'project_cover_image', 'project_workspace'))
                        ->joinleft(array('u' => 'users'), 'p.user_id = u.user_id', array('u.first_name', 'u.last_name',))
                        ->joinleft(array('um' => 'usersmeta'), 'p.user_id = um.user_id', array('um.user_profile_pic'))
                        ->where("tc.user_id=?", $user_id);
                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    public function getClassPublishStatus() {
        if (func_num_args() > 0) {
            $class_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('tc' => 'teachingclasses'), array('tc.publish_status'))
                    ->where('tc.class_id=?', $class_id);

            $result = $this->getAdapter()->fetchRow($select);
            if ($result) {
                return $result;
            }
        }
    }

    public function updatePublishStatus() {
        if (func_num_args() > 0) {

            $where = func_get_arg(0);
            $data = func_get_arg(1);

            $update = $this->update($data, 'publish_status =' . $where);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    }

    //dev:priyankav varanasi
    //desC to get total no of units created for all classes created by logged user
    public function getUnitsCountForClasses() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('tc' => 'teachingclasses'), array('tc.user_id', 'tc.class_id'))
                        ->join(array('tcu' => 'teachingclassunit'), 'tcu.class_id = tc.class_id', array('tcu.class_unit_id'))
                        ->where("tc.user_id=?", $user_id);
                $result = $this->getAdapter()->fetchAll($select);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return count($result);
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /* Dev.:Namrata Singh
     * Desc.:get all negative reviews
     * Date: 25/4/15
     */

    Public function getReviews() {
        if (func_num_args() > 0) {
            $recommended = 0;
            $user_id = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'teachingclasses'), array('class_id'))
                    ->where('l.user_id = ?', $user_id);
            $result = $this->getAdapter()->fetchAll($select);

            if ($result) {

                foreach ($result as $val) {
                    $select1 = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('cr' => 'classesreview'))
                            ->where('cr.class_id=?', $val['class_id']);

                    $result1 = $this->getAdapter()->fetchAll($select1);

                    if ($result1) {

                        foreach ($result1 as $val1) {
                            if ($val1['recommend_class'] == 1) {
                                $recommended++;
                            }
                        }
                    }
                }
                return $recommended;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /* Dev.:Namrata Singh
     * Desc.:get all reviews
     * Date: 25/4/15
     */

    public function getAllReview() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $getall = 0;
            try {
                $select = $this->select()
                        ->where("user_id=?", $user_id);

                $result = $this->getAdapter()->fetchAll($select);
                if ($result) {
                    foreach ($result as $val) {
                        $select = $this->select()
                                ->setIntegrityCheck(false)
                                ->from(array('cr' => 'classesreview'))
                                ->where("cr.class_id=?", $val['class_id']);
                        
                       $result1 = $this->getAdapter()->fetchAll($select);
                        $getall = $getall + count($result1);
                    }
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($getall) {
                return $getall;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
    /* Dev.:Namrata Singh
     * Desc.:get userid based on classid
     * Date: 9/6/15
     */

    public function getUserId(){
       if (func_num_args() > 0) {
            $classid= func_get_arg(0);
            try {

                $select = $this->select()
                        ->from($this,'user_id')
                        ->where('class_id= ?', $classid);

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
    
    
    public function getTeachingClassescre() {

        if (func_num_args() > 0) {
        
            $classid = func_get_arg(0);
          

            try {
                $select = $this->select()
                   
                    ->where("class_id=?",$classid);
                    
                $result = $this->getAdapter()->fetchRow($select); 
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return $result;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    public function getAllCLassescount() {
         try{
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'teachingclasses'), array('l.class_id', 'l.class_title', 'l.publish_status', 'l.category_id', 'l.class_created_date'))
                    // ->joinLeft(array('tcv' => 'teachingclassvideo'), 'l.class_id = tcv.class_id', array('tcv.video_id',   'tcv.video_thumb_url', 'tcv.class_video_url', 'tcv.user_id', 'tcv.class_unit_id'))
                    ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                    ->joinLeft(array('ct' => 'category'), 'ct.category_id = l.category_id', array('ct.category_name'))
                    ->joinLeft(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name', 'con.user_id'))
                    ->where('publish_status=?', 0);
            $result = $this->getAdapter()->fetchAll($select);
            if($result){
                return count($result);
            }
         
        }   catch (Exception $e) {
            throw new Exception('Unable To Insert Exception Occured :' . $e);
         }
    }
     public function getAllRecentlyCLasses() {
        
              $start=func_get_arg(0);
     $category = "";
            if (func_num_args() > 1) {
                $category = func_get_arg(1);
            }
              $start = $start-1;
         $start= 9*$start;
         if(isset($start)){
           try{
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'teachingclasses'), array('l.class_id', 'l.class_title', 'l.publish_status', 'l.category_id', 'l.class_created_date'))
                    // ->joinLeft(array('tcv' => 'teachingclassvideo'), 'l.class_id = tcv.class_id', array('tcv.video_id',   'tcv.video_thumb_url', 'tcv.class_video_url', 'tcv.user_id', 'tcv.class_unit_id'))
                    ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                    ->joinLeft(array('ct' => 'category'), 'ct.category_id = l.category_id', array('ct.category_name'))
                    ->joinLeft(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name', 'con.user_id'))
                    ->where('publish_status=?', 0)
                    ->order("class_created_date desc")
                        ->limit(9,$start);
                     if(!empty($category)){
                    $select = $select->where('l.category_id=?', $category);
                    }
                     
            $result = $this->getAdapter()->fetchAll($select);
        } catch (Exception $e) {
            throw new Exception('Unable To Insert Exception Occured :' . $e);
        }
        if ($result) {
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
    }
    
     }
     public function getsingleCLass() {
        
              $classid = func_get_arg(0);
              if (func_num_args() > 1) {
                $category = func_get_arg(1);
            }
           try{
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('l' => 'teachingclasses'), array('l.class_id', 'l.class_title', 'l.publish_status', 'l.category_id', 'l.class_created_date'))
                    ->joinLeft(array('tcv' => 'teachingclassvideo'), 'l.class_id = tcv.class_id', array('tcv.video_id','tcv.cover_image','tcv.video_thumb_url', 'tcv.class_video_url', 'tcv.user_id', 'tcv.class_unit_id'))
                    ->joinLeft(array('ul' => 'usersmeta'), 'ul.user_id = l.user_id', array('ul.user_profile_pic'))
                    ->joinLeft(array('ct' => 'category'), 'ct.category_id = l.category_id', array('ct.category_name'))
                    ->joinLeft(array('con' => 'users'), 'con.user_id = l.user_id', array('con.first_name', 'con.last_name', 'con.user_id'))
                    ->where('publish_status=?', 0)
                     ->where('l.class_id=?', $classid);  
             if(!empty($category)){
                    $select = $select->where('l.category_id=?', $category);
                    }
            $result = $this->getAdapter()->fetchRow($select);
           
        } catch (Exception $e) {
            throw new Exception('Unable To Insert Exception Occured :' . $e);
        }if($result){
            $i = 0;
           
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('cr' => 'classenroll'), array("stud_count" => "COUNT(*)"))
                        ->where('cr.class_id = ?', $classid);
                $resultcount = $this->getAdapter()->fetchRow($select);
                $result['stud_count'] = $resultcount['stud_count'];
                $i++;
            return $result;
       
        }
     }
     public function checkTeacher(){
         if (func_num_args() > 0) {
       
             $user_id = func_get_arg(0);

            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('tc' => 'teachingclasses'), array("user_id" => "COUNT(user_id)"))
                    ->where('tc.user_id = ?', $user_id);
            $result = $this->getAdapter()->fetchRow($select);
            if ($result) {
             
                return $result;
            }
             
         }
     }

}

?>