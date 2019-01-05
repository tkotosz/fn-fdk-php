<?php

require('vendor/autoload.php');

$fdk = new Tkotosz\FnPhpFdk\Fdk();

/**
 * inputMode: stream allows to get the input as a stream
 */
$fdk->handle(function ($input) {
    return new React\Promise\Promise(function ($resolve, $reject) use ($input) {
        $buffer = '';

        $input->on('data', function ($data) use (&$buffer) {
            $buffer .= $data;
        });

        $input->on('error', function ($error) use ($reject) {
            $reject(new \RuntimeException('Buffering error', 0, $error));
        });

        $input->on('close', function () use ($resolve, &$buffer) {
            $name = $buffer ? json_decode($buffer, true)['name'] : 'World';

            $resolve(['message' => 'Hello ' . $name]);
        });
    });
}, ['inputMode' => 'stream']);
