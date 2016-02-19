<?php
 /*
     * dev: priyanka varanasi
     * date:24/8/2015
     * desc: Coupon Modal design
     * 
     */
class Application_Model_Coupons extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'coupons';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Coupons();
        return self::$_instance;
    }
   
    /*
     * dev: priyanka varanasi
     * date:24/8/2015
     * desc: to get the coupon details on the basis of coupon code 
     * 
     */
    
  public function checkCouponCode(){
    
         if (func_num_args() > 0) {
            $couponcode = func_get_arg(0);
            $currdate = date('Y-m-d');
           try {

                $select = $this->select()
                        ->from($this)
                        ->where('coupon_code = ?', $couponcode)
                        ->where('coupon_enddate >=?', $currdate)
                        ->where('coupon_startdate <=?', $currdate)
                        ->where('coupon_limit >0');
 
                $result = $this->getAdapter()->fetchRow($select);
                if ($result) {
                    return $result;
                } else {
                    return 0;
                }
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
   
      /*
     * dev: priyanka varanasi
     * date:25/8/2015
     * desc: to decrease the coupon limit
     * 
     */
   public function updateCouponLimit($couponid){
        $decreasecount = 1;
        $data = array('coupon_limit' => new Zend_Db_Expr('coupon_limit - '.$decreasecount));
        $result = $this->update($data, "coupon_id = $couponid and coupon_limit>0");
                      
        if ($result) {
            return $result;
        }
    } 
   
}

?>