<?php 

require_once('include/SuppleApplication.php');
require_once('include/util.php');
require_once('include/imapmailbox/ImapMailbox.php');

class SuppleImap {

	function getImapMessages($user_name, $pass, $mailbox, $table){
	
		$db = SuppleApplication::getdb();

		$r = array();
	
		// $stream = imap_open ($mailbox , $user_name , $pass);
		
		// if ($stream !== false){
		
			// $a = imap_search($stream, "ALL");
			$mailbox = new ImapMailbox($mailbox, $user_name, $pass, 'uploaded/'.$table, 'utf-8');

			foreach ($mailbox->searchMailbox('ALL') as $mailId){
			
				$uid = $mailId;
				$row = $db->from($table)->where(array('uid' => $uid))->getArray();
				
				if ($row){
				
					// CACHE HIT!!! :D :D :D
					$row = $row[0];
				
				} else {
				
					
					$mail = $mailbox->getMail($mailId);
					
					$time = strtotime($mail->date);
					$date = date("d/m/Y H:i:s", $time);
					
					$row = array(
						'from' => utf8_decode($mail->fromName),
						'subject' => utf8_decode($mail->subject),
						'message' => utf8_decode($mail->textPlain),
						'date' => $date,
						'time' => $time,
						'uid' => $mail->mId,
					);
					
					
					
					foreach ($mail->attachments as $file => $full_filename){
						$row['picture'] = 'uploaded/'.$table.'/'.$file;
						// ¿No se merece un resize?
						require_once('include/SuppleFile.php');
						$sf = new SuppleFile();
						if ($sf->is_image($row['picture'])){
							resizeToFile($row['picture'], $sf->img_max_width, $sf->img_max_height, $row['picture'], 90);
						}
						
						break;
					}
					
					
					// INSERT!
					// print_r($row);
					$db->insert($table, $row);
					
					
				}
				
				$r[] = $row;
			}

		
		return $r;

	}

}

?>
