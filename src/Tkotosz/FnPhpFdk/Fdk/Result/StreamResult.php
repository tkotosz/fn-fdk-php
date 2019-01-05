<?php

namespace Tkotosz\FnPhpFdk\Fdk\Result;

use React\Stream\ReadableStreamInterface;

class StreamResult
{
    private $stream;

    public function __construct(ReadableStreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function get()
    {
        return $this->stream;
    }
}
