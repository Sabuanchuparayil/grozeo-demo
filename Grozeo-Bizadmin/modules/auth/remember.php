<?php

/* Used for the remember me settings
 * Created on 13-Aug-08
 * app.rgcb
 * Created by niju
 *
 */
/* Modified by sreeram on 5/4/2010
 * reason - Introduced encryption for cookie value
 */
	$remember	= 0;
	$rememberLogin = false;
	 if(isset($_COOKIE['remember_uidnr_admin'])){
	   
                  $cookie_id_time=array();
                  // Cookie contains user id and token in encrypted format
                  $cookie_val = $_COOKIE['remember_uidnr_admin'];
                  $cookie_val = decrypt($cookie_val);
                   // split it
                  $cookie_id_time=explode(':',$cookie_val);
               if(is_numeric($cookie_id_time['0']) && count($cookie_id_time) >= 3){

                   $admin_id = $cookie_id_time['0'];
                   $rememberToken = $cookie_id_time['1'];
                   $rememberMicrotime = $cookie_id_time['2'];
                   $expectedToken = hash_hmac('sha256', $admin_id . ':' . $rememberMicrotime, ENCRASS_KEY);

                    if (hash_equals($expectedToken, $rememberToken)) {
                        $query = sprintf('select UserName, Passwd, IsActive from ' . FINASCOP_DB . 'finascop_usr_master where IsActive=%d and UserId =%d', 1, $admin_id);

                        $login = $db->getFromDB($query, true);
                        if (!empty($login)) {
                                $remember	= 1;
                                $rememberMe = 1;
                                $rememberLogin = true;
                                $loginUsername = $login['UserName'];
                                $loginPassword = '';
                                $op = 'authenticate';
                        }
                    }
               }
	 }


