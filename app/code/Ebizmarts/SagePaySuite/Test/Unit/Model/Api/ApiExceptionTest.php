<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ApiExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    private $apiExceptionModel;

    /**
     * @dataProvider getUserMessageDataProvider
     */
    public function testGetUserMessage($data)
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->apiExceptionModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Api\ApiException',
            [
                "phrase" => new \Magento\Framework\Phrase($data["message"]),
                "cause" => new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase($data["message"])
                ),
                "code" => $data["code"]
            ]
        );

        $this->assertEquals(
            $data["expected"],
            $this->apiExceptionModel->getUserMessage()
        );
    }

    public function getUserMessageDataProvider()
    {
        return [
            'test API_INVALID_IP' => [
                [
                    'message' => "INVALID",
                    'code' => \Ebizmarts\SagePaySuite\Model\Api\ApiException::API_INVALID_IP,
                    'expected' => "Information received from an invalid IP address."
                ]
            ],
            'test VALID_VALUE_REQUIRED' => [
                [
                    'message' => "vpstxid=12345",
                    'code' => \Ebizmarts\SagePaySuite\Model\Api\ApiException::VALID_VALUE_REQUIRED,
                    'expected' => "Transaction NOT found / Invalid transaction Id."
                ]
            ],
            'test VALID_VALUE_REQUIRED_USERNANE' => [
                [
                    'message' => "username=pedro",
                    'code' => \Ebizmarts\SagePaySuite\Model\Api\ApiException::VALID_VALUE_REQUIRED,
                    'expected' => "Invalid Opayo API credentials."
                ]
            ],
            'test VALID_VALUE_REQUIRED_DEFAULT' => [
                [
                    'message' => "x=y",
                    'code' => \Ebizmarts\SagePaySuite\Model\Api\ApiException::VALID_VALUE_REQUIRED,
                    'expected' => "x=y"
                ]
            ],
            'test INVALID_MERCHANT_AUTHENTICATION' => [
                [
                    'message' => "INVALID",
                    'code' => \Ebizmarts\SagePaySuite\Model\Api\ApiException::INVALID_MERCHANT_AUTHENTICATION,
                    'expected' => "Invalid merchant authentication."
                ]
            ],
            'test INVALID_USER_AUTH' => [
                [
                    'message' => "INVALID",
                    'code' => \Ebizmarts\SagePaySuite\Model\Api\ApiException::INVALID_USER_AUTH,
                    'expected' => "Your Opayo API user/password is invalid or the user might be locked out."
                ]
            ],
            'test INVALID_SIGNATURE' => [
                [
                    'message'  => "INVALID",
                    'code'     => \Ebizmarts\SagePaySuite\Model\Api\ApiException::INVALID_SIGNATURE,
                    'expected' => "Invalid signature. Please check Reporting API User and Password."
                ]
            ],
            'test default' => [
                [
                    'message' => "INVALID",
                    'code' => 1000,
                    'expected' => "INVALID"
                ]
            ]
        ];
    }
}
