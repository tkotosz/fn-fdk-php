<?php

require('vendor/autoload.php');

$fdk = new Tkotosz\FnPhpFdk\Fdk();

$fdk->handle(function ($input) {
    $name = 'World';
    if (isset($input['name'])) {
        $name = $input['name'];
    }
    return ['message' => 'Hello ' . $name];
});
