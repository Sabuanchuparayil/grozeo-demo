<?php

function allowSingleSessionOfUser($userid) {
    global $db;
    $db->query('begin');
    $session_id_to_destroy = $db->getItemFromDB('SELECT admin_user_session_id from admin_users WHERE uidnr_admin = ' . $userid);

// 1. commit session if it's started.
    if (session_id()) {
        session_commit();
    }

// 2. store current session id
    session_start();
    $current_session_id = session_id();
    @\session_write_close();
    $db->query("UPDATE admin_users SET admin_user_session_id = '{$current_session_id}' WHERE uidnr_admin =  '{$userid}'");
    $success = $db->query('commit');
	//echo "Current session id " .  $current_session_id . "\n";
    if (!$success) {
        if (session_id()) {
            session_commit();
        }
        if(!empty($session_id_to_destroy)){
        session_id($session_id_to_destroy);
        session_start();
        @\session_write_close();
        }
        return false;
    }
    session_commit();

// 3. hijack then destroy session specified.
    if(!empty($session_id_to_destroy)){
    session_id($session_id_to_destroy);
    session_start();
    session_destroy();
    session_commit();
	//echo "DESTROYED session id " .  $session_id_to_destroy . "\n";
    }
    
// 4. restore current session id. If don't restore it, your current session will refer to the session you just destroyed!
    session_id($current_session_id);
    session_start();
    @\session_write_close();
    session_commit();
	return true;
}

// TODO: Replace mf+ak machine-finger auth with a proper API key/token mechanism.
function validateMachineFinger($machinefinger) {
    $tmpdb = new sqlDb(DSN);
    $rs = $tmpdb->getItemSafe("SELECT count(*) FROM vb_branch_auth WHERE machine_id = ?", "s", [$machinefinger]);
    if ($rs == 0)
        return false;
    else
        return true;
}

function validateAuthKey($appkey) {
    $t = md5(date("dMyH", (time() - 3600)) . 'KTC');
    $t = preg_replace("[^0-9\s]", "", $t);
    $keyB = strtoupper($t);
    $t = md5(date("dMyH", (time())) . 'KTC');
    $t = preg_replace("[^0-9\s]", "", $t);
    $keyN = strtoupper($t);
    $t = md5(date("dMyH", (time() + 3600)) . 'KTC');
    $t = preg_replace("[^0-9\s]", "", $t);
    $keyA = strtoupper($t);
    if ($keyB == $appkey || $keyN == $appkey || $keyA == $appkey)
        return true;
    else
        return false;
}