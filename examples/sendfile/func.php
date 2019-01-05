<?php

require('vendor/autoload.php');

$fdk = new Tkotosz\FnPhpFdk\Fdk();

/**
 * Sends a file to the output as a stream - this does not load the whole file into memory
 */
$fdk->handle(function ($input, $context) use ($fdk) {
    $context->setResponseContentType('text/html');
    return $fdk->streamResult(fopen('testfile.html', 'r'));
});
