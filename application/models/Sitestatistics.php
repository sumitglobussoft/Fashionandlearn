<?php

class Application_Model_Sitestatistics extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'sitestatistics';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Sitestatistics();
        return self::$_instance;
    }

    //abhishek M
    public function insertstatistics() {
        if (func_num_args() > 0) {
            $typeid = func_get_arg(0);
            $date = gmdate('Y-m-d', time());



            $select = $this->select()
                    ->where("date=?", $date);
            $result = $this->getAdapter()->fetchRow($select);
            
           
            
            $data = array();
            switch ($typeid) {
                case "freeuser":
                    $data["freeuser"] = 1;
                    break;

                case "trialing":
                    $data["trailing"] = 1;
                    break;

                case "paid":
                    $data["premium"] = 1;
                    break;
                case "visit":
                    $data["visit"] = 1;
                    break;

                case "default":
                    $data["freeuser"] = 1;
                    break;
            }


            if ($result) {
                $where["date"] = $date;
              
                foreach ($data as $key => $value) {
                    $data[$key] += $result[$key];
                    
                    $res = $this->update($data,"`date` ='$date'");
                }
            } else {
                $data["date"] = $date;
                $res = $this->insert($data);
            }
        }
    }
    
    public function removestatistics() {
        
          
            $date = gmdate('Y-m-d', time());
            
           $data=array("visit"=>new Zend_Db_Expr("visit - 1")); 
            try{
             $result = $this->update($data, "date ='$date'");
            
           }
 catch (Exception $e)
 {
     
 }
    
        
    }
    

    public function getdaysstatistics() {

        $date1 = gmdate('Y-m-d', time());
      
        $date = strtotime($date1 . '-1 week');
          $date = date('Y-m-d', $date);
       

        $select = $this->select()
                ->where("date>='$date'")
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
                    ->from(array('p' => 'payment_new'),array('count(*) as count'))
                    ->where('p.customer_status=3 and p.subscription_start="'.$value["date"].'"');
                     $results = $this->getAdapter()->fetchRow($select);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment_new'),array('count(*) as count'))
                    ->where('p.customer_status=2 and p.trail_start="'.$value["date"].'"');
                     $resultss = $this->getAdapter()->fetchRow($select);         
                    
            
            
            
            
            array_push($data["labels"], $stamp);
            array_push($data["free"], $value["freeuser"]);
            array_push($data["premium"], $results["count"]);
            array_push($data["trailing"], $resultss["count"]);
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
                    ->from(array('p' => 'payment_new'),array('count(*) as count'))
                    ->where('p.customer_status=3 and p.subscription_start>"'.$date.'" and p.subscription_start<="'.$date1.'"');
                     $results = $this->getAdapter()->fetchRow($select);
             $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment_new'),array('count(*) as count'))
                    ->where('p.customer_status=2 and p.trail_start>"'.$date.'" and p.trail_start<="'.$date1.'"');
                     $resultss = $this->getAdapter()->fetchRow($select);        


            array_push($data["labels"], "week " . $i);
            array_push($data["free"], $result["freeuser"]);
            array_push($data["premium"], $results["count"]);
            array_push($data["trailing"], $resultss["count"]);
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
                    ->from(array('p' => 'payment_new'),array('count(*) as count'))
                    ->where('p.customer_status=3 and p.subscription_start>="'.$date.'" and p.subscription_start<"'.$date1.'"');
                     $results = $this->getAdapter()->fetchRow($select);
             
              $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'payment_new'),array('count(*) as count'))
                    ->where('p.customer_status=2 and p.trail_start>="'.$date.'" and p.trail_start<"'.$date1.'"');
                     $resultss = $this->getAdapter()->fetchRow($select);        
            $stamp = date("M", strtotime($date));

            array_push($data["labels"], $stamp);
            array_push($data["free"], $result["freeuser"]);
            array_push($data["premium"], $results["count"]);
            array_push($data["trailing"], $resultss["count"]);
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

?>