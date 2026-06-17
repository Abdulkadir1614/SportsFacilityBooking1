<?php

echo "<pre>";

echo "HOST = " . getenv('MYSQLHOST') . "<br>";
echo "USER = " . getenv('MYSQLUSER') . "<br>";
echo "DATABASE = " . getenv('MYSQLDATABASE') . "<br>";

$pass = getenv('MYSQLPASSWORD');

echo "PASSWORD LENGTH = " . strlen($pass);