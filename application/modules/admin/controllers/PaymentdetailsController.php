<?php
require_once 'Engine/Pagarme/Pagarme/RestClient.php';
/**
 * PaymentdetailsController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class Admin_PaymentdetailsController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function preDispatch() {
        
        $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }

    public function paymentDetailsAction() {

        $objpayment = Admin_Model_Payment::getInstance();
        $paymentdetails = $objpayment->getPaymentDetails();
         $this->view->paymentdetails = $paymentdetails;
    }



    public function paymentTeacher() {
        $payment = Admin_Model_Payment::getInstance();
        $adminpaymentmonthly = Admin_Model_AdminPaymentMonthly::getInstance();
         $usrmodel = Application_Model_Users::getInstance();
        $teachingclasses = Admin_Model_TeachingClasses::getInstance();
        $teacherpaymentdetails = Admin_Model_Teacherpaymentdetails::getInstance();
        $paymentdata = Admin_Model_paymentdata::getInstance();
        $monthlyleftover = $this->getRequest()->getPost('monthlyleftover');
        $checkarruser1 = $this->getRequest()->getPost('checkarruser');
        $checkarruser=array();
          $erroremails=array();
       
        foreach ($checkarruser1 as $itemss) {
              if($itemss["email"]!="")
              {
                  array_push($checkarruser, $itemss);
              }
               else {
                   
                    $userinfo=$usrmodel->getUserDetail($itemss["userid"]);
     array_push($erroremails, $userinfo["first_name"]);
    
 }

              
            }
      
        $total_teacher = $teachingclasses->totalTeacher();
        $totalClasses = $teachingclasses->totalClasses();

        $result = $payment->getPaidStudents();
        $yearlystudentcount = 0;
        $monthlystudentcount = 0;
        $yearlyamount = 0;
        $monthlyamount = 0;
        $currentMonth = date('Y-m');
        
        $c = date('Y-m-d',strtotime($currentMonth.'-15'));
        $p = date('Y-m-d', strtotime($c.'-1 month'));

        $date = date("Y/m/d");
        $date = explode('/', $date);
        $year = $date[0];
        $curdate = (int) $date[2];

        if ($curdate < 15) {
            $month = $date[1] - 1;
        } else {

            $month = $date[1];
        }
     
             
        foreach ($result as $key => $value) {
            if ($value['subscription_id'] == 4 || $value['subscription_id'] == 5) {
            
                $yearlyamount = $value['payment_amount'];
                $yearlystudentcount++;
            }
            if ($value['subscription_id'] == 1 || $value['subscription_id'] == 3) {
                $monthlyamount = $value['payment_amount'];
                $monthlystudentcount++;
            }
        }
 
        $monthly_total_amount = $monthlystudentcount * $monthlyamount;

        $total_yearly_payment = $yearlystudentcount * $yearlyamount;


        $getpercentage = $paymentdata->getpercentage();
        $monthly = $total_yearly_payment / 12;
        $total_assets = $monthly_total_amount + $monthly;
        $teacher_patternship = ($total_assets * $getpercentage['devide_percentage']) / 100;
        $this_month = $monthly + $monthly_total_amount;
       
        $data = array('Month' => $month, 'monthlyleftover' => $monthlyleftover, 'no_of_class' => $totalClasses, 'no_of_teacher' => count($total_teacher), 'annual_earned' => $total_yearly_payment, 'monthly_earned' => $monthly_total_amount, 'Total_subscibe_monthly' => $monthlystudentcount, 'Total_subscribe_Annually' => $yearlystudentcount, 'Teacher_patternship' => $teacher_patternship, 'devided_by_12' => $monthly, 'Year' => $year, 'Total' => $this_month);
 
        $adminpaymentmonthly->insertmonthlyDetails($data,$month,$year);
    
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->isPost()) {
            $checkarr = $this->getRequest()->getPost('checkarr');
          
            $paytousers=array();
          
            foreach ($checkarr as $items) {
              if($items["email"]!=""&&$items["amount"]!=0)
              {
                  array_push($paytousers, $items);
              }

              
            }
            

            $objPaypal = Engine_Payment_Paypal_Paypal::getInstance();
           
            $massPay = $objPaypal->MassPay($paytousers);
          

            if ($massPay['ACK'] == "Success") {

// 
                foreach ($checkarruser as $monthlypaymentdata) {
                  
                    $systemdate1 = date("Y/m/d");

                    $systemdate = split('/', $systemdate1);
                    $year = $systemdate[0];
                    if($systemdate[2]<=15){
                    $month = $systemdate[1] - 1;}
                    else{
                        $month = $systemdate[1];
                    }
                    $teacher_id = $monthlypaymentdata['userid'];
                    $amount = $monthlypaymentdata['amount'];
                    $teacher_monthly_paymentdata = array('paid_status' =>'paid','date_of_payment'=>$systemdate1,);
                    
                    $teacherpaymentdetails->updatepaymentstatus($teacher_monthly_paymentdata,$teacher_id);
                }

                $objUserMeta = Admin_Model_UsersMeta::getInstance();
                foreach ($checkarruser as $val) {
                    $resultusers = $objUserMeta->updatePaymentUsermeta($val['userid']);
                }
                $response=array();
                $response['MESS']=$massPay['ACK'];
                 $response['err']=$erroremails;
                echo json_encode($response);
            } else {
                $response=array();
                $response['MESS']=$massPay['L_LONGMESSAGE0'];
                 $response['err']=$erroremails;
                echo json_encode($response);
            }
        }
    }
 
 
    
    /* Developer:Rakesh Jha
      Dated:13-03-15
      Description:Teacher Payment through referal
     */

    public function referalPaymentAction() {
        $users = Application_Model_Users::getInstance();
        $result = $users->getReferedStudents();
        if ($result) {
            $count = 0;
            foreach ($result as $key => $value) {
                $result1 = $users->getReferStudents();
//             $result2=$users->getReferalStudents();
                $monthly = $result1['monthly_user'];

//          $yearly=$result2['yearly_user'];
                $totaluser = $value['total_user'];
                $year = ($totaluser - $monthly);
                $result[$count]['monthly_user'] = $monthly;
                $result[$count]['yearly'] = $year;
                $amount = ($year * 25 + $monthly * 10);
                $result[$count]['amount'] = $amount;
                $name = $users->getTeachername($value['user_id']);
                $result[$count]['name'] = $name['first_name'];
                $count++;
            }
            $this->view->result = $result;
        }
    }
    
    
    
  //////////////// CALCULATION OF TEACHER PAYMENT///////////////////////

  /*     DEV: Priyanka varanasi
         DESC: Teacher Formula
        DATE:TIME 23/9/2015 7:22pm 
   
 */  
    
        public function getPaidStudentsAction() {
  
        
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teachingclass = Admin_Model_TeachingClasses::getInstance();
       $teachersstudents = Admin_Model_Classenroll::getInstance();
       $uservideostatus = Admin_Model_UserVideoStatus::getInstance();
       $classreviewscount = Admin_Model_ClassesReview::getInstance();
       $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
       $projects = Admin_Model_Projects::getInstance();
       
       
       $monthlypaiduserscount  = $paymentnewtable->getNoOfMonthlyPaidUsers();
      if($monthlypaiduserscount){
          
         $this->view->monthlypaiduserscount = $monthlypaiduserscount['monthlyusers'];
      }
       $Yearlypaiduserscount  = $paymentnewtable->getNoOfYearlyPaidUsers();
      
        if($Yearlypaiduserscount){
          
         $this->view->yearlypaiduserscount = $Yearlypaiduserscount['yearlyusers'];
      }
      
        $getmonthlysum = $paymentnewtable->getMonthlySumOfAmount();
        
       if($getmonthlysum){
          
         $this->view->getmonthlysum = $getmonthlysum['monthlysum'];
          }
          
        $getyearlysum = $paymentnewtable->getYearlySumOfAmount();
       
        
       if($getyearlysum){
          
         $this->view->getyearlysum = $getyearlysum['yearlysum'];
       }
       $convertyearlytomonthly = 0 ;
       
       if($getyearlysum['yearlysum']!=0){
           
       $convertyearlytomonthly = ($getyearlysum['yearlysum'])/12 ;
       
       }
       $total = $getmonthlysum['monthlysum'] + $convertyearlytomonthly;
      
       if($total){
           
          $this->view->totalsum = $total;  
       }else{
           $total = 0;
            $this->view->totalsum = $total;  
           
       }
       $commissiondata = $paymentformulamodal ->getPaymentFormulaValues();
       if($commissiondata){
         $this->view->commissiondata = $commissiondata;
           
       }
       
       
       $listofclasswithprojects= $teachingclass->getClassesInPublishStatus();
  
      
       $listofclasswithstudents= $teachingclass->getClassesOfPublishStatus();
    
       $classreviewsarray = $teachingclass->getClassesReviewsOfPublishStatus();
       $totalprojectsinFashionlearn['totalprojectscount']  =  $projects->getTotalNoOFProjectsInFAshionlearn();
       
       $totalstudentsinFashionlearn['totalstudentscount']  = $teachersstudents->getTotalNoOFStudentsInFAshionlearn();
       
       $totalteachsalary= array();
       $actualtotal = ($total*$commissiondata['percentage_divide'])/100;
   
        
        $totalwithsat=0;
        $totalwithoutsat=0;
         for($i=0;$i<count($listofclasswithprojects);$i++)
        { 
             if(!isset($totalteachsalary[$listofclasswithprojects[$i]["user_id"]]))
             {
              $totalteachsalary[$listofclasswithprojects[$i]["user_id"]]=0;   
             }
         $listofclasswithprojects[$i]["studentcount"]= $listofclasswithstudents[$i]["studentcount"];
          $satisfaction_per = $classreviewsarray[$i]["avgreviewspercentage"];
            if ($satisfaction_per >= 90) {
                $satisfaction_per = 100;
            } else if ($satisfaction_per >= 80) {

                $satisfaction_per = 95;
            } else if ($satisfaction_per >= 70) {
                $satisfaction_per = 90;
            } else {
                $satisfaction_per = 85;
            }
         $listofclasswithprojects[$i]["avgreviewspercentage"]= $satisfaction_per;
         
         $listofclasswithprojects[$i]["pernoofprojects"]= ((($listofclasswithprojects[$i]["projectcount"]/$totalprojectsinFashionlearn['totalprojectscount'])*$commissiondata['projects_weightage'])/100)*100 ;
         $listofclasswithprojects[$i]["pernoofstudents"]= ((($listofclasswithprojects[$i]["studentcount"]/$totalstudentsinFashionlearn['totalstudentscount'])*$commissiondata['students_weightage'])/100)*100 ;
         $listofclasswithprojects[$i]["sumtotalper"]= (($listofclasswithprojects[$i]["pernoofprojects"]+ $listofclasswithprojects[$i]["pernoofstudents"])) ;
         $listofclasswithprojects[$i]["valuewidoutsatifaction"]= ($actualtotal *($listofclasswithprojects[$i]["sumtotalper"]))/100;
         $listofclasswithprojects[$i]["valuewidsatifaction"]= (( $actualtotal * $listofclasswithprojects[$i]["sumtotalper"]*($satisfaction_per/100)))/100 ;
         $listofclasswithprojects[$i]["leftover"]= ($listofclasswithprojects[$i]["valuewidoutsatifaction"])-($listofclasswithprojects[$i]["valuewidsatifaction"]);
         $totalteachsalary[$listofclasswithprojects[$i]["user_id"]]+=$listofclasswithprojects[$i]["valuewidsatifaction"];
         $totalwithsat+=$listofclasswithprojects[$i]["valuewidsatifaction"];
         $totalwithoutsat+=$listofclasswithprojects[$i]["valuewidoutsatifaction"]   ;     
         }
//    
      
        if($listofclasswithprojects){
            ($totalwithoutsat);
      
       $this->view->teacherformulaevalues =  $listofclasswithprojects; 
        }
        
        
        
        
         ###################### if ajax request ##############
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $totalteachsalary=array();
            
             $p=0;
         $studentpercentage = $this->getRequest()->getPost('studentpercentage');
         $projectpercentage = $this->getRequest()->getPost('projectpercentage');
         $percentagetodivide = $this->getRequest()->getPost('dividepercentage');
         $totaldisamount  =    $this->getRequest()->getPost('totalamount');
         
       
         $totalwithsat=0;
        $totalwithoutsat=0;
        for($i=0;$i<count($listofclasswithprojects);$i++)
        {   
          if(!isset($totalteachsalary[$listofclasswithprojects[$i]["user_id"]]))
             {
              $totalteachsalary[$listofclasswithprojects[$i]["user_id"]]=0;   
             }
         $listofclasswithprojects[$i]["studentcount"]=$listofclasswithstudents[$i]["studentcount"];
          $satisfaction_per = $classreviewsarray[$i]["avgreviewspercentage"];
            if ($satisfaction_per >= 90) {
                $satisfaction_per = 100;
            } else if ($satisfaction_per >= 80) {

                $satisfaction_per = 95;
            } else if ($satisfaction_per >= 70) {
                $satisfaction_per = 90;
            } else {
                $satisfaction_per = 85;
            }
         $listofclasswithprojects[$i]["avgreviewspercentage"]= $satisfaction_per;
         $listofclasswithprojects[$i]["pernoofprojects"]= (((($listofclasswithprojects[$i]["projectcount"]/$totalprojectsinFashionlearn['totalprojectscount'])*$projectpercentage))/100)*100 ;
         $listofclasswithprojects[$i]["pernoofstudents"]= (((($listofclasswithprojects[$i]["studentcount"]/$totalstudentsinFashionlearn['totalstudentscount'])*$studentpercentage))/100)*100 ;
         $listofclasswithprojects[$i]["sumtotalper"]= ($listofclasswithprojects[$i]["pernoofprojects"]+ $listofclasswithprojects[$i]["pernoofstudents"]) ;
         $listofclasswithprojects[$i]["valuewidoutsatifaction"]= ($totaldisamount *($listofclasswithprojects[$i]["sumtotalper"]))/100;
         $listofclasswithprojects[$i]["valuewidsatifaction"]= (( $totaldisamount * $listofclasswithprojects[$i]["sumtotalper"]*($satisfaction_per/100)))/100 ;
         $listofclasswithprojects[$i]["leftover"]= ($listofclasswithprojects[$i]["valuewidoutsatifaction"])-($listofclasswithprojects[$i]["valuewidsatifaction"]);
         $totalteachsalary[$listofclasswithprojects[$i]["user_id"]]+=$listofclasswithprojects[$i]["valuewidsatifaction"];
         $totalwithsat+=$listofclasswithprojects[$i]["valuewidsatifaction"];
         $totalwithoutsat+=$listofclasswithprojects[$i]["valuewidoutsatifaction"]   ;
         
         $p+=$listofclasswithprojects[$i]["pernoofprojects"]+$listofclasswithprojects[$i]["pernoofstudents"];
      
           }
          if($listofclasswithprojects){
             $result = array('code'=>200,
                           'data' => $listofclasswithprojects,'total'=>$totalteachsalary,"t"=>$totalwithoutsat,"ts"=>$totalwithsat,"totalp"=>$p);
             echo json_encode($result);
             die();
             
         }else{
           $result = array('code'=>198,
                      'data' => 'no change'); 
            echo json_encode($result);
           die();
             
         }
    
        }
     ############################### code ends ##############################
         
         
         
         
        }     
     
        
    /*     DEV: Priyanka varanasi
         DESC: Teacher Formula updation
        DATE:TIME 25/9/2015 11:45am 
   
 */       
    public function teacherFormulaAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true); 
         $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
         $commissiondata = $paymentformulamodal ->getPaymentFormulaValues();
         $totaldisamount  =    $this->getRequest()->getPost('totalamount');
         $data['percentage_divide'] = $this->getRequest()->getPost('dividepercentage');
         $data['students_weightage'] = $this->getRequest()->getPost('studentpercentage');
         $data['projects_weightage'] = $this->getRequest()->getPost('projectpercentage');
         
         $updateresult = $paymentformulamodal ->UpdatePaymentFormulaValues($commissiondata['formula'],$data);
         
         if($updateresult){
             $result = array('code'=>200,
                             'message' => 'updated successfully');
             echo json_encode($result);
            die();
             }else{
                     $result = array('code'=>198,
                             'message' => 'Error in updation');
            echo  json_encode($result);      
              die();      
                      }
 
    }
    
           
    /*   DEV: Priyanka varanasi
         DESC: Teacher payment action 
        DATE:TIME 28/9/2015 7:17pm
      
                     **Cron Function**
   
 */   
    public function teacherPaymentAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);  
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teachingclass = Admin_Model_TeachingClasses::getInstance();
       $teachersstudents = Admin_Model_Classenroll::getInstance();
       $uservideostatus = Admin_Model_UserVideoStatus::getInstance();
       $classreviewscount = Admin_Model_ClassesReview::getInstance();
       $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
       $projects = Admin_Model_Projects::getInstance();  
       $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance();  
       $classwiseearningsmodal = Admin_Model_ClassWiseEarnings::getInstance();  
       $teacherpaymonthlystatistics = Admin_Model_TeacherpayMonthlystatistics::getInstance();  
       
        $monthlypaiduserscount  = $paymentnewtable->getNoOfMonthlyPaidUsers(); // monthly paid users count
        
        $Yearlypaiduserscount  = $paymentnewtable->getNoOfYearlyPaidUsers(); // yearly paid users count
        
        $getmonthlysum = $paymentnewtable->getMonthlySumOfAmount(); // monthly sum of amount
         
        
       $getyearlysum = $paymentnewtable->getYearlySumOfAmount(); // yearly sum of amount
       
       $convertyearlytomonthly = 0 ;
       
       if($getyearlysum['yearlysum']!=0){
           
       $convertyearlytomonthly = ($getyearlysum['yearlysum'])/12 ; //yearly converted to monthly
       
       }
       $total = $getmonthlysum['monthlysum'] + $convertyearlytomonthly; // sum of monthly and monthly converted yearly
          
      $commissiondata = $paymentformulamodal ->getPaymentFormulaValues(); //weightage given to teachers(%)
      
      $listofclasswithprojects= $teachingclass->getClassesInPublishStatus(); // list of all projects count for class
  
      
       $listofclasswithstudents= $teachingclass->getClassesOfPublishStatus();//list of all student count for a class
    
       $classreviewsarray = $teachingclass->getClassesReviewsOfPublishStatus();//satisfaction %
       
       $totalprojectsinFashionlearn['totalprojectscount']  =  $projects->getTotalNoOFProjectsInFAshionlearn();// Total projectcs in site
       
       $totalstudentsinFashionlearn['totalstudentscount']  = $teachersstudents->getTotalNoOFStudentsInFAshionlearn(); //total students in site
       
       $actualtotal = ($total*$commissiondata['percentage_divide'])/100; // amount divided among teachers
      
     if(!isset($addleftover)){
                 $addleftover= 0;
         }
        if(!isset($totalwithsat)){
                 $totalwithsat= 0;
         } 
         for($i=0;$i<count($listofclasswithprojects);$i++)
        { 
         $listofclasswithprojects[$i]["studentcount"]= $listofclasswithstudents[$i]["studentcount"];
          $satisfaction_per = $classreviewsarray[$i]["avgreviewspercentage"];
            if ($satisfaction_per >= 90) {
                $satisfaction_per = 100;
            } else if ($satisfaction_per >= 80) {

                $satisfaction_per = 95;
            } else if ($satisfaction_per >= 70) {
                $satisfaction_per = 90;
            } else {
                $satisfaction_per = 85;
            }
         $listofclasswithprojects[$i]["avgreviewspercentage"]= $satisfaction_per;
         $listofclasswithprojects[$i]["pernoofprojects"]= ((($listofclasswithprojects[$i]["projectcount"]/$totalprojectsinFashionlearn['totalprojectscount'])*$commissiondata['projects_weightage'])/100)*100 ;//projects percentage of class
         $listofclasswithprojects[$i]["pernoofstudents"]= ((($listofclasswithprojects[$i]["studentcount"]/$totalstudentsinFashionlearn['totalstudentscount'])*$commissiondata['students_weightage'])/100)*100 ; // students percentage of class
         $listofclasswithprojects[$i]["sumtotalper"]= (($listofclasswithprojects[$i]["pernoofprojects"]+ $listofclasswithprojects[$i]["pernoofstudents"])) ; // sum of total percentage
         $listofclasswithprojects[$i]["valuewidoutsatifaction"]= ($actualtotal *($listofclasswithprojects[$i]["sumtotalper"]))/100; //value without satisfaction
         $listofclasswithprojects[$i]["valuewidsatifaction"]= (( $actualtotal * $listofclasswithprojects[$i]["sumtotalper"]*($satisfaction_per/100)))/100 ; //value with satisfaction
         $listofclasswithprojects[$i]["leftover"]= ($listofclasswithprojects[$i]["valuewidoutsatifaction"])-($listofclasswithprojects[$i]["valuewidsatifaction"]); // leftover 
         $addleftover+= $listofclasswithprojects[$i]["leftover"];
         $totalwithsat+= $listofclasswithprojects[$i]["valuewidsatifaction"];
         }
         $classearnings = array();
         $l = 0;
                 foreach($listofclasswithprojects as $pval){
           $classearnings[$l]['class_id'] = 
                   $classearnings[$l]['class_id'] = $pval['class_id'];
                   $classearnings[$l]['user_id'] = $pval['user_id'];
                   $classearnings[$l]['class_name'] = $pval['class_title'];
                   $classearnings[$l]['class_earned'] = $pval['valuewidsatifaction'];
                   $classearnings[$l]['calculated_date'] = date('Y-m-d');
            $l++;
       }
       
       
        $classwiseearningsmodal->insertClassWiseEarningsInfo($classearnings); //// to insert class data in class wise earnings
         
         
       $data['cur_month_userscount'] =  $monthlypaiduserscount['monthlyusers'];
       $data['cur_year_userscount'] =  $Yearlypaiduserscount['yearlyusers'];
       $data['admin_weigtage'] = 100 - $commissiondata['percentage_divide'];
       $data['insert_date'] = date('Y-m-d');
       $data['monthly_sum'] = $getmonthlysum['monthlysum'];
       $data['yearly_sum'] = $convertyearlytomonthly;
       $data['admin_weightage_amount'] = ($total)-($actualtotal);
       $data['teacher_weightage_amount'] = $actualtotal;
       $data['totalwithsatisfaction'] = $totalwithsat;
       $data['current_leftover'] = $addleftover;
       $data['divide_percentage'] = $commissiondata['percentage_divide'];
                                   
       $teacherpaymonthlystatistics->insertTeacherCurrentPaymentStatics($data); /// inserting admin monthly statics 
         
         $finalarray = array();
          $userid = 0 ;
         foreach ($listofclasswithprojects as $value) {
          $userid=$value['user_id'];

       if(!isset($finalarray[$userid]['total_classes'])) {
                $finalarray[$userid]['total_classes'] = 0;
         }
       if(!isset($finalarray[$userid]['total_students'])){
               $finalarray[$userid]['total_students']= 0;
         }
       if(!isset($finalarray[$userid]['total_projects'])){
                $finalarray[$userid]['total_projects']= 0;
         }
       if(!isset($finalarray[$userid]['satisfaction'])){
                 $finalarray[$userid]['satisfaction']= 0;
         }
       if(!isset($finalarray[$userid]['projects_percentage'])){
                 $finalarray[$userid]['projects_percentage']= 0;
         }
       if(!isset($finalarray[$userid]['students_percentage'])){
                 $finalarray[$userid]['students_percentage']= 0;
         }
       if(!isset($finalarray[$userid]['total_percentage'])){
                 $finalarray[$userid]['total_percentage']= 0;
         }
       if(!isset($finalarray[$userid]['without_satisfaction'])){
                 $finalarray[$userid]['without_satisfaction']= 0;
         }
       if(!isset($finalarray[$userid]['with_satisfaction'])){
                 $finalarray[$userid]['with_satisfaction']= 0;
         }
       if(!isset($finalarray[$userid]['leftover'])){
                 $finalarray[$userid]['leftover']= 0;
         }
       
         
                 $finalarray[$userid]['user_id'] = $userid;
                 $finalarray[$userid]['total_classes'] += count($value['class_id']);
                 $finalarray[$userid]['total_students']+= $value['studentcount'];
                 $finalarray[$userid]['total_projects']+= $value['projectcount'];
                 $finalarray[$userid]['satisfaction']+= ($value['avgreviewspercentage']);
                 $finalarray[$userid]['projects_percentage']+= ($value['pernoofprojects']);
                 $finalarray[$userid]['students_percentage']+=($value['pernoofstudents']);
                 $finalarray[$userid]['total_percentage']+=($value['sumtotalper']);
                 $finalarray[$userid]['without_satisfaction']+=($value['valuewidoutsatifaction']);
                 $finalarray[$userid]['with_satisfaction']+=($value['valuewidsatifaction']);
                 $finalarray[$userid]['leftover']+=($value['leftover']);
                 $finalarray[$userid]['pay_date'] = date('Y-m-d');
                 $finalarray[$userid]['payment_status'] = 0;
                 $finalarray[$userid]['trans_id'] = null;
              
             
         
         }
         
         foreach ($finalarray as $value) {
             $response  = $teacherpaymentmodal->getLastMonthCurrentBal($value['user_id']);
             if(!isset( $finalarray[$value['user_id']]['teacher_last_leftover'])){
                 $finalarray[$value['user_id']]['teacher_last_leftover']= 0;
         }
          $finalarray[$value['user_id']]['teacher_last_leftover']+= $response['teacher_current_leftover'];
          
         }
        
        $teacherpaymentmodal->insertPaymentValuesInTOTable($finalarray); // inserting teacher monthly payment statistcs 
          
         
         
  
         }
           
    /*   DEV: Priyanka varanasi
         DESC: Teacher payment  processing using pagar
        DATE:TIME 3/10/2015 12:35pm
      
                     
   
 */   
      public function paymentProcessAction(){
             
       $this->_helper->layout()->disableLayout();
       $this->_helper->viewRenderer->setNoRender(true); 
              
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teachingclass = Admin_Model_TeachingClasses::getInstance();
       $teachersstudents = Admin_Model_Classenroll::getInstance();
       $uservideostatus = Admin_Model_UserVideoStatus::getInstance();
       $classreviewscount = Admin_Model_ClassesReview::getInstance();
       $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
       $mailer = Engine_Mailer_Mailer::getInstance();
       $projects = Admin_Model_Projects::getInstance();  
       $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance(); 
       $payinfo  = $this->getRequest()->getParam('payinfo');
       
       $newpayusers = implode(',',$payinfo);
       $result   = $teacherpaymentmodal->selectTeachersTOBePaidByUserid($newpayusers);
        $userrprocessarray = array();
        $i=0;
       foreach ($result as $value) {
        if((($value['with_satisfaction']+$value['teacher_last_leftover'])*100)>=100){
           if(!empty($value['pagar_bank_id'])  && ($value['payment_status'] == 0 || 3 || 5 || 6 )){
            $data["api_key"] = $apikey;
            $data["amount"] = $value['with_satisfaction']*100;
            $data["bank_account_id"] = $value['pagar_bank_id'];
              try {
               $params["url"]='https://api.pagar.me/1/transfers';
               $params["parameters"]= $data;        
               $params["method"]= "POST";        
               $rs= new RestClient($params);
               $result= $rs->run();
               if($result['code']=== 200){
               $pagarinfo = (array)json_decode($result['body']);
               $dat['trans_id'] = $pagarinfo['id'];
              $updateresult = $teacherpaymentmodal->updateTeacherPaymentInfo($value['pay_id'],$dat);
               if($updateresult){
              $userrprocessarray[$i]['useremail'] = 'Transaction is successful for this user of email id'.$value['email'];
               $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = "financeiro@fashionlearn.com.br";
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => 'Transaction is successful for this user of email id <strong>'.$value['email'].'</strong>'
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers,"financeiro@fashionlearn.com.br"); 
               }else{
              $userrprocessarray[$i]['useremail'] = 'Transaction is successful but the data is not updated  for this user of email id <strong>'.$value['email'].'</strong>'; 
               $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email ="financeiro@fashionlearn.com.br";
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => 'Transaction is successful but the data is not updated  for this user of email id  '.$value['email']
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers,"financeiro@fashionlearn.com.br"); 
                   
               }
               }else{
                $userrprocessarray[$i]['useremail'] = 'Error occurs while processing the transaction for the user of email id &nbsp <strong>'.$value['email'].'</strong>';
                 $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = "financeiro@fashionlearn.com.br";
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => 'Error occurs while processing the transaction for thie user of email id &nbsp <strong>'.$value['email'].'</strong>'
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers,"financeiro@fashionlearn.com.br"); 
                }
            } catch (Exception $e) {
                
            } 
           
           
           
       }else{
         $userrprocessarray[$i]['useremail'] = 'This user of email &nbsp <strong>'.$value['email'].'</strong> do not have neccessary info for processing the transactions';        
         $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = "financeiro@fashionlearn.com.br";
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => 'This user of email <strong>'.$value['email'].'</strong> &nbsp do not have neccessary info for processing the transactions'
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers,"financeiro@fashionlearn.com.br");   
        }
        
       }else{
        $det['teacher_current_leftover'] =  $value['with_satisfaction']+$value['teacher_last_leftover'];
        $det['payment_status'] =  6;
        $updateresult = $teacherpaymentmodal->updateTeacherPaymentInfo($value['pay_id'],$det);   
        $userrprocessarray[$i]['useremail'] = 'This user of email &nbsp <strong>'.$value['email'].'</strong> have salary less than 1$ and hence payment cannot be done , it will added to next month';        
        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = "financeiro@fashionlearn.com.br";
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => $userrprocessarray[$i]['useremail']
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers,"financeiro@fashionlearn.com.br");      
        }
        $i++;    
         }
       
        echo json_encode($userrprocessarray);
        die();
      }
      
     
      
     /*   DEV: Priyanka varanasi
         DESC: Teacher payment transaction status update cron 
        DATE:TIME 5/10/2015 11:40am
      
                     
   
 */  
      
    public function teachertransactionstatusCronAction(){
      $this->_helper->layout()->disableLayout();
      $this->_helper->viewRenderer->setNoRender(true);    
      
       $objCore = Engine_Core_Core::getInstance();
       $realobj = $objCore->getAppSetting();
       $apikey = $realobj->pagar->ApiKey;
        
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teachingclass = Admin_Model_TeachingClasses::getInstance();
       $teachersstudents = Admin_Model_Classenroll::getInstance();
       $uservideostatus = Admin_Model_UserVideoStatus::getInstance();
       $classreviewscount = Admin_Model_ClassesReview::getInstance();
       $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
       $mailer = Engine_Mailer_Mailer::getInstance();
       $projects = Admin_Model_Projects::getInstance();  
       $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance();
       
        
       echo '<-------------------------------------Teacher transaction status update cron ------------------------------------------->';
       echo '<html><table border="1">';
       print '<th>userid</th><th>email</th>status<th>comments</th>';
       $result   = $teacherpaymentmodal->selectAllStatusUpdateTeachers();
      if($result){
          echo "<tbody>"; 
            foreach ($result as $val) {
                  echo "<tr>"; 
                 if(!empty($val["trans_id"])){
             $data["api_key"] = $apikey;
           try {
               $params["url"]='https://api.pagar.me/1/transfers/'.$val['trans_id'].'';
               $params["parameters"]= $data;        
               $params["method"]= "GET";        
               $rs= new RestClient($params);
               $result= $rs->run();
               if($result['code']=== 200){
               $pagarinfo = (array)json_decode($result['body']);
               if($pagarinfo['status']=='pending_transfer'){
                 $dat['payment_status'] = 2;  
                }else if($pagarinfo['status']=='Transferred'){
                  $dat['payment_status'] = 1;  
                   
               }else if($pagarinfo['status']=='failed'){
                   
                 $dat['payment_status'] = 3;   
               }else if($pagarinfo['status']=='processing '){
                 $dat['payment_status'] = 4;   
                   
               }else if($pagarinfo['status']=='canceled'){
                 $dat['payment_status'] = 5;   
               }
               $teacherpaymentmodal->updateTeacherPaymentInfo($val["pay_id"],$dat);
                print "<td> " . $val["user_id"] . "</td>" . "<td> " . $val["email"] . "</td>" . "<td>" . $pagarinfo["status"] . "</td>";    
               }
              echo "</tr>";   
            } catch (Exception $e) {
                
            } 
           }else{
             print "<td> " . $val["user_id"] . "</td>" . "<td> " . $val["email"] . "</td>" . "<td>Payment is not done yet</td>";        
               
           }
            }
             echo "</tbody>";  
          }
        else{
          echo "<tbody>";
          print '<td>No Pending status user exists to check</td><td>-</td><td>-</td><td>-</td>';  
          echo "</tbody>";
        } 
     echo "</table></html>";
       echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die();  
    
       
       
        
        
    } 
    
        /*   DEV: Priyanka varanasi
         DESC: calculation admin revenue and displaying on the basis of filter
        DATE:TIME 7/10/2015 1:06pm
      
                     
   
 */  
    public function getadminRevenueAction(){
     $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);  
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teachingclass = Admin_Model_TeachingClasses::getInstance();
       $teachersstudents = Admin_Model_Classenroll::getInstance();
       $uservideostatus = Admin_Model_UserVideoStatus::getInstance();
       $classreviewscount = Admin_Model_ClassesReview::getInstance();
       $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
       $projects = Admin_Model_Projects::getInstance();  
       $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance();  
       $teacherpaymonthlystatistics = Admin_Model_TeacherpayMonthlystatistics::getInstance();  
       
        $monthlypaiduserscount  = $paymentnewtable->getNoOfMonthlyPaidUsers(); // monthly paid users count
        
        $Yearlypaiduserscount  = $paymentnewtable->getNoOfYearlyPaidUsers(); // yearly paid users count
        
        $getmonthlysum = $paymentnewtable->getMonthlySumOfAmount(); // monthly sum of amount
         
        
       $getyearlysum = $paymentnewtable->getYearlySumOfAmount(); // yearly sum of amount
       
       $convertyearlytomonthly = 0 ;
       
       if($getyearlysum['yearlysum']!=0){
           
       $convertyearlytomonthly = ($getyearlysum['yearlysum'])/12 ; //yearly converted to monthly
       
       }
       
       $total = $getmonthlysum['monthlysum'] + $convertyearlytomonthly; // sum of monthly and monthly converted yearly
          
      $commissiondata = $paymentformulamodal ->getPaymentFormulaValues(); //weightage given to teachers(%)
      
      $listofclasswithprojects= $teachingclass->getClassesInPublishStatus(); // list of all projects count for class
  
      
       $listofclasswithstudents= $teachingclass->getClassesOfPublishStatus();//list of all student count for a class
    
       $classreviewsarray = $teachingclass->getClassesReviewsOfPublishStatus();//satisfaction %
       
       $totalprojectsinFashionlearn['totalprojectscount']  =  $projects->getTotalNoOFProjectsInFAshionlearn();// Total projectcs in site
       
       $totalstudentsinFashionlearn['totalstudentscount']  = $teachersstudents->getTotalNoOFStudentsInFAshionlearn(); //total students in site
       
       $actualtotal = ($total*$commissiondata['percentage_divide'])/100; // amount divided among teachers
      
     if(!isset($addleftover)){
                 $addleftover= 0;
         }
       
         for($i=0;$i<count($listofclasswithprojects);$i++)
        { 
         $listofclasswithprojects[$i]["studentcount"]= $listofclasswithstudents[$i]["studentcount"];
          $satisfaction_per = $classreviewsarray[$i]["avgreviewspercentage"];
            if ($satisfaction_per >= 90) {
                $satisfaction_per = 100;
            } else if ($satisfaction_per >= 80) {

                $satisfaction_per = 95;
            } else if ($satisfaction_per >= 70) {
                $satisfaction_per = 90;
            } else {
                $satisfaction_per = 85;
            }
         $listofclasswithprojects[$i]["avgreviewspercentage"]= $satisfaction_per;
         $listofclasswithprojects[$i]["pernoofprojects"]= ((($listofclasswithprojects[$i]["projectcount"]/$totalprojectsinFashionlearn['totalprojectscount'])*$commissiondata['projects_weightage'])/100)*100 ;//projects percentage of class
         $listofclasswithprojects[$i]["pernoofstudents"]= ((($listofclasswithprojects[$i]["studentcount"]/$totalstudentsinFashionlearn['totalstudentscount'])*$commissiondata['students_weightage'])/100)*100 ; // students percentage of class
         $listofclasswithprojects[$i]["sumtotalper"]= (($listofclasswithprojects[$i]["pernoofprojects"]+ $listofclasswithprojects[$i]["pernoofstudents"])) ; // sum of total percentage
         $listofclasswithprojects[$i]["valuewidoutsatifaction"]= ($actualtotal *($listofclasswithprojects[$i]["sumtotalper"]))/100; //value without satisfaction
         $listofclasswithprojects[$i]["valuewidsatifaction"]= (( $actualtotal * $listofclasswithprojects[$i]["sumtotalper"]*($satisfaction_per/100)))/100 ; //value with satisfaction
         $listofclasswithprojects[$i]["leftover"]= ($listofclasswithprojects[$i]["valuewidoutsatifaction"])-($listofclasswithprojects[$i]["valuewidsatifaction"]); // leftover 
         $addleftover+= $listofclasswithprojects[$i]["leftover"];
         }
         $month = $this->getRequest()->getParam('month');
       
         $year = $this->getRequest()->getParam('year');
         
         
         $curentstats = $teacherpaymonthlystatistics->getStatsofAdminstatistics($month,$year);
         
       if($curentstats){
       $data['cur_month_userscount'] =  $monthlypaiduserscount['monthlyusers'];
       $data['cur_year_userscount'] =  $Yearlypaiduserscount['yearlyusers'];
       $data['admin_weigtage'] = 100 - $commissiondata['percentage_divide'];
       $data['insert_date'] = date('Y-m-d');
       $data['monthly_sum'] = $getmonthlysum['monthlysum'];
       $data['yearly_sum'] = $convertyearlytomonthly;
       $data['admin_weightage_amount'] = ($total)-($actualtotal);
       $data['teacher_weightage_amount'] = $actualtotal;
       $data['current_leftover'] = $addleftover;
       $data['lastmonth_yearlysum'] = $curentstats['yearly_sum'];
       $data['lastmonth_monthlyysum'] = $curentstats['monthly_sum'];
       $data['last_adminweightage'] = $curentstats['admin_weightage_amount'];
       $data['last_leftover'] = $curentstats['current_leftover'];
       $data['month_differencs'] =  round(((($data['monthly_sum']-$curentstats['monthly_sum'])/$curentstats['monthly_sum'])*100));
       $data['yearly_differencs'] =  round(((($data['yearly_sum']-$curentstats['yearly_sum'])/$curentstats['yearly_sum'])*100));
       $data['admin_revenue'] =   round(((($data['admin_weightage_amount']-$curentstats['admin_weightage_amount'])/$curentstats['admin_weightage_amount'])*100));
       $data['admin_leftover'] = round(((($data['current_leftover']-$curentstats['current_leftover'])/$curentstats['current_leftover'])*100));
    
        if($data){
          $res = array('code'=>200,
                         'data'=>$data) ;
          echo json_encode($res);
          die();
        }
        }else{
         
          $res = array('code'=>198,
                         'data'=>'Sorry You dont have any statistics for the month and year choosen') ;
          echo json_encode($res); 
           die();
            
            
        }
        
    }
    
    
       /*DEV: Priyanka varanasi
        DESC: calculation admin revenue and displaying on the basis of filter in modal
        DATE:TIME 7/10/2015 1:06pm
      
                     
   
 */ 

