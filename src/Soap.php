<?php

namespace Meng\Soap;

use SoapClient;

/**
 * @internal
 */
class Soap extends SoapClient
{
    private $endpoint;
    private $soapRequest;
    private $soapResponse;
    private $soapAction;
    private $soapVersion;

    public function __construct($wsdl, array $options)
    {
        unset(
            $options['login'],
            $options['password'],
            $options['proxy_host'],
            $options['proxy_port'],
            $options['proxy_login'],
            $options['proxy_password'],
            $options['local_cert'],
            $options['passphrase'],
            $options['authentication'],
            $options['compression'],
            $options['trace'],
            $options['connection_timeout'],
            $options['user_agent'],
            $options['stream_context'],
            $options['keep_alive'],
            $options['ssl_method']
        );
        parent::__construct($wsdl, $options);
    }

    /** @inheritDoc */
    public function __doRequest($request, $location, $action, $version, $one_way = 0): string
    {
        if (null !== $this->soapResponse) {
            return $this->soapResponse;
        }

        $this->endpoint    = (string) $location;
        $this->soapAction  = (string) $action;
        $this->soapVersion = (string) $version;
        $this->soapRequest = (string) $request;
        return '';
    }

    /**
     * @param $function_name
     * @param $arguments
     * @param $options
     * @param $input_headers
     * @return SoapRequest
     */
    public function request($function_name, $arguments, $options, $input_headers): SoapRequest
    {
        $this->__soapCall($function_name, $arguments, $options, $input_headers);
        return new SoapRequest($this->endpoint, $this->soapAction, $this->soapVersion, $this->soapRequest);
    }

    /**
     * @param $response
     * @param $function_name
     * @param $output_headers
     * @return mixed
     */
    public function response($response, $function_name, &$output_headers)
    {
        $this->soapResponse = $response;
        try {
            $response = $this->__soapCall($function_name, [], null, null, $output_headers);
        } catch (\SoapFault $fault) {
            $this->soapResponse = null;
            throw $fault;
        }
        $this->soapResponse = null;
        return $response;
    }
}
