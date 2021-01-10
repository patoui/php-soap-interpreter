<?php

namespace Tests;

use Exception;
use Meng\Soap\Interpreter;
use Meng\Soap\SoapRequest;
use PHPUnit\Framework\TestCase;
use SoapFault;
use SoapHeader;

class InterpreterTest extends TestCase
{
    /** @test */
    public function requestWsdlArrayArguments()
    {
        $interpreter = new Interpreter('http://www.webservicex.net/CurrencyConvertor.asmx?WSDL');
        $request = $interpreter->request('ConversionRate', [['FromCurrency' => 'AFA', 'ToCurrency' => 'ALL']]);
        self::assertEquals('http://www.webservicex.net/CurrencyConvertor.asmx', $request->getEndpoint());
        self::assertEquals('http://www.webserviceX.NET/ConversionRate', $request->getSoapAction());
        self::assertEquals('1', $request->getSoapVersion());
        self::assertNotEmpty($request->getSoapMessage());
        self::assertStringContainsString('http://schemas.xmlsoap.org/soap/envelope/', $request->getSoapMessage());
        self::assertStringContainsString('ConversionRate', $request->getSoapMessage());
        self::assertStringContainsString('FromCurrency', $request->getSoapMessage());
        self::assertStringContainsString('AFA', $request->getSoapMessage());
        self::assertStringContainsString('ToCurrency', $request->getSoapMessage());
        self::assertStringContainsString('ALL', $request->getSoapMessage());
    }

    /** @test */
    public function requestWsdlObjectArguments(): void
    {
        $interpreter = new Interpreter('http://www.webservicex.net/CurrencyConvertor.asmx?WSDL');
        $rate = new ConversionRate;
        $rate->FromCurrency = 'AFA';
        $rate->ToCurrency = 'ALL';
        $request = $interpreter->request('ConversionRate', [$rate]);
        self::assertEquals('http://www.webservicex.net/CurrencyConvertor.asmx', $request->getEndpoint());
        self::assertEquals('http://www.webserviceX.NET/ConversionRate', $request->getSoapAction());
        self::assertEquals('1', $request->getSoapVersion());
        self::assertNotEmpty($request->getSoapMessage());
        self::assertStringContainsString('http://schemas.xmlsoap.org/soap/envelope/', $request->getSoapMessage());
        self::assertStringContainsString('ConversionRate', $request->getSoapMessage());
        self::assertStringContainsString('FromCurrency', $request->getSoapMessage());
        self::assertStringContainsString('AFA', $request->getSoapMessage());
        self::assertStringContainsString('ToCurrency', $request->getSoapMessage());
        self::assertStringContainsString('ALL', $request->getSoapMessage());
    }

    /** @test */
    public function requestWsdlInputHeaders(): void
    {
        $interpreter = new Interpreter('http://www.webservicex.net/CurrencyConvertor.asmx?WSDL');
        $request = $interpreter->request(
            'ConversionRate',
            [['FromCurrency' => 'AFA', 'ToCurrency' => 'ALL']],
            null,
            [new SoapHeader('www.namespace.com', 'test_header', 'header_data')]
        );
        self::assertEquals('http://www.webservicex.net/CurrencyConvertor.asmx', $request->getEndpoint());
        self::assertEquals('http://www.webserviceX.NET/ConversionRate', $request->getSoapAction());
        self::assertEquals('1', $request->getSoapVersion());
        self::assertNotEmpty($request->getSoapMessage());
        self::assertStringContainsString('http://schemas.xmlsoap.org/soap/envelope/', $request->getSoapMessage());
        self::assertStringContainsString('www.namespace.com', $request->getSoapMessage());
        self::assertStringContainsString('test_header', $request->getSoapMessage());
        self::assertStringContainsString('header_data', $request->getSoapMessage());
        self::assertStringContainsString('ConversionRate', $request->getSoapMessage());
        self::assertStringContainsString('FromCurrency', $request->getSoapMessage());
        self::assertStringContainsString('AFA', $request->getSoapMessage());
        self::assertStringContainsString('ToCurrency', $request->getSoapMessage());
        self::assertStringContainsString('ALL', $request->getSoapMessage());
    }

