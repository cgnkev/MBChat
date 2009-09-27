<?php
/*
 	Copyright (c) 2009 Alan Chandler
    This file is part of MBChat.

    MBChat is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    MBChat is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with MBChat (file COPYING.txt).  If not, see <http://www.gnu.org/licenses/>.
*/
if(!(isset($_POST['user']) && isset($_POST['password']) && isset($_POST['rid'])))
	die('Hacking attempt - wrong parameters');
$uid = $_POST['user'];
if ($_POST['password'] != sha1("Key".$uid))
	die('Hacking attempt got: '.$_POST['password'].' expected: '.sha1("Key".$uid));
$rid = $_POST['rid'];

define('MBCHAT_MAX_TIME',	3);		//Max hours of message to display in a room
define('MBCHAT_MAX_MESSAGES',	100);		//Max message to display in room initially

define ('MBC',1);   //defined so we can control access to some of the files.
require_once('db.php');
$didre = false;
$messages = array();
$result = dbQuery('SELECT rid, name, type FROM rooms WHERE rid = '.dbMakeSafe($rid).';');
if(mysql_num_rows($result) != 0) {
	$room = mysql_fetch_assoc($result);
	mysql_free_result($result);
	$result = dbQuery('SELECT uid, name, role, moderator,question FROM users WHERE uid = '.dbMakeSafe($uid).';');
	if(mysql_num_rows($result) != 0) {
		$user = mysql_fetch_assoc($result);
		mysql_free_result($result);
		
		if ($room['type'] == 'M'  && $user['moderator'] != 'N') {
		//This is a moderated room, and this person is not normal - so swap them into moderated room role
			$role = $user['moderator'];
			$mod = $user['role'];
		} else {
			$role = $user['role'];
			$mod = $user['moderator'];
		}
		
		dbQuery('UPDATE users SET rid = '.dbMakeSafe($rid).', time = NOW(), role = '.dbMakeSafe($role)
					.', moderator = '.dbMakeSafe($mod).' WHERE uid = '.dbMakeSafe($uid).';');
		dbQuery('INSERT INTO log (uid, name, role, type, rid, text) VALUES ('.
						dbMakeSafe($user['uid']).','.dbMakeSafe($user['name']).','.dbMakeSafe($role).
						', "RE" ,'.dbMakeSafe($rid).','.dbMakeSafe($user['question']).');');
		$didre = true;
		$name = $user['name'];
		$question = $user['question'];
        $lid= mysql_insert_id();
		$sql = 'SELECT lid, UNIX_TIMESTAMP(time) AS time, type, rid, log.uid AS uid , name, role, text  FROM log';
		$sql .= ' LEFT JOIN participant ON participant.wid = rid WHERE ( (participant.uid = '.dbMakeSafe($uid).' AND type = "WH" )' ;
		$sql .= 'OR rid = '.dbMakeSafe($rid).') AND NOW() < DATE_ADD(log.time, INTERVAL '.MBCHAT_MAX_TIME.' HOUR) ';
		$sql .= 'ORDER BY lid DESC LIMIT '.MBCHAT_MAX_MESSAGES.';';
		$result = dbQuery($sql);
		if(mysql_num_rows($result) != 0) {
			while($row=mysql_fetch_assoc($result)) {
				$user = array();
				$item = array();
				$item['lid'] = $row['lid'];
				$item['type'] = $row['type'];
				$item['rid'] = $row['rid'];
				$user['uid'] = $row['uid'];
				$user['name'] = $row['name'];
				$user['role'] = $row['role'];
				$item['user'] = $user;
				$item['time'] = $row['time'];
				$item['message'] = $row['text'];
				$messages[]= $item;
			}		
		};
		mysql_free_result($result);
		$result = dbQuery('SELECT max(lid) AS lid FROM log;');
		$row = mysql_fetch_assoc($result);
		mysql_free_result($result);
	}	
}
echo '{ "room" :'.json_encode($room).', "messages" :'.json_encode(array_reverse($messages)).', "lastid" :'.$row['lid'].'}';
if ($didre) {
    include_once('send.php');
    send_to_all($lid,$uid, $name,$role,"RE",$rid,$question);	
}
?>