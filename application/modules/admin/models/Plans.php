<?php
 /*
     * dev: priyanka varanasi
     * date:27/8/2015
     * desc: Plan Modal design
     * 
     */
class Admin_Model_Plans extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'plans';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Plans();
        return self::$_instance;
    }

      /*
     * dev: priyanka varanasi
     * date:27/8/2015
     * desc: to get the details from db
     * 
     */
    
   public function getPlanDetails(){
    
            $select = $this->select()
                     ->from($this)
                  ->setIntegrityCheck(false);
        $result = $this->getAdapter()->fetchAll($select);
       if ($result){
            return $result;
        
       } 
  
   }
   
   /*
     * dev: priyanka varanasi
     * date:27/8/2015
     * desc: to get the paln details on the basis of id
     * 
     */
 
 	public function selectPlanOnId() {
         if (func_num_args() > 0) {
        $sid = func_get_arg(0);
        $select = $this->select()
		         ->from($this)
                         ->where('plan_type_id = ?', $sid);
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }
	}
  
   	  /*
     * dev: priyanka varanasi
     * date:27/8/2015
     * desc: to update plan on the basis of id
     * 
     */     
    public function updatePlanInfo() {
         
          if (func_num_args() > 0) {

            $where = func_get_arg(0);
            $subdata = func_get_arg(1);

            $update = $this->update($subdata, 'plan_type_id =' . $where);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    }

 }


?>