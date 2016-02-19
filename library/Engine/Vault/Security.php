<?php

class Engine_Vault_Security {

    private $_coreObj;
    private $_appSetting;
    private $_env;
    private $_logger;
    private $_session;
    private $_auth;
    private $_db;
    private static $_instance = null;

    private function __clone() {
        
    }

//Prevent any copy of this object

    public static function getInstance() {
        if (!is_object(self::$_instance))  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Engine_Vault_Security();
        return self::$_instance;
    }

    public function __construct() {

        $this->_coreObj = Engine_Core_Core::getInstance();

        /** get the website defaults * */
        $this->_appSetting = $this->_coreObj->getAppSetting();

        /** get app enviornment * */
        $this->_env = $this->_coreObj->getEnv();

        /** get loggers * */
        $this->_logger = $this->_coreObj->getLogger();

        /** get the session * */
        $this->_session = $this->_coreObj->getSession();

        /** get the auth instance * */
        $this->_auth = $this->_coreObj->getAuth();

        /** get the auth instance * */
        $this->_db = $this->_coreObj->getDb();
    }

    public function authenticate() {
        $data = new stdClass();
        $data->email = func_get_arg(0);
        $data->password = func_get_arg(1);
       // echo ("<pre>"); print_r($data); echo ("</pre>"); 
        //	$storage = new Zend_Auth_Storage_Session($this->_appSetting->appName);
        $auth = Zend_Auth::getInstance();
        $storage = new Zend_Auth_Storage_Session($this->_appSetting->appName);
        $auth->setStorage($storage);
        $authAdapter = new Zend_Auth_Adapter_DbTable($this->_db);
		
		
        /** check creds against the this table and column * */
        $authAdapter->setTableName('users')
                ->setIdentityColumn('email')
                ->setCredentialColumn('password');
        // ->setCredentialTreatment('MD5(?)');
        /** check creds against this values * */
        $authAdapter->setIdentity($data->email)
                ->setCredential($data->password);
        //->setCredentialTreatment('MD5(?)');
        //->setCredentialTreatment('SHA1(CONCAT(?,salt))');
        $result = $this->_auth->authenticate($authAdapter);
        switch ($result->getCode()) {

            case Zend_Auth_Result::SUCCESS:
                $this->_logger->info('success');
                break;

            case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
            case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:

            default:

                foreach ($result->getMessages() as $message) {
                    //throw new Exception($message);
                    $errorMsg = $message;
                    // print_r($errorMsg);
                }
                break;
        }
        $response = new stdClass();
        if ($result->isValid()) {
            if ($auth->hasIdentity()) {
                $dataResponse = $authAdapter->getResultRowObject();
                if ($dataResponse->status == 0) {
                    $auth->clearIdentity();
                    $response->code = 196;
                } else {
                    $storage->write($authAdapter->getResultRowObject());
                    $this->_logger->info($storage);
                    $this->_logger->info($auth->getIdentity());
                    $response->code = 200;
                    $response->data = $result;
                }
            } else {
                $this->_logger->info('No Identity found');
                $response->code = 198;
            }

            return $response;
        } else {
            $response->code = 198;
            $response->data = $errorMsg;
            return $response;
        }
    }

}
