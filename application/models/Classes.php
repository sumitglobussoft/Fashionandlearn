<?php

/*
 * Developer : Ankit Singh
 * Date : 30/12/2014
 */

class Application_Model_Classes extends Zend_Db_Table_Abstract {

    private static $_instance = null;
    protected $_name = 'teachingclasses';

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Application_Model_Classes();
        return self::$_instance;
    }

    /*
     * Developer : Ankit Singh
     * Date: 20 Jan 2015
     * Desc : Insert Teaching classed data 
     */

    public function insertTeachingClasses() {

        if (func_num_args() > 0) {
            $classes = func_get_arg(0);


            try {
                $responseId = $this->insert($classes);
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
     * Developer : Ankit Singh
     * Date: 20 Jan 2015
     * Desc : Select User Class id
     */

    public function selectUserClassId($classTeach) {

        $select = $this->select()
                ->where('class_id = ?', $classTeach);

        $result = $this->getAdapter()->fetchAll($select);
        if ($result) {
            return $result;
        }
    }

    /*
     * Developer : Ankit Singh
     * Date: 20 Jan 2015
     * Desc : Insert User Classes Details at the time of start
     */

    public function insertTeachingClassesStart() {

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
     * Developer : Ankit Singh
     * Date: 20 Jan 2015
     * Desc : Update User Classes Details on the basis of class id.
     */

    public function updateTeachingClasses($data, $classTeachId) {
        $where = "class_id =" . $classTeachId;
        $result = $this->update($data, $where);
        if ($result) {
            return $result;
        }
    }

    public function selectTeachingClassesId($data) {
        $select = $this->select()
                ->where('class_id =?', $data);
        $result = $this->getAdapter()->fetchRow($select);
        if ($result) {
            return $result;
        }
    }

}
