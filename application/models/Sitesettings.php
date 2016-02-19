<?php

class Application_Model_Sitesettings extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'sitesettings';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Sitesettings();
        return self::$_instance;
    }
    
    
   public function updastatistics(){
        $select = $this->select();
         $result = $this->getAdapter()->fetchAll($select);
        
         if($result){
             if($result[0]['teachmode'] == 0){
                 $data = array('teachmode' => 1,'teachurl' => '/teachdetails?via=teach');
             }else{
                  $data = array('teachmode' => 0,'teachurl' => '/applicationform');
             }
             $where = array('stasticid' => 1);
             $result = $this->update($data,$where);
         }
         return $result;
   }
    public function permissionstatus(){
        $select = $this->select();
         $result = $this->getAdapter()->fetchAll($select);
         if($result){
              return $result;
         }
        
   }
    


//    public function getlevelsinfo() {
//
//        if (func_num_args() > 0) {
//            $typeid = func_get_arg(0);
//
//            $select = $this->select()
//                    ->where('badge_id = ?', $typeid);
//
//            $result = $this->getAdapter()->fetchRow($select);
//
//
//            return $result;
//        }
//    }
//
//    public function checkbadge() {
//
//        if (func_num_args() > 0) {
//            $stats = func_get_arg(0);
//            $achievements = func_get_arg(1);
//            if (count($achievements) != 0)
//                $select = $this->select()
//                        ->where('likes <= ?', $stats['likes_count'])
//                        ->where('classes_completed <= ?', $stats['classes_completed'])
//                        ->where('projects_created <= ?', $stats['projects_created'])
//                        ->where("achevementsid NOT IN (?)", $achievements);
//            else
//                $select = $this->select()
//                        ->where('likes <= ?', $stats['likes_count'])
//                        ->where('classes_completed <= ?', $stats['classes_completed'])
//                     ->where('projects_created <= ?', $stats['projects_created']);
//
//            $result = $this->getAdapter()->fetchAll($select);
//
//            return $result;
//        }
//    }

}

?>