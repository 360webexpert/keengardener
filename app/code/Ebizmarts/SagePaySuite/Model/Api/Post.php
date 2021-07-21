<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Helper\Request;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;

/**
 * Sage Pay Reporting API parent class
 */
class Post
{
    /**
     * @var ApiExceptionFactory
     */
    private $apiExceptionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuite\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var Request
     */
    private $suiteHelper;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\HttpText  */
    private $httpTextFactory;

    /**
     * Post constructor.
     * @param HttpTextFactory $httpTextFactory
     * @param ApiExceptionFactory $apiExceptionFactory
     * @param Config $config
     * @param Logger $suiteLogger
     * @param Request $suiteHelper
     */
    public function __construct(
        HttpTextFactory $httpTextFactory,
        ApiExceptionFactory $apiExceptionFactory,
        Config $config,
        Logger $suiteLogger,
        Request $suiteHelper
    ) {
        $this->config              = $config;
        $this->apiExceptionFactory = $apiExceptionFactory;
        $this->suiteLogger         = $suiteLogger;
        $this->suiteHelper         = $suiteHelper;
        $this->httpTextFactory     = $httpTextFactory;
    }

    private function handleApiErrors($response, $expectedStatus, $defaultErrorMessage)
    {
        $success = false;

        if (!empty($response) &&
            $response["status"] == 200 &&
            isset($response["data"]) &&
            isset($response["data"]["Status"])
        ) {
            $expectedStatusCnt = count($expectedStatus);
            //check against all possible success response statuses
            for ($i = 0; $i < $expectedStatusCnt; $i++) {
                if ($response["data"]["Status"] == $expectedStatus[$i]) {
                    $success = true;
                }
            }
        }

        if ($success == true) {
            return $response;
        } else {
            //there was an error
            $exceptionPhrase = $defaultErrorMessage;
            $exceptionCode = 0;

            if (!empty($response) &&
                isset($response["data"]) &&
                isset($response["data"]["StatusDetail"])
            ) {
                $detail = explode(":", $response["data"]["StatusDetail"]);

                if (count($detail) == 2) {
                    $exceptionCode = trim($detail[0]);
                    $exceptionPhrase = trim($detail[1]);
                } else {
                    $exceptionPhrase = trim($detail[0]);
                }
            }

            $exception = $this->apiExceptionFactory->create([
                'phrase' => __($exceptionPhrase),
                'code' => $exceptionCode
            ]);

            throw $exception;
        }
    }

    /**
     * @param $postData
     * @param $url
     * @param array $expectedStatus
     * @param string $errorMessage
     * @return mixed
     * @throws
     */
    public function sendPost($postData, $url, $expectedStatus = [], $errorMessage = "Invalid response from Opayo")
    {
        /** @var \Ebizmarts\SagePaySuite\Model\Api\HttpText $rest */
        $rest = $this->httpTextFactory->create();

        $body = $rest->arrayToQueryParams($postData);

        $rest->setUrl($url);
        $response = $rest->executePost($body);

        $responseData = [];
        if ($response->getStatus() == 200) {
            $responseData = $rest->rawResponseToArray();
        }

        $response = [
            "status" => $response->getStatus(),
            "data"   => $responseData
        ];

        return $this->handleApiErrors($response, $expectedStatus, $errorMessage);
    }
}
