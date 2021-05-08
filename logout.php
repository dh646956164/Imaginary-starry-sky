<?php
    session_start();
    unset($_SESSION['LoggedIn']);
    session_destroy();
    header('Location: social.php');
    exit();
?>