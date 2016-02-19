<?php

/**
 * Facebook Operation Assistant Class
 * 
 * This file makes use of Facebook's SDK for PHP varsion 4 to create some 
 * functions which can be used for interaction with Facebook server. For using
 * this class, you need to create a session object of Facebook and pass it to
 * the FacebookHelper class constructor while creating its instance. 
 * THIS CLASS DOES NOT CREATE FACEBOOK SESSION.
 * 
 * PHP varsion 5.4
 * 
 * @author Abhinish Kumar Singh <abhinish@globussoft.com>
 * 
 * Various files included from the Facebook SDK for PHP version 4 for
 * the functionality implementation
 */
include 'Facebook/FacebookCurlHttpClient.php';

require_once( 'Facebook/FacebookRequest.php' );

require_once( 'Facebook/FacebookResponse.php' );

require_once( 'Facebook/FacebookSDKException.php' );

require_once( 'Facebook/FacebookRequestException.php' );

require_once( 'Facebook/FacebookAuthorizationException.php' );

require_once( 'Facebook/GraphObject.php' );

require_once( 'Facebook/GraphUser.php' );

use Facebook\FacebookRequest;
use Facebook\FacebookHttpable;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;

//{{{ FacebookHelper

/**
 * FacebookHelper is the class that contains various functions for Facebook 
 * interaction. It needs the user to pass a valid Facebook session object while 
 * creating instance of this class. That instance can be used for invoking 
 * various functions of this class with valid parameters. All the functions
 * of this class throw Exception if some exceptions generate while processing
 * the request and a VALID FacebookSession object is necessary for executing any
 * of the functions.
 */
class FacebookHelper {

    /**
     *
     * @var FacebookSession
     * 
     * This stores the session object passed by the user when creating the 
     * instance
     */
    private $uSession;

    /**
     *  This is a constructor which will be called when the user tries to create
     * a object of this class.
     * 
     * @param FacebookSession $sessionObject
     */
    public function __construct($sessionObject) {
        $this->uSession = $sessionObject;
    }

    /**
     * This function returns the user details in form of a Graph Object, use 
     * getProperty() function to get the various details.
     * 
     * @return null if Provided FacebookSession object is null
     * @return GraphObject if a valid FacebookSession object is provided
     */
    public function getUserDetails() {
        if ($this->uSession != NULL) {
            $user_profile = (new FacebookRequest(
                            $this->uSession, 'GET', '/me'
                    ))->execute()->getGraphObject();
            return $user_profile;
        } else {
            return null;
        }
    }

    /**
     * This function uploads an image to the Facebook account.
     * 
     * @param String $filepath Required. Path to the image to be uploaded
     * @param String $message Optional. Message to be posted with image
     * @return null if session is null
     * @return String The unique id of the uploaded image in form of string
     */
    public function photoUpload($filepath, $message = null) {
        if ($this->uSession != NULL) {

            try {
                $graphObject = (new FacebookRequest(
                                $this->uSession, 'POST', '/me/photos', array(
                            'source' => new CURLFile('' . $filepath . '', 'image/png'),
                            'message' => '' . $message . ''
                                )
                        ))->execute()->getGraphObject();
                return $graphObject->getProperty('id');
            } catch (FacebookRequestException $e) {

                echo "Exception occured, code: " . $e->getCode();
                echo " with message: " . $e->getMessage();
            }
        } else {
            return null;
        }
    }

    /**
     * This function returns the ids of all the taggable friends(Usually all the
     * friends) in form of an array.
     * 
     * @return null If the session is null
     * @return array If the session is valid
     */
    public function getTaggableFriends() {
        if ($this->uSession != NULL) {
            try {
                $graphObject = (new FacebookRequest(
                                $this->uSession,
                                'GET',
                                '/me/taggable_friends'
                        ))->execute()->getGraphObject();

                $data = $graphObject->asArray();
                foreach ($data['data'] as $val) {
                    $ids[] = $val->id;
                }
                return $ids;
            } catch (FacebookRequestException $e) {
                echo "Exception occured, code: " . $e->getCode();
                echo " with message: " . $e->getMessage();
            }
        } else {
            return null;
        }
    }

