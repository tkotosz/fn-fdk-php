<?php

namespace Tkotosz\FnPhpFdk;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use React\Http\Middleware\RequestBodyBufferMiddleware;
use React\Http\Response;
use React\Http\StreamingServer;
use React\Promise\Promise;
use React\Socket\UnixServer as Socket;
use React\Stream\ReadableResourceStream;
use React\Stream\ReadableStreamInterface;
use Throwable;
use Tkotosz\FnPhpFdk\Fdk\Context;
use Tkotosz\FnPhpFdk\Fdk\Result\RawResult;
use Tkotosz\FnPhpFdk\Fdk\Result\StreamResult;

class Fdk
{
    private $loop;

    public function __construct()
    {
        $this->loop = LoopFactory::create();
    }

    public function rawResult($text): RawResult
    {
        return new RawResult($text);
    }

    public function streamResult($stream): StreamResult
    {
        if (is_resource($stream) && get_resource_type($stream) === "stream") {
            $stream = new ReadableResourceStream($stream, $this->loop());
        }

        if (!($stream instanceof ReadableStreamInterface))  {
            throw new InvalidArgumentException(
                sprintf('Invalid stream, should be instance of %s', ReadableStreamInterface::class)
            );
        }

        return new StreamResult($stream);
    }

    public function loop(): LoopInterface
    {
        return $this->loop;
    }

    public function handle(callable $fnFunction, array $options = [])
    {
        $options = array_merge(['inputMode' => 'json'], $options);

        $format = getenv('FN_FORMAT') ?: '';

        if ($format != '' && $format != 'http-stream') {
            exit('only http-stream format is supported, please set function.format=http-stream against your fn service');
        }

        $path = getenv('FN_LISTENER') ?: '';

        $uri = parse_url($path);
        if ($uri === false || !isset($uri['scheme'])) {
            exit(sprintf('url parse error: %s', $path));
        }

        if ($uri['scheme'] != 'unix' || $uri['path'] == '') {
            exit(sprintf('url scheme must be unix with a valid path, got: %s', $path));
        }
        
        $handlers = [];

        if ($options['inputMode'] !== 'stream') {
            $handlers[] = new RequestBodyBufferMiddleware();
        }

        $handlers[] = function (ServerRequestInterface $request) use ($fnFunction, $options) {
            return new Promise(function ($resolveResponse, $rejectResponse) use ($request, $fnFunction, $options) {
                $context = new Context(array_merge($_SERVER, $_ENV), $request->getHeaders(), $request->getBody());

                if ($options['inputMode'] === 'string') {
                    $input = $request->getBody()->getContents();
                    $context->setResponseContentType('text/plain');
                } elseif ($options['inputMode'] === 'stream') {
                    $input = $request->getBody();
                    $context->setResponseContentType('application/octet-stream');
                } else {
                    $input = json_decode($request->getBody()->getContents(), true);
                    $context->setResponseContentType('application/json');
                }

                (new Promise(function ($resolve, $reject) use ($fnFunction, $input, $context) {
                    try {
                        $resolve($fnFunction($input, $context));
                    } catch (Throwable $t) {
                        $reject($t);
                    }
                }))->then(function ($output) use ($resolveResponse, $context) {
                    if ($output instanceof RawResult) {
                        $responseBody = $output->get();
                    } elseif ($output instanceof StreamResult) {
                        $responseBody = $output->get();
                    } else {
                        $responseBody = json_encode($output);
                    }
                    
                    $response = new Response(
                        200,
                        $context->getResponseHeaders(),
                        $responseBody
                    );

                    $resolveResponse($response);
                })->otherwise(function ($err) use ($rejectResponse)  {
                    printf('Error in function: %s', $err);
                    $rejectResponse();
                });
            });
        };

        $server = new StreamingServer($handlers);
        $socket = new Socket($uri['path'], $this->loop);
        $server->listen($socket);
        
        $this->loop->run();
    }
}
