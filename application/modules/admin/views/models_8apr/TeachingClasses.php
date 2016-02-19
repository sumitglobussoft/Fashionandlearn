<?php

/*
 * Developer : priyanka varanasi
 * Date : 2/1/2015
 */

class Admin_Model_TeachingClasses extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teachingclasses';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_TeachingClasses();
        return self::$_instance;
    }

    /*
     * Developer :priyanka varanasi
     * Date: 5,feb 2015
     * Desc : update Teaching classed data 
     */

    public function insertclassdata() {

        if (func_num_args() > 0) {
            $classdata = func_get_arg(0);
            $classes = func_get_arg(1);


            try {
                $result = $this->update($classdata, 'class_id = "' . $classes . '"');
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

    //dev:priyanka varanasi
    //desc:31/1/2015

    public function selectClasses() {

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('tc' => 'teachingclasses'))
                ->joinLeft(array('con' => 'users'), 'con.user_id = tc.user_id', array('con.first_name', 'con.last_name'));
        $result = $this->getAdapter()->fetchAll($select);

        $result = $this->getAdapter()->fetchAll($select);

        if ($result) {
            return $result;
        }
    }

    /*
     * Developer : Ankit Singh
     * Date: 20 Jan 2015
     * Desc : Insert User Classes Details at the time of start
     */

    public function insertTeachingClassesStart() {

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

    /*
     * Developer : Ankit Singh
     * Date: 20 Jan 2015
     * Desc : Update User Classes Details on the basis of class id.
     */

    public function updateTeachingClasses($data, $classTeachId) {
        $where = "class_id =" . $classTeachId;
        $result = $this->update($data, $where);
        if ($result) {
            return $result;
        }
    }

    //dev:priyanka varanasi
    //desc:5/2/2015

    public function selectTeachingClassesId() {
        if (func_num_args() > 0):
            $classid = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('tc' => 'teachingclasses', array('tc.user_id', 'tc.class_id','tc.album_id')))
                        ->setIntegrityCheck(false)
                        ->joinleft(array('tcv' => 'teachingclassvideo'), 'tc.class_id = tcv.class_id', array('tcv.video_id'))
                        ->joinleft(array('u' => 'users'), 'u.user_id = tc.user_id', array('u.first_name'))
                        ->where('tc.class_id =?', $classid);
                $result = $this->getAdapter()->fetchAll($select);
                if ($result) :
                    return $result;
                endif;
            } catch (Exception $e) {
                throw new Exception($e);
            }
        else :
            throw new Exception('Argument Not Passed');
        endif;
    }
//dev:priyanka varanasi
//desc: TO autofill the class need to edit
       public function selectTeaching() {
        if (func_num_args() > 0):
            $classid = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from(array('tc' => 'teachingclasses', array('tc.user_id', 'tc.class_id','tc.album_id')))
                        ->setIntegrityCheck(false)
                        ->joinleft(array('u' => 'users'), 'u.user_id = tc.user_id', array('u.first_name'))
                        ->where('tc.class_id =?', $classid);
                $result = $this->getAdapter()->fetchRow($select);
                if ($result) :
                    return $result;
                endif;
            } catch (Exception $e) {
                throw new Exception($e);
            }
        else :
            throw new Exception('Argument Not Passed');
        endif;
    }
    
    //dev:priyanka varanasi
    //date:11/2/2015
    //desc: to delete class related info from every table based on class id

    public function deleteclass() {

        if (func_num_args() > 0):
            $classid = func_get_arg(0);
            try {
              $sql = 'DELETE teachingclasses, teachingclassfile,teachingclassvideo,teachingclassunit,projects,projectlikes,projectcomments,projectcommentlikes,myclasses,discussionlikes,discussioncomments,discussioncommentlikes,classfiles,classesreview,classenroll,classdiscussions,certificate,user_video_status
FROM teachingclasses
 LEFT JOIN  teachingclassfile ON teachingclasses.class_id = teachingclassfile.class_id
 LEFT JOIN  teachingclassvideo ON teachingclasses.class_id = teachingclassvideo.class_id
 LEFT JOIN  teachingclassunit ON teachingclasses.class_id = teachingclassunit.class_id
 LEFT JOIN  projects ON teachingclasses.class_id = projects.class_id
 LEFT JOIN  projectlikes ON teachingclasses.class_id = projectlikes.class_id
 LEFT JOIN  projectcomments ON teachingclasses.class_id = projectcomments.class_id
 LEFT JOIN  projectcommentlikes ON teachingclasses.class_id = projectcommentlikes.class_id
 LEFT JOIN  myclasses ON teachingclasses.class_id = myclasses.class_id
 LEFT JOIN  discussionlikes ON teachingclasses.class_id = discussionlikes.class_id
 LEFT JOIN  discussioncomments ON teachingclasses.class_id = discussioncomments.class_id
 LEFT JOIN  discussioncommentlikes ON teachingclasses.class_id = discussioncommentlikes.class_id
 LEFT JOIN  classfiles ON teachingclasses.class_id = classfiles.class_id
 LEFT JOIN  classesreview ON teachingclasses.class_id = classesreview.class_id
 LEFT JOIN  classenroll ON teachingclasses.class_id = classenroll.class_id
 LEFT JOIN  classdiscussions ON teachingclasses.class_id = classdiscussions.class_id
 LEFT JOIN  certificate ON teachingclasses.class_id = certificate.class_id
 LEFT JOIN  user_video_status ON teachingclasses.class_id = user_video_status.class_id
 
WHERE teachingclasses.class_id = ' . $classid . '';
                $responseid = $this->getAdapter()->query($sql);
            } catch (Exception $e) {
                throw new Exception($e);
            }
            return $classid;
        else:
            throw new Exception('Argument Not Passed');
        endif;
    }

    //dev:priyanka varanasi
    //date:11/2/2015
    //desc: to change the status to publish
    public function getstatustopublish() {

        if (func_num_args() > 0) {
            $classuid = func_get_arg(0);

            $data['publish_status'] = 0;
            try {
                $where = "class_id =" . $classuid;
                $result = $this->update($data, $where);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return $classuid;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    //dev:priyanka varanasi
    //desc:5/2/2015

    public function selectTeachingClassesalbum() {
        if (func_num_args() > 0):
            $classid = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from($this)
                        ->where('class_id =?', $classid);
                $result = $this->getAdapter()->fetchRow($select);
                if ($result) :
                    return $result;
                endif;
            } catch (Exception $e) {
                throw new Exception($e);
            }
        else :
            throw new Exception('Argument Not Passed');
        endif;
    }

    /* Developer:Rakesh Jha
      Dated:02-03-15
      Desc: Teacher Details for payment distribution
     */

    public function getTeachersDetails() {

        $select = $this->select()
                ->from(array('tc' => 'teachingclasses'), array("class count" => "COUNT(*)"))
                ->setIntegrityCheck(false)
                ->join(array('u' => 'users'), 'u.user_id = tc.user_id', array('u.first_name', 'tc.user_id'))
                ->joinLeft(array('um' => 'usersmeta'), 'um.user_id = tc.user_id', array('um.paypal_email','um.teacher_payment_status'))
                ->joinLeft(array('c' => 'classenroll'), 'c.class_id = tc.class_id', array("studentcount" => "COUNT(c.user_id)"))
                ->joinLeft(array('p' => 'projects'), 'p.class_id=tc.class_id', array("projectcount" => "COUNT(p.project_id)"))
//                ->joinLeft(array('uv' => 'user_video_status'), 'uv.class_id=tc.class_id', array("seenvideocount" => "COUNT(tc.user_id)"))
//                ->group('tc.user_id')
                ->group('tc.user_id')
                ->group('u.first_name');
//echo $select; die;
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
       
            return $result;
        } else {
            
        }
    }
  public function countTotalClasses(){
    if (func_num_args() > 0) {

            $class_id  = func_get_arg(0);
                         $select = $this->select();
                         $result = $this->getAdapter()->fetchAll($select);
			 if (isset($result)) {
                return $result;
            } else {
                throw new Exception('Argument Not Passed');
            } 
}
 }
   
  public function countTotalTeacher(){
            if(func_num_args()>0){
               
                            
                try {
                    $select = $this->select()
                                 ->distinct()
                                  ->group('user_id');
                    $result = $this->getAdapter()->fetchAll($select);
                } catch (Exception $exc) {
                    throw new Exception('Unable to update, exception occured'.$exc);
                }
                if($result){
                    return $result;
                }
            }else{
                 throw new Exception('Argument not passed');
            }
        }
        // dev: priyanka varanasi
  //desc: To get albums ids from db
        public function getAlbumidsByClassids(){
       $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('tc' => 'teachingclasses'),array('tc.album_id'));
        $result = $this->getAdapter()->fetchAll($select);

        $result = $this->getAdapter()->fetchAll($select);

        if ($result) {
            return $result;
        }    
            
        }
         // dev: priyanka varanasi
  //desc: To update the  publish status  in db
