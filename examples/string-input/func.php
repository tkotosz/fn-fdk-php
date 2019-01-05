<?php

require('vendor/autoload.php');

$fdk = new Tkotosz\FnPhpFdk\Fdk();

/**
 * When inputMode: string used the request body is buffered then passed to the function as it is
 * Using $fdk->rawResult() the result will be passed to the response as it is (otherwise the fdk would json encode it)
 */
$fdk->handle(function ($input) use ($fdk) {
    return $fdk->rawResult('Hello '. ($input ?: 'World'));
}, ['inputMode' => 'string']);
