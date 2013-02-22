<?php

require 'database.php';
$db->query('drop table if exists users');

$db->query('create table users (id int, username text, password text)');
$db->query('insert into users (id, username, password) values (1, "ss23", "$6$rounds=5000$5127787115ade$4HyqiX7qz8TFTkT7FvH3enSmkvDL3bg0EQBa3TzvBM.aOUV7g47Y8YglIjUD8UnNPrKjs5FvkvVr2sLKk0KL81")'); // god
$db->query('insert into users (id, username, password) values (2, "rms", "$6$rounds=5000$5127787115ade$up3p2K0T9hJ2cWNNT.W9SeIdDfDVhyz56EBWG.jJcO1oosyxwWjWj7zKONloJINXDnDZssr6dwRkyLvrKarZO1")'); // sex
$db->query('insert into users (id, username, password) values (3, "anon_hacker", "$6$rounds=5000$5127787115ade$3IZO2KKDOrS357RBxABqjMfcKJ1G4NZXFAKIaKlQ1O9CDhMMyKYFJCIi7nG3DLiPT24oPKGfeZneBtBV6/Cfn.")'); // passw0rd

