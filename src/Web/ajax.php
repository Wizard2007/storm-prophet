<?php
$data = include('kyoto.php');
header('Content-type: text/json');
echo json_encode($data);