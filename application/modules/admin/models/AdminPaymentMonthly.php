<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Admin_Model_AdminPaymentMonthly extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'adminpaymentmonthly';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_AdminPaymentMonthly();
        return self::$_instance;
    }

    public function insertmonthlyDetails() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $month= func_get_arg(1);
            $year= func_get_arg(2);
            //$where=array('Month'=>$month,'Year'=>$year);
            $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this)
               ->Where('Month=?', $month)
               ->Where('Year=?', $year);
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
         
           $this->update($data, "Month=$month&&Year=$year");
        }
        else{
            try {
                
                $responseId = $this->insert($data);
                
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($responseId) {
                return $responseId;
            }
       
        else {
            throw new Exception('Argument Not Passed');
        }
        
    }
        }
    }

    public function getAnnualData() {

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this);
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }

    public function getAllincome() {

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this);
        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }

    public function currentmonth() {
        if (func_num_args() > 0) {
            $month = func_get_arg(0);
            $year = func_get_arg(1);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->Where('Month=?', $month)
                    ->Where('Year=?', $year);
 
            $result = $this->getAdapter()->fetchRow($select);
       
                   
            if ($result) {
                return $result;
            }
        }
    }
      public function currentyear() {
        if (func_num_args() > 0) {
            $month = func_get_arg(0);
            $year = func_get_arg(1);
            $select = $this->select()
                    ->setIntegrityCheck(false)
                    ->Where('Month=?', $month)
                    ->Where('Year=?', $year);
//            echo $select; die;
            $result = $this->getAdapter()->fetchAll($select);
                    
                   
            if ($result) {
                return $result;
            }
        }
    }

}