public function getTheStatusToPublish(){
    
          if (func_num_args() > 0) {
            $album['album_id'] = func_get_arg(0);
            $data['publish_status'] = 0;
            $albumid = (int)$album['album_id'];
             try {
                $where =  $albumid;
                $result = $this->update($data,'album_id='.$where);
                
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
      //dev:priyanka varanasi
    //date:11/2/2015
    //desc: to change the status to draft
    public function getstatustopending() {

        if (func_num_args() > 0) {
            $classuid = func_get_arg(0);

            $data['publish_status'] = 2;
            try {
                $where = "class_id =" . $classuid;
                $result = $this->update($data, $where);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($result) {
                return $classuid;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }         
 
    /* public function getprojectscount() {
      if (func_num_args() > 0) {
      $user_id = func_get_arg(0);

      try {
      $select = $this->select()
      ->setIntegrityCheck(false)
      ->from(array('l' => 'teachingclasses'), array('l.class_id'))
      ->join(array('p' => 'projects'), 'p.class_id = l.class_id', array("num" => "COUNT('p.project_id')"))
      ->where('l.user_id = 2'//,$user_id
      );
      $checkrequest = $this->getAdapter()->fetchRow($select);
      //print_r($checkrequest["num"]);die('test');
      } catch (Exception $e) {
      throw new Exception('Unable To Insert Exception Occured :' . $e);
      }

      if ($result) {
      print_r($result); die;
      return $result;
      }
      } else {
      throw new Exception('Argument Not Passed');
      }
      } */
}
