<?php
//EMail scripts from PHPMailer (https://github.com/PHPMailer/PHPMailer)
//This is a very minimalist usage

//require 'PHPMailerAutoload.php';
require 'class.phpmailer.php';
require 'class.smtp.php';
$mail = new PHPMailer;

$mail->isSMTP();
$mail->isHTML();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 0;
//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';
//Set the hostname of the mail server
$mail->Host = TALI_SMTP_HOSTNAME;
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = TALI_SMTP_PORT;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
$mail->SMTPSecure = TALI_SMTP_SECURE;
//Username to use for SMTP authentication
$mail->Username = TALI_SMTP_USERNAME;
//Password to use for SMTP authentication
$mail->Password = TALI_SMTP_PASSWORD;
//Set who the message is to be sent from
$mail->setFrom(TALI_SMTP_FROMADDRESS, TALI_SMTP_FROMNAME);
//Set an alternative reply-to address
//$mail->addReplyTo('replyto@example.com', 'First Last');
//Set who the message is to be sent to
if (count($toArray == 1)) {
	//Sending to one person, so make it look nice
	$mail->addAddress($toArray[0][0], $toArray[0][1]);
}
else
{
	//Sending to group, so hide info via bcc
	foreach ($toArray as $toDefine) {
		$mail->addBCC($toDefine[0], $toDefine[1]);
	}
}
//Set the subject line
$mail->Subject = $subject;
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
$mail->Body = $msgBody;
//Replace the plain text body with one created manually
//$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');

//Debug
if (!$mail->send()) {
	//echo 'Message was not sent.<br/>';
	//echo 'Mailer error: ' . $mail->ErrorInfo;
} else {
	//echo 'Message has been sent.';
}
?>