<?php
if($_SESSION['user_role'] !== 'admin') {
    header("Location: ../logout.php");
}

?>