<?php
class Engine_Plugins_Auth extends Zend_Controller_Plugin_Abstract {

    protected $_role = 'guest';
    protected $_session = null;
    protected $_resourceModule = null;
    protected $_resourceController = null;
    protected $_resourceActionName = null;
    protected $_resourceString = null;
    protected $_redirectUrl = null;
    protected $_auth = null;
    protected $_appSetting = null;
    protected $_env = null;
    protected $_logger = null;

    private $_access = 0;

    public function __construct(){
        $this->_auth = Engine_Core_Core::getInstance()->getAuth();
        $this->_appSetting = Engine_Core_Core::getInstance()->getAppSetting();
        $this->_env = Engine_Core_Core::getInstance()->getEnv();
        $this->_logger = Engine_Core_Core::getInstance()->getLogger();
        $this->_session = Engine_Core_Core::getInstance()->getSession();
    }
	
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
		try {
			$acl = new Engine_Vault_Acl();
			//$this->_session = Zend_Registry::get('sessionNamespace');
			
			if ($this->_auth->hasIdentity()){
                            
                            
//                                print "<pre>"; print_r($this->_session->storage); print "</pre>"; die;
				$this->_role = isset($this->_session->storage->role)?($this->_session->storage->role):$this->_role;
                                if(isset($this->_session->storage->role)){
                                    switch ($this->_session->storage->role) {
                                        case 0:
                                             //   $this->_session->storage->role = 'guest';
                                            $this->_role = $this->_role;
                                            break;
                                        case 1: 
                                              //  $this->_session->storage->role = 'user';
                                            $this->_role = 'user';
                                            break;
                                        case 2:
                                               // $this->_session->storage->role = 'admin';
                                            $this->_role = 'admin';
                                            break;
                                       
                                        default:
                                            break;
                                    }
                                }
                                
                                
                                
				$this->_resourceModule = $request->getModuleName();
				$this->_resourceController = $request->getControllerName();
				$this->_resourceActionName = $request->getActionName();
				$this->_resourceString = $this->_resourceModule .'::'. $this->_resourceController .'::'. $this->_resourceActionName;
				$this->_redirectUrl = $request->getPathInfo();
				$this->_session->redirectUrl = $this->_redirectUrl;
				if($this->_env!="production"):
					$this->_logger->info($this->_role.$this->_resourceString);
				endif;
				
				try{
				
					if ($acl->isAllowed($this->_role,$this->_resourceString )):
						$this->_access = 1;
					else:
						$this->_access = 0;
					endif;	
				}catch(Exception $e){
					$this->getResponse()->setHttpResponseCode(404);
					throw new Exception($e->getMessage());
				}
				if(!$this->_access){
				
					$this->getResponse()->setHttpResponseCode(403);
					throw new Exception('Access Forbidden',403);
				}
			}else{
                            
                            
				$this->_resourceModule = $request->getModuleName();
				$this->_resourceController = $request->getControllerName();
				$this->_resourceActionName = $request->getActionName();
				$this->_resourceString = $this->_resourceModule .'::'. $this->_resourceController .'::'. $this->_resourceActionName;
				$this->_redirectUrl = $request->getPathInfo();
				$this->_session->redirectUrl = $this->_redirectUrl;
				if($this->_env!="production"):
					$this->_logger->info($this->_role.$this->_resourceString);
				endif;
				
				try{
				
					if ($acl->isAllowed($this->_role,$this->_resourceString )):
						$this->_access = 1;
					else:
						$this->_access = 0;
					endif;	
				}catch(Exception $e){
					$this->getResponse()->setHttpResponseCode(404);
					throw new Exception($e->getMessage());
				}
				if(!$this->_access){	
                                   
//					$this->getResponse()->setHttpResponseCode(403);
//					throw new Exception('Access Forbidden',403);
                                        $this->session->x=12;
                                        Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->setGotoUrl('/');  
				}
                         
			}
		
		// do something that throws an exception

		} catch (Exception $e) {
		
				Zend_Registry::get('logger')->info($e);
			// Repoint the request to the default error handler
			$request->setModuleName('home');
			$request->setControllerName('error');
			$request->setActionName('error');

			// Set up the error handler
			$error = new Zend_Controller_Plugin_ErrorHandler();
			$error->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
			$error->request = clone($request);
			$error->exception = $e;
			$request->setParam('error_handler', $error);
		}
    }

}

