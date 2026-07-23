<?php
/*
 * Created on 22-Jul-08
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * This module does the authentication, stores
 * admin data in session as a ':' separated string with
 * base64 encoding.
 */
writeLog(__FILE__);
 require(THIS_MODULE_PATH."/remember.php");

 switch($op){
     case 'authenticate':
     		require(THIS_MODULE_PATH."/authenticate.php");
     	break;
    
     case 'forgot-password':
     		require(THIS_MODULE_PATH."/forgot-password.php");
     	break;

     default:
            require(THIS_MODULE_PATH."/login.php");
        break;
 }