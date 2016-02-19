<?php

class Engine_Utilities_Abbreviations{
    
    private static $_instance = null;
    
    private function  __clone() { } //Prevent any copy of this object
	
    public static function getInstance(){
            if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Engine_Utilities_Abbreviations();
            return self::$_instance;
    }
    
    public function getNHLAbbreviations(){
        
        $nhl = array();

        $nhl['Ana'] = 'Anaheim Ducks';
        $nhl['Win'] = 'Winnipeg Jets';
        $nhl['Bos'] = 'Boston Bruins';        
        $nhl['Buf'] = 'Buffalo Sabres';
        $nhl['Car'] = 'Carolina Hurricanes';
        $nhl['Cbs'] = 'Columbus Blue Jackets';
        $nhl['Cgy'] = 'Calgary Flames';
        $nhl['Chi'] = 'Chicago Blackhawks';
        $nhl['Col'] = 'Colorado Avalanche';
        $nhl['Dal'] = 'Dallas Stars';
        $nhl['Det'] = 'Detroit Red Wings';
        $nhl['Edm'] = 'Edmonton Oilers';
        $nhl['Fla'] = 'Florida Panthers';  
        $nhl['LA']  = 'Los Angeles Kings';
        $nhl['Min'] = 'Minnesota Wild';
        $nhl['Mtl'] = 'Montreal Canadiens';
        $nhl['NJ']  = 'New Jersey Devils';
        $nhl['Nsh'] = 'Nashville Predators'; 
        $nhl['NYI'] = 'New York Islanders';
        $nhl['NYR'] = 'New York Rangers';
        $nhl['Ott'] = 'Ottawa Senators';
        $nhl['Phi'] = 'Philadelphia Flyers';
        $nhl['Arc'] = 'Arizona Coyotes';
        $nhl['Pit'] = 'Pittsburgh Penguins';
        $nhl['SJ']  = 'San Jose Sharks';
        $nhl['StL'] = 'St. Louis Blues';
        $nhl['TB']  = 'Tampa Bay Lightning';
        $nhl['Tor'] = 'Toronto Maple Leafs';
        $nhl['Van'] = 'Vancouver Canucks';
        $nhl['Wsh'] = 'Washington Capitals';
        
        
        
        //$nhl['Clf'] = 'California Golden Seals';
        //$nhl['Cle'] = 'Cleveland Barons';
        //$nhl['CoR'] = 'Colorado Rockies';
        //$nhl['AtF'] = 'Atlanta Flames';
        //$nhl['Atl'] = 'Atlanta Trashers';
        //$nhl['Bkn'] = 'Brooklyn Americans'; 
//        $nhl['Dal'] = 'Dallas Stars';        
        //$nhl['DtC'] = 'Detroit Cougars';
        //$nhl['DtF'] = 'Detroit Falcons';
//              
        //$nhl['Ham'] = 'Hamilton Tigers';
        //$nhl['Har'] = 'Hartford Whalers';
        //$nhl['KC'] = 'Kansas City Scouts';
        //$nhl['MNS'] = 'Minnesota North Stars';
        //$nhl['MtM'] = 'Montreal Maroons';
        //$nhl['MtW'] = 'Montreal Wanderers';
        //$nhl['NYA'] = 'New York Americans';
        //$nhl['Oak'] = 'Oakland Seals';        
        //$nhl['PhQ'] = 'Philadelphia Quakers';
//        $nhl['Pho'] = 'Phoenix Coyotes';
//        $nhl['Pit'] = 'Pittsburgh Penguins';
        //$nhl['PiP'] = 'Pittsburgh Pirates';        
//        $nhl['Ott'] = 'Ottawa Senators';
        //$nhl['Que'] = 'Quebec Nordiques';
        //$nhl['QuB'] = 'Quebec Bulldogs';
        //$nhl['StE'] = 'St Louis Eagles';        
        //$nhl['TrA'] = 'Toronto Arenas';
        //$nhl['TrS'] = 'Toronto St Pats';     
//        $nhl['Wsh'] = 'Washington Capitals';
        
        
        return json_encode($nhl);
        
    }
    
    public function getNFLAbbreviations(){
        
        $nfl = array();
        
        $nfl['Ari'] = 'Arizona Cardinals';
        $nfl['Atl'] = 'Atlanta Falcons';
        $nfl['Bal'] = 'Baltimore Ravens';
        $nfl['Buf'] = 'Buffalo Bills';
        $nfl['Car'] = 'Carolina Panthers';         
        $nfl['Chi'] = 'Chicago Bears';
        $nfl['Cin'] = 'Cincinnati Bengals';
        $nfl['Cle'] = 'Cleveland Browns';
        $nfl['Dal'] = 'Dallas Cowboys';
        $nfl['Den'] = 'Denver Broncos';  
        
        $nfl['Det'] = 'Detroit Lions';
        $nfl['GB'] = 'Green Bay Packers';
        $nfl['Hou'] = 'Houston Texans';
        $nfl['Ind'] = 'Indianapolis Colts';
        $nfl['Jac'] = 'Jacksonville Jaguars';         
        $nfl['KC'] = 'Kansas City Chiefs';
        $nfl['Mia'] = 'Miami Dolphins';
        $nfl['Min'] = 'Minnesota Vikings';
        $nfl['NYG'] = 'NY Giants';
        $nfl['NYJ'] = 'NY Jets'; 
        
        $nfl['NE'] = 'New England Patriots'; //didn't found roster url for this team
        $nfl['NO'] = 'New Orleans Saints';
        $nfl['Oak'] = 'Oakland Raiders';
        $nfl['Phi'] = 'Philadelphia Eagles';
        $nfl['Pit'] = 'Pittsburgh Steelers';         
        $nfl['SD'] = 'San Diego Chargers';
        $nfl['SF'] = 'San Francisco 49ers';
        $nfl['Sea'] = 'Seattle Seahawks';
        $nfl['StL'] = 'St. Louis Rams';
        $nfl['TB'] = 'Tampa Bay Buccaneers'; 
        
        $nfl['Ten'] = 'Tennessee Titans';
        $nfl['Wsh'] = 'Washington Redskins';
        
        return json_encode($nfl);
        
    }
    
