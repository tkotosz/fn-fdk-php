<?php

namespace Tkotosz\FnPhpFdk\Fdk\Result;

class RawResult
{
    private $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function get()
    {
        return $this->result;
    }
}
