<?php

require('vendor/autoload.php');

$fdk = new Tkotosz\FnPhpFdk\Fdk();

$fdk->handle(function ($input, $context) {
    $context->setResponseHeader('Location', 'http://fnproject.io');
    $context->setResponseStatus(302);
});
