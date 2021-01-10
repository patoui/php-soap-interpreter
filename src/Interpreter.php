<?php

namespace Meng\Soap;

use SoapFault;

/**
 * Class Interpreter
 * @package Meng\Soap
 */
class Interpreter
{
    /** @var Soap */
    private $soap;

    /**
     * @param null|string $wsdl         URI of the WSDL file or NULL if working in non-WSDL mode.
     * @param array       $options      Supported options: location, uri, style, use, soap_version, encoding,
     *                                  exceptions, classmap, typemap, cache_wsdl and feature.
     * @throws SoapFault
     */
    public function __construct(?string $wsdl, array $options = [])
    {
        $this->soap = new Soap($wsdl, $options);
    }

    /**
     * Interpret the given method and arguments to a SOAP request message.
     *
     * @param string     $function_name The name of the SOAP function to interpret.
     * @param array      $arguments     An array of the arguments to $function_name.
     * @param array|null $options       An associative array of options.
     *                                  The location option is the URL of the remote Web service.
     *                                  The uri option is the target namespace of the SOAP service.
     *                                  The soapaction option is the action to call.
     * @param mixed      $input_headers An array of headers to be interpreted along with the SOAP request.
     * @return SoapRequest
     */
    public function request(string $function_name, array $arguments = [], array $options = null, $input_headers = null): SoapRequest
    {
        return $this->soap->request($function_name, $arguments, $options, $input_headers);
    }

    /**
     * Interpret a SOAP response message to PHP values.
     *
     * @param string     $response       The SOAP response message.
     * @param string     $function_name  The name of the SOAP function to interpret.
     * @param array|null $output_headers If supplied, this array will be filled with the headers from the SOAP response.
     * @return mixed
     * @throws SoapFault
     */
    public function response(string $response, string $function_name, array &$output_headers = null)
    {
        return $this->soap->response($response, $function_name, $output_headers);
    }
}