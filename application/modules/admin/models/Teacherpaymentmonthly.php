<?php 
class Admin_Model_Teacherpaymentmonthly extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teacherpaymentmonthly';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Admin_Model_Teacherpaymentmonthly();
        return self::$_instance;
}


public function insertmonthlyreferal(){
     if (func_num_args() > 0) {
            $data = func_get_arg(0);
     
             $year=$data['Year'];
             $month=$data['month'];
             $user_id=$data['user_id'];
            $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this)
               ->Where('month=?', $data['month'])
               ->Where('Year=?', $data['Year'])
               ->where('user_id=?',$data['user_id']);
            $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
   
           $this->update($data,"month=$month&&Year=$year&&user_id=$user_id");
        } 
        else{
            try {
           
                $responseId = $this->insert($data);
                
            } catch (Exception $e) {
               die($e->getMessage());
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

public function  teacherreferalmoney(){
      if (func_num_args() > 0) {
            $data = func_get_arg(0);
          
             $where=array('month'=>$data['month'],'Year'=>$data['Year']);
            $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this)
               ->Where('month=?', $data['month'])
               ->Where('Year=?', $data['Year'])
               ->Where('user_id=?', $data['user_id']);
        $result = $this->getAdapter()->fetchRow($select);
       
        if ($result) {
            return $result['referalmoney'];
      } 
      
        }
}

}
?>