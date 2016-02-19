<?php

class Admin_Model_AdminData extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'admindata';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_AdminData();
        return self::$_instance;
    }

    public function insertAdmindata() {

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

    public function studentPay() {

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this);
        $result = $this->getAdapter()->fetchRow($select);
        if ($result) {
            return $result;
        }
    }

}
