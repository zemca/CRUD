<?php
require "../_includes/bootstrap.inc.php";

$sesionStorage = new SessionStorage();
$sesionStorage->set('login', false);
$sesionStorage->set('admin', false);

header("Location: ./login.php");