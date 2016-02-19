<?php

class Admin_Model_Payment extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'payment';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Payment();
        return self::$_instance;
    }
     /* Developer:priyanka varanasi
       Desc : Getting all the mebership users data
    */
      public function getpaymembershipusers(){
         try {
        $select = $this->select()
                ->from(array('p'=>'payment'))
                ->setIntegrityCheck(false)
                ->join(array('u'=>'users'),'u.user_id = p.user_id',array('u.user_id','u.first_name','reg_date','u.last_name','u.password','u.email'))
                ->where('p.status =?','paid')
                ->order('p.payment_id DESC');
        $result = $this->getAdapter()->fetchAll($select);
       } catch (Exception $e) {
                throw new Exception('Unable To Insert Exception Occured :' . $e);
            }
               if ($result) {
                $count = 0;
                foreach ($result as $val) {
                    $select1 = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('u' => 'users'))
                            ->where('u.user_id=?', $val['user_id']);
                    $result1 = $this->getAdapter()->fetchRow($select1);
                    $result[$count]['curstatus'] = $result1['status'];
                    $count++;
                }
                return $result;
            }
      
    } 
       /* Developer: Rakesh Jha
      Desc:Get all the Students who is paying
     Dated:01-03-15
     */
   public function  getPaidStudents(){
       
         $select = $this->select()
                ->from(array('p'=>'payment'))
                ->setIntegrityCheck(false)
                ->join(array('s'=>'subscription'),'s.subscription_id = p.subscription_id',array('p.user_id','s.payment_amount'))
                ->where('p.status=?','paid');
         echo $select; die;
         $result = $this->getAdapter()->fetchAll($select);
        if ($result) :
            return $result;
        endif;
        
    }
        public function getPaymentDetails(){
        $select = $this->select()
                ->from(array('p'=>'payment'))
                ->setIntegrityCheck(false)
                ->join(array('u'=>'users'),'u.user_id = p.user_id',array('u.first_name','u.last_name','u.email'));
               
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) :
            return $result;
        endif;
    } 
  

}
