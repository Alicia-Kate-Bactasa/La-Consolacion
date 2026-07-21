<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect to main login
header('Location: login.php');
exit();
?> 