<?php 
header('Access-Control-Allow-Origin: *');

use SDFT\PusherNotification;

require_once('../../../../vendor/autoload.php');

# parse input
$queryString = file_get_contents('php://input'); 
$data = array();
parse_str($queryString, $data);

$push = new PusherNotification();
$token = @$push->authorize($data['channel_name'], $data['socket_id']);

if($token) {
  echo $token;
}

?>