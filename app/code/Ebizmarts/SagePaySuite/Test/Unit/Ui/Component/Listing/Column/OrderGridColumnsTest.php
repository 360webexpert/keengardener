<?php
/**
 * Copyright © 2019 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Ui\Component\Listing\Column;


use Ebizmarts\SagePaySuite\Ui\Component\Listing\Column\OrderGridColumns;

class OrderGridColumnsTest extends \PHPUnit\Framework\TestCase
{
    const IMAGE_URL_CHECK = 'Ebizmarts_SagePaySuite::images/icon-shield-check.png';
    const IMAGE_URL_CROSS = 'Ebizmarts_SagePaySuite::images/icon-shield-cross.png';
    const IMAGE_URL_OUTLINE = 'Ebizmarts_SagePaySuite::images/icon-shield-outline.png';
    const IMAGE_URL_ZEBRA = 'Ebizmarts_SagePaySuite::images/icon-shield-zebra.png';

    public function testGetImageThreeDS()
    {
        $additional = ['3DSecureStatus' => 'AUTHENTICATED'];
        $index = '3DSecureStatus';
        $status = 'AUTHENTICATED';

        $orderGridColumnsMock = $this->getMockBuilder(OrderGridColumns::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus','getThreeDStatus'])
            ->getMock();

        $orderGridColumnsMock
            ->expects($this->once())
            ->method('getStatus')
            ->with($additional, $index)
            ->willReturn($status);

        $orderGridColumnsMock
            ->expects($this->once())
            ->method('getThreeDStatus')
            ->with($status)
            ->willReturn(self::IMAGE_URL_CHECK);

        $this->assertEquals(self::IMAGE_URL_CHECK, $orderGridColumnsMock->getImage($additional, $index));
    }

    public function testGetImageColumns()
    {
        $additional = ['PostCodeResult' => 'MATCHED'];
        $index = 'PostCodeResult';
        $status = 'MATCHED';

        $orderGridColumnsMock = $this->getMockBuilder(OrderGridColumns::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus','getStatusImage'])
            ->getMock();

        $orderGridColumnsMock
            ->expects($this->once())
            ->method('getStatus')
            ->with($additional, $index)
            ->willReturn($status);

        $orderGridColumnsMock
            ->expects($this->once())
            ->method('getStatusImage')
            ->with($status)
            ->willReturn(self::IMAGE_URL_CHECK);

        $this->assertEquals(self::IMAGE_URL_CHECK, $orderGridColumnsMock->getImage($additional, $index));
    }

    /**
     * @dataProvider threeDSProvider
     */
    public function testGetThreeDStatus($data)
    {
        $orderGridColumnsMock = $this->getMockBuilder(OrderGridColumns::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getThreeDStatus'])
            ->getMock();

        $result = $orderGridColumnsMock->getThreeDStatus($data["status"]);

        $this->assertEquals($data["image"], $result);
    }

    /**
     * @dataProvider statusProvider
     */
    public function testGetStatusImage($data)
    {
        $orderGridColumnsMock = $this->getMockBuilder(OrderGridColumns::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getStatusImage'])
            ->getMock();

        $result = $orderGridColumnsMock->getStatusImage($data["status"]);

        $this->assertEquals($data["image"], $result);
    }

    public function threeDSProvider()
    {
        return [
                "testThreeDSAuthenticated" => [
                [
                    "status" => "AUTHENTICATED",
                    "image" => self::IMAGE_URL_CHECK
                ],
                "testThreeDSNotChecked" =>
                [
                    "status" => "NOTCHECKED",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testThreeDSNotAuthenticated" =>
                [
                    "status" => "NOTAUTHENTICATED",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testThreeDSCardNotEnrolled" =>
                [
                    "status" => "CARDNOTENROLLED",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testThreeDSIssuerNotEnrolled" =>
                [
                    "status" => "ISSUERNOTENROLLED",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testThreeDSAttemptOnly" =>
                [
                    "status" => "ATTEMPTONLY",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testThreeDSNotAvailable" =>
                [
                    "status" => "NOTAVAILABLE",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testThreeDSIncomplete" =>
                [
                    "status" => "INCOMPLETE",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testThreeDSDefault" =>
                [
                    "status" => "",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testThreeDSError" =>
                [
                    "status" => "ERROR",
                    "image" => self::IMAGE_URL_CROSS
                ],
                "testThreeDSMalformedOrInvalid" =>
                [
                    "status" => "MALFORMEDORINVALID",
                    "image" => self::IMAGE_URL_CROSS
                ],

            ]
        ];

    }

    public function statusProvider()
    {
        return [

                "testStatusMatched" => [
                [
                    "status" => "MATCHED",
                    "image" => self::IMAGE_URL_CHECK
                ],
                "testStatusNotChecked" =>
                [
                    "status" => "NOTCHECKED",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testStatusNotProvided" =>
                [
                    "status" => "NOTPROVIDED",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testStatusDefault" =>
                [
                    "status" => "",
                    "image" => self::IMAGE_URL_OUTLINE
                ],
                "testStatusNotMatched" =>
                [
                    "status" => "NOTMATCHED",
                    "image" => self::IMAGE_URL_CROSS
                ],
                "testStatusPartial" =>
                [
                    "status" => "PARTIAL",
                    "image" => self::IMAGE_URL_ZEBRA
                ]
            ]
        ];

    }
}
