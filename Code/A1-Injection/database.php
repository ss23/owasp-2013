<?php

error_reporting(-1);
ini_set('display_errors', 'On');

$db = new PDO('sqlite:one.sqlite3');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
