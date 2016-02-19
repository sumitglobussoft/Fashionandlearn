<?php
class Static_Model_Referals extends Zend_Db_Table_Abstract
{    
    private static $_instance = null;
    protected $_name = 'refer_friends';    
    
    private function  __clone() { } //Avoid Cloning
	
    public static function getInstance(){
		if( !is_object(self::$_instance) )  
		self::$_instance = new Static_Model_Referals();
		return self::$_instance;
    }
    /**
     * Developer    : vivek Chaudhari
     * Date         : 02/07/2014
     * Description  :  Get all offer details
     */
    public function getReferalDeatails(){
        if(func_num_args()>0):
            $userId = func_get_arg(0);
            $select = $this->select()
                        ->where('ref_by=?',$userId);

            $result = $this->getAdapter()->fetchAll($select);
                if($result):
                    return $result;
                endif;
        else:
          throw new Exception("Argument not passed");
        endif;
        
    }
    
     /**
     * Developer    : vivek Chaudhari
     * Date         : 02/07/2014
     * Description  : add new referals or update count of request
     */
    public function addReferal(){
        if(func_num_args()>0):
            $data = func_get_arg(0);
        
             $select = $this->select()
                        ->setIntegrityCheck(false)
                        ->from(array('r'=>'refer_friends'),array('r.email','r.ref_id'))
                        ->where('email = ?',$data['email'])
                        ->where('ref_by = ?',$data['ref_by']);
                $result = $this->getAdapter()->fetchRow($select); 
                if($data['email']==$result['email']):
                    $udata = array('req_count' => new Zend_Db_Expr('req_count + 1'),'ref_date'=> new Zend_Db_Expr('CURDATE()'));
                    $this->update($udata,'ref_id =' . $result['ref_id']);
                else:
                    $idata['email'] = $data['email'];
                    $idata['ref_by'] = $data['ref_by'];
                    $idata['ref_date'] = new Zend_Db_Expr('CURDATE()');
                    $idata['req_count'] = 1;
                    $this->insert($idata);
                endif;
          else:
              throw new Exception("Argument not passed");
          endif;
    }
    
     /**
     * Developer    : vivek Chaudhari
     * Date         : 03/07/2014
     * Description  : Update reminder count by ref_id
     */
    public function updateReminder(){
        if(func_num_args()>0):
        $rId = func_get_arg(0);
        $db = Zend_Db_Table::getDefaultAdapter();
        $where = $db->quoteInto('ref_id IN (?)', $rId);
        $udata = array('req_count' => new Zend_Db_Expr('req_count + 1'),'ref_date'=> new Zend_Db_Expr('CURDATE()'));
            try {
                $db->update('refer_friends',$udata,$where);
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        else:
           throw new Exception("Argument not passed");
        endif;
    }
}
?>