public function getannualOverviewAction(){
    
      $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);  
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teachingclass = Admin_Model_TeachingClasses::getInstance();
       $teachersstudents = Admin_Model_Classenroll::getInstance();
       $uservideostatus = Admin_Model_UserVideoStatus::getInstance();
       $classreviewscount = Admin_Model_ClassesReview::getInstance();
       $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
       $projects = Admin_Model_Projects::getInstance();  
       $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance();  
       $teacherpaymonthlystatistics = Admin_Model_TeacherpayMonthlystatistics::getInstance(); 
       
      $year =  $this->getRequest()->getParam('year');
       
                     //  date :09/10/2015 12:11 pm
     ///////////Anuual teacher payment over view /////////////////
      $monthlystatistics    = $teacherpaymonthlystatistics->getAdminstaticsByMonthlyByYear($year);
      
     
      
      $paid_teachers    = $teacherpaymentmodal->getsalaryPaidTeachersMonhtlyByYear($year);
    
      $unpaid_teachers    = $teacherpaymentmodal->getunpaidTeacherCountByYear($year);
      
      
      $adminmonthlystatistics = array();
      if($monthlystatistics){
           $i=0;
      foreach ($monthlystatistics as $value) {
          if($paid_teachers){
          foreach($paid_teachers as $val){
           if(date("M",strtotime($value['insert_date']))== date("M",strtotime($val['pay_date']))){
              
            $adminmonthlystatistics[$i]['totalannualsubscriptions'] = $value['cur_year_userscount'];  
            $adminmonthlystatistics[$i]['totalmonthlysubscriptions'] = $value['cur_month_userscount'];  
            $adminmonthlystatistics[$i]['totalthismonthyearlysum'] = $value['yearly_sum'];  
            $adminmonthlystatistics[$i]['totalthismonthmonthlysum'] = $value['monthly_sum'];  
            $adminmonthlystatistics[$i]['monthlyplusyearly'] = $value['yearly_sum']+$value['monthly_sum']; 
            $adminmonthlystatistics[$i]['teacherpartnershippercent'] = $value['divide_percentage']; 
            $adminmonthlystatistics[$i]['amounttodivide'] = $value['totalwithsatisfaction']; 
            $adminmonthlystatistics[$i]['alreadydivided'] =$val['withsatisfaction'] ;
            $adminmonthlystatistics[$i]['pay_date'] = date("F", strtotime($val['pay_date'])) ;
            $adminmonthlystatistics[$i]['date'] = $value['insert_date'];
            $adminmonthlystatistics[$i]['balance_to_divide'] = ($adminmonthlystatistics[$i]['amounttodivide'])-($adminmonthlystatistics[$i]['alreadydivided']);
            
            }else{
            $adminmonthlystatistics[$i]['totalannualsubscriptions'] = $value['cur_year_userscount'];  
            $adminmonthlystatistics[$i]['totalmonthlysubscriptions'] = $value['cur_month_userscount'];  
            $adminmonthlystatistics[$i]['totalthismonthyearlysum'] = $value['yearly_sum'];  
            $adminmonthlystatistics[$i]['totalthismonthmonthlysum'] = $value['monthly_sum'];  
            $adminmonthlystatistics[$i]['monthlyplusyearly'] = $value['yearly_sum']+$value['monthly_sum']; 
            $adminmonthlystatistics[$i]['teacherpartnershippercent'] = $value['divide_percentage']; 
            $adminmonthlystatistics[$i]['amounttodivide'] = $value['totalwithsatisfaction']; 
            $adminmonthlystatistics[$i]['alreadydivided'] = 0;
            $adminmonthlystatistics[$i]['date'] = $value['insert_date'];
            $adminmonthlystatistics[$i]['pay_date'] = date("F,", strtotime($value['insert_date'])) ;
            $adminmonthlystatistics[$i]['balance_to_divide'] = 0;    
              
          }
         }
          }
          else{
              
            $adminmonthlystatistics[$i]['totalannualsubscriptions'] = $value['cur_year_userscount'];  
            $adminmonthlystatistics[$i]['totalmonthlysubscriptions'] = $value['cur_month_userscount'];  
            $adminmonthlystatistics[$i]['totalthismonthyearlysum'] = $value['yearly_sum'];  
            $adminmonthlystatistics[$i]['totalthismonthmonthlysum'] = $value['monthly_sum'];  
            $adminmonthlystatistics[$i]['monthlyplusyearly'] = $value['yearly_sum']+$value['monthly_sum']; 
            $adminmonthlystatistics[$i]['teacherpartnershippercent'] = $value['divide_percentage']; 
            $adminmonthlystatistics[$i]['amounttodivide'] = $value['totalwithsatisfaction']; 
            $adminmonthlystatistics[$i]['alreadydivided'] = 0;
            $adminmonthlystatistics[$i]['date'] = $value['insert_date'];
            $adminmonthlystatistics[$i]['pay_date'] = date("F,", strtotime($value['insert_date'])) ;
            $adminmonthlystatistics[$i]['balance_to_divide'] = 0;    
              
          }
      $i++;
      } 
      
      $total = 0;
        foreach($adminmonthlystatistics as $v){
          $total+= $v['alreadydivided'] ;
        }  
            
      $j=0;
     foreach ($adminmonthlystatistics as $value) {
       if($unpaid_teachers){
         foreach($unpaid_teachers as $val){
           if(date("M",strtotime($value['date']))== date("M",strtotime($val['pay_date']))){    
             $adminmonthlystatistics[$j]['unpaidcount'] = $val['unpaidsteachers'];   
           }
         }
       }else{
         $adminmonthlystatistics[$j]['unpaidcount'] = 0;  
       }
    
      $j++;
      }
    
       $count=0;
      foreach ($adminmonthlystatistics as $value) {
          if(!isset($value['unpaidcount'])){
             $count++;
           }
      }
    
     if($adminmonthlystatistics){
          $res = array('code'=>200,
                         'data1'=>$adminmonthlystatistics,
                       'data2'=>$count,
                       'data3'=>$total,
                  ) ;
          echo json_encode($res);
          die();
        }
      }else{
          $res = array('code'=>198,
                         'data'=>'Sorry You dont have any statistics for the month and year choosen') ;
          echo json_encode($res); 
           die();
            
            
        }
       
} 


 /*   DEV: Priyanka varanasi
         DESC: referral payment filteration
        DATE:TIME 15/10/2015 3:30pm
   
 */ 
 public function getreferralsDetailsAction(){
   
       $this->_helper->layout()->disableLayout();
       $this->_helper->viewRenderer->setNoRender(true);  
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teachingclass = Admin_Model_TeachingClasses::getInstance();
       $teachersstudents = Admin_Model_Classenroll::getInstance();
       $uservideostatus = Admin_Model_UserVideoStatus::getInstance();
       $classreviewscount = Admin_Model_ClassesReview::getInstance();
       $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
       $projects = Admin_Model_Projects::getInstance();  
       $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance();  
       $teacherpaymonthlystatistics = Admin_Model_TeacherpayMonthlystatistics::getInstance();  
       $referralPaymenttableModal  =  Admin_Model_ReferralPaymentTable::getInstance();
       if($this->getRequest()->isPost()){
           $refyear = $this->getRequest()->getPost('year');
          $refmonth = $this->getRequest()->getPost('month');
  
      $result = $referralPaymenttableModal->getReferalsByYearAndMonth($refyear,$refmonth);
      if($result){
          $res = array('code'=>200,
                        'data'=>$result);
                  
                  echo json_encode($res);
               die();
       }else{
            $res = array('code'=>198,
                        'data'=>'No result found ');
                  
                  echo json_encode($res); 
                  die();
           
       }
      }
  
}
/*   DEV: Priyanka varanasi
         DESC: referral payment process
        DATE:TIME 15/10/2015 3:30pm
   
 */ 
