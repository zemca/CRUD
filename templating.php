<?php

require "_includes/bootstrap.inc.php";
use Tracy\Debugger;

Debugger::enable();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<?php


$n = new Mustache_Engine(array('entity_flags' => ENT_QUOTES));

$template = "
<h2> Welcome {{name}}</h2>
<p>{{team}}</p>
";

$data1 = ["name" => "Bob", "team" => "fsfs"];

echo $n->render($template,$data1);




?>
</body>
</html>