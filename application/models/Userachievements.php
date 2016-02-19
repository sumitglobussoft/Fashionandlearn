<?php

class Application_Model_Userachievements extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'user_achievements';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Userachievements();
        return self::$_instance;
    }
    
    
    
    public function getachinfo() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
          $select = $this->select()
                    ->from(array('u' => 'user_achievements'),
                    array('u.achevementsid'))
                        ->where('user_id = ?', $user_id);
                $result = $this->getAdapter()->fetchAll($select);
        return $result;
        }
    }
    
    public function getuserbadges() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
          $select = $this->select()
                  ->setIntegrityCheck(false)
                    ->from(array('u' => 'user_achievements'),array("achevementsid"))
                    ->join(array('a'=>'achievements'),"u.achevementsid=a.achevementsid")
                    ->where('u.user_id = ?', $user_id);
                $result = $this->getAdapter()->fetchAll($select);
        return $result;
        }
    }
    
   
    
        public function awardbadge() {
        
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $dat = func_get_arg(1);
          
            
            $data["user_id"]=$user_id;
             $data["achevementsid"]=$dat['achevementsid'];
              $data["badge_title"]=$dat['badge_title'];
               $data["badge_link"]=$dat['badge_link'];
            
          $result = $this->insert($data);
  
                
               
             if($result)
       
        return $result;
             }
    }
    
    
    
    public function getlachinfo() {
        
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

          $select = $this->select()
        
                  ->setIntegrityCheck(false)
                    ->from(array('u' => 'user_achievements'),array("achevementsid"))
                    ->join(array('a'=>'achievements'),"u.achevementsid=a.achevementsid")
                    ->where('u.user_id = ?', $user_id)
                        ->limit(8);
  
                $result = $this->getAdapter()->fetchAll($select);
               
             
       
        return $result;
        
        
        }
        
        
        
        
    }
    
     public function getuserbadgeinfo() {
        
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);

          $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('u' => 'user_achievements'),array("achevementsid"))
                    ->join(array('a'=>'achievements'),"u.achevementsid=a.achevementsid")
                    ->where('u.user_id = ?', $user_id)
                        ->order('u.achevementsid desc');
  
                $result = $this->getAdapter()->fetchAll($select);
               
             
       
        return $result;
        
        
        }
        
        
        
        
    }
    
}
?>