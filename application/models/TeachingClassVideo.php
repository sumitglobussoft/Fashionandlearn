<?php

/*
 * Developer : Ankit Singh
 * Date : 30/12/2014
 */

class Application_Model_TeachingClassVideo extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teachingclassvideo';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_TeachingClassVideo();
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
            {
                $responseId = $this->insert($data);
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

    
    public function updateunassignedClassvideosByUserid() {

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
                    return 1;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    
    
    
    
    
    public function getvideodetails() {
        if (func_num_args() > 0) {
            $class_unit_id = func_get_arg(0);
            $class_id = func_get_arg(1);
            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from($this)
                        ->where('class_unit_id = ?', $class_unit_id)
                        ->where('class_id = ?', $class_id)
                        ->where('transcode_status=?',0)
                        //priyanka added this line//
                        ->order('class_unit_id  ASC')
                        ->order('orderid  ASC');
                       //code ends/// 
//                          ->group('class_unit_id');
                         
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
   
    public function insertvideoinformation() {
        if (func_num_args() > 0) {
            $videoData = func_get_arg(0);
//            print_r($videoData); die;
            try {
                $responseId = $this->insert($videoData);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($responseId) {
                
                
                
                  $data1["orderid"]=$responseId;
                   
                  
                     $response = $this->update($data1,"class_video_id=".$responseId);
                
            
              /*
                  Commented By rakesh Jha 
                  Purpose :Backup
               
                  $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('tcv'=>'teachingclassvideo'),array('tcv.class_unit_id','tcv.class_video_id'))
                        ->limit(1)
                       ->order(array('tcv.class_unit_id DESC'));

                $result = $this->getAdapter()->fetchRow($select);

               if($result){
                return $result;
                
                } */
              return $responseId;
                
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*
      Developer:Rakesh Jha
      Dated:10/02/15
      Desc:Get the Unit_id of teacher's class

     */

    public function getUnitsCreated() {
       if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('tcv' => 'teachingclassvideo'), array('class_id' => 'COUNT(*)'))
                    ->where('tcv.user_id = ?', $user_id);
            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
//                print_r($result); die;
                return $result;
            }
        }
    }

    public function updateCoverImage($data,$where) {
        $update = $this->update($data,$where);
        if (isset($update)) {
            return $update;
        } else {
            throw new Exception('Argument Not Passed');
        }
    }
    public function deleteVideo(){
                if (func_num_args() > 0) {
                $video_id=  func_get_arg(0);
                 $where = (array('class_video_id = ?' => $video_id));
               $result= $this->delete($where);
                 if($result){
                     return $result;
                     
                 }
                    
                }
     }
	 //dev:priyanka varanasi
	 //desc: To select video ids to hit url 
	 public function getVimeoVideoIds(){
	 $select = $this->select()
                ->from(array('tcv' => 'teachingclassvideo'),array('tcv.video_id','tcv.video_thumb_url'))
                ->setIntegrityCheck(false);
        $result = $this->getAdapter()->fetchAll($select);
       if ($result) :
         return $result;
        endif;
	 
	 }
	  //dev:priyanka varanasi
	 //desc: To update thumbnail column in the db
public function updateDbWithNewThumb(){
   if (func_num_args() > 0) {

            $data = func_get_arg(0);
            $where = func_get_arg(1);

            $update = $this->update($data, 'video_id =' . $where);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
}
//dev: varanasi priyanka
//dec: to select the class videos oan display on one defualt video in ternding page

public function getterndingclassvideos(){
    
     if (func_num_args() > 0) {
            $class_id = func_get_arg(0);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from($this)
                    ->where('class_id = ?', $class_id);
              $result = $this->getAdapter()->fetchAll($select);
            if ($result) {           
                return $result;
            }
        } 
    
    
}

//dev:Priyanaka varanasi
//desc:To update cover image
public function updateVideoCoverImage(){
       if (func_num_args() > 0) {
//           print_r($data); die;
            $data = func_get_arg(0);
            $video_id = func_get_arg(1);

            $update = $this->update($data, 'class_video_id =' . $video_id);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    
}

public function updateVideoname(){
       if (func_num_args() > 0) {
 
            $data = func_get_arg(0);
            $class_video_ids = func_get_arg(1);
//            print_r($data);die;
            $update = $this->update($data, 'class_video_id =' . $class_video_ids);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    
}
public function updateClassVideoname(){
       if (func_num_args() > 0) {
 
            $data = func_get_arg(0);
//            print_r($data); die;
            $class_video_ids = func_get_arg(1);
//            print_r($data['class_title']); die;
            $update = $this->update($data, 'class_video_id =' . $class_video_ids);
//            print_r($update); die;
            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    
}

 public  function getClassUnitID(){
         if(func_num_args() > 0) {
            $classid = func_get_arg(0);
//            print_r($classid); die;
            try {
                 $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('tc'=>'teachingclassvideo'))
                        ->join(array('tcu'=>'teachingclassunit'),'tcu.class_unit_id=tc.class_unit_id',array('tcu.class_unit_titile'))
                        ->where('tc.class_id=?',$classid)
                        ->order('tc.class_no ASC');
//                        ->group('tcu.class_unit_id');
//                 echo $select ;die;
               
                $result = $this->getAdapter()->fetchAll($select);
                
                $CombArr = array();
                foreach($result as $rval){
                    if(isset($CombArr[$rval['class_unit_id']])){
                        $last_key = key( array_slice( $CombArr[$rval['class_unit_id']]['details'], -1, 1, TRUE ) );
                        $CombArr[$rval['class_unit_id']]['details'][$last_key+1] = $rval;
                    }else{
                        $CombArr[$rval['class_unit_id']]['class_unit_titile'] = $rval['class_unit_titile'];
                        $CombArr[$rval['class_unit_id']]['class_unit_id'] = $rval['class_unit_id'];
                         $CombArr[$rval['class_unit_id']]['class_id'] = $rval['class_id'];
                        $CombArr[$rval['class_unit_id']]['details'][] = $rval;
                    }
                }
//                 echo '<pre>';              print_r($result);  echo '</pre>';  
//              echo '<pre>';              print_r($CombArr);  echo '</pre>';   die;
               
            }
            catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }

            if ($CombArr) {
                
                return $CombArr;
            }
        }
        else{
            
        }
    }
    
    
    
  public function getvideothumbnails(){
    
     if (func_num_args() > 0) {
            $class_id = func_get_arg(0);
            $select = $this->select()
                    ->from($this)
                    ->where('user_id = ?', $class_id)
                    ->order('video_uploaded_date DESC')
                    ->limit(4);
           
              $result = $this->getAdapter()->fetchAll($select);
            
              
              
            if ($result) {           
                return $result;
            }
        } 
    
    
}

  public function getvideothumbnailss(){
    
     if (func_num_args() > 0) {
            $class_id = func_get_arg(0);
            $select = $this->select()
                    ->from($this)
                    ->where('class_id = ?', $class_id)
                    ->order('video_uploaded_date DESC')
                    ->limit(1);
           
              $result = $this->getAdapter()->fetchAll($select);
            
              
              
            if ($result) {           
                return $result;
            }
        } 
    
    
}

  public function getclassunitvideoID() {
        if (func_num_args() > 0) {
            $class_id = func_get_arg(0);

            try {
                $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->distinct()
                       ->from(array('tcv'=>'teachingclassvideo'),array('tcv.class_unit_id'))
                       ->joinLeft(array('tcu'=>'teachingclassunit'),'tcu.class_unit_id=tcv.class_unit_id',array('tcu.class_unit_titile','tcu.class_id'))
                        ->where('tcv.class_id = ?', $class_id);
//               echo $select; die;
                $result = $this->getAdapter()->fetchAll($select);
//                echo '<pre>';    print_r($result); die;
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
    
  //dev: priyanka varanasi  
  //desc: to update the video order no based onthe class video id
    public function updateTheDbBasedOnSortOrder(){
     if (func_num_args() > 0) {
 
            $data = func_get_arg(0);
//            print_r($data); die;
            $class_video_ids = func_get_arg(1);
//            print_r($data['class_title']); die;
            $update = $this->update($data, 'class_video_id =' . $class_video_ids);
//            print_r($update); die;
            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
       
    }
    
    /*abhishekm
     * to reorder video order after swapping
     * 
     */
      public function ordervideo(){
     if (func_num_args() > 0) {
 
            $data = func_get_arg(0);
//            print_r($data); die;
            $class_video_id = func_get_arg(1);
//            print_r($data['class_title']); die;
            $update = $this->update($data, 'class_video_id =' . $class_video_id);
//            print_r($update); die;
            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
       
    }
    
    
     /*abhishekm
     * to get class id usin order id
     * 
     */
      public function getclassvidid(){
     if (func_num_args() > 0) {
 $orderid = func_get_arg(0);
             $select = $this->select()
                    ->from($this)
                    ->where('orderid = ?', $orderid);
              $result = $this->getAdapter()->fetchRow($select);
              return $result["class_video_id"];
        }
       
    }
    
    
    
    
    
    
    
}
