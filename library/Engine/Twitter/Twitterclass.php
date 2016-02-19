<?php
class Engine_Twitter_Twitterclass {

    // public $fbsession;
    ///Condition 1 - Presence of a static member variable
    private static $_instance = null;

    ///Condition 2 - Locked down the constructor
    private function __construct() {


        $objCore = Engine_Core_Core::getInstance();
        $this->_appSetting = $objCore->getAppSetting();
    }

//Prevent any oustide instantiation of this class
    ///Condition 3 - Prevent any object or instance of that class to be cloned
    private function __clone() {
        
    }

//    public function twittersignup() {
//        $objCore = Engine_Core_Core::getInstance();
//        $this->_appSetting = $objCore->getAppSetting();
//
//
//        $config = array(
//            'signatureMethod' => $this->_appSetting->twitter->consumerKey,
//            'callbackUrl' => 'http://' . $_SERVER['HTTP_HOST'] . '/twittersignup',
//            'requestTokenUrl' => $this->_appSetting->twitter->requestTokenUrl,
//            'authorizeUrl' => $this->_appSetting->twitter->authorizeUrl,
//            'accessTokenUrl' => $this->_appSetting->twitter->accessTokenUrl,
//            'consumerKey' => $this->_appSetting->twitter->consumerKey,
//            'consumerSecret' => $this->_appSetting->twitter->consumerSecret
//        );
//
//
//        $consumer = new Zend_Oauth_Consumer($config);
////        if (!empty($_GET) && isset($_SESSION['storemio']['TWITTER_REQUEST_TOKEN'])) {
////            $token1 = $consumer->getAccessToken(
////                    $_GET, unserialize($_SESSION['storemio']['TWITTER_REQUEST_TOKEN'])
////            );
////
////            $_SESSION['storemio']['TWITTER_ACCESS_TOKEN'] = serialize($token1);
////            $_SESSION['storemio']['TWITTER_REQUEST_TOKEN'] = null;
////
////
////            $token = unserialize($_SESSION['storemio']['TWITTER_ACCESS_TOKEN']);
////            
////            $_SESSION['storemio']['twitter']['uname'] = $token->screen_name;
////            $_SESSION['storemio']['twitter']['id'] = $token->user_id;
////            $_SESSION['storemio']['twitter']['token'] = $token;
////
////            $uname = $token->screen_name;
////            
////            
////         
////     
////        }
//    }

    public function twittersignup() {
        $objCore = Engine_Core_Core::getInstance();
        $this->_appSetting = $objCore->getAppSetting();


        $config = array(
//            'signatureMethod' => $this->_appSetting->twitter->consumerKey,
            'callbackUrl' => 'http://' . $_SERVER['HTTP_HOST'],
            'requestTokenUrl' => $this->_appSetting->twitter->requestTokenUrl,
            'authorizeUrl' => $this->_appSetting->twitter->authorizeUrl,
            'accessTokenUrl' => $this->_appSetting->twitter->accessTokenUrl,
            'consumerKey' => $this->_appSetting->twitter->consumerKey,
            'consumerSecret' => $this->_appSetting->twitter->consumerSecret
        );


        $consumer = new Zend_Oauth_Consumer($config);
       
    }

}

?>