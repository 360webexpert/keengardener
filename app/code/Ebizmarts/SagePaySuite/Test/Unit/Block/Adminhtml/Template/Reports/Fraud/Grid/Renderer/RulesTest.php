<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RulesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer\Rules
     */
    private $rulesRendererBlock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        $columnMock = $this
            ->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this
            ->getMockBuilder('\Magento\Backend\Block\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock();

        $loggerMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Logger\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $additionalInformation = $objectManagerHelper
            ->getObject(\Ebizmarts\SagePaySuite\Helper\AdditionalInformation::class,
                [
                    'serializer' => $serializerMock,
                    'logger' => $loggerMock
                ]
            );

        $this->rulesRendererBlock = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer\Rules',
            [
                'context' => $contextMock,
                'information' => $additionalInformation,
                []
            ]
        );

        $this->rulesRendererBlock->setColumn($columnMock);
    }
    // @codingStandardsIgnoreEnd

    public function testRenderEmpty()
    {
        $rowMock = $this
            ->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();
        $rowMock->expects($this->once())
            ->method('getData')
            ->with('additional_information')
            ->willReturn("");

        $this->assertEquals(
            '',
            $this->rulesRendererBlock->render($rowMock)
        );
    }

    public function testRenderNotEmpty()
    {
        $rowMock = $this
            ->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();
        $rowMock->expects($this->once())
            ->method('getData')
            ->with('additional_information')
            ->willReturn('{"fraudrules":"Sage Pay Direct","statusCode":"0000"}');

        $this->assertEquals(
            "Sage Pay Direct",
            $this->rulesRendererBlock->render($rowMock)
        );
    }

    public function testRenderNotEmptyArray()
    {
        $rowMock = $this
            ->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();
        $rowMock->expects($this->once())
            ->method('getData')
            ->with('additional_information')
            ->willReturn('{"cc_last4":null,"merchant_session_key":null,"card_identifier":null,"statusCode":"2007","statusDetail":"Please redirect your customer to the ACSURL to complete the 3DS Transaction","moto":false,"vendorname":"activeplus","mode":"test","paymentAction":"Deferred","bankAuthCode":null,"txAuthNo":null,"vendorTxCode":"000000000-2018-12-14-0000000000000000","method_title":"Sage Pay Direct","fraudrules":[{"description":"Delivery surname is within the email address","score":"-11"},{"description":"Telephone number is a landline","score":"-3"},{"description":"Delivery address or email domain is a business","score":"-10"},{"description":"Card verification code passed [Amount less than 1000000]","score":"-10"},{"description":"Bank address check match [Amount less than 1000000]","score":"-6"},{"description":"Bank Postcode check Match [Amount less than 1000000]","score":"-6"},{"description":"Number of purchases at delivery address exceeds lower threshold [More than 2 purchases at the delivery address in the last 14 days]","score":"2"},{"description":"Number of purchases at delivery address exceeds medium threshold [More than 4 purchases at the delivery address in the last 14 days]","score":"3"},{"description":"Number of purchases at delivery address exceeds higher threshold [More than 6 purchases at the delivery address in the last 14 days]","score":"3"},{"description":"Recent spend at delivery address exceeds lower threshold [More than 1 purchases at the delivery address in the last 30 days with a total spend of greater than 50000]","score":"5"},{"description":"Recent spend at delivery address exceeds medium threshold [More than 1 purchases at the delivery address in the last 30 days with a total spend of greater than 100000]","score":"5"},{"description":"Number of purchases at billing address exceeds lower threshold [More than 1 purchases at the billing address in the last 14 days]","score":"2"},{"description":"Recent spend at billing address exceeds lower threshold [More than 1 purchases at the billing address in the last 30 days with a total spend greater than 50000]","score":"5"}]}');

        $rendered = $this->rulesRendererBlock->render($rowMock);

        $this->assertEquals('<ul><li>Delivery surname is within the email address <strong>(score: -11)</strong></li><li>Telephone number is a landline <strong>(score: -3)</strong></li><li>Delivery address or email domain is a business <strong>(score: -10)</strong></li><li>Card verification code passed [Amount less than 1000000] <strong>(score: -10)</strong></li><li>Bank address check match [Amount less than 1000000] <strong>(score: -6)</strong></li><li>Bank Postcode check Match [Amount less than 1000000] <strong>(score: -6)</strong></li><li>Number of purchases at delivery address exceeds lower threshold [More than 2 purchases at the delivery address in the last 14 days] <strong>(score: 2)</strong></li><li>Number of purchases at delivery address exceeds medium threshold [More than 4 purchases at the delivery address in the last 14 days] <strong>(score: 3)</strong></li><li>Number of purchases at delivery address exceeds higher threshold [More than 6 purchases at the delivery address in the last 14 days] <strong>(score: 3)</strong></li><li>Recent spend at delivery address exceeds lower threshold [More than 1 purchases at the delivery address in the last 30 days with a total spend of greater than 50000] <strong>(score: 5)</strong></li><li>Recent spend at delivery address exceeds medium threshold [More than 1 purchases at the delivery address in the last 30 days with a total spend of greater than 100000] <strong>(score: 5)</strong></li><li>Number of purchases at billing address exceeds lower threshold [More than 1 purchases at the billing address in the last 14 days] <strong>(score: 2)</strong></li><li>Recent spend at billing address exceeds lower threshold [More than 1 purchases at the billing address in the last 30 days with a total spend greater than 50000] <strong>(score: 5)</strong></li></ul>', $rendered);
    }
}
