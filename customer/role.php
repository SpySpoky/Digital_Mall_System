<?php 
if($_SESSION['user_role'] !== 'customer') {
    header("Location: ../logout.php");
}
?>