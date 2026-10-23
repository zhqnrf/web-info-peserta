<?php
require_once __DIR__ . '/lib.php';
unset($_SESSION['pid']);
setcookie('pid', '', ['expires' => time() - 3600, 'path' => '/']);
redirect('index.php');
