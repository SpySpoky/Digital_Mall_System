<?php
if($_SESSION['user_role'] !== 'courier') {
    header("Location: ../logout.php");
}
?>