<?php
class Engine_Utilities_CurlRequestHandler{

	private static $_instance = null;
        public $_NFL = null;


        //Prevent any oustide instantiation of this class
	private function  __construct() { 
		

	} 
	
	private function  __clone() { } //Prevent any copy of this object
	
	public static function getInstance(){
		if( !is_object(self::$_instance) )  //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
		self::$_instance = new self();
		return self::$_instance;
	}
        
        public function serveRequest(){
            
            if(func_num_args() > 0){
                $url = func_get_arg(0);
                $data = func_get_arg(1);
                      
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch); 
                
                
            }else{
                $message = 'Parameter not Passed';
                $code = 500;
                throw new Exception($message,$code);
            }
            
            
        }
        
        public function curlUsingPost($url, $data)
        {
            $response = new stdClass();
            if(empty($url) OR empty($data)){
                
             
                $response->code = 198;
                $response->message = 'Parameter not Passed';
                return $response;
            }


            //url-ify the data for the POST
            $fields_string = '';
            foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
            $fields_string = rtrim($fields_string,'&');
            $url = $url. '?'.$fields_string;
           $data = http_build_query($data);

//            $ch = curl_init();
//            curl_setopt($ch,CURLOPT_URL,$url);
//            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
//            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10); 
//         
//            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);  
//            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            $result = curl_exec($ch);
//            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
//          
//            curl_close($ch);
           $context = [
                'https' => [
                  'method' => 'get',
                  'header' => "custom-header: custom-value\r\n" .
                              "custom-header-two: custome-value-2\r\n",
                  'content' => $data
                ]
              ];
              $context = stream_context_create($context);
              $result = file_get_contents($url, false, $context);
              $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
            if($result){
                $response->data = $result;
                $response->code = 200;
                $response->message = 'Request served';  
            }else{
              
                $response->code = 198;
                $response->message = 'Invalid Request';  
            }
            return $response;
        }

}

?>