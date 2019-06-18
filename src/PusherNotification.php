<?php
namespace SDFT;
require __DIR__ . '../../vendor/autoload.php';
require __DIR__ . '../../config/pusher.php';

global $CONF;
$CONF = $PUSHER_CONFIG;


/**
* 
*/
class PusherNotification
{

  function __construct () {
   
    $this->options = array(
      'cluster' => $GLOBALS['CONF']['cluster'],
      'useTLS' => true
    );
    $this->pusher = new \Pusher\Pusher(
      $GLOBALS['CONF']['key'],
      $GLOBALS['CONF']['secret'],
      $GLOBALS['CONF']['app_id'],
      $this->options
    );
  }

  function send ($chanel, $data) {
    $payload['payload'] = $data;
    $this->pusher->trigger($chanel, 'notifications', $payload);
  }

  function authorize ($channel, $socket_id) {
    return $this->pusher->socket_auth($channel, $socket_id);
  }
	
}
?>