<?php
	
	
	
	class SMSEmailAutoSch
	{
		public function mailsms()
		{
			$db = new sqlDb(DSN);
			$orquery = "SELECT receiver_id,text_message,id FROM retaline_emailsms_queue where is_sent=0 and is_sms=1";
			$datas = $db->getMulipleData($orquery, true);
			foreach ($datas as $data) {
			    try {
					sms::send($data["receiver_id"],$data["text_message"],$db,"");
					$id = $data["id"];
					$phone = $data["receiver_id"];
				
					$db->query("UPDATE retaline_emailsms_queue SET is_sent='1',updated_on=now() WHERE id=$id");
					$sql1 = 'INSERT INTO sms_email_logs ' .
					'(smsemail_id,smsemail_datetime, smsemail_text, issms,sms_isaccepted) ' .
					'VALUES ( "' . $phone . '", NOW(),"", 1,1 )';
					$db->query($sql1);
					}catch (phpmailerException $e) {
					// echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
					
					$error = $e->errorMessage();
					$date = date('Y-m-d H:i:s');
					$sql1 = 'INSERT INTO sms_email_logs ' .
                    '(smsemail_id,smsemail_datetime, smsemail_text, issms,sms_isaccepted) ' .
                    'VALUES ( "' . $phone . '", NOW(),"' . $error . '", 1,0 )';
					$db->query($sql1);
					$db->query("UPDATE retaline_emailsms_queue SET is_sent='2' WHERE id=$id");
					
					
					} catch (Exception $e) {
					$error = $e->errorMessage();
					$date = date('Y-m-d H:i:s');
					$sql1 = 'INSERT INTO sms_email_logs ' .
                    '(smsemail_id,smsemail_datetime, smsemail_text, issms,sms_isaccepted) ' .
                    'VALUES ( "' . $phone . '", NOW(),"' . $error . '", 1,0 )';
                    $db->query($sql1);
                    $db->query("UPDATE retaline_emailsms_queue SET is_sent='2' WHERE id=$id");
				}
				
			}
		}
		
		public function mailsend()
		{
			// Replace smtp_username with your Amazon SES SMTP user name.
			//$usernameSmtp = 'harish.a@velosit.in';
			//$usernameSmtp = 'AKIA2OW5T4GM5PQK6MVK';
			// Replace smtp_password with your Amazon SES SMTP password.
			//$passwordSmtp = 'BHD5xwn2AyGtWjQssyYzzQx1xaf1Jh4gDRguSbpA+hdF';
			
			// Specify a configuration set. If you do not want to use a configuration
			// set, comment or remove the next line.
			$configurationSet = 'ConfigSet';
			
			// If you're using Amazon SES in a region other than US West (Oregon),
			// replace email-smtp.us-west-2.amazonaws.com with the Amazon SES SMTP
			// endpoint in the appropriate region.
			//$host = 'email-smtp.us-east-1.amazonaws.com';
			//$port = 587;
			
			
			// Replace sender@example.com with your "From" address.
			// This address must be verified with Amazon SES.
			$sender = '';
			$senderName = 'Sender Name';
			
			$db = new sqlDb(DSN);
			$orquery = "SELECT * FROM retaline_emailsms_queue where is_sent=0 and is_sms=0";
			$datas = $db->getMulipleData($orquery, true);
			// require_once(INCLUDE_PATH . "/phpMailer_v2.3/class.phpmailer.php");
			
			
			foreach ($datas as $data) {
				$id = $data["id"];
				$subject = $data["extra_info"];
				$bodyHtml = $data["text_message"];
				$email = $data["receiver_id"];
				$mail = new PHPMailer(true);
				
				try {
					// Specify the SMTP settings.
					$mail->isSMTP();
					$mail->setFrom($data["sender_id"],$data["sender_name"]);
					$mail->Username   = AWS_SES_SMTP_USER;
					$mail->Password   = AWS_SES_SMTP_PASSWORD;
					$mail->Host       = AWS_SES_SMTP_HOST;
					$mail->Port       = AWS_SES_SMTP_PORT;
					$mail->SMTPAuth   = true;
					$mail->SMTPSecure = 'tls';
					$mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);
					// Specify the message recipients.
					$mail->addAddress($data["receiver_id"]);
					// You can also add CC, BCC, and additional To recipients here.
					// Specify the content of the message.
					$mail->isHTML(true);
					$mail->Subject    = $subject;
					$mail->Body       = $bodyHtml;
					if ($mail->Send()) {
						$db->query("UPDATE retaline_emailsms_queue SET is_sent='1',updated_on=now() WHERE id=$id");
						$sql1 = 'INSERT INTO sms_email_logs ' .
						'(smsemail_id,smsemail_datetime, smsemail_text, issms,sms_isaccepted) ' .
						'VALUES ( "' . $email . '", NOW(),"", 0,1 )';
						$db->query($sql1);
					}
					
					} catch (phpmailerException $e) {
					// echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
					
					$error = $e->errorMessage();
					$date = date('Y-m-d H:i:s');
					$sql1 = 'INSERT INTO sms_email_logs ' .
                    '(smsemail_id,smsemail_datetime, smsemail_text, issms,sms_isaccepted) ' .
                    'VALUES ( "' . $email . '", NOW(),"' . $error . '", 0,0 )';
					$db->query($sql1);
					$db->query("UPDATE retaline_emailsms_queue SET is_sent='2' WHERE id=$id");
					
					
					} catch (Exception $e) {
					$error = $e->errorMessage();
					$date = date('Y-m-d H:i:s');
					$sql1 = 'INSERT INTO sms_email_logs ' .
                    '(smsemail_id,smsemail_datetime, smsemail_text, issms,sms_isaccepted) ' .
                    'VALUES ( "' . $email . '", NOW(),"' . $error . '", 0,0 )';
                    $db->query($sql1);
                    $db->query("UPDATE retaline_emailsms_queue SET is_sent='2' WHERE id=$id");
				}
			}
			return 1;
		}
	}
	
