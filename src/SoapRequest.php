<?php

namespace Meng\Soap;

/**
 * Class SoapRequest
 * @package Meng\Soap
 */
class SoapRequest
{
    /** @var string */
    private $endpoint;

    /** @var string */
    private $soapAction;

    /** @var string */
    private $soapVersion;

    /** @var string */
    private $soapMessage;

    /**
     * @param string $endpoint
     * @param string $soapAction
     * @param string $soapVersion
     * @param string $soapMessage
     */
    public function __construct(string $endpoint, string $soapAction, string $soapVersion, string $soapMessage)
    {
        $this->endpoint = $endpoint;
        $this->soapAction = $soapAction;
        $this->soapVersion = $soapVersion;
        $this->soapMessage = $soapMessage;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return string
     */
    public function getSoapAction(): string
    {
        return $this->soapAction;
    }

    /**
     * @return string
     */
    public function getSoapVersion(): string
    {
        return $this->soapVersion;
    }

    /**
     * @return string
     */
    public function getSoapMessage(): string
    {
        return $this->soapMessage;
    }
}