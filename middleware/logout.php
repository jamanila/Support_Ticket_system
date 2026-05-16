<?php
session_start();
session_destroy();
session_unset();

header("Location: /OOP/SupportSystem/middleware/login.php");
exit();

?>