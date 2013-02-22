<?php

require 'database.php';
$db->query('drop table if exists products');
$db->query('drop table if exists users');

$db->query('create table products (id int, title text, description text)');
$db->query('insert into products (id, title, description) values (1, "Penetration Testing", "Awesome penetration testing, provided by a l33t haker!")');
$db->query('insert into products (id, title, description) values (2, "Systems Administration Training", "Learn to become an awesome sysadmin today")');
$db->query('insert into products (id, title, description) values (99999, "Hacked!", "Hacked by Chinese")');

$db->query('create table users (id int, username text, password text)');
$db->query('insert into users (id, username, password) values (1, "ss23", "l33th@x0r")');
$db->query('insert into users (id, username, password) values (2, "rms", "gnu_me_harder")');
$db->query('insert into users (id, username, password) values (3, "anon_hacker", "iam12")');

