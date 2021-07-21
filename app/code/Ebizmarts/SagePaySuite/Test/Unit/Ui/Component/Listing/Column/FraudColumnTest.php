<?php
/**
 * Copyright © 2019 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Ui\Component\Listing\Column;

use Ebizmarts\SagePaySuite\Ui\Component\Listing\Column\FraudColumn;

class FraudColumnTest extends \PHPUnit\Framework\TestCase
{
    const INDEX = "fraudcode";
    const IMAGE_URL_TEST = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/test.png';
    const IMAGE_URL_CHECK = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-check.png';
    const IMAGE_URL_CROSS = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-cross.png';
    const IMAGE_URL_OUTLINE = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-outline.png';
    const IMAGE_URL_ZEBRA = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-zebra.png';
    const IMAGE_URL_WAITING = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/waiting.png';

    public function testGetImageTest()
    {
        $orderTest = ['mode' => 'test'];

        $fraudColumnMock = $this->getMockBuilder(FraudColumn::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkTestModeConfiguration', 'getTestImage'])
            ->getMock();

        $fraudColumnMock
            ->expects($this->once())
            ->method('checkTestModeConfiguration')
            ->with($orderTest)
            ->willReturn(true);

        $fraudColumnMock
            ->expects($this->once())
            ->method('getTestImage')
            ->willReturn(self::IMAGE_URL_TEST);


        $this->assertEquals(self::IMAGE_URL_TEST, $fraudColumnMock->getImage($orderTest, self::INDEX));
    }

    public function testGetImageLive()
    {
        $orderTest = ['mode' => 'live'];

        $fraudColumnMock = $this->getMockBuilder(FraudColumn::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkTestModeConfiguration', 'getFraudImage'])
            ->getMock();

        $fraudColumnMock
            ->expects($this->once())
            ->method('checkTestModeConfiguration')
            ->with($orderTest)
            ->willReturn(false);

        $fraudColumnMock
            ->expects($this->once())
            ->method('getFraudImage')
            ->willReturn(self::IMAGE_URL_CHECK);

        $this->assertEquals(self::IMAGE_URL_CHECK, $fraudColumnMock->getImage($orderTest, self::INDEX));
    }

    /**
     * @dataProvider fraudCodeProvider
     */
    public function testGetFraudImage($data)
    {
        $additional = $data["fraudcode"];
        $fraudColumnMock = $this->getMockBuilder(FraudColumn::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkIfThirdMan', 'getImageNameThirdman', 'checkIfRed', 'getImageNameRed', 'getWaitingImage'])
            ->getMock();

        $fraudColumnMock
            ->expects($this->once())
            ->method('checkIfThirdMan')
            ->with($additional)
            ->willReturn($data["checkIfThirdMan"]);

        $fraudColumnMock
            ->expects($this->exactly($data["expectedGetImageNameThirdman"]))
            ->method('getImageNameThirdman')
            ->with($additional["fraudcode"])
            ->willReturn($data["image"]);

        $fraudColumnMock
            ->expects($this->exactly($data["expectedCheckIfRed"]))
            ->method('checkIfRed')
            ->with($additional)
            ->willReturn($data["checkIfRed"]);

        $fraudColumnMock
            ->expects($this->exactly($data["expectedGetImageNameRed"]))
            ->method('getImageNameRed')
            ->with($additional)
            ->willReturn($data["image"]);

        $fraudColumnMock
            ->expects($this->exactly($data["expectedGetWaitingImage"]))
            ->method('getWaitingImage')
            ->willReturn($data["image"]);

        $this->assertEquals($data["image"], $fraudColumnMock->getFraudImage($additional, self::INDEX));

    }

    public function fraudCodeProvider()
    {
        return [
            "testThirdManCheck" => [
                [
                    "fraudcode" => ['fraudcode' => '20'],
                    "image" => self::IMAGE_URL_CHECK,
                    "checkIfThirdMan" => true,
                    "checkIfRed" => false,
                    "expectedGetImageNameThirdman" => 1,
                    "expectedCheckIfRed" => 0,
                    "expectedGetImageNameRed" => 0,
                    "expectedGetWaitingImage" => 0

                ],
                "testThirdManZebra" =>
                [
                    "fraudcode" => ['fraudcode' => '40'],
                    "image" => self::IMAGE_URL_ZEBRA,
                    "checkIfThirdMan" => true,
                    "checkIfRed" => false,
                    "expectedGetImageNameThirdman" => 1,
                    "expectedCheckIfRed" => 0,
                    "expectedGetImageNameRed" => 0,
                    "expectedGetWaitingImage" => 0
                ],
                "testThirManCross" =>
                [
                    "fraudcode" => ['fraudcode' => '50'],
                    "image" => self::IMAGE_URL_CROSS,
                    "checkIfThirdMan" => true,
                    "checkIfRed" => false,
                    "expectedGetImageNameThirdman" => 1,
                    "expectedCheckIfRed" => 0,
                    "expectedGetImageNameRed" => 0,
                    "expectedGetWaitingImage" => 0
                ],
                "testRedAccept" =>
                [
                    "fraudcode" => ['fraudcode' => 'ACCEPT'],
                    "image" => self::IMAGE_URL_CHECK,
                    "checkIfThirdMan" => false,
                    "checkIfRed" => true,
                    "expectedGetImageNameThirdman" => 0,
                    "expectedCheckIfRed" => 1,
                    "expectedGetImageNameRed" => 1,
                    "expectedGetWaitingImage" => 0
                ],
                "testRedDeny" =>
                [
                    "fraudcode" => ['fraudcode' => 'DENY'],
                    "image" => self::IMAGE_URL_CROSS,
                    "checkIfThirdMan" => false,
                    "checkIfRed" => true,
                    "expectedGetImageNameThirdman" => 0,
                    "expectedCheckIfRed" => 1,
                    "expectedGetImageNameRed" => 1,
                    "expectedGetWaitingImage" => 0
                ],
                "testRedChallenge" =>
                [
                    "fraudcode" => ['fraudcode' => 'CHALLENGE'],
                    "image" => self::IMAGE_URL_ZEBRA,
                    "checkIfThirdMan" => false,
                    "checkIfRed" => true,
                    "expectedGetImageNameThirdman" => 0,
                    "expectedCheckIfRed" => 1,
                    "expectedGetImageNameRed" => 1,
                    "expectedGetWaitingImage" => 0
                ],
                "testRedNotChecked" =>
                [
                    "fraudcode" => ['fraudcode' => 'NOTCHECKED'],
                    "image" => self::IMAGE_URL_OUTLINE,
                    "checkIfThirdMan" => false,
                    "checkIfRed" => true,
                    "expectedGetImageNameThirdman" => 0,
                    "expectedCheckIfRed" => 1,
                    "expectedGetImageNameRed" => 1,
                    "expectedGetWaitingImage" => 0
                ],
                "testWaitingImage" =>
                [
                    "fraudcode" => ['fraudcode' => ''],
                    "image" => self::IMAGE_URL_WAITING,
                    "checkIfThirdMan" => false,
                    "checkIfRed" => false,
                    "expectedGetImageNameThirdman" => 0,
                    "expectedCheckIfRed" => 0,
                    "expectedGetImageNameRed" => 0,
                    "expectedGetWaitingImage" => 1
                ]
            ]
        ];
    }

}
