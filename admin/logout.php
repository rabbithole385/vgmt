<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';

admin_logout();
redirect('/admin/login.php');
