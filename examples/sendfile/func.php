<?php

require('vendor/autoload.php');

$fdk = new Tkotosz\FnPhpFdk\Fdk();

$fdk->handle(function ($input, $context) use ($fdk) {
    $context->setResponseContentType('text/html');
    return $fdk->streamResult(fopen('testfile.html', 'r'));
});
