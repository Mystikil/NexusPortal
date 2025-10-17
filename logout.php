<?php
require __DIR__ . '/includes/auth.php';
logout();
header('Location: /N1/index.php');
exit;
