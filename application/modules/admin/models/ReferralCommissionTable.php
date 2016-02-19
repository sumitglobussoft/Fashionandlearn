<?php

class Admin_Model_ReferralCommissionTable extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'referral_commission_table';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_ReferralCommissionTable();
        return self::$_instance;
    }
     /*
     * dev: priyanka varanasi
     * date:14/10/2015
     * desc: to get the commission values from the table 
     * 
     */
    
   public function getReferralCommissionDetails(){
     try {
            $select = $this->select()
                     ->from($this);
        $result = $this->getAdapter()->fetchAll($select);
       if ($result){
            return $result;
        
       }
        } catch (Exception $e) {
                throw new Exception($e);
            }
       
       
   }
    /*
     * dev: priyanka varanasi
     * date:14/10/2015
     * desc: to update the commission vlaues in table
     * 
     */ 
public function updateReferralCommissionDetails(){
  
            if (func_num_args() > 0):
            $comdata = func_get_arg(0); 
            $id = func_get_arg(1);
            try {
                $result = $this->update($comdata, 'com_id = "' . $id . '"');
                if ($result) {
                    return $result;
                } else {
                    return 0;
                }
            } catch (Exception $e) {
                throw new Exception($e);
            }
        else :
            throw new Exception('Argument Not Passed');
        endif;   
    
    
    
    
}
 
}
?>