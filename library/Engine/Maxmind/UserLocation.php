<?php
require 'vendor/autoload.php';    
class Engine_Maxmind_UserLocation{
    
    protected $maxmindcityLocation;    
    protected $record;
    private static $_instance = null;

    ///Condition 2 - Locked down the constructor
    private function  __construct() { 
        $this->maxmindcityLocation = __DIR__.'/database/GeoIPCity.dat';       
                
        } //Prevent any oustide instantiation of this class

    private function  __clone() { } //Prevent any copy of this object
	
    public static function getInstance(){
            if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
            self::$_instance = new Engine_Maxmind_UserLocation();
            return self::$_instance;
    }
   
    public function getHostDetails($clientIpAddresss) {          
        $gi = geoip_open($this->maxmindcityLocation,GEOIP_STANDARD);               
        $record = GeoIP_record_by_addr($gi, $clientIpAddresss);           
        geoip_close($gi);
        return $record;
    }
    

   
}
?>