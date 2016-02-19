<?php

class Admin_Model_Subscription extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'subscription';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Subscription();
        return self::$_instance;
    }
     /* Developer:priyanka varanasi
       Desc : Getting all the mebership users data
    */
      // public function getmembershipusers(){
        // $select = $this->select()
                // ->from(array('sc'=>'subscription'))
                // ->setIntegrityCheck(false)
                // ->joinleft(array('u'=>'users'),'u.user_id = sc.user_id',array('u.user_id','u.first_name','u.last_name','u.password','u.email'))
                // ->order('sc.subscription_id DESC');
        // $result = $this->getAdapter()->fetchAll($select);
        // if ($result) :
            // return $result;
        // endif;
    // } 
	/* Dev: Namrata Singh
	   Date: 28-feb-2015
	   Desc: to get all the subscription plan details from DB
	*/
	public function selectSubscription() {
         
        $select = $this->select();
                 
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }
	/* Dev: Namrata Singh
	   Date: 28-feb-2015
	   Desc: to get all the subscription plan details from DB
	*/
	public function updateSubscription() {
         
          if (func_num_args() > 0) {

            $where = func_get_arg(0);
            $subdata = func_get_arg(1);

            $update = $this->update($subdata, 'subscription_id =' . $where);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    }
	/* Dev: Namrata Singh
	   Date: 28-feb-2015
	   Desc: to get all the subscription plan details from DB based on subscriptio ID
	*/
	public function selectSubscriptionOnId() {
         if (func_num_args() > 0) {

            $sid = func_get_arg(0);
        $select = $this->select()
		         ->from($this)
                 ->where('subscription_id = ?', $sid);
                 
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }
	}
	/* Dev: Namrata Singh
	   Date: 28-feb-2015
	   Desc: to insert subscription plan details to the DB 
	*/
	public function insertSubscriptionPlan() {

      if (func_num_args() > 0) {
            $insertdata = func_get_arg(0);
            try {
                $responseId = $this->insert($insertdata);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($responseId) {
                return $responseId;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
	}
	  public function subdelete(){
          if (func_num_args() > 0):
            $subid = func_get_arg(0);
            try {
                $db = Zend_Db_Table::getDefaultAdapter();
                $where = (array('subscription_id = ?' => $subid));
                $db->delete('subscription', $where);
            } catch (Exception $e) {
                throw new Exception($e);
            }
            return $subid;
        else:
            throw new Exception('Argument Not Passed');
        endif; 
  }
}
