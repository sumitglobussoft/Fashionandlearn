<?php
/*
 * Copyright 2010 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

global $apiConfig;
//$clientid = '608219738865-plq4idfkgps1i41g0qk5ffd46kq59mp2.apps.googleusercontent.com';
//$clientsecret = 'sgtHejiF_MQuGr9-0q-AFmpk';
//$redirecturi = 'http://localhost.fashiontuts.com/oauth2callback'; 
//$maxresults = 50; // Number of mailid you want to display.

$apiConfig = array(
    // True if objects should be returned by the service classes.
    // False if associative arrays should be returned (default behavior).
    'use_objects' => false,
  
    // The application_name is included in the User-Agent HTTP header.
    'application_name' => 'fashiontuts',

    // OAuth2 Settings, you can get these keys at https://code.google.com/apis/console
    'oauth2_client_id' => '348350668424-5427u9f59dq551e5p0q2veu2ked2b7e4.apps.googleusercontent.com',
    'oauth2_client_secret' => '3LgeLuSuVzFWvsz5iKrsbbm6',
   'oauth2_redirect_uri' => 'http://localhost.fashiontuts.com/referrals',

    // The developer key, you get this at https://code.google.com/apis/console
   // 'developer_key' => 'AIzaSyAbq5FvlTFVWQy2RduSDNbxL-fCMvL-UIQ',
  
    // Site name to show in the Google's OAuth 1 authentication screen.
    'site_name' => 'http://localhost.fashiontuts.com/',

    // Which Authentication, Storage and HTTP IO classes to use.
    'authClass'    => 'Google_OAuth2',
    'ioClass'      => 'Google_CurlIO',
    'cacheClass'   => 'Google_FileCache',

    // Don't change these unless you're working against a special development or testing environment.
    'basePath' => 'https://www.googleapis.com',

    // IO Class dependent configuration, you only have to configure the values
    // for the class that was configured as the ioClass above
    'ioFileCache_directory'  =>
        (function_exists('sys_get_temp_dir') ?
            sys_get_temp_dir() . '/Google_Client' :
        '/tmp/Google_Client'),

    // Definition of service specific values like scopes, oauth token URLs, etc
    'services' => array(
      'analytics' => array('scope' => 'https://www.googleapis.com/auth/analytics.readonly'),
      'calendar' => array(
          'scope' => array(
              "https://www.googleapis.com/auth/calendar",
              "https://www.googleapis.com/auth/calendar.readonly",
          )
      ),
      'books' => array('scope' => 'https://www.googleapis.com/auth/books'),
      'latitude' => array(
          'scope' => array(
              'https://www.googleapis.com/auth/latitude.all.best',
              'https://www.googleapis.com/auth/latitude.all.city',
          )
      ),
      'moderator' => array('scope' => 'https://www.googleapis.com/auth/moderator'),
      'oauth2' => array(
          'scope' => array(
              'https://www.googleapis.com/auth/userinfo.profile',
              'https://www.googleapis.com/auth/userinfo.email',
          )
      ),
      'plus' => array('scope' => 'https://www.googleapis.com/auth/plus.login'),
      'siteVerification' => array('scope' => 'https://www.googleapis.com/auth/siteverification'),
      'tasks' => array('scope' => 'https://www.googleapis.com/auth/tasks'),
      'urlshortener' => array('scope' => 'https://www.googleapis.com/auth/urlshortener')
    )
);
