<?php
class Engine_Utilities_GameXmlParser{

	private static $_instance = null;
        public $_sig = null;

        //Prevent any oustide instantiation of this class
	private function  __construct() { 
            $objCore = Engine_Core_Core::getInstance();
            $this->_appsetting = $objCore->getAppSetting();
            
            $timestamp = time(); // current time stamp for generating signature 

            $this->_sig = hash("sha256",$this->_appsetting->apiKey . $this->_appsetting->secret . $timestamp); //generate signature with SHA256 for stats api
        } 
	
	private function  __clone() { } //Prevent any copy of this object
	
	public static function getInstance(){
		if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
		self::$_instance = new Engine_Utilities_GameXmlParser();
		return self::$_instance;
	}
        
         public function xmlLoad($url) {
            try {
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL,$url);
                $content = curl_exec($ch); 
                
                return json_decode($content,true);
            } catch (Exception $e) {
                throw new Exception;die;
            }
        }
        /**
        * Developer    : Vivek Chaudhari
        * Description  : get NFL game stats details
        * Date         : 19/11/2014
        * @return      : <array> game stats details
        */
        public function getGameLists(){
           
            $url = 'http://api.stats.com/v1/stats/football/nfl/events/?season='.date('Y').'&api_key='.$this->_appsetting->apiKey.'&sig='.$this->_sig;
            $result = $this->xmlLoad($url);
            $matchesArray = array();
            if($result){
                if(isset($result['apiResults'][0]['league']['season']['eventType'])){
                    foreach($result['apiResults'][0]['league']['season']['eventType'] as $event){
                        foreach($event['events'] as $eVal){
//                            echo "<pre>"; print_r($event); echo "</pre>"; //die;
                            if($eVal['startDate'][1]['month'] >= date('n')){
                                $matchFormatDate = strtotime(date('Y-m-d',strtotime($eVal['startDate'][1]['full'])));
                                
                                if(isset($matchesArray[$matchFormatDate])){
                                    $lastIndex = array_pop(array_keys($matchesArray[$matchFormatDate]['match']));
                                    $index = $lastIndex+1;
                                    $matchesArray[$matchFormatDate]['match_count'] = $matchesArray[$matchFormatDate]['match_count'] + 1;
                                    $matchesArray[$matchFormatDate]['match'][$index]['id'] = (string)$eVal['eventId'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['time'] = (string)date('H:i:s',strtotime($eVal['startDate'][1]['full']));
                                    $matchesArray[$matchFormatDate]['match'][$index]['formatted_date'] = (string)date('Y-m-d',strtotime($eVal['startDate'][1]['full']));
                                    $matchesArray[$matchFormatDate]['match'][$index]['status'] = (string)$eVal['eventStatus']['name'];

                                    $matchesArray[$matchFormatDate]['match'][$index]['hometeam']['name'] = (string)$eVal['teams'][0]['nickname'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['hometeam']['abbreviation'] = (string)$eVal['teams'][0]['abbreviation'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['hometeam']['record']['wins'] = (string)$eVal['teams'][0]['record']['wins'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['hometeam']['record']['losses'] = (string)$eVal['teams'][0]['record']['losses'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['hometeam']['record']['ties'] = (string)$eVal['teams'][0]['record']['ties'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['hometeam']['record']['percentage'] = (string)$eVal['teams'][0]['record']['percentage'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['hometeam']['score'] = (string)$eVal['teams'][0]['score'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['hometeam']['id'] = (string)$eVal['teams'][0]['teamId'];

                                    $matchesArray[$matchFormatDate]['match'][$index]['awayteam']['name'] = (string)$eVal['teams'][1]['nickname'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['awayteam']['abbreviation'] = (string)$eVal['teams'][1]['abbreviation'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['awayteam']['record']['wins'] = (string)$eVal['teams'][1]['record']['wins'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['awayteam']['record']['losses'] = (string)$eVal['teams'][1]['record']['losses'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['awayteam']['record']['ties'] = (string)$eVal['teams'][1]['record']['ties'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['awayteam']['record']['percentage'] = (string)$eVal['teams'][1]['record']['percentage'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['awayteam']['score'] = (string)$eVal['teams'][1]['score'];
                                    $matchesArray[$matchFormatDate]['match'][$index]['awayteam']['id'] = (string)$eVal['teams'][1]['teamId'];
                                    
                                }else{
                                    
                                    $matchesArray[$matchFormatDate]['match_on'] = (string)$eVal['startDate'][1]['full'];
                                    $matchesArray[$matchFormatDate]['timezone'] = (string)$eVal['startDate'][1]['dateType'];
                                    $matchesArray[$matchFormatDate]['week'] = (string)$eVal['week'];
                                    $matchesArray[$matchFormatDate]['match_count'] = 1;
                                    
                                    $matchesArray[$matchFormatDate]['match'][0]['id'] = (string)$eVal['eventId'];
                                    $matchesArray[$matchFormatDate]['match'][0]['time'] = (string)date('H:i:s',strtotime($eVal['startDate'][1]['full']));
                                    $matchesArray[$matchFormatDate]['match'][0]['formatted_date'] = (string)date('Y-m-d',strtotime($eVal['startDate'][1]['full']));
                                    $matchesArray[$matchFormatDate]['match'][0]['status'] = (string)$eVal['eventStatus']['name'];

                                    $matchesArray[$matchFormatDate]['match'][0]['hometeam']['name'] = (string)$eVal['teams'][0]['nickname'];
                                    $matchesArray[$matchFormatDate]['match'][0]['hometeam']['abbreviation'] = (string)$eVal['teams'][0]['abbreviation'];
                                    $matchesArray[$matchFormatDate]['match'][0]['hometeam']['record']['wins'] = (string)$eVal['teams'][0]['record']['wins'];
                                    $matchesArray[$matchFormatDate]['match'][0]['hometeam']['record']['losses'] = (string)$eVal['teams'][0]['record']['losses'];
                                    $matchesArray[$matchFormatDate]['match'][0]['hometeam']['record']['ties'] = (string)$eVal['teams'][0]['record']['ties'];
                                    $matchesArray[$matchFormatDate]['match'][0]['hometeam']['record']['percentage'] = (string)$eVal['teams'][0]['record']['percentage'];
                                    $matchesArray[$matchFormatDate]['match'][0]['hometeam']['score'] = (string)$eVal['teams'][0]['score'];
                                    $matchesArray[$matchFormatDate]['match'][0]['hometeam']['id'] = (string)$eVal['teams'][0]['teamId'];

                                    $matchesArray[$matchFormatDate]['match'][0]['awayteam']['name'] = (string)$eVal['teams'][1]['nickname'];
                                    $matchesArray[$matchFormatDate]['match'][0]['awayteam']['abbreviation'] = (string)$eVal['teams'][1]['abbreviation'];
                                    $matchesArray[$matchFormatDate]['match'][0]['awayteam']['record']['wins'] = (string)$eVal['teams'][1]['record']['wins'];
                                    $matchesArray[$matchFormatDate]['match'][0]['awayteam']['record']['losses'] = (string)$eVal['teams'][1]['record']['losses'];
                                    $matchesArray[$matchFormatDate]['match'][0]['awayteam']['record']['ties'] = (string)$eVal['teams'][1]['record']['ties'];
                                    $matchesArray[$matchFormatDate]['match'][0]['awayteam']['record']['percentage'] = (string)$eVal['teams'][1]['record']['percentage'];
                                    $matchesArray[$matchFormatDate]['match'][0]['awayteam']['score'] = (string)$eVal['teams'][1]['score'];
                                    $matchesArray[$matchFormatDate]['match'][0]['awayteam']['id'] = (string)$eVal['teams'][1]['teamId'];
       
                                }
                                 
                            }
                            
                        }
                    }
                }
            }
            if($matchesArray){
                return $matchesArray;
            }
        }
        /**
        * Developer    : Vivek Chaudhari
        * Description  : get NFL game Players details
        * Date         : 18/11/2014
        * @return      : <array> player details
        */
        public function getPlayerLists(){
            $url = 'http://api.stats.com/v1/stats/football/nfl/participants/?api_key='.$this->_appsetting->apiKey.'&sig='.$this->_sig;
            
            $result = $this->xmlLoad($url);
            $players = array(); $index = 0;
            if($result['status'] == "OK"){
                if(isset($result['apiResults'][0]['league']['players'])){
                    foreach($result['apiResults'][0]['league']['players'] as $pval){ 
                        $players[$index]['playerId'] = $pval['playerId'];
                        $players[$index]['name'] = $pval['firstName']." ".$pval['lastName'];
                        $players[$index]['status'] = $pval['isActive'];
                        if(isset($pval['team'])){
                            $players[$index]['team_code'] = $pval['team']['abbreviation'];
                            $players[$index]['team_id'] = $pval['team']['teamId'];
                        }else if(isset($pval['draft'])){
                            $players[$index]['team_code'] = $pval['draft']['team']['abbreviation'];
                            $players[$index]['team_id'] = $pval['draft']['team']['teamId'];
                        }
                        $players[$index]['pos_code'] = $pval['positions'][0]['abbreviation'];
                        $players[$index]['injury'] = $pval['isInjured'];
                        $players[$index]['plr_details'] = json_encode($pval,true);
                        
                        $index++;
                    }
                }
            }
            if($players){
                return $players;
            }
            
        }

        /**
         * Desc : Filter Array by searchkey and searchvalue
         * @param <String> $searchValue
         * @param <Array> $array
         * @param <String> $searchKey
         * @return <Array> $filtered
         */
        public function filterArray($searchValue,$array,$searchKey){
            if($searchValue != "" && $searchKey != ""){
                $filter = function($array) use($searchValue,$searchKey) { if($array[$searchKey]){return $array[$searchKey] == $searchValue;} };       
                $filtered = array_filter($array, $filter);     
                return $filtered;
            }
        }
        
    function calculateFppgNFL($playerStat){ //echo "<pre>"; print_r($playerStat); echo "</pre>"; die;
        $points = 0;  $fumble = 0; $loss = 0; $gamesPlay = 1;
//        if($gamesPlay)
        if(isset($playerStat['Passing'])){
            $passBonus = 0;
            if($playerStat['Passing']['yards'] >0){$gamesPlay =  round($playerStat['Passing']['yards'] / $playerStat['Passing']['yards_per_game']); }
            $passYds = $playerStat['Passing']['yards'] * 0.04;
            $passInt = $playerStat['Passing']['interceptions'] * (-1);
            $passTd = $playerStat['Passing']['passing_touchdowns'] * 4;
            if($playerStat['Passing']['yards'] > 300){
                $passBonus = 3;
            }
            $points = $points + $passYds + $passInt + $passTd +$passBonus;
           
        }
        if(isset($playerStat['Rushing'])){
            $rushBonus = 0;
           if($playerStat['Rushing']['yards_per_game'] > 0){ $gamesPlay = round($playerStat['Rushing']['yards'] / $playerStat['Rushing']['yards_per_game']); }
            $rushYds = $playerStat['Rushing']['yards'] * 0.1;
            if($playerStat['Rushing']['yards'] > 100){
                $rushBonus = 3;
            }
            $rushTd = $playerStat['Rushing']['rushing_touchdowns'] * 6;
            $fumbLoss = $playerStat['Rushing']['fumbles_lost'] * (-1);
            $fumble = $fumble + $playerStat['Rushing']['fumbles'];
            $loss = $loss + $playerStat['Rushing']['fumbles_lost'];
            $points = $points + $rushTd + $rushYds + $fumbLoss + $rushBonus;
        }
          if(isset($playerStat['Receiving'])){
            $recBonus = 0;
            if($playerStat['Receiving']['yards_per_game'] > 0){$gamesPlay =   round($playerStat['Receiving']['receiving_yards'] / $playerStat['Receiving']['yards_per_game']);}
            $rec = $playerStat['Receiving']['receptions'] * 1;
            $recYds = $playerStat['Receiving']['receiving_yards'] *0.1;
            $recTd = $playerStat['Receiving']['receiving_touchdowns'] * 6;
            if($playerStat['Receiving']['receiving_yards'] > 100){
                $recBonus = 3;
            }
            $fumbLoss = $playerStat['Receiving']['fumbles_lost'] * (-1);
            $fumble = $fumble + $playerStat['Receiving']['fumbles'];
            $loss = $loss + $playerStat['Receiving']['fumbles_lost'];
            $points = $points + $rec + $recYds + $recTd + $fumbLoss +$recBonus;
        }
        if(isset($playerStat['Kicking'])){
            $extPoints = 0; $yrds39Points = 0; $yrd49Points = 0; $yrd50Points = 0;
            $extPoints = $playerStat['Kicking']['extra_points_made'] * 1;
            if(isset($playerStat['Kicking']['field_goals_from_1_19_yards'])){$yrd1decode = explode("-", $playerStat['Kicking']['field_goals_from_1_19_yards']);}
            if(isset($playerStat['Kicking']['field_goals_from_20_29_yards'])){ $yrd2decode = explode("-", $playerStat['Kicking']['field_goals_from_20_29_yards']);}
            if(isset($playerStat['Kicking']['field_goals_from_30_39_yards'])){$yrd3decode = explode("-", $playerStat['Kicking']['field_goals_from_30_39_yards']);}
            
            if(!empty($yrd1decode) && !empty($yrd2decode) && !empty($yrd3decode)){
                $yrds39Points = ($yrd1decode[0]+$yrd2decode[0]+$yrd3decode[0])*3;
            }
            
            
            if(isset($playerStat['Kicking']['field_goals_from_40_49_yards'])){$yrds49decode = explode("-",$playerStat['Kicking']['field_goals_from_40_49_yards']);
                $yrd49Points = $yrds49decode[0]*4;
            }
            
            if(isset($playerStat['Kicking']['field_goals_from_50_yards'])){$yrd50decode =  explode("-",$playerStat['Kicking']['field_goals_from_50_yards']);
                $yrd50Points = $yrd50decode[0]*5;
            }
            
            $points = $points + $extPoints + $yrds39Points + $yrd49Points + $yrd50Points;
        }
        if(isset($playerStat['Defense'])){// echo "<pre>";print_r($playerStat);echo "</pre>";die;
            $sack = 0; $interceptions=0; $fumRecv = 0; $fumbRetTD=0; $intRetTD=0; $blockRet= 0;
            foreach($playerStat['Defense'] as $dkey=>$dvalue){
                if(isset($dvalue['sacks']))                                 {$sack          = $sack + $dvalue['sacks'];} 
                if(isset($dvalue['interceptions']))                         {$interceptions = $interceptions + $dvalue['interceptions'];}
                if(isset($dvalue['fumbles_recovered']))                     {$fumRecv       = $fumRecv + $dvalue['fumbles_recovered'];}
                if(isset($dvalue['fumbles_returned_for_touchdowns']))       {$fumbRetTD     = $fumbRetTD + $dvalue['fumbles_returned_for_touchdowns'];}
                if(isset($dvalue['interceptions_returned_for_touchdowns'])) {$intRetTD      = $intRetTD + $dvalue['interceptions_returned_for_touchdowns'];}
                if(isset($dvalue['blocked_kicks']))                         {$blockRet      = $blockRet+ $dvalue['blocked_kicks'];}
               
            }
            
            $points = $points + ($sack*1) + ($interceptions * 2) + ($fumRecv * 2) + ($fumbRetTD * 6) + ($intRetTD * 6) + ($blockRet * 2);
        }
        if(isset($playerStat['Returning'])){
            $kkfRetTD=0; $puntRetTD = 0;
            foreach($playerStat['Returning'] as $rkey=>$rvalue){
                if(isset($rvalue['kickoff_return_touchdows']))      {$kkfRetTD  = $kkfRetTD + $rvalue['kickoff_return_touchdows'];}
                if(isset($rvalue['punt_return_touchdowns']))        {$puntRetTD = $puntRetTD + $rvalue['punt_return_touchdowns'];}
              }
            $points = $points + ($kkfRetTD* 6) + ($puntRetTD * 6);
           //echo $gamesPlay; echo "POINTS".$points; die;
        }
        if(isset($gamesPlay) && isset($points)){ 
         if($points >0 && isset($gamesPlay)){$fppg = round($points/$gamesPlay,2);}else{$fppg = 0.0;  }   
         $playerStat['fumble'] = $fumble;
         $playerStat['loss'] = $loss;
         $playerStat['game'] = $gamesPlay;
         $playerStat['fppg'] = $fppg;
        }
       // echo "<pre>"; print_r($playerStat); echo "</pre>";die;
        return $playerStat;
    }
    
}
?>