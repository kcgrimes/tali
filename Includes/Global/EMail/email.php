<?php
	//EMail scripts from PHPMailer (https://github.com/PHPMailer/PHPMailer)
	//This is a very minimalist usage
	
	//require 'PHPMailerAutoload.php';
	require 'class.phpmailer.php';
	require 'class.smtp.php';
	$mail = new PHPMailer;

	$mail->isSMTP();
	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 0;
	//Ask for HTML-friendly debug output
	$mail->Debugoutput = 'html';
	//Set the hostname of the mail server
	$mail->Host = $_SESSION['TALI_SMTP_hostname'];
	//Set the SMTP port number - likely to be 25, 465 or 587
	$mail->Port = $_SESSION['TALI_SMTP_port'];
	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;
	$mail->SMTPSecure = $_SESSION['TALI_SMTP_secure'];
	//Username to use for SMTP authentication
	$mail->Username = $_SESSION['TALI_SMTP_username'];
	//Password to use for SMTP authentication
	$mail->Password = $_SESSION['TALI_SMTP_password'];
	//Set who the message is to be sent from
	$mail->setFrom($_SESSION['TALI_SMTP_fromAddress'], $_SESSION['TALI_SMTP_fromName']);
	//Set an alternative reply-to address
	//$mail->addReplyTo('replyto@example.com', 'First Last');
	//Set who the message is to be sent to
	$mail->addAddress($toEmail, $toName);
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