<?php

require __DIR__.'/../vendor/autoload.php';
use JJG\Ping as Ping;
use RedisClient\RedisClient;
use RedisClient\Client\Version\RedisClient2x6;
use RedisClient\ClientFactory;

$config = json_decode(file_get_contents($argv[1]), TRUE);

$rClient = ClientFactory::create([
    'server' => $config['redis']['host'].":".$config['redis']['port'], // or 'unix:///tmp/redis.sock'
    'timeout' => 2,
    'version' => '2.8.24'
]);

$now_time_epoch = time();
$now_timestamp = new DateTime();
$timestamp = $now_timestamp->format(DateTime::ISO8601);


$body = [];
$body['timestamp'] = $now_timestamp->format(DateTime::ISO8601);
$body['location'] = $config['location'];

foreach ($config['hosts'] as $k=>$v) {

  $host = $v['host'];
  $ttl = 128;
  $timeout = 5;

  $ping = new Ping($host, $ttl, $timeout);
  $latency = $ping->ping();

  if ($latency !== false) {
    $body[$k]['latency'] = $latency;
    $body[$k]['status'] = 'ok';
    $body[$k]['status_val'] = 1;
  }
  else {
    $body[$k]['latency'] = -99;
    $body[$k]['status'] = 'error';
    $body[$k]['status_val'] = -1;
  }

}

$internet_ok = FALSE;
foreach ($config['hosts'] as $k=>$v) {

  if ($body[$k]['status_val'] == 1) {
    $internet_ok = TRUE;
  }

}

if ($internet_ok) {
  $body['internet']['status'] = 'online';
  $body['internet']['status_val'] = 1;
} else {
  $body['internet']['status'] = 'offline';
  $body['internet']['status_val'] = -1;
}

$rClient->set($config['redis']['key'], json_encode($body));

$id = hash('sha256',$timestamp);

$rClient->hset($config['redis']['key'].'_message', $id, json_encode($body));
$rClient->zadd($config['redis']['key'].'_timestamp', array($id => $now_time_epoch));
