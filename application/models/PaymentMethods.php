<?php

class Application_Model_PaymentMethods extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'paymentmethods';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_PaymentMethods();
        return self::$_instance;
    }

    /*
     * Developer : Namrata Singh
     * Date : 5 feb'15
     * Desc: inserts the card details as a payment method  
     */

    public function insertCardDetail() {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);

            try {
                $responseId = $this->insert($data);

                if ($responseId) {
                    return $responseId;
                } else {
                    throw new Exception('Argument Not Passed');
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 5 feb'15
     * Desc: selects details of card added based on userid 
     */

    public function selectCardDetail() {
        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);


            try {
                $select = $this->select()
                        ->from($this)
                        ->where('user_id = ?', $user_id);
                $result = $this->getAdapter()->fetchAll($select);
                // echo "<pre>"; print_r($result); echo "</pre>"; die;
                if ($result) {
                    return $result;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 6 feb'15
     * Desc: selects details of card added based on userid 
     */

    public function selectCurrentPayment() {
        if (func_num_args() > 0) {
            $paymentid = func_get_arg(0);


            try {
                $select = $this->select()
                        ->from($this)
                        ->where('payment_type_id = ?', $paymentid);
                $result = $this->getAdapter()->fetchRow($select);
                if ($result) {
                    return $result;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 7 feb'15
     * Desc: selects details of cards which are primary based on userid 
     */

    public function selectPrimaryPayment() {

        if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            //$primary = 7;
            try {
                $select = $this->select()
                        ->where('user_id = ?', $user_id)
                        ->where('primary_card = 1');
                $result = $this->getAdapter()->fetchRow($select);
                // echo "<pre>"; print_r($result); die('----');
                if ($result) {
                    return $result;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 7 feb'15
     * Desc: update details of cards which are primary based on userid 
     */

    public function updatePrimaryPayment() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $payment_type_id = func_get_arg(1);

            //$primary = 7;
            try {
                $result = $this->update($data, 'payment_type_id =' . $payment_type_id);
                if ($result) {
                    return $result;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

    /*
     * Developer : Namrata Singh
     * Date : 9 April'15
     * Desc: update card details to 0 when user wants to delete based on userid and card id
     */

    public function updateCard() {
        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $card_id = func_get_arg(1);
           //$db = Zend_Db_Table::getDefaultAdapter();
          
            $deleted = $this->update($data, 'payment_type_id =' . $card_id);
                        echo $deleted; die;
            if ($deleted) {
                return $deleted;
            }
        }
    }

}

?>
