<?php

// Cookies are insecure, pass sessions via the URL instead
ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies', 0);
ini_set('session.use_trans_sid', 1);

require 'database.php';

// Start our session
session_start();
if (SID == '') {
	header('Location: ?' . SID);
}
