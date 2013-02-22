<?php

require 'database.php';
$db->query('drop table if exists users');

$db->query('create table users (id int, username text, password text)');
$db->query('insert into users (id, username, password) values (1, "ss23", "197bcc298645b84bd95bba43749f708745375ce30262d3ecdc8439605d157786a6f9d8bac4a8dd31e9e68364820e3fc39ab6a15a75988d994f3a6361d0d37309")');
$db->query('insert into users (id, username, password) values (2, "rms", "98923710a30301e03d25bc9ea565d4cfb738b7390dc91871cdd368bd58b959e57dac211730538be0433f85a1a3011bbab9a91b1232022694b7f66ac49109f4a1")');
$db->query('insert into users (id, username, password) values (3, "anon_hacker", "e0469addd8d57a3623494096dabc19bebca1a038c9da696940b3f853d106a6ecfa5bd60ce8e72884efa3bd92b930da178fd616f40facad654212d7c2f8817dd4")');

