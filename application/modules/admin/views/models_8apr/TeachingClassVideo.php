<?php


class Admin_Model_TeachingClassVideo extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teachingclassvideo';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_TeachingClassVideo();
        return self::$_instance;
    }

    /*
     * Developer : Ankit Singh
     * Date: 20 Jan 2015
     * Desc : Insert Teaching classed data 
     */

    public function insertTeachingClassesVideo() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
           
            $row = array();
            foreach ($data as $row) {
                $responseId = $this->insert($row);
                if ($responseId) {
                    return $responseId;
                }
            }

//            try {
//                $responseId = $this->insert($data);
//            } catch (Exception $e) {
//                return $e->getMessage();
//            }
//        } else {
//            throw new Exception('Argument Not Passed');
//        }
        }
    }
    /* Developer:priyankav
       Desc : insert video information url, thumburl, id form vimeo
    */
    public function getvideoinfo(){
       
           if (func_num_args() > 0) {
            $class_id = func_get_arg(0);

            try {
                 $select = $this->select()
                          ->setIntegrityCheck(false)
                  ->from(array('tcv' => 'teachingclassvideo'),array('tcv.class_unit_id','tcv.class_video_title','tcv.video_id','tcv.class_video_url','tcv.video_thumb_url','tcv.video_uploaded_date'))
                  ->joinleft(array('ul' => 'usersmeta'),'ul.user_id = tcv.user_id',array('ul.user_profile_pic'))
                  ->joinleft(array('con' => 'users'), 'con.user_id = tcv.user_id', array('con.first_name', 'con.last_name'))    
                  ->where('tcv.class_id = ?', $class_id);
                 
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
        /* Developer:priyankav
       Desc : insert video information url, thumburl, id form vimeo
    */
    public function insertvideoinfo(){
               if (func_num_args() > 0) {
            $videoData = func_get_arg(0);
            


            try {
                $responseId = $this->insert($videoData);
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
         /* Developer:priyankav
       Desc : to get video on the based of class unit id
    */
public function getclasstitlevideos(){
              if (func_num_args() > 0) {
            $class_id = func_get_arg(0);
         try {
           $select = $this->select()
                  ->setIntegrityCheck(false)
                  ->from(array('tcv' => 'teachingclassvideo'),array('tcv.class_unit_id','tcv.class_id','tcv.class_video_title','tcv.video_id','tcv.class_video_url','tcv.video_thumb_url','tcv.video_uploaded_date'))
                  ->joinleft(array('tcu' => 'teachingclassunit'),'tcu.class_unit_id = tcv.class_unit_id',array('tcu.user_id','tcu.class_unit_titile'))
                  ->joinleft(array('u' => 'users'),'tcu.user_id = u.user_id',array('u.first_name','u.last_name'))
                  ->where('tcv.class_id = ?', $class_id);
          
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

   /* Developer:priyankav
       Desc : to get edit class unit id info
    */
public function getclassunitvideos(){
              if (func_num_args() > 0) {
            $classunit_id = func_get_arg(0);
         
          try {
            $select = $this->select()
                   ->setIntegrityCheck(false)
                  ->from(array('tcv' => 'teachingclassvideo'),array('tcv.class_unit_id','tcv.class_id','tcv.class_video_title','tcv.video_id','tcv.class_video_url','tcv.video_thumb_url','tcv.video_uploaded_date','tcv.transcode_status'))
                  ->joinleft(array('tcu' => 'teachingclassunit'),'tcu.class_unit_id = tcv.class_unit_id',array('tcu.user_id','tcu.class_unit_titile'))
                  ->joinleft(array('u' => 'users'),'tcu.user_id = u.user_id',array('u.first_name','u.last_name'))
                  ->where('tcv.class_unit_id = ?', $classunit_id);
            
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
/* Developer:priyankav
       Desc : to delete video records on the basis of class unit id 
    */
public function deleteclassunits(){
           
      if (func_num_args() > 0):
            $classid = func_get_arg(0);
            try {
               
           $sql = 'DELETE teachingclassvideo,teachingclassunit
FROM teachingclassvideo
LEFT JOIN  teachingclassunit ON teachingclassvideo.class_unit_id = teachingclassunit.class_unit_id
WHERE teachingclassvideo.class_unit_id = '.$classid.'';
             $responseid = $this->getAdapter()->query($sql);
             } catch (Exception $e) {
                throw new Exception($e);
            }
            return $classid;
        else:
            throw new Exception('Argument Not Passed');
        endif; 
}
  // dev: priyanka varanasi
  //desc: To update the  transcoding status  in db
  public function  updateTranscodeId(){
         
        if (func_num_args() > 0) {
            $videoid['video_id'] = func_get_arg(0);
            $data = func_get_arg(1);
            $videoid= (int)$videoid['video_id'];

             try {
                $where =  $videoid;
                $result = $this->update($data,'video_id='.$where);
                
            } catch (Exception $e) {

                return $e->getMessage();
            }
            if ($result) {
//                print_r($result); die;
                return $result;
            }
           
        } else {
            throw new Exception('Argument Not Passed');
        }   
         
     }

  // dev: priyanka varanasi
  //desc: To get the video ids from  db
  public function getvideosIds(){
        try {
       $select = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('tcv' => 'teachingclassvideo'),array('tcv.video_id'))
                ->join(array('tc'=>'teachingclasses'),'tcv.class_id =tc.class_id',array('tc.class_id'))
                ->where('tcv.transcode_status!=?',0)
                ->where('tc.publish_status=?','1' )
               ->ORWhere('tc.publish_status=?','0'); 
//       echo $select;die;
        $result = $this->getAdapter()->fetchAll($select);
//       echo"<pre>";print_r($result);die;
        
        } catch (Exception $e) {
                return $e->getMessage();
            }
             if ($result) {
            return $result;
        }

}

// dev: priyanka varanasi
  //desc: To get the video ids from  db
public function getClassVideos(){
         if (func_num_args() > 0) {
            $classunit_id = func_get_arg(0);
     
            try {
           $select = $this->select()
                   ->setIntegrityCheck(false)
                  ->from(array('tcv' => 'teachingclassvideo'),array('tcv.class_unit_id','tcv.class_id','tcv.class_video_title','tcv.video_id','tcv.class_video_url','tcv.video_thumb_url','tcv.video_uploaded_date','tcv.transcode_status','tcv.cover_image'))
                  ->joinleft(array('tcu' => 'teachingclassunit'),'tcu.class_unit_id = tcv.class_unit_id',array('tcu.user_id','tcu.class_unit_titile'))
                  ->joinleft(array('u' => 'users'),'tcu.user_id = u.user_id',array('u.first_name','u.last_name'))
                  ->where('tcv.class_unit_id = ?', $classunit_id);
            
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

// dev: priyanka varanasi
  //desc: To get the video ids from  db
  public function selectTeachingVideos(){
       if (func_num_args() > 0) {
            $videoid = func_get_arg(0);
     
            try {
           $select = $this->select()
                   ->setIntegrityCheck(false)
                  ->from(array('tcv' => 'teachingclassvideo'),array('tcv.class_unit_id','tcv.user_id','tcv.class_id','tcv.class_video_title','tcv.video_id','tcv.class_video_url','tcv.video_thumb_url','tcv.video_uploaded_date','tcv.transcode_status','tcv.cover_image'))
                  ->joinleft(array('u' => 'users'),'tcv.user_id = u.user_id',array('u.first_name','u.last_name'))
                  ->where('tcv.video_id = ?', $videoid);
            
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
  
  // dev: priyanka varanasi
  //desc: To get video  units 
 public function getUnitVideos(){
           if (func_num_args() > 0) {
            $classunit_id = func_get_arg(0);
     
            try {
           $select = $this->select()
                   ->setIntegrityCheck(false)
                  ->from(array('tcv' => 'teachingclassvideo'),array('tcv.video_id'))
                  ->where('tcv.class_unit_id = ?', $classunit_id);
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
 
 public function deleteclassvideos(){
       if (func_num_args() > 0) {
            $videoid['video_id'] = func_get_arg(0);
            $videoid= (int)$videoid['video_id'];
             try {
                $where =  $videoid;
                $result = $this->delete('video_id='.$where);
                
            } catch (Exception $e) {

                return $e->getMessage();
            }
            if ($result) {
                return $videoid ;
            }
           
        } else {
            throw new Exception('Argument Not Passed');
        }
     
 }
 
}