    public function getMLBAbbreviations(){
        
        $mlb = array();
        
        $mlb['Ari'] = 'Arizona Diamondbacks';
        $mlb['Atl'] = 'Atlanta Braves';
        $mlb['Bal'] = 'Baltimore Orioles';
        $mlb['Bos'] = 'Boston Red Sox';
        $mlb['ChC'] = 'Chicago Cubs';
        
        $mlb['ChW'] = 'Chicago White Sox';
        $mlb['Cin'] = 'Cincinnati Reds';
        $mlb['Cle'] = 'Cleveland Indians';
        $mlb['Col'] = 'Colorado Rockies';
        $mlb['Det'] = 'Detroit Tigers';
        
        $mlb['Hou'] = 'Houston Astros';
        $mlb['KC'] = 'Kansas City Royals';
        $mlb['LAA'] = 'Los Angeles Angels';
        $mlb['LAD'] = 'Los Angeles Dodgers';
        $mlb['Mia'] = 'Miami Marlins';
        
        $mlb['Mil'] = 'Milwaukee Brewers';
        $mlb['Min'] = 'Minnesota Twins';
        $mlb['NYM'] = 'New York Mets';
        $mlb['Oak'] = 'Oakland Athletics';
        $mlb['Phi'] = 'Philadelphia Phillies';
        
        $mlb['Pit'] = 'Pittsburgh Pirates';
        $mlb['SD'] = 'San Diego Padres';
        $mlb['SF'] = 'San Francisco Giants';
        $mlb['Sea'] = 'Seattle Mariners';
        $mlb['StL'] = 'St. Louis Cardinals';
        
        $mlb['TB'] = 'Tampa Bay Rays';
        $mlb['Tex'] = 'Texas Rangers';
        $mlb['Tor'] = 'Toronto Blue Jays';
        $mlb['Wsh'] = 'Washington Nationals';
        
        $mlb['FLA'] = 'Florida Marlins';
        $mlb['SFO'] = 'San Francisco Giants';
        $mlb['SDG'] = 'San Diego Padres';
        
        $mlb['NYY'] = 'New York Yankees';
        $mlb['KAN'] = 'Kansas City Royals';
        $mlb['TAM'] = 'Tampa Bay Devil Rays';
        
        return json_encode($mlb);
    }
    
    public function getNBAAbbreviations(){
        
        $nba = array();
        
        $nba['Atl'] = 'Atlanta Hawks';
        $nba['Bos'] = 'Boston Celtics';
        $nba['Bkn'] = 'Brooklyn Nets';
        $nba['Cha'] = 'Charlotte Bobcats';
        $nba['Chi'] = 'Chicago Bulls';
        $nba['Cle'] = 'Cleveland Cavaliers';
        $nba['Dal'] = 'Dallas Mavericks';
        $nba['Den'] = 'Denver Nuggets';
        $nba['Det'] = 'Detroit Pistons';
        $nba['GS'] = 'Golden State Warriors';
        
        $nba['Hou'] = 'Houston Rockets';
        $nba['Ind'] = 'Indiana Pacers';
        $nba['LAC'] = 'Los Angeles Clippers';
        $nba['LAL'] = 'Los Angeles Lakers';
        $nba['Mem'] = 'Memphis Grizzlies';
        $nba['Mia'] = 'Miami Heat';
        $nba['Mil'] = 'Milwaukee Bucks';
        $nba['Min'] = 'Minnesota Timberwolves';
        $nba['NO'] = 'New Orleans Pelicans';
        $nba['NY'] = 'New York Knicks';
        
        $nba['OkC'] = 'Oklahoma City Thunder';
        $nba['Orl'] = 'Orlando Magic';
        $nba['Phi'] = 'Philadelphia 76ers';
        $nba['PHX'] = 'Phoenix Suns';
        $nba['Por'] = 'Portland Trail Blazers'; $nba['Por'] = 'Portland Trailblazers';
        
        $nba['Sac'] = 'Sacramento Kings';
        $nba['SA'] = 'San Antonio Spurs';
        $nba['Tor'] = 'Toronto Raptors';
        $nba['Uta'] = 'Utah Jazz';
        $nba['Wsh'] = 'Washington Wizards';
        
        return json_encode($nba);
    }
}
?>
