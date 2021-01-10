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
    public function requestWsdlArrayArguments(): void
    {
        $interpreter = new Interpreter('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1');
        $request = $interpreter->request('LookupCity', [['zip' => 90210]]);
        self::assertEquals('https://www.crcind.com:443/csp/samples/SOAP.Demo.cls', $request->getEndpoint());
        self::assertEquals('http://tempuri.org/SOAP.Demo.LookupCity', $request->getSoapAction());
        self::assertEquals('1', $request->getSoapVersion());
        self::assertNotEmpty($request->getSoapMessage());
        self::assertStringContainsString('http://schemas.xmlsoap.org/soap/envelope/', $request->getSoapMessage());
        self::assertStringContainsString('zip', $request->getSoapMessage());
    }

    /** @test */
    public function requestWsdlObjectArguments(): void
    {
        $interpreter = new Interpreter('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1');
        $lookup_city = new LookupCity;
        $lookup_city->zip = 90210;
        $request = $interpreter->request('LookupCity', [$lookup_city]);
        self::assertEquals('https://www.crcind.com:443/csp/samples/SOAP.Demo.cls', $request->getEndpoint());
        self::assertEquals('http://tempuri.org/SOAP.Demo.LookupCity', $request->getSoapAction());
        self::assertEquals('1', $request->getSoapVersion());
        self::assertNotEmpty($request->getSoapMessage());
        self::assertStringContainsString('http://schemas.xmlsoap.org/soap/envelope/', $request->getSoapMessage());
        self::assertStringContainsString('zip', $request->getSoapMessage());
    }

    /** @test */
    public function requestWsdlInputHeaders(): void
    {
        $interpreter = new Interpreter('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1');
        $request = $interpreter->request(
            'LookupCity',
            [['zip' => 90210]],
            null,
            [new SoapHeader('www.namespace.com', 'test_header', 'header_data')]
        );
        self::assertEquals('https://www.crcind.com:443/csp/samples/SOAP.Demo.cls', $request->getEndpoint());
        self::assertEquals('http://tempuri.org/SOAP.Demo.LookupCity', $request->getSoapAction());
        self::assertEquals('1', $request->getSoapVersion());
        self::assertNotEmpty($request->getSoapMessage());
        self::assertStringContainsString('http://schemas.xmlsoap.org/soap/envelope/', $request->getSoapMessage());
        self::assertStringContainsString('www.namespace.com', $request->getSoapMessage());
        self::assertStringContainsString('test_header', $request->getSoapMessage());
        self::assertStringContainsString('header_data', $request->getSoapMessage());
        self::assertStringContainsString('zip', $request->getSoapMessage());
    }

    /** @test */
    public function requestTypeMapToXML(): void
    {
        self::markTestSkipped('Determine appropriate test parameters');
        $interpreter = new Interpreter(
            'https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1',
            [
                'typemap' => [
                    [
                        'type_name' => 'LookupCity',
                        'type_ns' => 'https://www.crcind.com/csp/samples/SOAP.Demo.CLS',
                        'to_xml' => function() {
                            return "<LookupCity><FromCity>OLD</FromCity><ToCity>NEW</ToCity></LookupCity>";
                        }
                    ]
                ]
            ]
        );

        $request = $interpreter->request('LookupCity', [[]]);
        self::assertEquals('https://www.crcind.com:443/csp/samples/SOAP.Demo.cls', $request->getEndpoint());
        self::assertEquals('http://tempuri.org/SOAP.Demo.LookupCity', $request->getSoapAction());
        self::assertEquals('1', $request->getSoapVersion());
        self::assertNotEmpty($request->getSoapMessage());
        self::assertStringContainsString('http://schemas.xmlsoap.org/soap/envelope/', $request->getSoapMessage());
        self::assertStringContainsString('LookupCity', $request->getSoapMessage());
        self::assertStringContainsString('FromCity', $request->getSoapMessage());
        self::assertStringContainsString('OLD', $request->getSoapMessage());
        self::assertStringContainsString('ToCity', $request->getSoapMessage());
        self::assertStringContainsString('NEW', $request->getSoapMessage());
    }

    /** @test */
    public function responseWsdl(): void
    {
        $responseMessage = <<<EOD
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:s="http://www.w3.org/2001/XMLSchema">
   <SOAP-ENV:Body>
      <LookupCityResponse xmlns="http://tempuri.org">
         <LookupCityResult>
            <City>Beverly Hills</City>
            <State>CA</State>
            <Zip>90210</Zip>
         </LookupCityResult>
      </LookupCityResponse>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOD;
        $interpreter = new Interpreter('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1');
        $responseMessage = $interpreter->response($responseMessage, 'LookupCity');
        self::assertInstanceOf('stdClass', $responseMessage);
        self::assertEquals('Beverly Hills', $responseMessage->LookupCityResult->City);
        self::assertEquals('CA', $responseMessage->LookupCityResult->State);
        self::assertEquals(90210, $responseMessage->LookupCityResult->Zip);
    }

    /** @test */
    public function responseWsdlOutputHeaders(): void
    {
        self::markTestSkipped('Find appropriate Soap action that has headers');
        $responseMessage = <<<EOD
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:s="http://www.w3.org/2001/XMLSchema">
   <SOAP-ENV:Body>
      <LookupCityResponse xmlns="http://tempuri.org">
         <LookupCityResult>
            <City>Beverly Hills</City>
            <State>CA</State>
            <Zip>90210</Zip>
         </LookupCityResult>
      </LookupCityResponse>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOD;
        $interpreter = new Interpreter('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1');
        $outputHeaders = [];
        $responseMessage = $interpreter->response($responseMessage, 'LookupCity', $outputHeaders);
        self::assertInstanceOf('stdClass', $responseMessage);
        self::assertEquals('Beverly Hills', $responseMessage->LookupCityResult->City);
        self::assertEquals('CA', $responseMessage->LookupCityResult->State);
        self::assertEquals(90210, $responseMessage->LookupCityResult->Zip);
        self::assertNotEmpty($outputHeaders);
    }

    /** @test */
    public function responseWsdlClassMap(): void
    {
        $responseMessage = <<<EOD
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:s="http://www.w3.org/2001/XMLSchema">
   <SOAP-ENV:Body>
      <LookupCityResponse xmlns="http://tempuri.org">
         <LookupCityResult>
            <City>Beverly Hills</City>
            <State>CA</State>
            <Zip>90210</Zip>
         </LookupCityResult>
      </LookupCityResponse>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOD;

        $interpreter = new Interpreter('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1', ['classmap' => ['LookupCityResponse' => LookupCityResponse::class]]);
        $responseMessage = $interpreter->response($responseMessage, 'LookupCity');
        self::assertInstanceOf(LookupCityResponse::class, $responseMessage);
        self::assertEquals('Beverly Hills', $responseMessage->LookupCityResult->City);
        self::assertEquals('CA', $responseMessage->LookupCityResult->State);
        self::assertEquals(90210, $responseMessage->LookupCityResult->Zip);
    }

    /** @test */
    public function responseTypeMapFromXML(): void
    {
        self::markTestSkipped('Determine appropriate test parameters');
        $responseMessage = <<<EOD
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:s="http://www.w3.org/2001/XMLSchema">
   <SOAP-ENV:Body>
      <LookupCityResponse xmlns="http://tempuri.org">
         <LookupCityResult>
            <City>Beverly Hills</City>
            <State>CA</State>
            <Zip>90210</Zip>
         </LookupCityResult>
      </LookupCityResponse>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOD;

        $interpreter = new Interpreter(
            'https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1',
            [
                'typemap' => [
                    [
                        'type_name' => LookupCityResponse::class,
                        'type_ns' => 'https://www.crcind.com:443/csp/samples/SOAP.ByNameDataSet.cls?XSD',
                        'from_xml' => function($xml) {
                            $lookupResponse = new LookupCityResponse();
                            $lookupResponse->City = 'Beverly Hills';
                            $lookupResponse->State = 'CA';
                            $lookupResponse->Zip = 90210;
                            return $lookupResponse;
                        }
                    ]
                ]
            ]
        );

        $responseMessage = $interpreter->response($responseMessage, 'LookupCity');
        self::assertInstanceOf(LookupCityResponse::class, $responseMessage);
        self::assertEquals(['MockedResult' => 100], (array) $responseMessage);
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
        $interpreter = new Interpreter('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1', ['soap_version' => SOAP_1_2]);
        $request = $interpreter->request('LookupCity', [['zip' => 90210]]);
        self::assertEquals('https://www.crcind.com:443/csp/samples/SOAP.Demo.cls', $request->getEndpoint());
        self::assertEquals('http://tempuri.org/SOAP.Demo.LookupCity', $request->getSoapAction());
        self::assertEquals('2', $request->getSoapVersion());
        self::assertNotEmpty($request->getSoapMessage());
        self::assertStringContainsString('http://www.w3.org/2003/05/soap-envelope', $request->getSoapMessage());
        self::assertStringContainsString('LookupCity', $request->getSoapMessage());
        self::assertStringContainsString('zip', $request->getSoapMessage());
    }

    /** @test */
    public function responseWsdlSoapV12(): void
    {
        $responseMessage = <<<EOD
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:s="http://www.w3.org/2001/XMLSchema">
   <SOAP-ENV:Body>
      <LookupCityResponse xmlns="http://tempuri.org">
         <LookupCityResult>
            <City>Beverly Hills</City>
            <State>CA</State>
            <Zip>90210</Zip>
         </LookupCityResult>
      </LookupCityResponse>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOD;

        $interpreter = new Interpreter('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1', ['soap_version' => SOAP_1_2]);
        $responseMessage = $interpreter->response($responseMessage, 'LookupCity');
        self::assertEquals('Beverly Hills', $responseMessage->LookupCityResult->City);
        self::assertEquals('CA', $responseMessage->LookupCityResult->State);
        self::assertEquals(90210, $responseMessage->LookupCityResult->Zip);
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
class LookupCity
{

}

/** Test support only */
class LookupCityResponse
{

}
