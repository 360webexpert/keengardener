<?php
/**
 * Copyright © 2019 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Ui\Component\Listing\Column;

use Ebizmarts\SagePaySuite\Ui\Component\Listing\Column\Fraud;
use Ebizmarts\SagePaySuite\Ui\Component\Listing\Column\FraudColumn;
use Ebizmarts\SagePaySuite\Ui\Component\Listing\Column\ThreeDSecure;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class FraudTest extends \PHPUnit\Framework\TestCase
{
    const FIELD_NAME = "sagepay_fraud";
    const INDEX = "fraudcode";
    const ENTITY_ID = 1;
    const IMAGE_URL_CHECK = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-check.png';
    const IMAGE_URL_CROSS = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-cross.png';
    const IMAGE_URL_OUTLINE = 'https://example.com/adminhtml/Magento/backend/en_US/Ebizmarts_SagePaySuite/images/icon-shield-outline.png';
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

    public function testPrepareDataSource()
    {
        $contextMock = $this->createMock(ContextInterface::class);
        $uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
        $fraudColumnMock = $this
            ->getMockBuilder(FraudColumn::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expectedResponse = [
            'data' => [
                'items' => [
                    [
                        'entity_id' => self::ENTITY_ID,
                        self::FIELD_NAME . '_src' => self::IMAGE_URL_CHECK,
                        'payment_method' => "sagepaysuite"
                    ]
                ]
            ]
        ];

        $fraudColumnMock
            ->expects($this->once())
            ->method('prepareColumn')
            ->with(self::DATA_SOURCE, self::INDEX, self::FIELD_NAME)
            ->willReturn($expectedResponse);

        $fraudMock = $this->getMockBuilder(Fraud::class)
            ->setConstructorArgs([
                'fraudColumn' => $fraudColumnMock,
                'context' => $contextMock,
                'uiComponentFactory' => $uiComponentFactoryMock,
                [],
                []
            ])
            ->setMethods(['getFieldName'])
            ->getMock();

        $fraudMock
            ->expects($this->once())
            ->method('getFieldName')
            ->willReturn(self::FIELD_NAME);

        $response = $fraudMock->prepareDataSource(self::DATA_SOURCE);

        $this->assertEquals($expectedResponse, $response);
    }

}
