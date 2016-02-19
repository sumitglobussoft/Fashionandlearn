<?php

class Application_Model_Notificationcenter extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'notificationcenter';

    private function __clone() {
        
    }

//Prevent any copy of this object
    
     
       
   
    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Notificationcenter();
        return self::$_instance;
    }
    
    
    
    
    
     public function insertnotifi() {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
           
     
     $result = $this->insert($data);
     return $result;
        }
     }
    
      public function getnotificount() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            
      $select = $this->select()
                       ->setIntegrityCheck(false)
                        ->from(array('n' => 'notificationcenter'))
                      
                        ->where('reciever_id = ?', $userid)
               ->where('seen_status = ?', 0);
                      
                      
                        
   
      $result = $this->getAdapter()->fetchAll($select);
      if($result)
      return count($result);
        }
     }
    
         public function getnotifi() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            
      $select = $this->select()
                       ->setIntegrityCheck(false)
                        ->from(array('n' => 'notificationcenter'))
                        ->join(array('u' => 'users'), 'u.user_id = n.initiator_id')
                        ->join(array('um' => 'usersmeta'), 'um.user_id = n.initiator_id') 
                        ->where('reciever_id = ?', $userid)
                      
                        ->order("notification_id desc")
                        ->limit(6);
 
      $result = $this->getAdapter()->fetchAll($select);
      
      if($result)
      return $result;
        }
     }
    
    
    
    public function notifiseen() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $data["seen_status"]=1;
            
            
            
            
            
      $result = $this->update($data, 'reciever_id = '. $userid);
                       
                       
 
    
      
      if($result)
      return $result;
        }
     }
    
    
    public function notificlick() {
        if (func_num_args() > 0) {
            $notification_id = func_get_arg(0);
            $data["click"]=1;
            
            
            
            
            
      $result = $this->update($data, 'notification_id = '. $notification_id);
                       
                       
 
    
      
      if($result)
      return $result;
        }
     }
     
     
     
     public function getmorenotifi() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $start=func_get_arg(1);
           
            
      $select = $this->select()
                       ->setIntegrityCheck(false)
                        ->from(array('n' => 'notificationcenter'))
                        ->join(array('u' => 'users'), 'u.user_id = n.initiator_id')
                        ->join(array('um' => 'usersmeta'), 'um.user_id = n.initiator_id') 
                        ->where('reciever_id = ?', $userid)
                      
                        ->order("notification_id desc")
                        
                        ->limit(5,$start);
 
      $result = $this->getAdapter()->fetchAll($select);
     
      if($result)
      return $result;
        }
     }
     
     
     
    
}