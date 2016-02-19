<?php

class Application_Model_Payment extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'payment';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Payment();
        return self::$_instance;
    }

    /*
     * Developer : Namrata Singh
     * Date : 4 feb'15
     * Desc: Selects data from table based on userid passed.
     */

    public function selectMemberships() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $data = array('paid', 'trialing', 'free', 'canceled', 'unpaid');

            $select = $this->select()
                    //->from($this)
                    ->where('user_id = ?', $userid)
                    ->where('status IN(?)', $data)
                    ->order('payment_id DESC')
                    ->limit(1);
//                ->orwhere('status = ?', 'paid');
            //echo $select;die;
            $result = $this->getAdapter()->fetchRow($select);
            //echo "<pre>"; print_r($result); echo "<pre>"; die('in select member model'); 
            if ($result) {
                return $result;
            } else {
                return 1;
            }
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 25 feb'15
     * Desc: inserts the payment details after successfull payment
     */

    public function insertPayment() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);

            try {
                $responseId = $this->insert($data);
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

    /*
     * Developer : Namrata Singh
     * Date : 4 March'15
     * Desc: Selects subscription based on userid passed.
     */

    public function selectSub() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);

            $select = $this->select()
                    ->from($this)
                    ->where('user_id = ?', $userid);

            $result = $this->getAdapter()->fetchAll($select);
            if ($result) {
                return $result;
            }
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 7 March'15
     * Desc: update status after checking status from the pagar
     */

    public function updateStatus() {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where = func_get_arg(1);

            $update = $this->update($data, 'payment_id =' . $where);
            if ($update) {
                $select = $this->select()
                        ->where('payment_id = ?', $where);

                $result = $this->getAdapter()->fetchRow($select);
               
                if ($result) {
                    return $result;
                }
            } else {
                return 0;
            }
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 10 March'15
     * Desc: update which is recent payment in the DB
     */

    public function updateRecentPayment() {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where = func_get_arg(1);
            $update = $this->update($data, 'user_id =' . $where);

            if (isset($update)) {
                return $update;
            } else {
                throw new Exception('Argument Not Passed');
            }
        }
    }

    public function selectTrialMember() {

        if (func_num_args() > 0) {
            $userid = func_get_arg(0);

            $status = 'trialing';
            $select = $this->select()
                    ->where('user_id = ?', $userid)
                    ->where('status = ?', $status);
//                ->orwhere('status = ?', 'paid');

            $result = $this->getAdapter()->fetchAll($select);

            //echo "<pre>"; print_r($result); echo "<pre>"; die('in select member model'); 
            if ($result) {
                return $result;
            } else {
                return 1;
            }
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 11 march'15
     * Desc: Selects cancel member data from table based on userid passed.
     */

    public function selectCancelMemberships() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $data = array('canceled');

            $select = $this->select()
                    //->from($this)
                    ->where('user_id = ?', $userid)
                    ->where('status IN(?)', $data);
//                ->orwhere('status = ?', 'paid');
            //echo $select;die;
            $result = $this->getAdapter()->fetchAll($select);
            //echo "<pre>"; print_r($result); echo "<pre>"; die('in select member model'); 
            if ($result) {
                return $result;
            }
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 12 march'15
     * Desc: selects all the paid members from the DB 
     */

    public function selectMember() {
        try {
            $select = $this->select()
                    ->where('status = ?', 'paid');

            $result = $this->getAdapter()->fetchAll($select);
            //  echo "<pre>"; print_r($result); die('----');
        } catch (Exception $exc) {
            throw new Exception('Unable to update, exception occured' . $exc);
        }

        if ($result) {
            return $result;
            // }
        }
    }

    /*
      Developer: Namrata Singh
      Date : 13 march'15
      Desc: selects the recent payment made
     */

    public function bonusFreeMonth() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from($this)
                        ->where('user_id = ?', $user_id)
                        ->order('payment_id DESC')
                        ->limit(1);

                $result = $this->getAdapter()->fetchAll($select);
                //echo "<pre>"; print_r($result); die('----');		
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            //  echo "<pre>"; print_r($result); die('----');
            if ($result) {
                return $result;
            }
        }
    }

    /*
      Developer: Namrata Singh
      Date : 14 march'15
      Desc: selects the members whose status is free and referrals count is 0
     */

    public function selectBonus() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            $status = "free";
            try {
                $select = $this->select()
                        ->from(array('p' => 'payment'), array('p.user_id', 'p.status'))
                        ->setIntegrityCheck(false)
                        ->join(array('u' => 'users'), 'u.user_id = p.user_id')
                        ->where('p.status = ?', $status)
                        ->where('u.referral_counts = 0')
                        ->where('p.user_id = ?', $user_id)
                        ->order('payment_id DESC')
                        ->limit(1);

                $result = $this->getAdapter()->fetchAll($select);
                //echo "<pre>"; print_r($result); die('----');	
                if (isset($result)) {
                    return $result;
                } else {
                    
                }
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            //  echo "<pre>"; print_r($result); die('----');
            if ($result) {
                return $result;
            }
        }
    }

    /*
      Developer: Namrata Singh
      Date : 16 march'15
      Desc: selects the status of user based on payment id
     */

    public function getStatus() {
        if (func_num_args() > 0) {

            $where = func_get_arg(0);

            try {
                $select = $this->select()
                        ->where('payment_id = ?', $where);

                $result = $this->getAdapter()->fetchAll($select);
                //  echo "<pre>"; print_r($result); die('----');
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            if ($result) {
                return $result;
            }
        }
    }

    /*
      Developer: Namrata Singh
      Date : 19 march'15
      Desc: selects the status of user based on payment id
     */

    public function getPaymentType() {
        if (func_num_args() > 0) {
            $pid = func_get_arg(0);

            try {
                $select = $this->select()
                        ->from(array('p' => 'payment'), array('p.subscription_id', 'p.payment_method'))
                        ->setIntegrityCheck(false)
                        ->joinleft(array('s' => 'subscription'), 's.subscription_id = p.subscription_id', array('s.payment_type'))
                        ->where('p.payment_id = ?', $pid);

                $result = $this->getAdapter()->fetchRow($select);
                //echo $select; die;
                // echo "<pre>"; print_r($result); die('##');
            } catch (Exception $exc) {
                throw new Exception('Unable to update, exception occured' . $exc);
            }
            if ($result) {
                return $result;
            }
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 1 April'15
     * Desc: Selects bill made till date based on userid passed.
     */

    public function selectBill() {
        if (func_num_args() > 0) {
            $userid = func_get_arg(0);
            $status = array('paid', 'canceled');
           $select = $this->select()
                    ->from(array('p' => 'payment'))
                    ->distinct()
                    ->setIntegrityCheck(false)
                    ->join(array('s' => 'subscription'), 's.subscription_id = p.subscription_id', array('s.payment_type'))
                    ->join(array('pm' => 'paymentmethods'), 'p.card_last_digits = pm.card_last_digits', array('pm.card_type'))
                    ->where('p.user_id = ?', $userid)
                    ->where('status IN(?)',$status)
                    ->where('pm.user_id = ?', $userid);
           //echo $select; die;
            $result = $this->getAdapter()->fetchAll($select);
            //echo "<pre>"; print_r($result); die('@@');
            if ($result) {
                return $result;
            }
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 8 April'15
     * Desc: inserts the payment details after successfull payment
     */

    public function updatePayment() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $where = func_get_arg(1);
            try {
                $update = $this->update($data, 'payment_id =' . $where);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($update) {
                return $update;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    
    
    
    
    
    
     public function getdaysstatistics() {

        $date1 = gmdate('Y-m-d', time());
      
        $date = strtotime($date1 . '-1 week');

        $select = $this->select()
                ->where("date>'$date'")
                ->where("date<='$date1'")
                ->order("date asc");

        $result = $this->getAdapter()->fetchAll($select);

        $data = array();
        $data["labels"] = array();
        $data["free"] = array();
        $data["premium"] = array();
        $data["trailing"] = array();
        $data["total"] = array();
        foreach ($result as $value) {

            $stamp = date("D", strtotime($value["date"]));
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment'),array('sum(amount) as sum'))
                    ->join(array('s' => 'subscription'),"p.subscription_type=s.subscription_type")
                    ->where('p.current_period_start="'.$value["date"].'"')
                    ->where('s.payment_type=1');
                           
                     $results = $this->getAdapter()->fetchRow($select);
                     die();
                    
            
            
            
            
            array_push($data["labels"], $stamp);
            array_push($data["free"], $value["freeuser"]);
            array_push($data["premium"], $results["count"]);
            array_push($data["trailing"], $value["trailing"]);
            array_push($data["total"], $value["visit"]);
        }
      
        if ($data) {
            return $data;
        }
    }

    public function getweekssstatistics() {

        $date = gmdate('Y-m-d', time());

//          $date = strtotime($date . ' +1 week');
//           $date=date('Y-m-d', $date);
//           



        $data["labels"] = array();
        $data["free"] = array();
        $data["premium"] = array();
        $data["trailing"] = array();
        $data["total"] = array();

        for ($i = 1; $i <= 5; $i++) {
            $date = strtotime($date . '-' . $i . ' week');
            $date = date('Y-m-d', $date);

            $date1 = strtotime($date . ' +1 week');
            $date1 = date('Y-m-d', $date1);



            $select = $this->select()
                    ->from('sitestatistics', array('sum(`freeuser`) as freeuser', 'sum(`trailing`) as trailing', 'sum(`premium`) as premium','sum(`visit`) as visit'))
                    ->where("date>'$date'")
                    ->where("date<='$date1'");
           

            $result = $this->getAdapter()->fetchRow($select);
            
             $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment'),array('count(*) as count'))
                    ->where('p.status="paid" and p.current_period_start>"'.$date.'" and p.current_period_start<="'.$date1.'"');
                     $results = $this->getAdapter()->fetchRow($select);


            array_push($data["labels"], "week " . $i);
            array_push($data["free"], $result["freeuser"]);
            array_push($data["premium"], $results["count"]);
            array_push($data["trailing"], $result["trailing"]);
            array_push($data["total"], ($result["visit"]));
        }

        $data["free"] = array_reverse($data["free"]);
        $data["premium"] = array_reverse($data["premium"]);
        $data["trailing"] = array_reverse($data["trailing"]);
        $data["total"] = array_reverse($data["total"]);

        if ($data) {
            return $data;
        }
    }

    public function getmonthsstatistics() {

        $datet = gmdate('Y-m-d', time());
    $month= date("m", strtotime($datet));
 $year=date("Y", strtotime($datet));


//           $date = strtotime($date . ' +1 week');
//           $date=date('Y-m-d', $date);
//           



        $data["labels"] = array();
        $data["free"] = array();
        $data["premium"] = array();
        $data["trailing"] = array();
        $data["total"] = array();
      
        for ($i = 1; $i <= 6; $i++) {
        
            if($month==0)
            {
                $date = "$year-01-01";
                $month=12;
                $year--;
                $date1 = "$year-12-01";
                 
               
            }
            else
            {
               $month++;
                 $date1 = "$year-$month-01";
                  $month--;
                  $date = "$year-$month-01";
                
            }    
                   $month--; 

            $select = $this->select()
                    ->from('sitestatistics', array('sum(`freeuser`) as freeuser', 'sum(`trailing`) as trailing', 'sum(`premium`) as premium','sum(`visit`) as visit'))
                    ->where("date>='$date'")
                    ->where("date<'$date1'");


            $result = $this->getAdapter()->fetchRow($select);

             $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment'),array('count(*) as count'))
                    ->where('p.status="paid" and p.current_period_start>="'.$date.'" and p.current_period_start<"'.$date1.'"');
                     $results = $this->getAdapter()->fetchRow($select);
            $stamp = date("M", strtotime($date));

            array_push($data["labels"], $stamp);
            array_push($data["free"], $result["freeuser"]);
            array_push($data["premium"], $results["count"]);
            array_push($data["trailing"], $result["trailing"]);
            array_push($data["total"], ($result["visit"]));
        }
    // die();
        $data["labels"] = array_reverse($data["labels"]);
        $data["free"] = array_reverse($data["free"]);
        $data["premium"] = array_reverse($data["premium"]);
        $data["trailing"] = array_reverse($data["trailing"]);
        $data["total"] = array_reverse($data["total"]);

        if ($data) {
            return $data;
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
