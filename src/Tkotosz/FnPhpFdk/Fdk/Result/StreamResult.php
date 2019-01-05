<?php

namespace Tkotosz\FnPhpFdk\Fdk\Result;

class StreamResult
{
    private $stream;

    public function __construct(React\Stream\ReadableStreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function get()
    {
        return $this->stream;
    }
}
