<?php

if (!function_exists('curl_init')) {
	throw new Exception('PagarMe needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('PagarMe needs the JSON PHP extension.');
}


// function __autoload($class){
// 
// 	$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "Pagarme" . DIRECTORY_SEPARATOR;
// 	
// 	$file = $dir . ((strstr($class, "PagarMe_")) ? str_replace("PagarMe_", "", $class) : $class) . ".php";
// 
// 	if (file_exists($file)){
// 		require_once($file);
// 		return;
// 	}else{
// 		throw new Exception("Unable to load" .$class);
// 	}
// }


require('Pagarme/PagarMe.php');
require('Pagarme/Set.php');
require('Pagarme/Object.php');
require('Pagarme/Util.php');
require('Pagarme/Error.php');
require('Pagarme/Exception.php');
require('Pagarme/RestClient.php');
require('Pagarme/Request.php');
require('Pagarme/Model.php');
require('Pagarme/CardHashCommon.php');
require('Pagarme/TransactionCommon.php');
require('Pagarme/Transaction.php');
require('Pagarme/Plan.php');
require('Pagarme/Subscription.php');
require('Pagarme/Customer.php');
require('Pagarme/Address.php');
require('Pagarme/Phone.php');
require('Pagarme/Card.php');

class Engine_Pagarme_PagarmeClass {
    
}

?>
