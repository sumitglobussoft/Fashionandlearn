<?php

/**
 * ProjectdetailsController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class Admin_ProjectdetailsController extends Zend_Controller_Action {

    public function init() {
        
    }
    
    public function preDispatch(){
       $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }
    //dev: priyanka varanasi
    //desc: to show list of project to admin
    public function projectDetailsAction(){
     $objprojectsModel = Admin_Model_Projects::getInstance();
     $result = $objprojectsModel->getprojectdetails(); 
     
    if($result){
        $this->view->projectresult = $result;
    }
    }
    
        //dev: priyanka varanasi
    //desc: to Edit project by admin
    public function editProjectsAction(){
        
      
      $objUserModel = Admin_Model_Users::getinstance();
      $objTeachingclassModel = Admin_Model_TeachingClasses::getinstance();
       $objprojectsModel = Admin_Model_Projects::getInstance();
         $project = $this->getRequest()->getParam('prid');
       if ($this->getRequest()->isPost()) {
            $data = array();
            $user = $this->getRequest()->getPost('user');
            $class= $this->getRequest()->getPost('class');
            $clastitle = $this->getRequest()->getPost('classtitle');
            $category = $this->getRequest()->getPost('Category');
            $projecttitle = $this->getRequest()->getPost('project');
            $projectdescription = $this->getRequest()->getPost('MyToolbar6');
            $coverphoto = $_FILES["file"]["name"];
            
            $dirpath = 'projectimages/' . $user . '/' . $class . '/';
                    if (!(is_dir($dirpath))) {
                            if (!$dirpath = mkdir($dirpath, 0777, true)) {
                                die('could not create directory');
                            }
                        }

                 if(!empty($coverphoto)){
                        $imagepath = $dirpath . $coverphoto;
                        $imageTmpLoc = $_FILES["file"]["tmp_name"];
                        $ext = pathinfo($coverphoto, PATHINFO_EXTENSION);
                        if ($ext != "jpg" && $ext != "png" && $ext != "jpeg" && $ext != "gif") {
                            echo json_encode("Something went wrong image upload");
                            
                        }else {

// Run the move_uploaded_file() function here
                            $imagemoveResult = (move_uploaded_file($_FILES["file"]["tmp_name"], $imagepath));
                           
                            $imagepath="/".$imagepath;
                            $data=array("user_id"=>$user,"class_id" => $class,"project_title"=>$projecttitle,"project_cover_image"=> $imagepath,"project_workspace"=>$projectdescription);
                            $saveresponse = $objprojectsModel->updateProjectdet($user, $class,$project,$data);
                              if($saveresponse){
                               $this->_redirect('admin/projectdetails');
                           }
                        }
                    }
                    else{
                        
                             $coverimage = $objprojectsModel->selectimage($project);
                             $imagepath = $coverimage['project_cover_image'];
                            $data= array("user_id"=>$user,"class_id" => $class,"project_title"=>$projecttitle,"project_cover_image"=> $imagepath,"project_workspace"=>$projectdescription);
                            $saveresponse = $objprojectsModel->updateProjectdet($user, $class,$project,$data);
                            if($saveresponse){
                               $this->_redirect('admin/projectdetails');
                           }
                            
                    }
       }
        $projects = $objprojectsModel->getprojectsbyid($project); 
       
       if($projects){
         $this->view->projects =$projects;   
      }
    
       }
  
 public function projectAjaxHandlerAction(){
      $this->_helper->layout()->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    $objCategoryModel =  Admin_Model_Category::getInstance();
    $objCore = Engine_Core_Core::getInstance();
        $this->_appSetting = $objCore->getAppSetting();
    $objUserModel = Admin_Model_Users::getinstance();
     $objTeachingclassModel = Admin_Model_TeachingClasses::getinstance();
      $objTeachingclassvideoModel = Admin_Model_TeachingClassVideo::getinstance();
       $objprojectsModel = Admin_Model_Projects::getInstance();
    if ($this->getRequest()->isPost()) {
     $method = $this->getRequest()->getParam('method');
        
        switch ($method) {
          case 'projectdelete':
        $projectid = $this->getRequest()->getParam('projectid');

               $ok = $objprojectsModel->deleteprojects($projectid);
              
                if($ok){
                  echo $ok;
                    return $ok;
                }else{
                    echo "Error";
                }
                break;
     
 } 
    }
}
}