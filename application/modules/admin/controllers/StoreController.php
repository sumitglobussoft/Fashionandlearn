<?php
/**
 * StoreController
 *
 * @author  : Vivek Chaudhari
 * @version
 */
require_once 'Zend/Controller/Action.php';

class Admin_StoreController extends Zend_Controller_Action {



    public function init() {     
        
    }
    
    public function preDispatch(){
       $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }
    
    /**
     * Developer    : vivek Chaudhari
     * Date         : 11/07/2014
     * Description  : get product details and create new product action
     * @params      : <int>form data to create new product
     */
    public function storeAction(){
        $objStoreModel = Admin_Model_Store::getInstance();
        if($this->getRequest()->isPost()):
        $data = array();
        $data['product_name'] = $this->getRequest()->getParam('product_name');
        $data['url'] = $this->getRequest()->getParam('url');
        $data['fpp_point'] = $this->getRequest()->getParam('fpp_point');
        $data['real_cash'] = $this->getRequest()->getParam('real_cash');
        $data['qty'] = $this->getRequest()->getParam('qty');
        $ok = $objStoreModel->insertNewProduct($data);
        endif;
        $store = $objStoreModel->getStoreDetails();
         if($store):
            $this->view->success = $store; 
        endif ;
        if($store):
            $this->view->store = $store;
       
        endif;
        
    }
    
    /**
     * Developer    : vivek Chaudhari
     * Date         : 11/07/2014
     * Description  : edit product details, and get product details by id for editing
     * @params      : <int>form data of editing product
     */
    public function editProductAction(){
        $productId = $this->getRequest()->getParam('productId');
        $objStoreModel = Admin_Model_Store::getInstance();
       
        if($this->getRequest()->isPost()):
        $edit = array();
        $edit['product_name'] = $this->getRequest()->getParam('product_name');
        $edit['url'] = $this->getRequest()->getParam('url');
        $edit['fpp_point'] = $this->getRequest()->getParam('fpp_point');
        $edit['real_cash'] = $this->getRequest()->getParam('real_cash');
        $edit['qty'] = $this->getRequest()->getParam('qty');
        $check = $objStoreModel->updateProductById($productId,$edit); 
            if($check):
                
             $this->_redirect('/admin/store');
                
                $this->view->error = "Product details edited successfully";
            else:
                $this->view->error = "Unable to edit product, some error occurred";
            endif;
        endif;
        
        $product = $objStoreModel->getProductDetailsById($productId);
        if($product):
            $this->view->data = $product;
        endif;
    }
    
    /**
     * Developer    : vivek Chaudhari
     * Date         : 11/07/2014
     * Description  : get ticket details and generate new tickets
     * @params      : <int>&<string>form data to generate new tickets
     */
    public function validTicketsAction(){
        $objTicketModel = Admin_Model_Tickets::getInstance();        
         $tickets = $objTicketModel->getValidTickets();
         
        if($tickets):
            $this->view->tickets = $tickets;
       
        endif;
    }
    /**
     * Developer    : vivek Chaudhari
     * Date         : 11/07/2014
     * Description  : edit ticket details
     * @params      : <int><string>form data to edit tickets
     * 
     * Modified by: Pradeep
     * Modified on: 22/11/14
     */
    public function editTicketAction()
    {
        $ticketId = $this->getRequest()->getParam('ticketId');
        $objTicketModel = Admin_Model_Tickets::getInstance();
        if($this->getRequest()->isPost())
        {
            $edit = array();
            $edit['bonus_amt'] = $this->getRequest()->getParam('bonus');
            $edit['valid_from'] = date('y-m-d',strtotime($this->getRequest()->getParam('validfrom')));
            $edit['valid_upto'] = date('y-m-d',strtotime($this->getRequest()->getParam('validupto')));
            $edit['limitation'] = $this->getRequest()->getParam('limitation');
            $edit['selling_status'] = $this->getRequest()->getParam('sellingstatus');
            
          //  echo "<pre>"; print_r($edit); echo "</pre>"; die;
            
            $ok = $objTicketModel->updateTicketById($ticketId,$edit);
            if($ok)
            {
                $this->_redirect("/admin/ticket");
            }
        }
        
        $data = $objTicketModel->getTicketDetailsById($ticketId);
        
        //echo "<pre>"; print_r($data); echo "</pre>"; die();
        
        if($data)
        {
            $this->view->data = $data;
        }
    }
    
    /**
     * Developer    : Pradeep K C
     * Date         : 21/11/2014
     * Description  : ticket details (manage ticket)
     */
    public function ticketAction()
    {
        $objTicketsModel = Admin_Model_Tickets::getInstance();
        $ticketDetails = $objTicketsModel->getTicketDetails();
        
        if($ticketDetails)
        {
            $this->view->data = $ticketDetails;
        }
        
        if ($this->getRequest()->isPost()) 
        {
            $method = $this->getRequest()->getParam('method');

            switch ($method) 
            {
                case 'ticketactive':
                    $this->_helper->layout()->disableLayout();
                    $this->_helper->viewRenderer->setNoRender(true);
                    $ticketId = $this->getRequest()->getParam('ticket_id');
                    $okay =  $objTicketsModel->toggleStatus($ticketId);
                    if($okay)
                    {
                        echo $ticketId;
                        return $ticketId;
                    }
                    else
                    {
                        echo "Error";
                    }
                    break;
            } 
        }
        
    }
    
    /**
     * Developer    : Pradeep K C
     * Date         : 21/11/2014
     * Description  : ticket details (manage ticket)
     */
    public function newTicketAction()
    {
        $objTicketsModel = Admin_Model_Tickets::getInstance();
        
        // get form values
        if($this->getRequest()->isPost())
        {
            $data = array();
            
            $data['bonus_amt'] = $this->getRequest()->getPost('bonus');
            $data['ticket_for'] = $this->getRequest()->getPost('ticketfor');
            $data['valid_from'] = $this->getRequest()->getPost('validfrom');
            $data['valid_upto'] = $this->getRequest()->getPost('validupto');
            $data['limitation'] = $this->getRequest()->getPost('limitation');
            $data['selling_status'] = $this->getRequest()->getPost('sellingstatus');
            $data['status'] = 1;
            
            // insert values to db
            $result = $objTicketsModel->uploadNewTicket($data);
            
            //echo "<pre>"; print_r($result); echo "</pre>";die();
        
            if($result)
            {
                $this->_redirect("/admin/ticket");
            }
        }
        
    }
    
    /**
     * Developer    : Pradeep K C
     * Date         : 21/11/2014
     * Description  : ticket details (manage ticket)
     */
    public function deleteTicketAction()
    {
        // get ticket id from url
        $ticketId = $this->getRequest()->getParam('ticketId');
        
        $objTicketModel = Admin_Model_Tickets::getInstance();
        
        // delete ticket by id from the database
        $response = $objTicketModel->ticketDelete($ticketId);
        
        if($response)
        {
            $this->_redirect("/admin/ticket");
        }
        
        
    }
    
    /**
     * Developer    : Pradeep K C
     * Date         : 27/11/2014
     * Description  : Activate/De-activate status
     */
    
    public function toggleStatusAction()
    {
        // get ticket and status id from url
        $ticketId = $this->getRequest()->getParam('ticketId');
        $statusId = $this->getRequest()->getParam('statusId');
        
        $objTicketModel = Admin_Model_Tickets::getInstance();
        
        // delete ticket by id from the database
        $response = $objTicketModel->toggleStatus($ticketId,$statusId);
        
        $this->view->response = $response;
    }
}