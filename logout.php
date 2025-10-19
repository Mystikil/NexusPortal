<?php
require __DIR__ . '/includes/auth.php';
logout();
header('Location: ' . siteUrl());
exit;
