# PHP Fn Development Kit (FDK)

fn-fdk-go provides convenience functions for writing php fn code

## Installing fn-fdk-php

You can install it manually with composer:
```
composer require tkotosz/fn-fdk-php
```

Or just use the [php init image](https://github.com/tkotosz/fn-php-init) to create new funtion like this:
```
fn init --init-image tkotosz/fn-php-init myfunc
```
This will generate the necessary files for your function including the composer json and docker file to install this fdk.

## Creating a PHP Function

Writing a PHP function is simply a matter of writing a handler function
that you pass to the FDK to invoke each time your function is called.

Start by creating a php function with `fn init`:

```sh
fn init --init-image tkotosz/fn-php-init phpfunc
cd phpfunc
```

This creates a simple hello world function in `func.php`:

```php
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
```

The handler function takes the input that is sent to the function and returns a response.
By default the input is treated as json which automatically converted to an array 
and the response is also an array (or json serializable object) which automatically converted to json.
Using the FDK you don't have to worry about reading the http request or sending back the response.
The FDK let's you focus on your function logic and not the mechanics.

Now run it!

```sh
fn deploy --local --app fdkdemo 
fn invoke fdkdemo phpfunc 
```

You should see the following output:

```sh
{"message":"Hello World"}
```

Run it with input:

```sh
echo -n '{"name":"Tibor"}' | fn invoke fdkdemo phpfunc
```

You should see the following output:

```sh
{"message":"Hello Tibor"}
```

Now you have a basic running php function that you can modify and add what you want.

## Function Context

Function invocation context details are available through an optional function argument.
To receive a context object, simply add a second argument to your handler function.
In the following example the `callId` is obtained from the context and included in 
the response message:

```php
<?php

require('vendor/autoload.php');

$fdk = new Tkotosz\FnPhpFdk\Fdk();

$fdk->handle(function ($input, $ctx) {
    $name = 'World';
    if (isset($input['name'])) {
        $name = $input['name'];
    }
    return ['message' => 'Hello ' . $name, 'callId' => $ctx->getCallId()];
});
```

Run it:

```sh
echo -n '{"name":"Tibor"}' | fn invoke fdkdemo phpfunc
```

You should see a similar output:

```sh
{"message":"Hello Tibor","callId":"01D0F7QX2QNG8G00GZJ00001YV"}
```

The context contains other context information about the request such as: 

* `ctx->getConfig` : An Object containing function config variables (from the environment variables) 
* `ctx->getHeaders` : An object containing input headers for the event as lists of strings
* `ctx->getDeadline` : A `DateTimeImmutable` object indicating when the function call must be processed by 
* `ctx->getCallId` : The call ID of the current call 
* `ctx->getId` : The Function ID of the current function 
* `ctx->getMemory` : Amount of ram in MB allocated to this function 
* `ctx->getContentType` : The incoming request content type (if set, otherwise null)
* `ctx->setResponseHeader(key,values...)` : Sets a response header to one or more values 
* `ctx->addResponseHeader(key,values...)` : Appends values to an existing response header
* `ctx->responseContentType` : Sets the response content type of the function
* `ctx->setResponseStatus` : Sets the response status code of the function (default: 200)
 
## Handling input/output

By default the FDK will try to json decode the input, likewise by default the output of a function will be treated as a JSON object and converted using `json_encode()`.

To change the handling of the input you can add an additional `options` parameter to `fdk->handle` that specifies the input handling strategy: 

```php
$fdk->handle(function ($input) use ($fdk) {
    return ['message' => 'Hello ' . ($input ?: 'World')];
}, ['inputMode' => 'string']);
```

valid input modes are: 
*  `json` (the default) attempts to parse the input as json
* `string` always treats input as a string 
* `stream` passes the input stream (streaming request body) to your function 

To change the output handling of your function from the default you should wrap the result value using a response decorator: 

```php
$fdk->handle(function ($input) use ($fdk) {
    return $fdk->rawResult('Hello '. ($input ?: 'World'));
}, ['inputMode' => 'string']);
```

the available decorators are: 
* `rawResult({string|ReadableStreamInterface})` passes the result directly to the response - the value can be a string or a readable stream
* `streamResult({resource|ReadableStreamInterface})` pipes the contents of the `resource` or `ReadableStreamInterface` into the output - this allows processing of data from files or HTTP responses

## Using HTTP headers and setting HTTP status codes
You can read http headers passed into a function invocation using `$ctx->getHeaderValue($key)`, this returns the first header value of the header matching `key` or you can use `$ctx->getHeaders()` or `$ctx->getHeaderValues($key)` methods as well.

```php
$fdk->handle(function ($input, $context) use ($fdk) {
    return $context->getHeaders(); // this will return all request headers as json
}, ['inputMode' => 'string']);
```

Outbound headers and the HTTP status code can be modified in a similar way:  

```php
$fdk->handle(function ($input, $context) {
    $context->setResponseStatus(201);
    $context->setResponseContentType('text/plain');
    $context->setResponseHeader('X-Awesomeness-level', 100);
    $context->addResponseHeader('X-Awesomene-number', 1);
    $context->addResponseHeader('X-Awesomene-number', 2);
    return 'Hello '. ($input ?: 'World');
}, ['inputMode' => 'string']);
```

## Examples

See examples [here](https://github.com/tkotosz/fn-fdk-php/tree/master/examples).