public function referralpaymentProcessAction(){
    
       $this->_helper->layout()->disableLayout();
       $this->_helper->viewRenderer->setNoRender(true); 
              
        $objCore = Engine_Core_Core::getInstance();
        $realobj = $objCore->getAppSetting();
        $apikey = $realobj->pagar->ApiKey;
        
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teachingclass = Admin_Model_TeachingClasses::getInstance();
       $teachersstudents = Admin_Model_Classenroll::getInstance();
       $uservideostatus = Admin_Model_UserVideoStatus::getInstance();
       $classreviewscount = Admin_Model_ClassesReview::getInstance();
       $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
       $mailer = Engine_Mailer_Mailer::getInstance();
       $projects = Admin_Model_Projects::getInstance();  
       $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance(); 
       $referralPaymenttableModal  =  Admin_Model_ReferralPaymentTable::getInstance();
       $payinfo  = $this->getRequest()->getParam('payinfo');
       
       $newpayusers = implode(',',$payinfo);
       $result   = $referralPaymenttableModal->getReferalsByIds($newpayusers);
       
       $userrprocessarray = array();
        $i=0;
       foreach ($result as $value) {
        if((($value['total_earned'])*100)>=100){
           if(!empty($value['pagar_bank_id'])  && ($value['pay_status'] == 0 || 3 || 5 || 6 )){
            $data["api_key"] = $apikey;
            $data["amount"] = $value['total_earned']*100;
            $data["bank_account_id"] = $value['pagar_bank_id'];
              try {
               $params["url"]='https://api.pagar.me/1/transfers';
               $params["parameters"]= $data;        
               $params["method"]= "POST";        
               $rs= new RestClient($params);
               $result= $rs->run();
               if($result['code']=== 200){
               $pagarinfo = (array)json_decode($result['body']);
               $dat['transaction_id'] = $pagarinfo['id'];
               $updateresult = $referralPaymenttableModal->updateReferralPaymentInfo($value['ref_id'],$dat);
               if($updateresult){
              $userrprocessarray[$i]['useremail'] = 'Transaction is successful for this user of email id'.$value['email'];
               $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = "financeiro@fashionlearn.com.br";
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => 'Transaction is successful for this user of email id <strong>'.$value['email'].'</strong>'
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers,"financeiro@fashionlearn.com.br"); 
               }else{
              $userrprocessarray[$i]['useremail'] = 'Transaction is successful but the data is not updated  for this user of email id <strong>'.$value['email'].'</strong>'; 
               $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email ="financeiro@fashionlearn.com.br";
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => 'Transaction is successful but the data is not updated  for this user of email id  '.$value['email']
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers,"financeiro@fashionlearn.com.br"); 
                   
               }
               }else{
                $userrprocessarray[$i]['useremail'] = 'Error occurs while processing the transaction for the user of email id &nbsp <strong>'.$value['email'].'</strong>';
                 $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = "financeiro@fashionlearn.com.br";
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => 'Error occurs while processing the transaction for thie user of email id &nbsp <strong>'.$value['email'].'</strong>'
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers,"financeiro@fashionlearn.com.br"); 
                }
            } catch (Exception $e) {
                
            } 
         }else{
         $userrprocessarray[$i]['useremail'] = 'This user of email &nbsp <strong>'.$value['email'].'</strong> do not have neccessary info for processing the transactions';        
         $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = "financeiro@fashionlearn.com.br";
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => 'This user of email <strong>'.$value['email'].'</strong> &nbsp do not have neccessary info for processing the transactions'
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers,"financeiro@fashionlearn.com.br");   
        }
        
       }else{
         if(!isset($det['referral_current_leftover'])){
             $det['referral_current_leftover'] = 0;
         }
        $det['referral_current_leftover']+= $value['total_earned'];
        $det['pay_status']= 6;
        $updateresult = $referralPaymenttableModal->updateReferralPaymentInfo($value['ref_id'],$det);   
        $userrprocessarray[$i]['useremail'] = 'This user of email &nbsp <strong>'.$value['email'].'</strong> have salary less than 1$ and hence payment cannot be done , it will added to next month';        
        $template_name = 'Blank-transaction';
                
                        $name = "test";
                        $email = "financeiro@fashionlearn.com.br";
                        $subject = "payment";

                        $mergers = array(
                            array(
                                'name' => 'text',
                                'content' => $userrprocessarray[$i]['useremail']
                            )
                        );

                        $mresult = $mailer->sendtemplates($template_name, $email, $name, $subject, $mergers,"financeiro@fashionlearn.com.br");      
        }
        $i++;    
         }
       
        echo json_encode($userrprocessarray);
        die();  

}

