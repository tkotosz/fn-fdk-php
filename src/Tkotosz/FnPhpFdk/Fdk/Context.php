<?php

namespace Tkotosz\FnPhpFdk\Fdk;

class Context
{
    private $config;
    private $body;
    private $headers;
    private $responseHeaders;

    public function __construct(array $config, array $headers, $body)
    {
        $this->config = $config;
        $this->headers = $headers;
        $this->body = $body;
        $this->responseHeaders = ['Fn-Http-Status' => 200];
    }

    public function getId()
    {
        return $this->getConfigValue('FN_FN_ID');
    }

    public function getAppId()
    {
        return $this->getConfigValue('FN_APP_ID');
    }

    public function getName()
    {
        return $this->getConfigValue('FN_NAME');
    }

    public function getAppName()
    {
        return $this->getConfigValue('FN_APP_NAME');
    }

    public function getListener()
    {
        return $this->getConfigValue('FN_LISTENER');
    }

    public function getMemory()
    {
        return $this->getConfigValue('FN_MEMORY');
    }

    public function getTmpSize()
    {
        return $this->getConfigValue('FN_TMPSIZE');
    }

    public function getDeadline()
    {
        $deadLineStr = $this->getHeaderValue('Fn-Deadline');

        if (empty($deadLineStr)) {
            return null;
        }

        return new DateTimeImmutable($deadLineStr);
    }

    public function getCallId()
    {
        return $this->getHeaderValue('Fn-Call-Id');
    }

    public function getContentType()
    {
        return $this->getHeaderValue('Content-Type');
    }

    public function getConfigValue(string $key)
    {
        return $this->config[$key] ?? null;
    }

    public function getHeaderValue(string $key)
    {
        return $this->getHeaderValues($key)[0] ?? null;
    }

    public function getResponseHeaderValue(string $key)
    {
        return $this->getResponseHeaderValues($key)[0] ?? null;
    }

    public function getHeaderValues(string $key)
    {
        return $this->headers[$key] ?? [];
    }

    public function getResponseHeaderValues(string $key)
    {
        return $this->responseHeaders[$key] ?? [];
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getResponseHeaders()
    {
        return $this->responseHeaders;   
    }

    public function setResponseHeader($key, $value)
    {
        if ($key !== 'Content-Type' && strpos($key, 'Fn-Http') !== 0) {
            $key = 'Fn-Http-H-' . $key;
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $this->responseHeaders[$key] = $value;
    }

    public function addResponseHeader($key, $value)
    {
        if ($key !== 'Content-Type' && strpos($key, 'Fn-Http') !== 0) {
            $key = 'Fn-Http-H-' . $key;
        }

        if (!isset($this->responseHeaders[$key])) {
            $this->responseHeaders[$key] = [];
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $this->responseHeaders[$key] = array_merge($this->responseHeaders[$key], $value);
    }

    public function setResponseContentType($contentType)
    {
        $this->setResponseHeader('Content-Type', $contentType);
    }

    public function setResponseStatus($responseStatusCode)
    {
        $this->setResponseHeader('Fn-Http-Status', $responseStatusCode);
    }
}
