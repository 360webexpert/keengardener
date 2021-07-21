<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Test\Unit\Helper;

use Ebizmarts\SagePaySuite\Helper\AdditionalInformation;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AdditionalnformationTest extends \PHPUnit\Framework\TestCase
{
    const INVALID_JSON = 'not valid JSON';

    public function testGetUnserializedData()
    {
        $loggerMock = $this->makeLoggerMock();
        $loggerMock->expects($this->never())->method('logException');

        $serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);

        /** @var AdditionalInformation $additionalInformation */
        $additionalInformation = $objectManagerHelper->getObject(
            AdditionalInformation::class,
            [
                'serializer' => $serializerMock,
                'logger' => $loggerMock,
                []
            ]
        );

        $result = $additionalInformation->getUnserializedData('
        {"statusCode":"2007",
         "vendorTxCode":"000000014-2018-02-15-1537431518709063",
         "method_title":"Sage Pay Direct"}');

        $this->assertEquals([
            "statusCode" => "2007",
            "vendorTxCode" => "000000014-2018-02-15-1537431518709063",
            "method_title" => "Sage Pay Direct"
        ], $result);
    }

    private function makeLoggerMock()
    {
        return $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetUnserializedDataInvalidArgument()
    {
        $unserializeException = new \InvalidArgumentException('Unable to unserialize value.');

        $loggerMock = $this->makeLoggerMock();
        $loggerMock
            ->expects($this->once())
            ->method('logException')
        ->with(
            $unserializeException,
            [self::INVALID_JSON, 'Ebizmarts\SagePaySuite\Helper\AdditionalInformation::getUnserializedData', 37]
        );

        $serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['unserialize'])
            ->getMock();
        $serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->willThrowException($unserializeException);

        $objectManagerHelper = new ObjectManager($this);

        /** @var AdditionalInformation $additionalInformation */
        $additionalInformation = $objectManagerHelper->getObject(
            AdditionalInformation::class,
            [
                'serializer' => $serializerMock,
                'logger' => $loggerMock,
                []
            ]
        );

        $result = $additionalInformation->getUnserializedData(self::INVALID_JSON);

        $this->assertEquals([], $result);
    }
}
