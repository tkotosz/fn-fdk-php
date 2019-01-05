<?php

require('vendor/autoload.php');

$fdk = new Tkotosz\FnPhpFdk\Fdk();

/**
 *  Function can return promise: In this case resolve the response later
 */
$fdk->handle(function ($input, $context) use ($fdk) {
    return new React\Promise\Promise(function ($resolve, $reject) use ($input, $fdk) {
        $name = 'World';
        if (isset($input['name'])) {
            $name = $input['name'];
        }
        $message = ['message' => 'Hello ' . $name];

        // we will resolve the reponse 1 second later
        $fdk->loop()->addTimer(1, function () use ($resolve, $message) {
            $resolve($message);
        });
    });
});
