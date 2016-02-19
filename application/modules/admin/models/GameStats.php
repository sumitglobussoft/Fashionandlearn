<?php

class Admin_Model_GameStats extends Zend_Db_Table_Abstract {
    
    private static $_instance = null;
    protected $_name = 'game_stats';
    
    private function  __clone() { } //Prevent any copy of this object
	
    public static function getInstance(){
		if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
		self::$_instance = new Admin_Model_GameStats();
		return self::$_instance;
    }
    

    
    public function getGameStats(){
        if (func_num_args() > 0){
            $currentDate = func_get_arg(0);
            $extendedDate = func_get_arg(1);
            
            $sql = $this->select()
                       ->from($this)
                       ->where('game_date >= ?',$currentDate)
                       ->where('game_date <= ?',$extendedDate);
               try {
                   $result = $this->getAdapter()->fetchAll($sql);
                   
                     if($result){
                         return $result;
                     }
               } catch (Exception $exc) {
                   echo $exc->getTraceAsString();
               }    
        }
    }
    
    public function checkGameStatsByDate(){
        if (func_num_args() > 0){
            $currentDate = func_get_arg(0);            
            
            $sql = $this->select()
                       ->from($this,array("gs_id"=>"COUNT(*)"))
                       ->where('game_date = ?',$currentDate);
               try {
                   $result = $this->getAdapter()->fetchRow($sql);
                   
                     if($result){
                         return $result;
                     }
               } catch (Exception $exc) {
                   echo $exc->getTraceAsString();
               }    
        }
    }    
    
    public function getGameStatsByID(){
        if (func_num_args() > 0){
            $matchID = func_get_arg(0);            
            
            $sql = $this->select()
                       ->from($this)
                       ->where('game_date = ?',$currentDate);
               try {
                   $result = $this->getAdapter()->fetchRow($sql);
                   
                     if($result){
                         return $result;
                     }
               } catch (Exception $exc) {
                   echo $exc->getTraceAsString();
               }    
        }
    }       
    
    
    public function getGameStatsByDate(){
        if(func_num_args()>0){            
            $game_date = func_get_arg(0);
            
                $weekDate = date('Y-m-d',strtotime($game_date. " +1 week"));    
                $sql = $this->select()
                        ->from($this,array('game_date','game_stat'))                        
                        ->where('game_date >= ?',$game_date)
                        ->where('game_date <= ?',$weekDate);
            
            
             $sql = stripslashes($sql); 
//             echo $sql;die;
                try {
                     $result = $this->getAdapter()->fetchRow($sql);
//                    $result = $this->getAdapter()->fetchAll($sql); // change fetchRow to fetchAll (Manoj)

                    if($result){
                        return $result;
                    }
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                }
        }else{
            throw new Exception("Argument not passed");
        }  
    }
    
    public function getFutureGameStats(){
        if(func_num_args()>0):           
            $game_date = func_get_arg(0);            
               
            //$weekDate = date('Y-m-d',strtotime($game_date. " +{$days} days"));
            
            $sql = $this->select()
                    ->from($this,array('game_date','game_stat'))
                    ->where('game_date >= ?',$game_date)
                    //->where('game_date <= ?',$weekDate)
                    ->limit(2); 
            
            
             $sql = stripslashes($sql); 
//             echo $sql;
                try {
                    $result = $this->getAdapter()->fetchAll($sql); // change fetchRow to fetchAll (Manoj)

                    if($result){
                        return $result;
                    }
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                }
        else:
            throw new Exception("Argument not passed");
        endif;  
    }
    
}
?>
