<?php
/**
 * Copyright © 2019 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\OrderGridInfo;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use \Ebizmarts\SagePaySuite\Helper\AdditionalInformation;

class OrderGridInfoTest extends \PHPUnit\Framework\TestCase
{
    const ENTITY_ID = 1;
    const IMAGE_URL_TEST = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/test.png';
    const IMAGE_URL_CHECK = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-check.png';
    const IMAGE_URL_CROSS = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-cross.png';
    const IMAGE_URL_ZEBRA = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-zebra.png';
    const IMAGE_URL_OUTLINE = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-outline.png';
    const IMAGE_URL_NOTCHECKED = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-outline.png';
    const IMAGE_URL_INVALID = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-';
    const DATA_SOURCE = [
        'data' => [
            'items' => [
                [
                    'entity_id' => self::ENTITY_ID,
                    'payment_method' => "sagepaysuite"
                ]
            ]
        ]
    ];

    /**
     * @dataProvider columnProvider
     */
    public function testPrepareColumn($data)
    {
        $orderTest = $data["status"];

        $suiteLoggerMock = $this->createMock(Logger::class);
        $orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->expects($this->once())->method('isSecure')->willReturn(true);

        $assetRepositoryMock = $this->createMock(Repository::class);
        $assetRepositoryMock->expects($this->once())->method('getUrlWithParams')->with(
            $data["image"],
            [
                '_secure' => true
            ]
        )
            ->willReturn($data["image"]);

        $orderMock = $this->createMock(OrderInterface::class);
        $paymentMock = $this->createMock(OrderPaymentInterface::class);
        $orderRepositoryMock->expects($this->once())->method('get')->with(self::ENTITY_ID)->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getAdditionalInformation')->willReturn($orderTest);
        $serializeMock = $this->createMock(AdditionalInformation::class);

        $columnMock = $this->getMockBuilder(OrderGridInfo::class)
            ->setConstructorArgs([
                'requestInterface' => $requestMock,
                'serialize' => $serializeMock,
                'orderRepository' => $orderRepositoryMock,
                'suiteLogger' => $suiteLoggerMock,
                'assetRepository' => $assetRepositoryMock,
                [],
                []
            ])
            ->setMethods(["getImage"])
            ->getMock();

        $columnMock->expects($this->once())->method("getImage")->willReturn($data["image"]);

        $dataSource = self::DATA_SOURCE;

        $response = $columnMock->prepareColumn($dataSource, $data["index"], $data["fieldName"]);

        $expectedResponse = [
            'data' => [
                'items' => [
                    [
                        'entity_id' => self::ENTITY_ID,
                        $data["fieldName"] . "_src" => $data["image"],
                        'payment_method' => "sagepaysuite"
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedResponse, $response);
    }

    public function columnProvider()
    {
        return [
              "testThreeDSecureAuthenticated" => [
                  [
                      "image" => self::IMAGE_URL_CHECK,
                      "status" => ['threeDStatus' => 'AUTHENTICATED'],
                      "index" => "threeDStatus",
                      "fieldName" => "sagepay_threeDSecure"
                  ],
              "testThreeDSNotChecked" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['threeDStatus' => 'NOTCHECKED'],
                      "index" => "threeDStatus",
                      "fieldName" => "sagepay_threeDSecure"
                  ],
              "testThreeDSNotAuthenticated" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['threeDStatus' => 'NOTAUTHENTICATED'],
                      "index" => "threeDStatus",
                      "fieldName" => "sagepay_threeDSecure"
                  ],
              "testThreeDSError" =>
                  [
                      "image" => self::IMAGE_URL_CROSS,
                      "status" => ['threeDStatus' => 'ERROR'],
                      "index" => "threeDStatus",
                      "fieldName" => "sagepay_threeDSecure"
                  ],
              "testThreeDSCardNotEnrolled" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['threeDStatus' => 'CARDNOTENROLLED'],
                      "index" => "threeDStatus",
                      "fieldName" => "sagepay_threeDSecure"
                  ],
              "testThreeDSIssuerNotEnrolled" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['threeDStatus' => 'ISSUERNOTENROLLED'],
                      "index" => "threeDStatus",
                      "fieldName" => "sagepay_threeDSecure"
                  ],
              "testThreeDSMalformedOrInvalid" =>
                  [
                      "image" => self::IMAGE_URL_CROSS,
                      "status" => ['threeDStatus' => 'MALFORMEDORINVALID'],
                      "index" => "threeDStatus",
                      "fieldName" => "sagepay_threeDSecure"
                  ],
              "testThreeDSAttemptOnly" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['threeDStatus' => 'ATTEMPTONLY'],
                      "index" => "threeDStatus",
                      "fieldName" => "sagepay_threeDSecure"
                  ],
              "testThreeDSNotAvailable" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['threeDStatus' => 'NOTAVAILABLE'],
                      "index" => "threeDStatus",
                      "fieldName" => "sagepay_threeDSecure"
                  ],
              "testAddressValidationMatched" =>
                  [
                      "image" => self::IMAGE_URL_CHECK,
                      "status" => ['avsCvcCheckAddress' => 'MATCHED'],
                      "index" => "avsCvcCheckAddress",
                      "fieldName" => "sagepay_addressValidation"
                  ],
              "testAddressValidationNotChecked" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['avsCvcCheckAddress' => 'NOTCHECKED'],
                      "index" => "avsCvcCheckAddress",
                      "fieldName" => "sagepay_addressValidation"
                  ],
              "testAddressValidationNotProvided" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['avsCvcCheckAddress' => 'NOTPROVIDED'],
                      "index" => "avsCvcCheckAddress",
                      "fieldName" => "sagepay_addressValidation"
                  ],
              "testAddressValidationNotMatched" =>
                  [
                      "image" => self::IMAGE_URL_CROSS,
                      "status" => ['avsCvcCheckAddress' => 'NOTMATCHED'],
                      "index" => "avsCvcCheckAddress",
                      "fieldName" => "sagepay_addressValidation"
                  ],
              "testAddressValidationPartial" =>
                  [
                      "image" => self::IMAGE_URL_ZEBRA,
                      "status" => ['avsCvcCheckAddress' => 'PARTIAL'],
                      "index" => "avsCvcCheckAddress",
                      "fieldName" => "sagepay_addressValidation"
                  ],
              "testPostCodeCheckMatched" =>
                  [
                      "image" => self::IMAGE_URL_CHECK,
                      "status" => ['avsCvcCheckPostalCode' => 'MATCHED'],
                      "index" => "avsCvcCheckPostalCode",
                      "fieldName" => "sagepay_postcodeCheck"
                  ],
              "testPostCodeNotChecked" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['avsCvcCheckPostalCode' => 'NOTCHECKED'],
                      "index" => "avsCvcCheckPostalCode",
                      "fieldName" => "sagepay_postcodeCheck"
                  ],
              "testPostCodeNotProvided" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['avsCvcCheckPostalCode' => 'NOTPROVIDED'],
                      "index" => "avsCvcCheckPostalCode",
                      "fieldName" => "sagepay_postcodeCheck"
                  ],
              "testPostCodeNotMatched" =>
                  [
                      "image" => self::IMAGE_URL_CROSS,
                      "status" => ['avsCvcCheckPostalCode' => 'NOTMATCHED'],
                      "index" => "avsCvcCheckPostalCode",
                      "fieldName" => "sagepay_postcodeCheck"
                  ],
              "testPostCodePartial" =>
                  [
                      "image" => self::IMAGE_URL_ZEBRA,
                      "status" => ['avsCvcCheckPostalCode' => 'PARTIAL'],
                      "index" => "avsCvcCheckPostalCode",
                      "fieldName" => "sagepay_postcodeCheck"
                  ],
              "testCvTwoMatched" =>
                  [
                      "image" => self::IMAGE_URL_CHECK,
                      "status" => ['avsCvcCheckSecurityCode' => 'MATCHED'],
                      "index" => "avsCvcCheckSecurityCode",
                      "fieldName" => "sagepay_cvTwoCheck"
                  ],
              "testCvTwoNotChecked" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['avsCvcCheckSecurityCode' => 'NOTCHECKED'],
                      "index" => "avsCvcCheckSecurityCode",
                      "fieldName" => "sagepay_cvTwoCheck"
                  ],
              "testCvTwoNotProvided" =>
                  [
                      "image" => self::IMAGE_URL_OUTLINE,
                      "status" => ['avsCvcCheckSecurityCode' => 'NOTPROVIDED'],
                      "index" => "avsCvcCheckSecurityCode",
                      "fieldName" => "sagepay_cvTwoCheck"
                  ],
              "testCvTwoNotMatched" =>
                  [
                      "image" => self::IMAGE_URL_CROSS,
                      "status" => ['avsCvcCheckSecurityCode' => 'NOTMATCHED'],
                      "index" => "avsCvcCheckSecurityCode",
                      "fieldName" => "sagepay_cvTwoCheck"
                  ],
              "testCvTwoPartial" =>
                  [
                      "image" => self::IMAGE_URL_ZEBRA,
                      "status" => ['avsCvcCheckSecurityCode' => 'PARTIAL'],
                      "index" => "avsCvcCheckSecurityCode",
                      "fieldName" => "sagepay_cvTwoCheck"
                  ]
          ]
        ];
    }
}
