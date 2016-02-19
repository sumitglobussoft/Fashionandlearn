<?php

/**
 * AdminController
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class Admin_PlayerController extends Zend_Controller_Action {

    public function init() {
        
    }
    public function preDispatch(){
       $objuserperminssion = Application_Model_Sitesettings::getInstance();
        $resultperminssion = $objuserperminssion->permissionstatus();
        $this->view->classpermissions = $resultperminssion['0'];
    }

    /**
     * Developer    : Vivek Chaudhari   
     * Date         : 14/07/2014
     * Description  : show team player details and show teams according to sport then send this data to ajax caller
     */
    public function playerDetailsAction() {
        $objGamePlayersModel = Application_Model_GamePlayers::getInstance();
        $objSportModel = Application_Model_Sports::getInstance();
        
        $sports = $objSportModel->getSports();
        if(!empty($sports)){ 
            //$this->_helper->viewRenderer->setNoRender(FALSE);
            $this->view->activesport = $sports;
        } 
        if ($this->getRequest()->isPost()):
            $this->_helper->_layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(TRUE);
            $sport = $this->getRequest()->getParam('sport');
            $team = $this->getRequest()->getParam('team');
            if (isset($sport)):
                $dbteams = $objGamePlayersModel->getTeamsBySport($sport);
            
                if (isset($dbteams)):
                    $teams = array();
                    foreach ($dbteams as $key => $value): 
                        $decode = json_decode($value['plr_details'],true);
                        if(isset($decode['team_code']) && isset($decode['team_name'])){
                            $teams[$decode['team_code']] = $decode['team_name'];
                        }
                        
                    endforeach;
                   // echo "<pre>"; print_r($teams); echo "</pre>";die;
                    $teams = array_unique($teams, SORT_REGULAR);
                    if (isset($teams)):
                        echo json_encode($teams);
                    endif;
                else:
                    echo 0;
                endif;
                die;
            endif;

            if (isset($team)):
                $details = $objGamePlayersModel->getAllPlayerDetails($team);
                echo json_encode($details);
            endif;
        endif;
    }

    public function playerStatsAction() {

        $objSportsModel = Admin_Model_Sports::getInstance();
        $getSportsDetails = $objSportsModel->getSportsDetails();
        if ($getSportsDetails) {
            $this->view->sportsDetails = $getSportsDetails;
        }
        $objParser = Engine_Utilities_GameXmlParser::getInstance();
        $gametype = 'MLB';
        //$gametype = 'NBA';

        if ($gametype == 'MLB') {
            $sports_id = 2;
        } else if ($gametype == 'NFL') {
            $sports_id = 1;
        } else if ($gametype == 'NBA') {
            $sports_id = 3;
        } else if ($gametype == 'NHL') {
            $sports_id = 4;
        }

        $statsData = $objParser->getPlayerStats($gametype);

        if ($statsData) {
            $objPlayerStatModel = Application_Model_PlayerStats::getInstance();
            $objPlayerStatModel->insertPlayersStats($statsData, $sports_id);
        }

//        echo "<pre>"; print_r($statsData); echo "</pre>"; die;
    }

    /**
     * Developer : Manoj 
     * Description : get game players 
     */
    public function gamePlayerAction() {

        $objParser = Engine_Utilities_GameXmlParser::getInstance();
        $gametype = 'MLB';
//      $gametype = 'NBA';
        $playerArray = $objParser->getGamePlayers($gametype);

        if ($gametype == 'MLB') {
            $sports_id = 2;
        } else if ($gametype == 'NFL') {
            $sports_id = 1;
        } else if ($gametype == 'NBA') {
            $sports_id = 3;
        } else if ($gametype == 'NHL') {
            $sports_id = 4;
        }

        if ($playerArray) {
            $objGamePlayers = Application_Model_GamePlayers::getInstance();
            $objGamePlayers->bulkInsert($playerArray, $sports_id);
        }
    }

    /**
     * Developer    : Vivek Chaudhari   
     * Date         : 14/07/2014
     * Description  : edit disability according to player id
     */
    public function editDisabilityAction() {
        $plr_id = $this->getRequest()->getParam('plr_id');
        $objGamePlayersModel = Application_Model_GamePlayers::getInstance();
        $data = $objGamePlayersModel->getPlayerByPlayerId($plr_id);
        $playerDetails = json_decode($data['plr_details'],true);
//       echo "<pre>"; print_r($data); echo "</pre>";die;
        if (isset($playerDetails) && !empty($playerDetails)):
            
            $this->view->plrDetails = $playerDetails;
        
        endif;

        if ($this->getRequest()->isPost()):

//            $data->age = $this->getRequest()->getParam('age');
//            $data->name = $this->getRequest()->getParam('player_name');
//            $data->position_name = $this->getRequest()->getParam('age');
            $playerDetails['age'] = $this->getRequest()->getParam('age');
            $playerDetails['name'] = $this->getRequest()->getParam('player_name');
            $playerDetails['position_name'] = $this->getRequest()->getParam('disable');
//            print_r($this->getRequest()->getParam('disable'));//die;
//            print_r($playerDetails); die;
            $udata = json_encode($playerDetails);
//            print_r($udata); die;
            $check = $objGamePlayersModel->updateDisability($plr_id, $udata);
            //print_r($check);die;

            if ($check) {
                $this->_redirect('/admin/player-details');
            }
        endif;
    }

    public function playerSalaryAction() {        
        
        $objGamePlayerModel = Application_Model_GamePlayers::getInstance();
        
        
        if ($this->getRequest()->isPost()) {
            if ($_FILES) {
               //echo "<pre>"; print_r($_FILES); echo "</pre>"; die('jbj');
                $upload = new Zend_File_Transfer();
                $upload->addValidator('Extension', false, array('csv'));
                //echo "<pre>"; print_r($upload); echo "</pre>"; die('jbj');
                $files = $upload->getFileInfo();

                $errorNotify = 0;
                foreach ($files as $file => $info) {
                    if (!$upload->isUploaded($file)) {

                        $errmsg = "Please select sheet to Upload!";
                        $errorNotify = 1;
                        continue;
                    }
                    if (!$upload->isValid($file)) {

                        $errmsg = "Invalid File extension. Please upload only *.csv file";
                        //$this->view->message=$errmsg;
                        
                        $errorNotify = 1;
                        continue;
                    }
                }
                           
                 
                if ($errorNotify == 0) {
                    $destination = 'assets/csv/';
                    $destination = str_replace('/', '\\', $destination);
                    $upload->setDestination($destination);
                    $filename1 = $files['file1']['name'];
                    $filename2 = $files['file2']['name'];
                    $upload->receive();
                    $filename1 = 'csv/' . $filename1;
                    $filename2 = 'csv/' . $filename2;
                    
                    
                   // $destination1 = '/assets/'.$filename1;
                    $destination1 = getcwd() . '/assets/'.$filename1;
                    $fp1 = fopen($destination1,'r');                  
                    //echo "<pre>"; print_r($fp1); echo "</pre>"; die;
                    $row = 0;
                    while($csv_line = fgetcsv($fp1)) {                         
                       // if(is_numeric( $csv_line[0]) && is_numeric($csv_line[1])){
                        $salaryData1[$row]['Name'] = $csv_line[0];
                        $salaryData1[$row]['Team'] = $csv_line[1];
                        $salaryData1[$row]['salary'] = $csv_line[2]; 
                        $row ++;    
                      // }
                    }
                    fclose($fp1); 
                    
                    //$destination2 = '/assets/'.$filename2;
                    $destination2 = getcwd() . '/assets/'.$filename2;
                    $fp2 = fopen($destination2,'r');                  
                    $row = 0;
                    while($csv_line = fgetcsv($fp2)) { 
                        
                       // if(is_numeric( $csv_line[0]) && is_numeric($csv_line[1])){
                        $salaryData2[$row]['Name'] = $csv_line[0];
                        $salaryData2[$row]['Team'] = $csv_line[1];
                        $salaryData2[$row]['salary'] = $csv_line[2];
                        $row ++;    
                      // }
                    }
                    fclose($fp2);
                    
                    function search($array, $key, $value) {
                        $results = array();

                        if (is_array($array)) {
                            if (isset($array[$key]) && $array[$key] == $value) {
                                $results[] = $array;
                            }

                            foreach ($array as $subarray) {
                                $results = array_merge($results, search($subarray, $key, $value));
                            }
                        }

                        return $results;
                    }
                    $arrayCount1 = count($salaryData1);
                    $arrayCount2 =  count($salaryData2);
                    if($arrayCount1 >= $arrayCount2){
                        
                        
                    foreach ($salaryData1 as $data){
                        
                        $arrayData = search($salaryData2, 'Name', $data['Name']);
                        
                        if(!empty($arrayData)){
                            if($arrayData[0]['salary']=="" || $data['salary']==""){
                                
                                if($arrayData[0]['salary']==""){
                                  $arrayData[0]['salary'] = $data['salary'];
                                       $array[] = $arrayData;
                                }else{
                                  $arrayData[0]['salary'] = $arrayData[0]['salary'];  
                                       $array[] = $arrayData;
                                }
                               
                            }else{
                            $arrayData[0]['salary'] = (($arrayData[0]['salary']+$data['salary'])/2);                                                        
                            $array[] = $arrayData;
                            }
                        }else{
                            $not[0] = $data;
                             $array[] = $not;
                             
                        }                       
                    }
                    
                    }else{                       
                        foreach ($salaryData2 as $data){
                        
                        $arrayData = search($salaryData1, 'Name', $data['Name']);
                        
                        if(!empty($arrayData)){
                            if($arrayData[0]['salary']=="" || $data['salary']==""){
                                
                                if($arrayData[0]['salary']==""){
                                  $arrayData[0]['salary'] = $data['salary'];
                                       $array[] = $arrayData;
                                }else{
                                  $arrayData[0]['salary'] = $arrayData[0]['salary'];  
                                       $array[] = $arrayData;
                                }
                               
                            }else{
                            $arrayData[0]['salary'] = (($arrayData[0]['salary']+$data['salary'])/2);                                                        
                            $array[] = $arrayData;
                            }
                        }else{
                            $not[0] = $data;
                             $array[] = $not;
                             
                        }                       
                      }
                    }
                    //echo "<pre>"; print_r($array); echo "</pre>"; die;
                    foreach ($array as $salaryData){           
                        $salaryData[0]['Name'] = addslashes($salaryData[0]['Name']);
                        $salaryData[0]['salary'] = addslashes($salaryData[0]['salary']);
                       $objGamePlayerModel->updateSalaryByName($salaryData[0]['Name'],$salaryData[0]['salary']);     
                  
                    }
                    
                    
                   // echo "<pre>"; print_r($not); echo "</pre>";
                                      
                   $this->view->success = '1';
                }
            }
            
                    
        }
    }

}
