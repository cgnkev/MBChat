<?php
/*
 	Copyright (c) 2009,2010 Alan Chandler
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
if(!(isset($_POST['user']) && isset($_POST['password']) && isset($_POST['rid']) && isset($_POST['rid'])&& isset($_POST['end'])))
	die('Log - Hacking attempt - wrong parameters');
$uid = $_POST['user'];
if ($_POST['password'] != sha1("Key".$uid))
	die('Log - Hacking attempt got: '.$_POST['password'].' expected: '.sha1("Key".$uid));


define ('MBC',1);   //defined so we can control access to some of the files.
require_once('./client.php');

$c = new ChatServer();

$c->fetch('getlog',$uid,$_POST['rid'],$_POST['start'],$_POST['end']);