/*   DEV: Priyanka varanasi
         DESC: referral payment status cron function
        DATE:TIME 15/10/2015 3:30pm
   
 */ 
public function referralstatusCronAction(){ 
    
      $this->_helper->layout()->disableLayout();
      $this->_helper->viewRenderer->setNoRender(true);    
      
       $objCore = Engine_Core_Core::getInstance();
       $realobj = $objCore->getAppSetting();
       $apikey = $realobj->pagar->ApiKey;
        
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teachingclass = Admin_Model_TeachingClasses::getInstance();
       $teachersstudents = Admin_Model_Classenroll::getInstance();
       $uservideostatus = Admin_Model_UserVideoStatus::getInstance();
       $classreviewscount = Admin_Model_ClassesReview::getInstance();
       $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
       $mailer = Engine_Mailer_Mailer::getInstance();
       $projects = Admin_Model_Projects::getInstance();  
       $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance();
       $referralPaymenttableModal  =  Admin_Model_ReferralPaymentTable::getInstance();
        
       echo '<-------------------------------------Teacher transaction status update cron ------------------------------------------->';
       echo '<html><table border="1">';
       print '<th>userid</th><th>email</th>status<th>comments</th>';
       $result   = $referralPaymenttableModal->selectAllPaidUsers();
      if($result){
          echo "<tbody>"; 
            foreach ($result as $val) {
                  echo "<tr>"; 
                 if(!empty($val["transaction_id"])){
             $data["api_key"] = $apikey;
           try {
               $params["url"]='https://api.pagar.me/1/transfers/'.$val['transaction_id'].'';
               $params["parameters"]= $data;        
               $params["method"]= "GET";        
               $rs= new RestClient($params);
               $result= $rs->run();
               if($result['code']=== 200){
               $pagarinfo = (array)json_decode($result['body']);
               if($pagarinfo['status']=='pending_transfer'){
                 $dat['pay_status'] = 2;  
                }else if($pagarinfo['status']=='Transferred'){
                  $dat['pay_status'] = 1;  
                   
               }else if($pagarinfo['status']=='failed'){
                   
                 $dat['pay_status'] = 3;   
               }else if($pagarinfo['status']=='processing '){
                 $dat['pay_status'] = 4;   
                   
               }else if($pagarinfo['status']=='canceled'){
                 $dat['pay_status'] = 5;   
               }
               $referralPaymenttableModal->updateReferralPaymentInfo($val["ref_id"],$dat);
                print "<td> " . $val["user_id"] . "</td>" . "<td> " . $val["email"] . "</td>" . "<td>" . $pagarinfo["status"] . "</td>";    
               }
              echo "</tr>";   
            } catch (Exception $e) {
                
            } 
           }else{
             print "<td> " . $val["user_id"] . "</td>" . "<td> " . $val["email"] . "</td>" . "<td>Payment is not done yet</td>";        
               
           }
            }
             echo "</tbody>";  
          }
        else{
          echo "<tbody>";
          print '<td>No Pending status user exists to check</td><td>-</td><td>-</td><td>-</td>';  
          echo "</tbody>";
        } 
     echo "</table></html>";
       echo '<-------------------------------------cron executed successfully-------------------------------------------->';
        die();   
        
}

