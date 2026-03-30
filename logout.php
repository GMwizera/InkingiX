<?php

/**
 * InkingiX Rwanda - Logout
 */

require_once 'includes/functions.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to home
header('Location: index.php');
exit;
