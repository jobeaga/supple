<?php

require_once('include/SuppleApplication.php');
require_once('include/phpmailer/class.phpmailer.php');

class SuppleMail {

	public $smtp_host; // smtp.gmail.com
	public $smtp_secure; // ssl
	public $smtp_auth; // 1
	public $smtp_port; // 465
	public $smtp_username; 
	public $smtp_password; 
	public $smtp_from;
	public $smtp_from_name;

	function __construct(){
	
		// Get config
		$params = array('smtp_host', 'smtp_secure', 'smtp_auth', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_from', 'smtp_from_name');
		foreach ($params as $param){
			$this->$param = SuppleApplication::getconfig()->getValue($param);
		}
	
	}
	
	function simpleSendWith($destination, $destination_name, $subject, $body, $attachments = array(), $smtp_auth, $smtp_secure, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from, $smtp_from_name, $bcc = array()){
		// Create the object?!
		$mail             = new PHPMailer();
		
		$mail->IsSMTP(); // telling the class to use SMTP
		// $mail->Host       = "mail.yourdomain.com"; // SMTP server ???????
		$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
												   // 1 = errors and messages
												   // 2 = messages only
		$mail->SMTPAuth   = ($smtp_auth == 1);                  // enable SMTP authentication
		$mail->SMTPSecure = $smtp_secure;                 // sets the prefix to the servier
		$mail->Host       = $smtp_host;      // sets GMAIL as the SMTP server
		$mail->Port       = (int) $smtp_port;                   // set the SMTP port for the GMAIL server
		$mail->Username   = $smtp_username;  // GMAIL username
		$mail->Password   = $smtp_password;            // GMAIL password

		$mail->SetFrom($smtp_from, $smtp_from_name);

		$mail->AddReplyTo($smtp_from, $smtp_from_name);

		$mail->Subject    = $this->sanitizeSubject($subject); // ;

		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test // TODO: Convert to ALT BODY

		$mail->MsgHTML(utf8_decode($body));
		
		if (is_array($destination)){
			foreach ($destination as $d => $n){
				$mail->AddAddress($d, $n);	
			}
		} else {
			$mail->AddAddress($destination, $destination_name);
		}

		foreach ($attachments as $a){
			$mail->AddAttachment($a);      // attachment
		}

		foreach ($bcc as $bc){
			if (!empty($bc)) { $mail->AddBCC($bc, $bc); }	
		}

		if(!$mail->Send()) {
		  return "Mailer Error: " . $mail->ErrorInfo;
		} else {
		  return true;
		}
	}

	function simpleSend($destination, $destination_name, $subject, $body, $attachments = array(), $smtp_data = array()){

		$cco = array();
		$cco[] = SuppleApplication::getconfig()->getValue('cco1');
		$cco[] = SuppleApplication::getconfig()->getValue('cco2');

		$smpt_props = array('smtp_auth', 'smtp_secure', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_from', 'smtp_from_name');
		foreach ($smpt_props as $sp){
			$$sp = (isset($smtp_data[$sp]))?$smtp_data[$sp]:$this->$sp;
		}

		return $this->simpleSendWith($destination, $destination_name, $subject, $body, $attachments, $smtp_auth, $smtp_secure, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from, $smtp_from_name, $cco);
	
	}

	function sanitizeSubject($text){
		$from = array('á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ');
		$to =   array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N');

		$r = str_replace($from, $to, $text);
		for ($i = 0; $i < strlen($r); $i++){
			$d = ord($r[$i]);
			//echo "$d <br>";
			if ($d < 32 || $d > 126){
				$r = substr($r,0,$i).'_'.substr($r,$i+2);
			}
		}
		return $r;
	}

	public static function smtp_test(){
		global $config, $lang;
		$to_email = $config->getValue('to_email');
		$to_name = $config->getValue('to_name');
		$subject = $lang->getValue('LBL_SMTP_TEST_SUBJECT');
		$body = $lang->getValue('LBL_SMTP_TEST_BODY');
		
		$sm = new SuppleMail(); 
		$sm->simpleSend($to_email, $to_name, $subject, $body);
	}

}

?>
