<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Api;

class HttpTextTest extends \PHPUnit\Framework\TestCase
{
    private $httpTextMock;

    protected function setUp()
    {
        $this->httpTextMock = $this->
        getMockBuilder(\Ebizmarts\SagePaySuite\Model\Api\HttpText::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResponseData'])
            ->getMock();
    }

    public function testParseResponseProvider1()
    {

        $string = "HTTP/1.1 200 OK
P3P: CP=\"CUR\"
Content-Language: en-GB
Content-Length: 150
Date: Fri, 30 Sep 2016 21:14:42 GMT
Server: undisclosed
Set-Cookie: NSC_WJQ-uftu.tbhfqbz.dpn-Kbwb7=ffffffff09ebb88e45525d5f4f58455e445a4a423660;path=/;secure;httponly

VPSProtocol=3.00
Status=INVALID
StatusDetail=4020 : Information received from an Invalid IP address.
VPSTxId={9B26DAD6-77FB-B4DB-DB52-FD500A89C05E}";

        $this->httpTextMock->expects($this->once())->method('getResponseData')->willReturn($string);

        $return = $this->httpTextMock->rawResponseToArray();

        $this->assertEquals(4, count($return));

        $this->assertArrayHasKey('VPSProtocol', $return);
        $this->assertArrayHasKey('Status', $return);
        $this->assertArrayHasKey('StatusDetail', $return);
        $this->assertArrayHasKey('VPSTxId', $return);

        $this->assertEquals($return['VPSProtocol'], "3.00");
        $this->assertEquals($return['Status'], "INVALID");
        $this->assertEquals($return['StatusDetail'], "4020 : Information received from an Invalid IP address.");
        $this->assertEquals($return['VPSTxId'], "{9B26DAD6-77FB-B4DB-DB52-FD500A89C05E}");
    }

    public function testParseResponseProvider2()
    {
        $string = "HTTP/1.1 200 OK
P3P: CP=\"CUR\"
Content-Language: en-GB
Content-Length: 88
Date: Fri, 30 Sep 2016 21:38:50 GMT
Server: undisclosed
Set-Cookie: NSC_WJQ-uftu.tbhfqbz.dpn-Kbwb7=ffffffff09ebb88e45525d5f4f58455e445a4a423660;path=/;secure;httponly

VPSProtocol=3.00
Status=INVALID
StatusDetail=3190 : The token value format is invalid.";

        $this->httpTextMock->expects($this->once())->method('getResponseData')->willReturn($string);

        $return = $this->httpTextMock->rawResponseToArray();

        $this->assertEquals(3, count($return));

        $this->assertArrayHasKey('VPSProtocol', $return);
        $this->assertArrayHasKey('Status', $return);
        $this->assertArrayHasKey('StatusDetail', $return);

        $this->assertEquals($return['VPSProtocol'], "3.00");
        $this->assertEquals($return['Status'], "INVALID");
        $this->assertEquals($return['StatusDetail'], "3190 : The token value format is invalid.");
    }
}
