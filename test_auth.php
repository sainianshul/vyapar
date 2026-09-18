<?php
require 'vendor/autoload.php';
$client = new GuzzleHttp\Client();
try {
    $res = $client->post('https://vyapar.rezel.online/api/v1/test-broadcasting-auth', [
        'headers' => [
            'Authorization' => 'Bearer 6|gKD2L6Zdns0pp3S4ZfbIN8KFCxwoiSIAuRW8h2qFe5bb908e',
            'Accept' => 'application/json'
        ],
        'form_params' => [
            'socket_id' => '123.456',
            'channel_name' => 'private-vyaparmitra_user.34'
        ]
    ]);
    echo "Status: " . $res->getStatusCode() . "\n";
    echo "Body: " . $res->getBody() . "\n";
} catch (\GuzzleHttp\Exception\ClientException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Response: " . $e->getResponse()->getBody() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
