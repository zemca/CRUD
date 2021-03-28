<?php
require "./_includes/bootstrap.inc.php";

$login = new SessionStorage();
if (!$login->get('login'))
    header("Location: ./login/login.php");
?>

<!DOCTYPE html>

<html>
<head>
    <meta charset="UTF-8">
    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <title>Prohlížeč databáze</title>
</head>
<body class="container">
<h1>Prohlížeč databáze</h1>
<ul class="list-group">
    <li class="list-group-item"><a href="employees/employeeList.php">Seznam zaměstnanců</a></li>
    <li class="list-group-item"><a href="rooms/roomList.php">Seznam místností</a></li>
    <form action="./login/logout.php" method="post" class="form-inline" onsubmit="return confirm('Opravdu se chcete odhlásit?')">
        <input type="hidden" name="room_id" value="{{ room_id }}">
        <input type="submit" value="Odhlásit se" class="btn btn-danger">
    </form>
</ul>
</body>
</html>

<?php
