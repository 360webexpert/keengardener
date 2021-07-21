<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Logger;

class LoggerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider sageLogDataProvider
     */
    public function testSageLog($data)
    {
        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->setMethods(['addRecord'])
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock
            ->expects($this->once())
            ->method('addRecord')
            ->with($data["type"], $data["message_p"] . "\r\n", $data['context'])
            ->willReturn(true);

        $this->assertTrue($loggerMock->sageLog($data["type"], $data["message"], $data['context']));
    }

    public function sageLogDataProvider()
    {
        return [
            'test null' => [
                [
                    'type'      => \Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST,
                    'message'   => null,
                    'message_p' => "NULL",
                    'context'   => ['Zarata', 34]
                ]
            ],
            'test string' => [
                [
                    'type'      => \Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST,
                    'message'   => "ERROR TEST",
                    'message_p' => "ERROR TEST",
                    'context'   => []
                ]
            ],
            'test array' => [
                [
                    'type'      => \Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST,
                    'message'   => ["error" => true],
                    'message_p' => json_encode(["error" => true], JSON_PRETTY_PRINT),
                    'context'   => []
                ]
            ],
            'test object' => [
                [
                    'type'      => \Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST,
                    'message'   => (object)["error" => true],
                    'message_p' => json_encode(((object)["error" => true]), JSON_PRETTY_PRINT),
                    'context'   => ['MyClass\\Test', 69]
                ]
            ]
        ];
    }

    public function testLogException()
    {
        $exceptionMock = $this
            ->getMockBuilder(\Exception::class)
            ->setMethods(['getMessage','getTraceAsString'])
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->setMethods(['addRecord'])
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock
            ->expects($this->once())
            ->method('addRecord')
            ->willReturn(true);

        $this->assertTrue($loggerMock->logException($exceptionMock, ['MyClass\\Response', 125]));
    }

    public function testInvalidMessage()
    {
        $loggerMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->setMethods(['addRecord'])
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock
            ->expects($this->once())
            ->method('addRecord')
            ->with('Request', "Type is not supported\r\n", [])
            ->willReturn(true);

        $obj = new \stdClass();
        $obj->resource = opendir('./'); // @codingStandardsIgnoreLine

        $this->assertTrue($loggerMock->sageLog('Request', $obj));
    }
}
