<?php

class Application_Model_Achievements extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'achievements';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Achievements();
        return self::$_instance;
    }

    public function getlevelsinfo() {

        if (func_num_args() > 0) {
            $typeid = func_get_arg(0);

            $select = $this->select()
                    ->where('badge_id = ?', $typeid);

            $result = $this->getAdapter()->fetchRow($select);


            return $result;
        }
    }

    public function checkbadge() {

        if (func_num_args() > 0) {
            $stats = func_get_arg(0);
            $achievements = func_get_arg(1);
            if (count($achievements) != 0)
                $select = $this->select()
                        ->where('likes <= ?', $stats['likes_count'])
                        ->where('classes_completed <= ?', $stats['classes_completed'])
                        ->where('projects_created <= ?', $stats['projects_created'])
                    ->where('comments <= ?', $stats['comments'])
                    ->where('discussion <= ?', $stats['discussion'])
                     ->where('invite <= ?', $stats['invite'])
                    ->where('freesignup <= ?', $stats['freesignup'])
                    ->where('premiumsignup <= ?', $stats['premiumsignup'])
                        ->where("achevementsid NOT IN (?)", $achievements);
            else
                $select = $this->select()
                        ->where('likes <= ?', $stats['likes_count'])
                        ->where('classes_completed <= ?', $stats['classes_completed'])
                     ->where('projects_created <= ?', $stats['projects_created'])
                    ->where('comments <= ?', $stats['comments'])
                    ->where('discussion <= ?', $stats['discussion'])
                      ->where('invite <= ?', $stats['invite'])
                    ->where('freesignup <= ?', $stats['freesignup'])
                    ->where('premiumsignup <= ?', $stats['premiumsignup']);

            $result = $this->getAdapter()->fetchAll($select);

            return $result;
        }
    }
     public function getallachievementsinfo() {
          $select = $this->select();
                $result = $this->getAdapter()->fetchAll($select);
        return $result;
    }
    public function updateachivements() {
        if (func_num_args() > 0) {
            $id = func_get_arg(0);
            $data = func_get_arg(1);
           if($id != 0){
            $select = $this->select()
                    ->where("achevementsid = " . $id);
            $result = $this->getAdapter()->fetchRow($select);
                $where = array("achevementsid = " . $id);
                $result = $this->update($data, $where);
                return $result;
            } else {
                $response[0] = $this->insert($data);
                if($response[0])
                {
                $response[1]=$this->getAdapter()->lastInsertId();
                return $response;
                }
            }
        }
    }
     public function deleteachivement() {
        if(func_num_args() > 0){
          $id = func_get_arg(0);
            $where = "achevementsid= " . $id;
          try{
                $responseId = $this->delete($where);
            }catch(Exception $e){             
                 return $e->getMessage(); 
            } 
          if($responseId){
                return $responseId;
            }
        }else{
            throw new Exception('Argument Not Passed');
        }
        
        
   }

}

?>