<?php

require('vendor/autoload.php');

$fdk = new Tkotosz\FnPhpFdk\Fdk();

/**
 * Redirects a user to a URL by accesing the context
 */
$fdk->handle(function ($input, $context) {
    $context->setResponseHeader('Location', 'http://fnproject.io');
    $context->setResponseStatus(302);
});