    /** @test */
    public function requestTypeMapToXML(): void
    {
        $interpreter = new Interpreter(
            'http://www.webservicex.net/CurrencyConvertor.asmx?WSDL',
            [
                'typemap' => [
                    [
                        'type_name' => 'ConversionRate',
                        'type_ns' => 'http://www.webserviceX.NET/',
                        'to_xml' => function() {
                            return "<ConversionRate><FromCurrency>OLD</FromCurrency><ToCurrency>NEW</ToCurrency></ConversionRate>";
                        }
                    ]
                ]
            ]
        );

        $request = $interpreter->request('ConversionRate', [[]]);
        self::assertEquals('http://www.webservicex.net/CurrencyConvertor.asmx', $request->getEndpoint());
        self::assertEquals('http://www.webserviceX.NET/ConversionRate', $request->getSoapAction());
        self::assertEquals('1', $request->getSoapVersion());
        self::assertNotEmpty($request->getSoapMessage());
        self::assertStringContainsString('http://schemas.xmlsoap.org/soap/envelope/', $request->getSoapMessage());
        self::assertStringContainsString('ConversionRate', $request->getSoapMessage());
        self::assertStringContainsString('FromCurrency', $request->getSoapMessage());
        self::assertStringContainsString('OLD', $request->getSoapMessage());
        self::assertStringContainsString('ToCurrency', $request->getSoapMessage());
        self::assertStringContainsString('NEW', $request->getSoapMessage());
    }

    /** @test */
    public function responseWsdl(): void
    {
        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <ConversionRateResponse xmlns="http://www.webserviceX.NET/">
      <ConversionRateResult>-1</ConversionRateResult>
    </ConversionRateResponse>
  </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter('http://www.webservicex.net/CurrencyConvertor.asmx?WSDL');
        $responseMessage = $interpreter->response($responseMessage, 'ConversionRate');
        self::assertInstanceOf('\StdClass', $responseMessage);
        self::assertEquals(['ConversionRateResult' => '-1'], (array)$responseMessage);
    }

    /** @test */
    public function responseWsdlOutputHeaders(): void
    {
        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <m:Trans xmlns:m="http://www.w3schools.com/transaction/" soap:mustUnderstand="1">
      234
    </m:Trans>
  </soap:Header>
  <soap:Body>
    <ConversionRateResponse xmlns="http://www.webserviceX.NET/">
      <ConversionRateResult>-1</ConversionRateResult>
    </ConversionRateResponse>
  </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter('http://www.webservicex.net/CurrencyConvertor.asmx?WSDL');
        $outputHeaders = [];
        $responseMessage = $interpreter->response($responseMessage, 'ConversionRate', $outputHeaders);
        self::assertInstanceOf('\StdClass', $responseMessage);
        self::assertEquals(['ConversionRateResult' => '-1'], (array)$responseMessage);
        self::assertNotEmpty($outputHeaders);
    }

    /** @test */
    public function responseWsdlClassMap(): void
    {
        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <ConversionRateResponse xmlns="http://www.webserviceX.NET/">
      <ConversionRateResult>-1</ConversionRateResult>
    </ConversionRateResponse>
  </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter('http://www.webservicex.net/CurrencyConvertor.asmx?WSDL', ['classmap' => ['ConversionRateResponse' => '\ConversionRateResponse']]);
        $responseMessage = $interpreter->response($responseMessage, 'ConversionRate');
        self::assertInstanceOf('\ConversionRateResponse', $responseMessage);
        self::assertEquals(['ConversionRateResult' => '-1'], (array)$responseMessage);
    }

    /** @test */
    public function responseTypeMapFromXML(): void
    {
        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <ConversionRateResponse xmlns="http://www.webserviceX.NET/">
      <ConversionRateResult>-1</ConversionRateResult>
    </ConversionRateResponse>
  </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter(
            'http://www.webservicex.net/CurrencyConvertor.asmx?WSDL',
            [
                'typemap' => [
                    [
                        'type_name' => 'ConversionRateResponse',
                        'type_ns' => 'http://www.webserviceX.NET/',
                        'from_xml' => function() {
                            $rateResponse = new ConversionRateResponse;
                            $rateResponse->MockedResult = 100;
                            return $rateResponse;
                        }
                    ]
                ]
            ]
        );

        $responseMessage = $interpreter->response($responseMessage, 'ConversionRate');
        self::assertInstanceOf('\ConversionRateResponse', $responseMessage);
        self::assertEquals(['MockedResult' => 100], (array)$responseMessage);
    }

    /** @test */
    public function responseWsdlDisableExceptions(): void
    {
        $interpreter = new Interpreter(null, ['uri'=>'www.uri.com', 'location'=>'www.location.com', 'exceptions' => false]);
        $responseMessage = <<<EOD
<SOAP-ENV:Envelope
  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
   <SOAP-ENV:Body>
       <SOAP-ENV:Fault>
           <faultcode>SOAP-ENV:Server</faultcode>
           <faultstring>Server Error</faultstring>
           <detail>
               <e:myfaultdetails xmlns:e="Some-URI">
                 <message>
                   My application didn't work
                 </message>
                 <errorcode>
                   1001
                 </errorcode>
               </e:myfaultdetails>
           </detail>
       </SOAP-ENV:Fault>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOD;
        $result = $interpreter->response($responseMessage, 'AnyMethod');
        self::assertInstanceOf(SoapFault::class, $result);
    }

