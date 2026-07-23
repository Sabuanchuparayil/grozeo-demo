<?php
class sendEmail
{
  public static function SupportCommonEmail($ticketId, $type)
  {
    $db = new sqlDb(DSN);

    $supportDetails = $db->getFromDB("SELECT ticketId,ticketNumber,ticketContactNo,ticketContactName,ticketContactEmail,ticketTitle,
    ticketDescription,createdOn FROM support_ticket WHERE ticketId = {$ticketId}", true);


    $ticketData['Customersname'] = $supportDetails['ticketContactName'];
    $ticketData['email'] = $supportDetails['ticketContactEmail'];
    $ticketData['TicketID'] = $supportDetails['ticketNumber'];
    $ticketData['IssueDescription'] = $supportDetails['ticketDescription'];
    $ticketData['createddate'] = $supportDetails['createdOn'];
    $ticketData['EmailType'] = $type;
    if ($type == 5) {
      $ticketRemarks = $db->getItemFromDB("SELECT ticketRemarks FROM support_ticket_log WHERE ticketId = {$ticketId} AND ticketStage = ticketStage ORDER BY id DESC");
      $ticketData['Query'] = $ticketRemarks;
    } else {
      $ticketData['Query'] = "";
    }

    $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'SUPPORTEMAIL'");
    $fields_string = json_encode($ticketData);
    //print_r($url . "/n");
    //print_r($fields_string . "/n");
    $opts = array(
      CURLOPT_URL => $url,
      CURLINFO_CONTENT_TYPE => "application/json",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POST => count($ticketData),
      CURLOPT_POSTFIELDS => $fields_string,
      CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    );

    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data = curl_exec($ch);

    $info = curl_getinfo($ch);
    curl_close($ch);
    return true;
  }
}
