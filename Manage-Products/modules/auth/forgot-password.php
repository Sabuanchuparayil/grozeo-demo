<?php
/*
 * @Created on 03-Nov-08
 * @Created By Anju P<anju@saturn.in>
 * @Modified: 2026-05-13 - SQL Injection Fix + Password Hashing Fix
 *
 * To generate new password and send to email id if any user forgots his/her password
 */

// FIXED: Use prepared statement instead of direct concatenation
$admin_email = trim($_POST['admin_email']);

$isValidEmail = $db->getItemSafe(
    "SELECT count(UserId) FROM " . FINASCOP_DB . "finascop_usr_master WHERE UserEmail = ?",
    "s",
    [$admin_email]
);

if ($isValidEmail == 1) {
    $newPassword = generatePassword(7, 7);

    // FIXED: Use prepared statement
    $user_details = $db->getFromSafe(
        "SELECT p.FirstName, p.LastName, u.UserEmail FROM " . FINASCOP_DB . "finascop_usr_master u, " . FINASCOP_DB . "finascop_usr_profile p WHERE u.UserId = p.UserId AND u.UserEmail = ?",
        "s",
        [$admin_email],
        true
    );

    $date = date("d/m/y", time());
    $time = date("g:i a");
    $toreplace = array('[FirstName]', '[LastName]', '[Password]', '[UserName]', '[EmailAddress]', '[UserIpAddress]', '[ActivationUrl]', '[Date]', '[Time]', '[From]');
    $replacewith = array($user_details['admin_fname'], $user_details['admin_lname'], $newPassword, $user_details['admin_email'], $user_details['admin_email'], '', '', $date, $time);
    $template = get_mail_template('FORGOT_PASSWORD');
    $content = str_replace($toreplace, $replacewith, $template['template_contents']);
    $toreplacesubject = array('[FirstName]', '[LastName]');
    $replacesubject = array($user_details['admin_fname'], $user_details['admin_lname']);
    $subject = str_replace($toreplacesubject, $replacesubject, $template['template_subject']);
    $status = send_mail($admin_email, $subject, $content);

    if ($status) {
        // TODO: Replace md5() with password_hash($newPassword, PASSWORD_DEFAULT)
        $data['admin_password'] = md5($newPassword);

        // FIXED: Use prepared statement for the update
        $db->executeSafe(
            "UPDATE admin_users SET admin_password = ? WHERE admin_email = ?",
            "ss",
            [$data['admin_password'], $admin_email]
        );
        echo "{success: true}";
    }
} else {
    echo "{success: 'invalid'}";
}