/*   DEV: Priyanka varanasi
         DESC: get month flter action by year for admin revenue, teacher referral, and teacher payments
        DATE:TIME 15/10/2015 3:30pm
   
 */ 

public function getmonthsFilterAction(){
    
     $this->_helper->layout()->disableLayout();
     $this->_helper->viewRenderer->setNoRender(true);
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance();  
       $teacherpaymonthlystatistics = Admin_Model_TeacherpayMonthlystatistics::getInstance(); 
       $referralPaymenttableModal  =  Admin_Model_ReferralPaymentTable::getInstance();
       
        if ($this->getRequest()->isPost()) {
            $method = $this->getRequest()->getParam('method');

            switch ($method) {
                case 'getmonthsforadminrevenue':
                    $year = $this->getRequest()->getParam('year');
                 $adminyears    = $teacherpaymonthlystatistics->getListOfMonthsByYear($year);
                 if($adminyears){
                     $res = array('code'=>200,
                        'data'=>$adminyears);
                  
                  echo json_encode($res);
               die(); 
                   }else{
                       $res = array('code'=>198,
                        'data'=>'no resultfound');
                   echo json_encode($res); 
                    die();   
                   }
                    break;
                         case 'getmonthsforteacherpayements':
                    $year = $this->getRequest()->getParam('year');
                 $teacheryears    = $teacherpaymentmodal->getListOfMonthsByYearForTeachers($year);
                 if($teacheryears){
                     $res = array('code'=>200,
                        'data'=>$teacheryears);
                  
                  echo json_encode($res);
               die(); 
                   }else{
                       $res = array('code'=>198,
                        'data'=>'no resultfound');
                   echo json_encode($res); 
                    die();   
                   }
                    break;
                         case 'getmonthsforreferredteachers':
                    $year = $this->getRequest()->getParam('year');
                 $referralyears    = $referralPaymenttableModal->getListOfMonthsByYearForReferrals($year);
                 if($referralyears){
                     $res = array('code'=>200,
                        'data'=>$referralyears);
                  
                  echo json_encode($res);
               die(); 
                   }else{
                       $res = array('code'=>198,
                        'data'=>'no resultfound');
                   echo json_encode($res); 
                    die();   
                   }
                    break;
    
    
}
        }
}

}
