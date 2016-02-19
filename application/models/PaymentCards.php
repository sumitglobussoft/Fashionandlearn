<?php
//DEV: priyanka varanasi
//DESC: Paymentcards modal created
//DATE: 22/8/2015
class Application_Model_PaymentCards extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'payment_cards';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_PaymentCards();
        return self::$_instance;
    }
/*
 * dev:priyanka varanasi
 * date:22/8/2015
 * desc: to insert the card details of session user
 * 
 */
      public function insertPagarCardInfo() {

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
 * dev:priyanka varanasi
 * desc: to get all card details for session user
 * date : 1/9/2015
 */
    
    public function getAllCardsDetailsOfUsers(){
        
            if (func_num_args() > 0) {
            $user_id = func_get_arg(0);
            try {
                $select = $this->select()
                        ->from($this)
                        ->where('user_id =?', $user_id);
                $result = $this->getAdapter()->fetchAll($select);

                if ($result){
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            throw new Exception("Argument not passed");
        }
        
    }
 /*
 * dev:priyanka varanasi
 * desc: to change the primary status of cards
 * date : 1/9/2015
 */ 
public function  UpdateCardsPrimarystatus(){
          if(func_num_args()>0){
            $userid = func_get_arg(0);
            $data = func_get_arg(1);
            
            try {
               $result = $this->update($data, 'user_id =' . $userid);
                if ($result) {
                    return $result;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }else{
            throw new Exception("Argument not passed");
        } 
    
}
 /*
 * dev:priyanka varanasi
 * desc: to make the card primary
 * date : 1/9/2015
 */

public function makeThisCardPrimary(){
       if(func_num_args()>0){
            
            $cardid = func_get_arg(0);
            $userid = func_get_arg(1);
             $data['primary'] = 0;
            
            try {
               $result = $this->update($data, 'user_id =' . $userid);
                if ($result) {
                    $dat['primary']= '1';
                    $res = $this->update($dat, 'ID =' . $cardid);
                    
                     if($res){
                    return $res;
                     }
                }else{
                  $dat['primary']= '1';
                    $res = $this->update($dat, 'ID =' . $cardid);
                    
                     if($res){
                    return $res;   
                }
            } }catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }else{
            throw new Exception("Argument not passed");
        }
}

 /*
 * dev:priyanka varanasi
 * desc: to delete this card from db
 * date : 1/9/2015
 */
public function deleteThisCard(){

  if (func_num_args() > 0):
            $crid = func_get_arg(0);
            try {
                $db = Zend_Db_Table::getDefaultAdapter();
                $where = (array('ID = ?' => $crid));
                $db->delete('payment_cards', $where);
            } catch (Exception $e) {
                throw new Exception($e);
            }
            return $crid;
        else:
            throw new Exception('Argument Not Passed');
        endif;

}

/*
 * dev:priyanka varanasi
 * date:22/8/2015
 * desc: to insert the card details of session user
 * 
 */
      public function insertPagarCardInfoWithUpdate() {

        if (func_num_args() > 0) {
            $data = func_get_arg(0);
            $userid = func_get_arg(1);
            $dat['primary'] = 0;
           try {
               $result = $this->update($dat, 'user_id =' . $userid);
               $responseId = $this->insert($data);
               
            }catch (Exception $e) {
                return $e->getMessage();
            }

            if ($responseId) {
                return $responseId;
            }
        } else {
            throw new Exception('Argument Not Passed');
        }
    }

}
?>