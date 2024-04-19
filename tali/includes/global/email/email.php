<?php
//EMail scripts from PHPMailer, see TALI_EMail for details
//This usage is adapted from the 6.4.1 example smtp.phps

//bug - 
//secure? 
//$mail->SMTPSecure = TALI_SMTP_SECURE;

//$mail->isHTML();   //is this needed anywhere if I do html? From old version. 
//

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
//date_default_timezone_set('Etc/UTC');

//require '../vendor/autoload.php';

require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

//Create a new PHPMailer instance
$mail = new PHPMailer();
//Tell PHPMailer to use SMTP
$mail->isSMTP();
//Enable SMTP debugging
//SMTP::DEBUG_OFF = off (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = SMTP::DEBUG_OFF;
//Set the hostname of the mail server
$mail->Host = TALI_SMTP_HOSTNAME;
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = TALI_SMTP_PORT;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //bug - ignores tali_init setting; //Enable SSL encryption; `PHPMailer::ENCRYPTION_STARTTLS` is for TLS
//Username to use for SMTP authentication
$mail->Username = TALI_SMTP_USERNAME;
//Password to use for SMTP authentication
$mail->Password = TALI_SMTP_PASSWORD;
//Set who the message is to be sent from
$mail->setFrom(TALI_SMTP_FROMADDRESS, TALI_SMTP_FROMNAME);
//Set an alternative reply-to address
//$mail->addReplyTo('replyto@example.com', 'First Last');
//Set who the message is to be sent to
//$mail->addAddress('whoto@example.com', 'John Doe');
if (count($toArray) == 1) {
	//Sending to one person, so make it look nice
	$mail->addAddress($toArray[0][0], $toArray[0][1]);
}
else
{
	//Sending to group, so hide info via bcc
	foreach ($toArray as $toDefine) {
		//bug - allow BCC as option someday
		//$mail->addBCC($toDefine[0], $toDefine[1]);
		$mail->addAddress($toDefine[0], $toDefine[1]);
	}
}
$mail->isHTML(true); 
//Set the subject line
$mail->Subject = $subject;
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
$mail->Body = $msgBody;
//Replace the plain text body with one created manually
//$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if (!$mail->send()) {
    //echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    //echo 'Message sent!';
}
?>