    /**
     * This function uploads a photo and tags all the taggable friends.
     * 
     * @param String $filepath Path of the file to be uploaded
     * @param String $message Message to be posted with Photo
     * @return null Nothing is returned
     */
    public function uploadNtag($filepath, $message = null) {
        if ($this->uSession != NULL) {
            try {
                $graphObjectPhoto = (new FacebookRequest(
                                $this->uSession, 'POST', '/me/photos', array(
                            'source' => new CURLFile('' . $filepath . '', 'image/png'),
                            'message' => '' . $message . ''
                                )
                        ))->execute()->getGraphObject();
                $photoId = $graphObjectPhoto->getProperty('id');

                $graphObjectFriends = (new FacebookRequest(
                                $this->uSession,
                                'GET',
                                '/me/taggable_friends'
                        ))->execute()->getGraphObject();

                $data = $graphObjectFriends->asArray();
                foreach ($data['data'] as $val) {
                    $ids[] = $val->id;
                }
                $len = count($ids);
                $code = "/{$photoId}/tags";
                $i = 0;
                while ($i < $len) {
                    $result = (new FacebookRequest(
                                    $this->uSession,
                                    'POST',
                                    $code,
                                    array(
                                        'tags' => '[{\'tag_uid\': \'' . $ids[$i] . '\'}]',
                                    )
                            ))->execute()->getGraphObject();
                    $i++;
                }
                return;
            } catch (FacebookRequestException $e) {

                echo "Exception occured, code: " . $e->getCode();
                echo " with message: " . $e->getMessage();
            }
        } else {
            return null;
        }
    }

    /**
     * This function takes photo id(required) and taglist(optional) and tags all
     * the ids provoded to the photo. If no taglist is provided it tags all the
     * taggable friends of the current user.
     * 
     * @param String $photoId Required. Id of the photograph to be tagged.
     * @param Array $tagList Optional. Array of ids to be tagged to photo.
     * @return null Returns nothing.
     */
    public function tagUploaded($photoId, $tagList = null) {
        if ($this->uSession != NULL) {
            if ($tagList == null) {
                $graphObjectFriends = (new FacebookRequest(
                                $this->uSession,
                                'GET',
                                '/me/taggable_friends'
                        ))->execute()->getGraphObject();
                $data = $graphObjectFriends->asArray();
                foreach ($data['data'] as $val) {
                    $ids[] = $val->id;
                }
                $len = count($ids);
                $code = "/{$photoId}/tags";
                $i = 0;
                while ($i < $len) {
                    $result = (new FacebookRequest(
                                    $this->uSession,
                                    'POST',
                                    $code,
                                    array(
                                        'tags' => '[{\'tag_uid\': \'' . $ids[$i] . '\'}]',
                                    )
                            ))->execute()->getGraphObject();
                    $i++;
                }
                return;
            } else {
                $len = count($tagList);
                $code = "/{$photoId}/tags";
                $i = 0;
                while ($i < $len) {
                    $result = (new FacebookRequest(
                                    $this->uSession,
                                    'POST',
                                    $code,
                                    array(
                                        'tags' => '[{\'tag_uid\': \'' . $tagList[$i] . '\'}]',
                                    )
                            ))->execute()->getGraphObject();
                    $i++;
                }
                return;
            }
        } else {
            return null;
        }
    }

    /**
     * This function returns the list of all the pages those are liked by the
     * current user.
     * 
     * @return null Returns nothing
     * @return array Various details of the pages liked in form of an array
     */
    public function getLikes() {
        if ($this->uSession != NULL) {
            $result = (new FacebookRequest(
                            $this->uSession,
                            'GET',
                            '/me/likes'
                    ))->execute()->getGraphObject();
            $data = $result->asArray();
//          Use these lines of codes to return particular details in an array
//          Just uncomment the following 4 lines and comment the 5th one.
//            foreach ($data['data'] as $val) {
//                $arr_data[] = $val->category;   /* category/name/created_time/id/category_list(if available, not present in all of the stdClass Objects) */
//            }
//            return $arr_data;
            return $data;
        } else {
            return null;
        }
    }

    /**
     * This function posts a link to the user's timeline with the provided message.
     * 
     * @param String $link_address Required. Link to be posted in form of a String.
     * @param String $message Optional. Message to be posted along with the link
     * @return null If session object is not valid
     * @return String ID of the link uploaded in form of String
     */
    public function postLinks($link_address, $message = null) {
        if ($this->uSession != NULL) {
            try {
                $response = (new FacebookRequest(
                                $this->uSession != NULL, 'POST', '/me/feed', array(
                            'link' => '' . $link_address . '',
                            'message' => '' . $message . ''
                                )
                        ))->execute()->getGraphObject();
                return $response->getProperty('id');
            } catch (FacebookRequestException $e) {
                echo "Exception occured, code: " . $e->getCode();
                echo " with message: " . $e->getMessage();
            }
        } else {
            return null;
        }
    }

}

// }}}
?>