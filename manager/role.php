<?php
if($_SESSION['user_role'] !== 'manager') {
    header("Location: ../logout.php");
    exit();
}
?>