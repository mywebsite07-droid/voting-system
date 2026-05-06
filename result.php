<?php
include('db.php');
session_start();

// Redirect students back to the ballot if they try to sneak into the results
if (isset($_SESSION['user'])) {
    header("Location: vote.php");
    exit();
}
?>