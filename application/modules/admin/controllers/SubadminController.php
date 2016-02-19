<?php

/**
 * AdminController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class Admin_SubadminController extends Zend_Controller_Action {

    public function init() {
        
    }
    
    public function preDispatch(){
       $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }
    
   public function createAdminAction() {
        if (isset($this->view->session->storage->role)){
            if($this->view->session->storage->role == '2'){
                $this->_redirect('/dashboard/admin');
            }
        }else{
            die("Sdfds");
            if ($this->_request->isPost()) {
            $method = $this->getRequest()->getPost('method');
            $username = $this->getRequest()->getPost('username');
            $password = $this->getRequest()->getPost('password');
               if (isset($username) && isset($password)) {
                   
                    $objSecurity = Engine_Vault_Security::getInstance();
                    $authStatus = $objSecurity->authenticate($username, $password);
                    
                    if ($authStatus->code == 200) {

                        if ($this->view->session->storage->role == '2') {

                            $this->_redirect('/dashboard/admin');
                        } elseif ($authStatus->code == 198) {

                            $this->view->error = "Invalid credentials";
                        }
                    }

             }

            }
        }
    }

    public function dashboardAction() {
        
    }
    //dev:priyanka varanasi 
    //desc: to change the password of admin
     public function changePasswordAction() {
            $objUserModel = Admin_Model_Users::getInstance();
         if ($this->getRequest()->isPost()) {
            $Passwordcurrent = $this->getRequest()->getPost('currentpassword');
            $Passwordnew = $this->getRequest()->getPost('newpassword');
            $Passwordconfirm = $this->getRequest()->getPost('confirmpassword');
            $user_id = $this->view->session->storage->user_id;
               $response = $objUserModel->validatePassword($user_id);
               $responsepass = $response[0];
             if (isset($Passwordnew) && isset($Passwordconfirm)) {
               
            
            if ($Passwordcurrent === $responsepass) {
                if ($Passwordnew === $Passwordconfirm) {
                     $data = array('password' => $Passwordnew);
                    $Result = $objUserModel->changePasswordsettings($data, $user_id);
                    if ($Result) {
                        $this->view->message ="password change sucessfully";
                    }
                    
     }
            }
             }
         }
     }
     
     //dev: priyanka varanasi
     //desc: to reset the password by the admin
     public function resetAjaxAction(){
     $mailer = Engine_Mailer_Mailer::getInstance();  
    $objUserModel = Admin_Model_Users::getInstance();
    $this->_helper->layout()->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
     if ($this->getRequest()->isPost()) {
         $email = $this->getRequest()->getParam('email');
      
                   if ($email != "") { 
                    $result = $objUserModel->validateUserEmail($email);
                    if ($result) {

                                $objCore = Engine_Core_Core::getInstance();
                                $this->_appSetting = $objCore->getAppSetting();


                                $userID = $result['user_id'];
                                $activationKey = base64_encode($result['user_id'] . '@' . $random = mt_rand(10000000, 99999999));
                                $link = 'http://' . $this->_appSetting->host . '/admin/reset/' . $activationKey;
                                $objUserModel->updateActivationLink($activationKey, $userID);
                                $template_name = 'Reset-password-fashionlearn';
                                $username = $email;
                                $subject = 'PasswordReset Mail';
                                $mergers = array(
                                    array(
                                        'name' => 'username',
                                        'content' => $username
                                    ),
                                    array(
                                        'name' => 'passwordresetlink',
                                        'content' => $link
                                    )
                                );
                               $result= $mailer->sendtemplate($template_name, $email, $username, $subject, $mergers);
                              
                                if ($mailer) {
                                    $this->view->success = 'send';
                                }
         
         }
    
         
        }
            }
     }
     
      //dev: priyanka varanasi
     //desc: to reset the password by the admin
     public function resetAction(){
       $objUserModel = Admin_Model_Users::getInstance();

        $key = $this->getRequest()->getParam('code');
        if ($key) {
            $decodeKey = base64_decode($key);
            $userId = explode('@', $decodeKey);

            $result = $objUserModel->checkActivationKey($userId[0], $key);
            if ($result) {
                $this->view->userData = $result;
            }
            if ($this->getRequest()->isPost()) {
                $newPassword = $this->getRequest()->getParam('password');
                 $confPassword = $this->getRequest()->getParam('confirmpassword');
                 if($newPassword == $confPassword){
                     $data['password']= $newPassword;
                $resultData = $objUserModel->changePasswordsettings($data,$userId[0]);
                if ($resultData) {
                    $this->view->message = "successfully reset your pasword ";
                    $this->view->success = $resultData;
                    $this->_redirect('/admin');
                }
            }
            }
        }
    }
  
     
    /**
     * Developer : Bhojraj Rawte
     * Date : 09/07/2014
     * Description : Logout admin.
     * @Todo :  
     */
    public function logoutAction() { 
        $this->_helper->layout->disableLayout();
        if($this->view->auth->hasIdentity()){

            $this->view->auth->clearIdentity();

            Zend_Session::destroy( true );

           $this->_redirect('/admin'); 

        }
    }

}
