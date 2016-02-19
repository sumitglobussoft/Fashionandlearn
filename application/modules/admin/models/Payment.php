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
                  ->distinct()
                  ->from(array('p'=>'payment'))
                ->setIntegrityCheck(false)
                ->join(array('s'=>'subscription'),'s.subscription_id = p.subscription_id',array('p.user_id','s.payment_amount'))
                ->where('p.status=?','paid');
         $result = $this->getAdapter()->fetchAll($select);
        if ($result) :
            return $result;
        endif;
        
    }
      public function currentmonth() {
       
            $select = $this->select()
                    ->from(array('p'=>'payment'))
                    ->setIntegrityCheck(false)
//                     ->join(array('s'=>'subscription'),'s.subscription_id = p.subscription_id')
                    ->Where('p.status=?','paid')
                    ->Where('p.subscription_id=?',1)
                    ->orWhere('p.subscription_id=?',3);
            $result = $this->getAdapter()->fetchAll($select);
                   
            if ($result) {
                return count($result);
            }
        
    }
    
     public function  countPaidStudents(){
       
         $select = $this->select()
                  ->distinct()
                  ->from(array('p'=>'payment'),'user_id')
                ->setIntegrityCheck(false)
                ->where('status=?','paid');
         $result = $this->getAdapter()->fetchAll($select);
        if ($result) :
            $result=count($result);
            return $result;
        endif;
        
    }
        public function getPaymentDetails(){
        $select = $this->select()
                ->distinct()
                ->from(array('p'=>'payment'))
                ->setIntegrityCheck(false)
                ->join(array('u'=>'users'),'u.user_id = p.user_id',array('u.first_name','u.last_name','u.email'));
//        echo $select;die;
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) :
            return $result;
        endif;
    } 
    
    public function  gettrailusers(){
        
         $select = $this->select()
                  ->distinct()
                  ->from(array('p'=>'payment'),'user_id')
                ->setIntegrityCheck(false)
                ->where('status=?','trialing');
       
         $result = $this->getAdapter()->fetchAll($select);
        if ($result) :
            $result=count($result);
            return $result;
        endif;
        
    }
  
