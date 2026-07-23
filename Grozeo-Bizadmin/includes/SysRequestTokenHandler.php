<?php

/**
 * Description of emailToken
 *
 * @author Lakshmi Jayaram
 * Created On 14 Oct 2010
 * Purpose : This class contains the methods to create unique email tokens, decode them etc
 */

define("SYS_REQUESTTOKEN_CLASS_FILE_INCLUDED",true);

class SysRequestTokenHandler
{
    private $db;
    public function  __construct(&$db)
    {
        $this->db = $db;
    }

    public function setToken($paramsArray)
    {
        $module      = $paramsArray['module'];
        $op          = $paramsArray['op'];
        $emailAction = $paramsArray['email_action'];

        $encodedEmailAction = urlencode(base64_encode(json_encode($emailAction)));
        $replaceVar = array(
            "module" => $module,
            "op" => $op,
            "email_action" => $encodedEmailAction
        );
        $replaceVar = serialize($replaceVar);
        $status = $this->db->query("INSERT INTO sys_email_token(TokenValue) VALUES ('$replaceVar ')");
        $tokenId = $this->db->insert_id();
        if ($status) {
            //$EmailLink = "http://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) . "/?ReqToken=" . urlencode(base64_encode(json_encode($tokenId)));
            $EmailLink = "http://localhost/poovarHTML/amount_payment.php/?ReqToken=" . urlencode(base64_encode(json_encode($tokenId)));
        }
        return $EmailLink;
    }

    public function getToken($encodedTokenId)
    {
        $tokenId          = json_decode(base64_decode(urldecode($encodedTokenId)));
        $tokenData        = $this->db->getItemFromDB("SELECT TokenValue FROM sys_email_token WHERE TokenId=$tokenId");
        $tokenDataDecoded = unserialize($tokenData);
        foreach ($tokenDataDecoded as $key => $val) {
            $_GET[$key] = $val;
        }
        $decodedEmailActions = json_decode(base64_decode(urldecode($_GET['email_action'])));
        return $decodedEmailActions;
    }

}
?>
