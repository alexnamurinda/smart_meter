<?php
session_start();
session_unset(); // Clear all session data
session_destroy(); // Destroy session completely
header("Location: getstarted.php");
exit();
?>
