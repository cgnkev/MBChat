<?php

	if (!defined('MBC'))
		die('Hacking attempt...');
		
	function send_to_all($lid,$uid,$name,$role,$type,$rid,$text) {
	
        $message = '<{"lid":'.$lid.',"user" :{"uid":'.$uid.',"name":"'.$name.'","role":"'.$role.'"},"type":"'.$type.'","rid":'.$rid.',';
        $message .= '"message":"'.$text.'","time":'.time().'}>';

        $dh = opendir(MBCHAT_PIPE_PATH);
        if ($dh = opendir(MBCHAT_PIPE_PATH)) {
            while (($file = readdir($dh)) !== false) {
                if (filetype(MBCHAT_PIPE_PATH.$file) == 'fifo') {
                    $writer=fopen (MBCHAT_PIPE_PATH.$file,'r+');
                    fwrite($writer,$message);
                    fclose($writer);  
                }
            }
            closedir($dh);
        }
        file_put_contents(MBCHAT_PIPE_PATH.'time.txt', ''.time());
 	}
?>