    /** @test */
    public function requestWsdlSoapV12(): void
    {
        $interpreter = new Interpreter('http://www.webservicex.net/airport.asmx?WSDL', ['soap_version' => SOAP_1_2]);
        $request = $interpreter->request('GetAirportInformationByCountry', [['country' => 'United Kingdom']]);
        self::assertEquals('http://www.webservicex.net/airport.asmx', $request->getEndpoint());
        self::assertEquals('http://www.webserviceX.NET/GetAirportInformationByCountry', $request->getSoapAction());
        self::assertEquals('2', $request->getSoapVersion());
        self::assertNotEmpty($request->getSoapMessage());
        self::assertStringContainsString('http://www.w3.org/2003/05/soap-envelope', $request->getSoapMessage());
        self::assertStringContainsString('GetAirportInformationByCountry', $request->getSoapMessage());
        self::assertStringContainsString('country', $request->getSoapMessage());
    }

    /** @test */
    public function responseWsdlSoapV12(): void
    {
        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <soap:Body>
        <GetAirportInformationByCountryResponse xmlns="http://www.webserviceX.NET">
            <GetAirportInformationByCountryResult>&lt;NewDataSet /&gt;</GetAirportInformationByCountryResult>
        </GetAirportInformationByCountryResponse>
    </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter('http://www.webservicex.net/airport.asmx?WSDL', ['soap_version' => SOAP_1_2]);
        $responseMessage = $interpreter->response($responseMessage, 'GetAirportInformationByCountry');
        self::assertEquals(['GetAirportInformationByCountryResult' => '<NewDataSet />'], (array)$responseMessage);
    }

    /** @test */
    public function requestWithoutWsdl(): void
    {
        $interpreter = new Interpreter(null, ['uri'=>'www.uri.com', 'location'=>'www.location.com']);
        $request = $interpreter->request('anything', [['one' => 'two', 'three' => 'four']]);
        self::assertEquals('www.location.com', $request->getEndpoint());
        self::assertEquals('www.uri.com#anything', $request->getSoapAction());
        self::assertEquals('1', $request->getSoapVersion());
        self::assertStringContainsString('one', $request->getSoapMessage());
        self::assertStringContainsString('two', $request->getSoapMessage());
        self::assertStringContainsString('three', $request->getSoapMessage());
        self::assertStringContainsString('four', $request->getSoapMessage());
    }

    /** @test */
    public function responseWithoutWsdl(): void
    {
        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <soap:Body>
        <GetAirportInformationByCountryResponse xmlns="http://www.webserviceX.NET">
            <GetAirportInformationByCountryResult>&lt;NewDataSet /&gt;</GetAirportInformationByCountryResult>
        </GetAirportInformationByCountryResponse>
    </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter(null, ['uri'=>'www.uri.com', 'location'=>'www.location.com', 'soap_version' => SOAP_1_2]);
        $responseMessage = $interpreter->response($responseMessage, 'GetAirportInformationByCountry');
        self::assertEquals('<NewDataSet />', $responseMessage);

        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <m:Trans xmlns:m="http://www.w3schools.com/transaction/" soap:mustUnderstand="1">
      234
    </m:Trans>
  </soap:Header>
  <soap:Body>
    <ConversionRateResponse xmlns="http://www.webserviceX.NET/">
      <ConversionRateResult>-1</ConversionRateResult>
    </ConversionRateResponse>
  </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter(null, ['uri'=>'www.uri.com', 'location'=>'www.location.com']);
        $outputHeaders = [];
        $responseMessage = $interpreter->response($responseMessage, 'ConversionRate', $outputHeaders);
        self::assertEquals('-1', $responseMessage);
        self::assertNotEmpty($outputHeaders);
    }

    /** @test */
    public function faultResponseNotAffectSubsequentRequests(): void
    {
        $interpreter = new Interpreter(null, ['uri'=>'www.uri.com', 'location'=>'www.location.com']);
        $responseMessage = <<<EOD
<SOAP-ENV:Envelope
  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
   <SOAP-ENV:Body>
       <SOAP-ENV:Fault>
           <faultcode>SOAP-ENV:Server</faultcode>
           <faultstring>Server Error</faultstring>
           <detail>
               <e:myfaultdetails xmlns:e="Some-URI">
                 <message>
                   My application didn't work
                 </message>
                 <errorcode>
                   1001
                 </errorcode>
               </e:myfaultdetails>
           </detail>
       </SOAP-ENV:Fault>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOD;
        try {
            $interpreter->response($responseMessage, 'AnyMethod');
        } catch (Exception $e) {
        }
        $request = $interpreter->request('AnyMethod');
        self::assertInstanceOf(SoapRequest::class, $request);
    }
}

/** Test support only */
class  ConversionRate
{

}

/** Test support only */
class ConversionRateResponse
{

}