public function getdaysstatistics() {

        $date1 = gmdate('Y-m-d', time());
      
        $date = strtotime($date1 . '-1 week');

        $select = $this->select()
                ->from(array('s'=>'sitestatistics'))
                ->setIntegrityCheck(false)
                ->where("s.date>'$date'")
                ->where("s.date<='$date1'")
                ->order("s.date asc");

        $result = $this->getAdapter()->fetchAll($select);
      

        $data = array();
        $data["labels"] = array();
        $data["submonthly"] = array();
        $data["subAnual"] = array();
        $data["total"] = array();
        foreach ($result as $value) {

            $stamp = date("D", strtotime($value["date"]));
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment'),array('sum(amount) as sum'))
                    ->join(array('s' => 'subscription'),"p.subscription_id=s.subscription_id")
                    ->where('p.current_period_start="'.$value["date"].'"')
                   ->where('p.status="paid"')
                    ->where('s.payment_type=1');
                           
                     $results = $this->getAdapter()->fetchRow($select);
                     
             $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment'),array('sum(amount) as sum'))
                    ->join(array('s' => 'subscription'),"p.subscription_id=s.subscription_id")
                    ->where('p.current_period_start="'.$value["date"].'"')
                   ->where('p.status="paid"')
                    ->where('s.payment_type=2');
                           
                     $resultss = $this->getAdapter()->fetchRow($select);        
           
                    
            
            if($results["sum"]==NULL)
               $results["sum"]=0; 
             if($resultss["sum"]==NULL)
               $resultss["sum"]=0; 
            
            
            array_push($data["labels"], $stamp);
            array_push($data["submonthly"], $results["sum"]);
            array_push($data["subAnual"], $resultss["sum"]);
           
            array_push($data["total"], $results["sum"]+$resultss["sum"]);
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



        $data = array();
        $data["labels"] = array();
        $data["submonthly"] = array();
        $data["subAnual"] = array();
        $data["total"] = array();

        for ($i = 1; $i <= 5; $i++) {
            $date = strtotime($date . '-' . $i . ' week');
            $date = date('Y-m-d', $date);

            $date1 = strtotime($date . ' +1 week');
            $date1 = date('Y-m-d', $date1);



            
           $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment'),array('sum(amount) as sum'))
                    ->join(array('s' => 'subscription'),"p.subscription_id=s.subscription_id")
                    ->where('p.current_period_start>"'.$date.'"')
                    ->where('p.current_period_start<="'.$date1.'"')
                   ->where('p.status="paid"')
                    ->where('s.payment_type=1');

            $results = $this->getAdapter()->fetchRow($select);
            
        $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment'),array('sum(amount) as sum'))
                    ->join(array('s' => 'subscription'),"p.subscription_id=s.subscription_id")
                    ->where('p.current_period_start>"'.$date.'"')
                    ->where('p.current_period_start<="'.$date1.'"')
                   ->where('p.status="paid"')
                    ->where('s.payment_type=2');
        
                     $resultss = $this->getAdapter()->fetchRow($select);
                     
                      if($results["sum"]==NULL)
               $results["sum"]=0; 
             if($resultss["sum"]==NULL)
               $resultss["sum"]=0; 


            array_push($data["labels"], "week " . $i);
         array_push($data["submonthly"], $results["sum"]);
            array_push($data["subAnual"], $resultss["sum"]);
           
            array_push($data["total"], $results["sum"]+$resultss["sum"]);
        }

        $data["submonthly"] = array_reverse($data["submonthly"]);
        $data["subAnual"] = array_reverse($data["subAnual"]);
     
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



       $data = array();
        $data["labels"] = array();
        $data["submonthly"] = array();
        $data["subAnual"] = array();
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
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment'),array('sum(amount) as sum'))
                    ->join(array('s' => 'subscription'),"p.subscription_id=s.subscription_id")
                    ->where('p.current_period_start>="'.$date.'"')
                    ->where('p.current_period_start<"'.$date1.'"')
                   ->where('p.status="paid"')
                    ->where('s.payment_type=1');

            $results = $this->getAdapter()->fetchRow($select);
            
            

              $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment'),array('sum(amount) as sum'))
                    ->join(array('s' => 'subscription'),"p.subscription_id=s.subscription_id")
                    ->where('p.current_period_start>="'.$date.'"')
                    ->where('p.current_period_start<"'.$date1.'"')
                   ->where('p.status="paid"')
                    ->where('s.payment_type=2');

            $resultss = $this->getAdapter()->fetchRow($select);
                     
                     if($results["sum"]==NULL)
               $results["sum"]=0; 
             if($resultss["sum"]==NULL)
               $resultss["sum"]=0; 
                     
                     
            $stamp = date("M", strtotime($date));

            array_push($data["labels"], $stamp);
             array_push($data["submonthly"], $results["sum"]);
            array_push($data["subAnual"], $resultss["sum"]);
           
            array_push($data["total"], $results["sum"]+$resultss["sum"]);
        }
    // die();
        $data["labels"] = array_reverse($data["labels"]);
        $data["submonthly"] = array_reverse($data["submonthly"]);
        $data["subAnual"] = array_reverse($data["subAnual"]);
     
        $data["total"] = array_reverse($data["total"]);

        if ($data) {
            return $data;
        }
    }
    public  function currentmonthannual(){
         if (func_num_args() > 0) {
            $c=  func_get_arg(0);
            $p=  func_get_arg(1);
            $select = $this->select()
                     ->setIntegrityCheck(false)
                    ->Where('current_period_start>=?',$p)
                    ->Where('current_period_start<=?',$c)
                    ->Where('status=?','paid')
                    ->Where('subscription_id=?',4)
                    ->Where('subscription_id=?',5);
             $result = $this->getAdapter()->fetchAll($select);
       
//                   echo '<pre>';                   print_r($result); die;
            if ($result) {
                return $result;
            }
        }
        
    }
}
