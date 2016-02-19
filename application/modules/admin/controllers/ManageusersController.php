<?php

/**
 * ManageusersController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';
require_once 'Engine/Pagarme/Pagarme/RestClient.php';

class Admin_ManageusersController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function preDispatch() {
        $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }

    //dev:priyanka varanasi
    //desc: To show  user details in admin panel
    public function manageusersAction() {
        $objUserModel = Admin_Model_Users::getInstance();
        $userDetails = $objUserModel->getUsersDetails();

        $objPaymentsNewModel = Admin_Model_PaymentNew::getInstance();
        $result = $objPaymentsNewModel->getPremiumUsers();
        $teacherdetails = $objUserModel->getteacherdetails();
        $trialusers = $objPaymentsNewModel->getAllTrialUsers();
        if($teacherdetails) {
            $this->view->teachersdetails = $teacherdetails;  
         }    
        if ($result) {
            $this->view->memberships = $result;
        }
        if ($userDetails) {
            $this->view->users = $userDetails;
        }
          if ($trialusers) {
            $this->view->trialusers = $trialusers;
        }
        
    }

    //dev:priyanka varanasi
    //desc: To deactivate users

    public function userAjaxHandlerAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $objUserModel = Admin_Model_Users::getInstance();
        $objPaymentsModel = Admin_Model_Payment::getInstance();
        $objstatistics = Application_Model_Sitesettings::getInstance();
        if ($this->getRequest()->isPost()) {
            $method = $this->getRequest()->getParam('method');

            switch ($method) {
                case 'useractive':
                    $userstate = $this->getRequest()->getParam('userid');
                    $ok = $objUserModel->getstatustodeactivate($userstate);

                    if ($ok) {
                        echo $userstate;
                        return $userstate;
                    } else {
                        echo "Error";
                    }
                    break;
                case 'userdelete':
                    $userid = $this->getRequest()->getParam('userid');
                    $result = $objUserModel->userdelete($userid);
                    if ($result) {
                        echo $result;
                        return $result;
                    } else {
                        echo "error";
                    }
                    break;
                case 'userperminssion':

                    $result = $objstatistics->updastatistics();
                    if ($result) {
                        print_r($result);
                        die();
                    } else {
                        echo "error";
                        die();
                    }
                    break;
//                case 'ALL':
//                    $userDetails = $objUserModel->getUsersDetails();
//                    if ($userDetails) {
//                        echo json_encode($userDetails);
//                    }
//                    break;
//                case 'Membership':
//                    $result = $objPaymentsModel->getpaymembershipusers();
//                    if ($result) {
//                        echo json_encode($result);
//                    }
//                    break;
            }
        }
    }

    //dev:priyanka varanasi
    //desc: To edit user
    public function editUserAction() {

        $objUserModel = Admin_Model_Users::getInstance();
        $objUsermetaModel = Admin_Model_UsersMeta::getInstance();
        $userID = $this->getRequest()->getParam('uid');

        if ($this->getRequest()->isPost()) {

            $imageName = $_FILES["fileToUpload"]["name"];
            $imageTmpLoc = $_FILES["fileToUpload"]["tmp_name"];
            $userdata['first_name'] = $this->getRequest()->getPost('firstname');
            $userdata['last_name'] = $this->getRequest()->getPost('lastname');
            $userdata['email'] = $this->getRequest()->getPost('email');
            $userdata['password'] = sha1(md5($this->getRequest()->getPost('password')));


            $ext = pathinfo($imageName, PATHINFO_EXTENSION);
            $imageNamePath = $imageName;

            if (!empty($imageName)) {


                if ($ext != "jpg" && $ext != "png" && $ext != "jpeg" && $ext != "gif") {

                    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                    $ext = 0;
                } else {
                    // Path and file name
                    $imagepathAndName = "profileimages/$userID/" . $imageNamePath;
                    if ($imagepathAndName) {
                        $imagemoveResult = (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $imagepathAndName));
                        $imagepathAndName = "/profileimages/$userID/" . $imageNamePath;
                        $data1 = array('user_profile_pic' => $imagepathAndName
                        );
                        $editResultumeta = $objUsermetaModel->editUsermeta($data1, $userID);
                        $check = $objUserModel->updateUserDetails($userID, $userdata);
                        if ($check || $editResultumeta) {
                            $this->view->message = "Data Edited succesfully";
                        }
                    }
                }
            } else {

                $result = $objUsermetaModel->getuserprofilepic($userID);

                ;
                $data1 = array('user_profile_pic' => $result['user_profile_pic']
                );

                $editResultumeta = $objUsermetaModel->editUsermeta($data1, $userID);

                $check = $objUserModel->updateUserDetails($userID, $userdata);

                if ($check || $editResultumeta) {
                    $this->view->message = "Data Edited succesfully";
                }
            }
        }
        $user = $objUserModel->getUsersDeatilsByID($userID);
        $this->view->user = $user;
    }

    //dev:priyanka varanasi
    //desc: To create user
    public function createUserAction() {

        $objUserModel = Admin_Model_Users::getInstance();
        $objUsermetaModel = Admin_Model_UsersMeta::getInstance();

        if ($this->getRequest()->isPost()) {
            $firstname = $this->getRequest()->getPost('firstname');
            $lastname = $this->getRequest()->getPost('lastname');
            $email = $this->getRequest()->getPost('email');
            $password = $this->getRequest()->getPost('password');
            $gender = $this->getRequest()->getPost('gender');
            $city = $this->getRequest()->getPost('city');
            $zip = $this->getRequest()->getPost('zip');
            $imageName = $_FILES["fileToUpload"]["name"];
            $imageTmpLoc = $_FILES["fileToUpload"]["tmp_name"];

            if (isset($firstname) && isset($lastname) && isset($email) && isset($password)) {

                $data = array('first_name' => $firstname,
                    'last_name' => $lastname,
                    'password' => sha1(md5($password)),
                    'email' => $email,
                    'status' => '0',
                    'reg_date' => date('Y-m-d'),
                    'role' => '1',
                );

                $insertionResult = $objUserModel->insertUser($data);


                $ext = pathinfo($imageName, PATHINFO_EXTENSION);
                $imageNamePath = $imageName;
                $target_dir = "/profileimages/$insertionResult";
                $location = getcwd() . $target_dir;
                @mkdir($location, 0777, true);

                if ($imageName) {

                    if ($ext != "jpg" && $ext != "png" && $ext != "jpeg" && $ext != "gif") {

                        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                        $ext = 0;
                    } else {
                        // Path and file name
                        $imagepathAndName = "profileimages/$insertionResult/" . $imageNamePath;

                        $imagemoveResult = (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $imagepathAndName));


                        $imagepathAndName = "/profileimages/$insertionResult/" . $imageNamePath;
                    }
                }
                if ($insertionResult) {
                    $metaData = array('user_id' => $insertionResult,
                        'user_profile_pic' => $imagepathAndName,
                        'city' => $city,
                        'zip' => $zip);
                    //$notifydata = array('user_id' => $insertionResult);

                    $result = $objUsermetaModel->insertUsermeta($metaData);

                    if ($result) {
                        $this->_redirect('/admin/manageusers');
                    }
                }
            }
        }
    }

    /**
     * dev:abhishek m
     * for admin to review the request made by clients
     */
    public function editbankdetailsAction() {
        if (isset($_GET["rid"]) && $_GET["rid"] != "") {
            $rid = $_GET["rid"];


            $objPagarbankcreq = Application_Model_Pagarbankcreq::getinstance();
            $res = $objPagarbankcreq->checkreq($rid);
            if ($res) {
                $objPagarbank = Application_Model_Pagarbank::getinstance();
                $ress = $objPagarbank->getbankdetails($res["user_id"]);
                $this->view->res = $res;
                $this->view->ress = $ress;
            } else {
                die("requestid doesnt exist");
            }
        } else {
            die("no request id");
        }
    }

    /**
     * dev:abhishek m
     * for saving bank details in pagar
     */
    public function pagarbankcrsAction() {

        $objUserModel = Admin_Model_Users::getInstance();
        $objPagarbankcreq = Application_Model_Pagarbankcreq::getinstance();

         $res = $objPagarbankcreq->checkreq($_POST["requestid"]);
        $user = $objUserModel->getUsersDeatilsByID($res["user_id"]);
        if ($_POST["method"] == "reject") {
            $mailer = Engine_Mailer_Mailer::getInstance();
            $template_name = 'rejectmessage';
            $email = $user["email"];
         
            $name = $user["first_name"];
           // $email = "abhishekm@globussoft.com";
            $subject = "bank account create/edit";
            //$username = $this->getRequest()->getPost('username');
            $subject = 'Bank account add/edit request';
            $mergers = array(
                array(
                    'name' => 'name',
                    'content' => $user["first_name"]
                ),
                array(
                    'name' => 'text',
                    'content' => $_POST["comment"]
                )
            );

            $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
            if ($mresult) {
                echo "1";
                $objPagarbankcreq->updatereq($_POST["requestid"]);
            } else {
                echo "0";
            }
        } else {
            $response=new stdClass();
            $objCore = Engine_Core_Core::getInstance();
            $realobj = $objCore->getAppSetting();
            $apikey = $realobj->pagar->ApiKey;
            $res = $objPagarbankcreq->checkreq($_POST["requestid"]);
          
           
            $data["api_key"] = $apikey;
            $data["bank_code"] =preg_replace("/[^0-9]/", "", $res['bank_code']) ;
            $data["agencia"] = $res['agency'];
            $data["agencia_dv"] = $res['agencia_dv'];
            $data["conta"] = $res['account_no'];
            $data["conta_dv"] = $res['conta_dv'];
            $data["document_number"] = preg_replace("/[^0-9]/", "", $res['document_number']);
            $data["legal_name"] = $res['legal_name'];



            try {
                $params["url"] = 'https://api.pagar.me/1/bank_accounts/';
                $params["parameters"] = $data;
                $params["method"] = "POST";
                $rs = new RestClient($params);
                $resulter = $rs->run();
            } catch (Exception $e) {
                
            }
       
         $result=(json_decode($resulter["body"],true));
        
            if ($resulter["code"] == "200") {
                $pbankk["bank_code"] = $result["bank_code"];
                $pbankk["agency"] = $result["agencia"];
                $pbankk["agencia_dv"] = $result["agencia_dv"];
                $pbankk["account_no"] = $result["conta"];
                $pbankk["conta_dv"] = $result["conta_dv"];
                $pbankk["document_number"] = $result["document_number"];
                $pbankk["documenttype"] = $result["document_type"];
                $pbankk["legal_name"] = $result["legal_name"];
                //$pbankk["user_id"]=$res["user_id"];
                $pbankk["pagar_bank_id"] = $result["id"];

                $objPagarbank = Application_Model_Pagarbank::getinstance();
                $done = $objPagarbank->bankupsert($pbankk, $res["user_id"]);
                if ($done)
                {
                    $objPagarbankcreq->updatereq($_POST["requestid"]);
                    $response->res=1;
                    echo json_encode($response);
                    die();
                }
                else
                {
                     $response->res=0;
                     $response->error="";
                    echo json_encode($response);
                    
                    die();
                
                }
            } 
            else
            {
                   $response->res=0;
                   $response->error=$result["errors"];
                    echo json_encode($response);
                    die();

            }




//            $mailer = Engine_Mailer_Mailer::getInstance();
//            $template_name = 'rejectmessage';
//            $email = $user["email"];
//            $name = $user["first_name"];
//            $email = "abhishekm@globussoft.com";
//            $subject = "bank account create/edit";
//            //$username = $this->getRequest()->getPost('username');
//            $subject = 'Bank account add/edit request';
//            $mergers = array(
//                array(
//                    'name' => 'name',
//                    'content' => $user["first_name"]
//                ),
//                array(
//                    'name' => 'text',
//                    'content' => $_POST["comment"]
//                )
//            );
//
//            $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers, "financeiro@fashionlearn.com.br");
//            if ($mresult) {
//                echo "1";
//                $objPagarbankcreq->updatereq($_POST["requestid"]);
//            } else {
//                echo "0";
//            }
        }



        die();
    }

}
