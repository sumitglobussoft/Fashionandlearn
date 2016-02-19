<?php
 /*
     * dev: priyanka varanasi
     * date:24/8/2015
     * desc: Coupon Modal design
     * 
     */
class Admin_Model_Coupons extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'coupons';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Coupons();
        return self::$_instance;
    }
   
    /*
     * dev: priyanka varanasi
     * date:24/8/2015
     * desc: to insert the coupon details 
     * 
     */
    public function insertCouponDetails(){
        
            if(func_num_args()>0):
            $data = func_get_arg(0);
            if($data):
               $result = $this->insert($data);
            return $result;
            endif;
        else:
            throw new Exception("Argument not passed");
        endif;  
        
    }
      /*
     * dev: priyanka varanasi
     * date:24/8/2015
     * desc: to get the coupon detailsfrom db 
     * 
     */
    
   public function getCouponDetails(){
    
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
     * date:24/8/2015
     * desc: to get the coupon details from db of particular coupon
     * 
     */
   public function getCouponDetailsById() {
       
               if (func_num_args() > 0){
            $edid = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from($this)
                        ->setIntegrityCheck(false)
                        ->where('coupon_id =?',$edid);

                $result = $this->getAdapter()->fetchRow($select);
               
                if ($result) :
                    return $result;
                endif;
            } catch (Exception $e) {
                throw new Exception($e);
            }
            }
        else{
            throw new Exception('Argument Not Passed');
        } 
       
   }
        /*
     * dev: priyanka varanasi
     * date:24/8/2015
     * desc: to update coupon details 
     * 
     */
   public function updateCouponDetails(){
       
            if (func_num_args() > 0):
            $edid = func_get_arg(0);
            $coupondata = func_get_arg(1); 
            try {
                $result = $this->update($coupondata, 'coupon_id = "' . $edid . '"');
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
       /**
     * Developer : priyanka varanasi
     * date:24/8/2015
      * Description : to delete coupon from db
     */
    public function couponDelete() {
        if (func_num_args() > 0):
            $edid = func_get_arg(0);
            try {
                $db = Zend_Db_Table::getDefaultAdapter();
                $where = (array('coupon_id = ?' => $edid));
                $db->delete('coupons', $where);
            } catch (Exception $e) {
                throw new Exception($e);
            }
            return $edid;
        else:
            throw new Exception('Argument Not Passed');
        endif;
    }
}

?>