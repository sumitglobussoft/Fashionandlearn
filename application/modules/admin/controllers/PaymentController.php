<?php

/**
 * AdminController
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class Admin_PaymentController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function preDispatch() {
        $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }

    /**
     * Developer : Bhojraj Rawte
     * Date : 19/03/2014
     * Description : Get Payment details
     */
    public function paymentDetailsAction() {
        $objWithdrawalModel = Admin_Model_WithdrawalRequest::getInstance();
        $withdrawalDetails = $objWithdrawalModel->getPaymentDeatils();
        if ($withdrawalDetails) :
            $this->view->withdrawal = $withdrawalDetails;
        endif;
    }

    /**
     * Developer : Bhojraj Rawte
     * Date : 19/03/2014
     * Description : Get Payment Approval details
     */
    public function paymentApprovalAction() {
        $objWithdrawalModel = Admin_Model_WithdrawalRequest::getInstance();
        $withdrawalDetails = $objWithdrawalModel->getPanddingPaymentDeatils();
        if ($withdrawalDetails) :
            $this->view->withdrawal = $withdrawalDetails;
        endif;
    }

    /**
     * Developer : Ramanjineyulu G
     * Date : 01/07/2014
     * Description : Get All Withdrawal details
     */
    public function withdrawalDetailsAction() {
//        $objWithdrawalDetails = Admin_Model_WithdrawalRequest::getInstance();
//        $details=$objWithdrawalDetails->getAllDeatils();
////         echo "<pre>"; print_r($details); echo "</pre>"; die;
//        
//        if ($details){
//            $this->view->allDetails = $details;
//    }
        $objWithdrawalModel = Admin_Model_WithdrawalRequest::getInstance();
        $withdrawalDetails = $objWithdrawalModel->getWithdrawalPaymentDeatils();
        if ($withdrawalDetails) :
            $this->view->withdrawal = $withdrawalDetails;
        endif;
    }

    /**
     * Developer : Bhojraj Rawte
     * Date : 06/08/2014
     * Description : Get depositor details
     */
    public function depositorDetailsAction() {
        $objUserTransactionsModel = Admin_Model_UserTransactions::getInstance();
        $transactionDetails = $objUserTransactionsModel->getdepositorDeatils();
        //echo "<pre>"; print_r($transactionDetails); echo "</pre>"; die;
        if ($transactionDetails) :
            $this->view->transaction = $transactionDetails;
        endif;
    }

    /**
     * Developer : Bhojraj Rawte
     * Date : 03/11/2014
     * Description : Get Profit details
     */
    public function profitStatsAction() {

        $objProfitModel = Admin_Model_Profit::getInstance();
        $currentProfitYear = date("Y");
        if ($this->getRequest()->getParam('profit')) {
            $currentProfitYear = $this->getRequest()->getParam('profit');
        }
        $profitStatics = $objProfitModel->adminProfitStatics($currentProfitYear);
        $month = date("m");
        if ($this->getRequest()->getParam('monthprofit')) {
            $currentProfitYear = $this->getRequest()->getParam('monthprofit');
            $month = $this->getRequest()->getParam('mo');
        }
        $profitData = $objProfitModel->getProfitStaticsByMonth($currentProfitYear, $month);
        $this->view->profityear = $currentProfitYear;
        $this->view->profitstatic = $profitStatics;
        $this->view->profitmonth = $month;
        $this->view->profitMonthstatic = $profitData;
    }

    //dev:priyanka varanasi
    //date:20/11/2014
    //desc:to get the transaction details
    public function transactionDetailsAction() {
        $objUserTransactionsModel = Admin_Model_UserTransactions::getInstance();
        $transactionDetails = $objUserTransactionsModel->gettransdetails();
        //echo "<pre>"; print_r($transactionDetails); echo "</pre>"; die;
        if ($transactionDetails) {
            $this->view->transaction = $transactionDetails;
        }
    }

 
    public function adminfinanceAction() {
        
        //$payment = Admin_Model_Payment::getInstance();
        $teacehrdetails = Admin_Model_Teacherpaymentdetails::getInstance();
        //$this->view->daysreport = $payment->getdaysstatistics();
        //$this->view->weeksreport = $payment->getweekssstatistics();
        //$this->view->monthsreport = $payment->getmonthsstatistics();

        $adminpaymentmonthly = Admin_Model_AdminPaymentMonthly::getInstance();
        //$result = $payment->getPaidStudents();
        $toal_earned = 0;
        $teacher_share = 0;
        $yearlyleftover = 0;
        $yearly_earned = 0;
        $adminmonthlydata = $adminpaymentmonthly->getAllincome();

        $best_teacher = $teacehrdetails->bestsalarymonth();
        $worst_teacher = $teacehrdetails->worstsalarymonth();

        $this->view->best_teacher = $best_teacher['maxsalary'];
        $this->view->worst_teacher = $worst_teacher['minsalary'];

        $best_teacher_month = $best_teacher['month'];
        $best_teacher_monthanme = date('F', mktime(0, 0, 0, $best_teacher_month, 10));

        $worst_teacher_month = $worst_teacher['month'];
        $worst_teacher_month = date('F', mktime(0, 0, 0, $worst_teacher_month, 10));
        $this->view->best_teacher_month = $best_teacher_monthanme;
        $this->view->worst_teacher_month = $worst_teacher_month;
        $this->view->adminmonthlydatas = $adminmonthlydata;


  if(isset($adminmonthlydata)){
        foreach ($adminmonthlydata as $value) {

            $toal_earned = $toal_earned + $value['monthly_earned'];
            $yearly_earned = $value['annual_earned'];
            $teacher_share = $teacher_share + $value['Teacher_patternship'];
        }
  }
        $this->view->toal_earned = $toal_earned + $yearly_earned;
        $this->view->teacher_share = $teacher_share;

        $date = date("Y/m/d");
        $date = explode('/', $date);
        $year = $date[0];
        if ($date[2] >= 15) {
            $month = $date[1];
        } else {
            $month = $date[1] - 1;
        }
        $monthName = $month;
        $monthName = date('F', mktime(0, 0, 0, $monthName, 10));
        $this->view->monthname = $monthName;
        $this->view->year = $year;
        $currentmonthincome = $adminpaymentmonthly->currentmonth($month, $year);

        $currentyearincome = $adminpaymentmonthly->currentyear($month, $year);
        if (isset($currentyearincome)) {
            foreach ($currentyearincome as $value) {
                $yearlyleftover = $yearlyleftover + $value['monthlyleftover'];
            }
        }
        $this->view->yearlyleftover = $yearlyleftover;
        $monthleftover = $currentmonthincome['monthlyleftover'];
        $this->view->monthleftover = $monthleftover;
        $total_this_month = $currentmonthincome['Total'];
        $teacherpartnership = $currentmonthincome['Teacher_patternship'];
        $this->view->teacherpartnership = $teacherpartnership;
        $this->view->total_this_month = $total_this_month;
        $yearlystudentcount = 0;
        $monthlystudentcount = 0;
        $yearlyamount = 0;
        $monthlyamount=0;
        if(isset($result)){
        foreach ($result as $value) {



            if ($value['subscription_id'] == 4 || $value['subscription_id'] == 5) {

                $yearlyamount = $value['payment_amount'];
                $yearlystudentcount++;
            }
            if ($value['subscription_id'] == 1 || $value['subscription_id'] == 3) {
                $monthlyamount = $value['payment_amount'];
                $monthlystudentcount++;
            }
        }
    }
        $monthlyamount = $monthlyamount * $monthlystudentcount;
        $yearlyamount = $yearlyamount * $monthlystudentcount;
        $totalamount = $monthlyamount + $yearlyamount;
        $this->view->totalamount = $totalamount;

        $totalstudents = $yearlystudentcount + $monthlystudentcount;
        $allincome = $adminpaymentmonthly->getAllincome();

        
            /// dev:priyanka varanasi
            /// desc: To get the statistics of teacher and admin statiscs
            ///date : 8/10/2015
        
         $teacherpaymonthlystatistics = Admin_Model_TeacherpayMonthlystatistics::getInstance();
         $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance();
         
         //// ******** admin earned amount in last year **********//
         
         $admin  = $teacherpaymonthlystatistics->getAdminEarnedLastYear();
         $admin['income'] = $admin['adminincome']+ $admin['admininleftover'];
         if($admin['income']){
              $this->view->adminincomelastyear = $admin['income'];
         }else{
             
             $this->view->adminincomelastyear = 0;
         }
         
         //// ******** admin divide amount to teachers in last year **********//
          
         $teacheralreadydividedlastyear  = $teacherpaymentmodal->getAdminDividedLastYear();
//         echo"<pre>";print_r($teacheralreadydivided);die;
         if($teacheralreadydividedlastyear['alreadydivided']){
           
             $this->view->teacheralreadydividedlastyear = $teacheralreadydividedlastyear['alreadydivided'];
             
         }else{
            $this->view->teacheralreadydivided =0; 
             
         }
       //// ******** total earned by admin in last month **********//   
      
         $lastmonthstats  = $teacherpaymonthlystatistics->getStatsofLastMonth();
         
         if($lastmonthstats){
             
             $this->view->lastmonthadminearn = $lastmonthstats['admin_weightage_amount'] + $lastmonthstats['current_leftover'];
         }
        
         //// ******** total divided by admin in last month **********//   
        
        $teacheralreadydividedlastmonth  = $teacherpaymentmodal->getAdminDividedLastMonth();
        if($teacheralreadydividedlastmonth) {
            $this->view->teacheralreadydividedlastmonth = $teacheralreadydividedlastmonth['alreadydivided'];
            
        }     
          //// ******** total left over last year **********//   
        
        
        $lastyearleftover  = $teacherpaymonthlystatistics->getTotalLeftoverInLastYear();
         
         if($lastyearleftover){
             
             $this->view->lastyearleftover = $lastyearleftover['lastyearleftover'];
         }
         
         
               //// ******** total left over last month **********//   
        
        
        $lastmonthleftover  = $teacherpaymonthlystatistics->getTotalLeftoverInLastMonth();
         
         if($lastmonthleftover){
             
             $this->view->lastmonthleftover = $lastmonthleftover['lastmonthleftover'];
         }
         
          //// ******** teacher more earned in last month **********// 
         
            $bestteacher  = $teacherpaymentmodal->teacherMoreEarnedLastMonth();
        if($bestteacher) {
            $this->view->bestteacher  = $bestteacher['bestearn'];
            
        } 
        
             //// ******** teacher less earned in last month **********// 
         
            $lessearnteacher  = $teacherpaymentmodal->teacherLessEarnedLastMonth();
           
        if($lessearnteacher) {
            
            $this->view->lessearnteacher  = $lessearnteacher['lessearnteacher'];
            
        }
         
        }
        
        
   
    public function paymentControlAction() {
   /////new code for teacher payment//////////////
      /**
     * Developer : priyanka varanasi
     * Date : 29/09/2015
     * Description : Payment Control new
     */
       $paymentnewtable =  Admin_Model_PaymentNew::getInstance();
       $teachingclass = Admin_Model_TeachingClasses::getInstance();
       $teachersstudents = Admin_Model_Classenroll::getInstance();
       $uservideostatus = Admin_Model_UserVideoStatus::getInstance();
       $classreviewscount = Admin_Model_ClassesReview::getInstance();
       $paymentformulamodal = Admin_Model_PaymentFormula::getInstance();
       $projects = Admin_Model_Projects::getInstance();  
       $teacherpaymentmodal = Admin_Model_TeacherPayment::getInstance();  
       $teacherpaymonthlystatistics = Admin_Model_TeacherpayMonthlystatistics::getInstance(); 
       $teacherpayinfo = $teacherpaymentmodal->selectAllTeachersToBePaid();
       
       if($teacherpayinfo){
           
           $this->view->teacherpayinfo = $teacherpayinfo;
       }
       
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
         
        $curentmonthstats = $teacherpaymonthlystatistics->getStatsoflastMonth();
    
       
       
        
       $data['cur_month_userscount'] =  $monthlypaiduserscount['monthlyusers'];
       $data['cur_year_userscount'] =  $Yearlypaiduserscount['yearlyusers'];
       $data['admin_weigtage'] = 100 - $commissiondata['percentage_divide'];
       $data['insert_date'] = date('Y-m-d');
       $data['monthly_sum'] = $getmonthlysum['monthlysum'];
       $data['yearly_sum'] = $convertyearlytomonthly;
       $data['admin_weightage_amount'] = ($total)-($actualtotal);
       $data['teacher_weightage_amount'] = $actualtotal;
       $data['current_leftover'] = $addleftover;
       
          if(empty($curentmonthstats)){
        $data['yearly_differencs']  = 0; 
        $data['month_differencs'] = 0;
        $data['admin_leftover'] = 0;
        $data['admin_revenue'] = 0;
        $data['last_month_sum'] =  0;
        $data['last_month_yearsum'] =  0;
        $data['last_month_admincomm'] = 0;
        $data['last_leftover'] =  0;
       }
       else{
        $data['last_month_sum'] =  $curentmonthstats['monthly_sum'];
        $data['last_month_yearsum'] =  $curentmonthstats['yearly_sum'];
        $data['last_month_admincomm'] =  $curentmonthstats['admin_weightage_amount'];
        $data['last_leftover'] =  $curentmonthstats['current_leftover'];
           
       $data['month_differencs'] =  round(((($data['monthly_sum']-$curentmonthstats['monthly_sum'])/$curentmonthstats['monthly_sum'])*100));
       $data['yearly_differencs'] = round(((($data['yearly_sum']-$curentmonthstats['yearly_sum'])/$curentmonthstats['yearly_sum'])*100));
       $data['admin_revenue'] =   round(((($data['admin_weightage_amount']-$curentmonthstats['admin_weightage_amount'])/$curentmonthstats['admin_weightage_amount'])*100));
       $data['admin_leftover'] = round(((($data['current_leftover']-$curentmonthstats['current_leftover'])/$curentmonthstats['current_leftover'])*100));
      
       }if($data){
        $this->view->admincurstatistcs =  $data;
       }
      $curentmonthstats = $teacherpaymonthlystatistics->getStatsoflastMonth();
      
                   //  date :09/10/2015 12:11 pm
     ///////////Anuual teacher payment over view /////////////////
      $monthlystatistics    = $teacherpaymonthlystatistics->getAdminstaticsOrderByMonthly();
      
      $paid_teachers    = $teacherpaymentmodal->getsalaryPaidTeachersMonhtly();
      
    
     
      $unpaid_teachers    = $teacherpaymentmodal->getunpaidTeacherCount();
     
      
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
            $adminmonthlystatistics[$i]['balance_to_divide'] = $adminmonthlystatistics[$i]['amounttodivide']-$adminmonthlystatistics[$i]['alreadydivided'];    
              
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
            $adminmonthlystatistics[$i]['balance_to_divide'] = $adminmonthlystatistics[$i]['amounttodivide']-$adminmonthlystatistics[$i]['alreadydivided'];    
              
          }
      $i++;
      } 
      }
    
       $j=0;
     $total =  0;
       foreach($adminmonthlystatistics as $v){
          $total+= $v['alreadydivided'] ;
       
       }
     foreach ($adminmonthlystatistics as $value) {
       if($unpaid_teachers){
         foreach($unpaid_teachers as $val){
           if(date("M",strtotime($value['date']))== date("M",strtotime($val['pay_date']))){    
             $adminmonthlystatistics[$j]['unpaidcount'] = $val['unpaidsteachers'];   
           }
         }
       }
    
      $j++;
      }
      
      
       $count =0;
      foreach ($adminmonthlystatistics as $value) {
          if(!isset($value['unpaidcount'])){
            $count++;  
           }
      }
          /////////code ends ////////
      
      
      ///dev:priyanka varanasi
      //desc: to get all referred teachers
      //date:15/10/2015
   $referralPaymenttableModal  =  Admin_Model_ReferralPaymentTable::getInstance();
   
   $resultreferrals =  $referralPaymenttableModal->getReferals();
   
  if($resultreferrals){
      
      $this->view->referredteachers  = $resultreferrals; 
  }
      
      /////////////////////////code ends ///////////////////
  
  if($adminmonthlystatistics){
      
    $this->view->adminmonthlystatistics  = $adminmonthlystatistics;
  }
   $this->view->parcelspaid  = $count;
   $this->view->totalpaid  = $total;
   
   //// To get the years and months present in db  for annual overview and admin revenue display/////////
   $years  = $teacherpaymonthlystatistics->getTheYearsFromDb();

   if($years){
       $this->view->years = $years;
   }
   //// To get the years and months present in db  for referrals filtration/////////
  $referralYears  = $referralPaymenttableModal->getReferralYearsNames();
 
  if($referralYears){
     $this->view->refyears = $referralYears; 
      
  }
    //// To get the years and months present in db  for teacher payment filtration filtration/////////
  $teachersYears  = $teacherpaymentmodal->getTeachersYearsNames();
 
  if($teachersYears){
     $this->view->teachersYears = $teachersYears; 
      
  }
  
    }

     /**
     * Developer : priyanka varanasi
     * Date : 29/09/2015
     * Description : Payment Control
     */
    
   public function getteacherPaydetailsAction() {
       
                   //new code///
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
       
      $month =   $this->getRequest()->getParam('month');
      $year =   $this->getRequest()->getParam('year');
      if($month && $year){
      $result = $teacherpaymentmodal->selectTeachersTOBePaidByDate($month,$year);
      
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
      
      
       $yr =   $this->getRequest()->getParam('yr');
       if($yr){
           //  date :09/10/2015 12:11 pm
     ///////////Anuual teacher payment over view /////////////////
      $monthlystatistics    = $teacherpaymonthlystatistics->getAdminstaticsByMonthlyByYear($yr);
      
      $paid_teachers    = $teacherpaymentmodal->getsalaryPaidTeachersMonhtlyByYear($yr);
     
      $i=0;
      $adminmonthlystatistics = array();
       if($monthlystatistics){
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
              if(!isset($adminmonthlystatistics[$i]['total'])){
              $adminmonthlystatistics[$i]['total']=0;  
            }
            $adminmonthlystatistics[$i]['total']+= $adminmonthlystatistics[$i]['alreadydivided'] ;
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
          }else{
           $adminmonthlystatistics[$i]['totalannualsubscriptions'] = $value['cur_year_userscount'];  
            $adminmonthlystatistics[$i]['totalmonthlysubscriptions'] = $value['cur_month_userscount'];  
            $adminmonthlystatistics[$i]['totalthismonthyearlysum'] = $value['yearly_sum'];  
            $adminmonthlystatistics[$i]['totalthismonthmonthlysum'] = $value['monthly_sum'];  
            $adminmonthlystatistics[$i]['monthlyplusyearly'] = $value['yearly_sum']+$value['monthly_sum']; 
            $adminmonthlystatistics[$i]['teacherpartnershippercent'] = $value['divide_percentage']; 
            $adminmonthlystatistics[$i]['amounttodivide'] = $value['totalwithsatisfaction']; 
            $adminmonthlystatistics[$i]['alreadydivided'] = 0;
            $adminmonthlystatistics[$i]['pay_date'] = date("F,", strtotime($value['insert_date'])) ;
            $adminmonthlystatistics[$i]['balance_to_divide'] = 0;    
              
          }
      $i++;
      }
        $res = array('code'=>200,
                        'data'=>$adminmonthlystatistics);
                  
                  echo json_encode($res); 
                  die();
      }else{
         
        $res = array('code'=>198,
                        'data'=>'No result found ');
                  
                  echo json_encode($res); 
                  die();   
          
      }
       }
        
                  /////////code ends ////////
 
    }

}
