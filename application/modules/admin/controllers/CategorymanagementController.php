<?php

/**
 * CategorymanagementController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class Admin_CategorymanagementController extends Zend_Controller_Action {

    public function init() {
        
    }
    public function preDispatch(){
       $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }
    //dev:priyanka varanasi
    //desc: to display all categories 
    public function categoryManageAction(){
         $objCategoryModel =  Admin_Model_Category::getInstance();
           $categories = $objCategoryModel->getCategorys();
       $this->view->categories = $categories;
   }
     //dev:priyanka varanasi
    //desc: to delete categories ,addcategories
     public function categoryAjaxHandlerAction(){
   $this->_helper->layout()->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);
    $objCategoryModel =  Admin_Model_Category::getInstance();
    if ($this->getRequest()->isPost()) {
     $method = $this->getRequest()->getParam('method');
        
        switch ($method) {
            case 'categorydelete':
                $categoryid = $this->getRequest()->getParam('categoryid');
                $ok = $objCategoryModel->deletecategory($categoryid);
               
                 if($ok){
                  echo $categoryid;
                    return $categoryid;
                }else{
                    echo "Error";
                }
                break;
                
                   case 'addcategory':
                $categoryname = $this->getRequest()->getParam('categoryname');
                $catid = $objCategoryModel->addcategory($categoryname);
               
                 if($catid){
                  echo $categoryname;
                    return $categoryname;
                }else{
                    echo "Error";
                }
                break;
    
     }
    }
     }    
     //dev:priyanka varanasi
    //desc: To edit categories
     
     public function editCategoryAction(){
        $objCategoryModel =  Admin_Model_Category::getInstance();
         $CatID = $this->getRequest()->getParam('cid');
     $catres  = $objCategoryModel->getcategorybyid($CatID);
     $this->view->catres = $catres;
       if ($this->getRequest()->isPost()) {
            $category['category_name'] = $this->getRequest()->getPost('categoryname');
          $cat = $objCategoryModel->updatecategoryDetails($CatID,$category);
          if($cat){
               $this->_redirect('/admin/categorymanage');
          }
       }     
     }
}