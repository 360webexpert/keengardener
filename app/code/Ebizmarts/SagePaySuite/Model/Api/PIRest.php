<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionRequestFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionResponse;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionResponseFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyRequestFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyResponseFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiRefundRequestFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiRepeatRequest;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiRepeatRequestFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiThreeDSecureRequestFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiThreeDSecureV2RequestFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAmountFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheck;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheckFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultCardFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultPaymentMethodFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeD;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeDFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeDInterface;
use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Store\Model\ScopeInterface;
use function property_exists;

/**
 * Sage Pay PI REST API
 *
 * @see https://live.sagepay.com/documentation/
 */
class PIRest
{
    const ACTION_GENERATE_MERCHANT_KEY    = 'merchant-session-keys';
    const ACTION_TRANSACTIONS             = 'transactions';
    const ACTION_TRANSACTION_INSTRUCTIONS = 'transactions/%s/instructions';
    const ACTION_SUBMIT_3D                = '3d-secure';
    const ACTION_SUBMIT_3Dv2              = '3d-secure-challenge';
    const ACTION_TRANSACTION_DETAILS      = 'transaction_details';

    /** @var Config */
    private $config;

    /** @var ApiExceptionFactory */
    private $apiExceptionFactory;

    /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface */
    private $piCaptureResultFactory;

    /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultPaymentMethodInterface */
    private $paymentMethodResultFactory;

    /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultCardInterface */
    private $cardResultFactory;

    /** @var PiTransactionResultThreeDInterface */
    private $threedStatusResultFactory;

    /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheck */
    private $avsCvcCheckResultFactory;

    /** @var PiTransactionResultAmountFactory */
    private $amountResultFactory;

    /** @var PiMerchantSessionKeyResponseFactory */
    private $mskResponse;

    /** @var PiMerchantSessionKeyRequestFactory */
    private $mskRequest;

    /** @var PiThreeDSecureRequestFactory */
    private $threedRequest;

    /** @var PiThreeDSecureV2RequestFactory */
    private $threedv2Request;

    /** @var PiRefundRequestFactory */
    private $refundRequest;

    /** @var PiInstructionRequestFactory */
    private $instructionRequest;

    /** @var PiInstructionResponseFactory */
    private $instructionResponse;

    /** @var HttpRestFactory */
    private $httpRestFactory;

    /** @var PiRepeatRequest */
    private $repeatRequestFactory;

    /**
     * PIRest constructor.
     * @param HttpRestFactory $httpRestFactory
     * @param Config $config
     * @param ApiExceptionFactory $apiExceptionFactory
     * @param PiTransactionResultFactory $piCaptureResultFactory
     * @param PiTransactionResultPaymentMethodFactory $paymentMethodResultFactory
     * @param PiTransactionResultCardFactory $cardResultFactory
     * @param PiTransactionResultThreeDFactory $threedResultFactory
     * @param PiTransactionResultAmountFactory $amountResultFactory
     * @param PiMerchantSessionKeyResponseFactory $mskResponse
     * @param PiMerchantSessionKeyRequestFactory $mskRequest
     * @param PiThreeDSecureRequestFactory $threeDRequest
     * @param PiRefundRequestFactory $refundRequest
     * @param PiInstructionRequestFactory $instructionRequest
     * @param PiInstructionResponseFactory $instructionResponse
     */
    public function __construct(
        HttpRestFactory $httpRestFactory,
        Config $config,
        ApiExceptionFactory $apiExceptionFactory,
        PiTransactionResultFactory $piCaptureResultFactory,
        PiTransactionResultPaymentMethodFactory $paymentMethodResultFactory,
        PiTransactionResultCardFactory $cardResultFactory,
        PiTransactionResultThreeDFactory $threedResultFactory,
        PiTransactionResultAmountFactory $amountResultFactory,
        PiMerchantSessionKeyResponseFactory $mskResponse,
        PiMerchantSessionKeyRequestFactory $mskRequest,
        PiThreeDSecureRequestFactory $threeDRequest,
        PiThreeDSecureV2RequestFactory $threeDV2Request,
        PiRefundRequestFactory $refundRequest,
        PiInstructionRequestFactory $instructionRequest,
        PiInstructionResponseFactory $instructionResponse,
        PiRepeatRequestFactory $repeatRequest,
        PiTransactionResultAvsCvcCheckFactory $avsCvcCheckResultFactory
    ) {
        $this->config = $config;
        $this->config->setMethodCode(Config::METHOD_PI);
        $this->apiExceptionFactory        = $apiExceptionFactory;
        $this->piCaptureResultFactory     = $piCaptureResultFactory;
        $this->paymentMethodResultFactory = $paymentMethodResultFactory;
        $this->cardResultFactory          = $cardResultFactory;
        $this->threedStatusResultFactory  = $threedResultFactory;
        $this->amountResultFactory        = $amountResultFactory;
        $this->mskResponse                = $mskResponse;
        $this->mskRequest                 = $mskRequest;
        $this->threedRequest              = $threeDRequest;
        $this->threedv2Request            = $threeDV2Request;
        $this->refundRequest              = $refundRequest;
        $this->instructionRequest         = $instructionRequest;
        $this->instructionResponse        = $instructionResponse;
        $this->httpRestFactory            = $httpRestFactory;
        $this->repeatRequestFactory       = $repeatRequest;
        $this->avsCvcCheckResultFactory   = $avsCvcCheckResultFactory;
    }

    /**
     * Makes the Curl POST
     *
     * @param $url
     * @param $body
     * @return \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface
     */
    private function executePostRequest($url, $body)
    {
        /** @var \Ebizmarts\SagePaySuite\Model\Api\HttpRest $rest */
        $rest = $this->httpRestFactory->create();
        $rest->getLogger()->sageLog(\Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST, $url, [__METHOD__, __LINE__]);
        $rest->setBasicAuth($this->config->getPIKey(), $this->config->getPIPassword());
        $rest->setUrl($url);
        $response = $rest->executePost($body);

        return $response;
    }

    /**
     * Makes the Curl GET
     *
     * @param $url
     * @return \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface
     */
    private function executeRequest($url)
    {
        /** @var \Ebizmarts\SagePaySuite\Model\Api\HttpRest $rest */
        $rest = $this->httpRestFactory->create();
        $rest->setBasicAuth($this->config->getPIKey(), $this->config->getPIPassword());
        $rest->setUrl($url);
        $response = $rest->executeGet();
        return $response;
    }

    /**
     * Returns url for each enviroment according the configuration.
     *
     * @param $action
     * @param null $vpsTxId
     * @return string
     */
    private function getServiceUrl($action, $vpsTxId = null)
    {
        switch ($action) {
            case self::ACTION_TRANSACTION_DETAILS:
                $endpoint = "transactions/$vpsTxId";
                break;
            case self::ACTION_SUBMIT_3D:
            case self::ACTION_SUBMIT_3Dv2:
                $endpoint = "transactions/$vpsTxId/$action";
                break;
            case self::ACTION_TRANSACTION_INSTRUCTIONS:
                $endpoint = sprintf(self::ACTION_TRANSACTION_INSTRUCTIONS, $vpsTxId);
                break;
            default:
                $endpoint = $action;
                break;
        }

        if ($this->config->getMode() == Config::MODE_LIVE) {
            return Config::URL_PI_API_LIVE . $endpoint;
        } elseif ($this->config->getMode() == Config::MODE_DEVELOPMENT) {
            return Config::URL_PI_API_DEV . $endpoint;
        } else {
            return Config::URL_PI_API_TEST . $endpoint;
        }
    }

    /**
     * Make POST request to ask for merchant key
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyResponseInterface
     * @throws
     */
    public function generateMerchantKey(\Magento\Quote\Model\Quote $quote)
    {
        $this->config->setConfigurationScopeId($quote->getStoreId());
        $this->config->setConfigurationScope(ScopeInterface::SCOPE_STORE);

        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyRequest $request */
        $request = $this->mskRequest->create();

        $request->setVendorName($this->config->getVendorname());

        $jsonBody = json_encode($request->__toArray());
        $url      = $this->getServiceUrl(self::ACTION_GENERATE_MERCHANT_KEY);
        $result   = $this->executePostRequest($url, $jsonBody);

        $resultData = $this->processResponse($result);

        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiMerchantSessionKeyResponse $response */
        $response = $this->mskResponse->create();
        $response->setExpiry($resultData->expiry);
        $response->setMerchantSessionKey($resultData->merchantSessionKey);

        return $response;
    }

    /**
     * Make capture payment request
     *
     * @param $paymentRequest
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface
     * @throws ApiException
     */
    public function capture($paymentRequest)
    {
        $jsonRequest   = json_encode($paymentRequest);
        $result        = $this->executePostRequest($this->getServiceUrl(self::ACTION_TRANSACTIONS), $jsonRequest);
        $captureResult = $this->processResponse($result);

        return $this->getTransactionDetailsObject($captureResult);
    }

    /**
     * Submit 3D result via POST
     *
     * @param string $paRes
     * @param string $vpsTxId
     * @return PiTransactionResultThreeD
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    public function submit3D($paRes, $vpsTxId)
    {
        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiThreeDSecureRequest $request */
        $request = $this->threedRequest->create();
        $request->setParEs($paRes);

        $resultData = $this->threeDSecureSubmit($request, self::ACTION_SUBMIT_3D, $vpsTxId);

        $this->validateThreeDSubmit($resultData);

        return $this->threeDSecureSubmitSetResultStatus($resultData);
    }

    /**
     * Submit 3D result via POST
     *
     * @param string $paRes
     * @param string $vpsTxId
     * @return PiTransactionResultThreeD
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    public function submit3Dv2($cRes, $vpsTxId)
    {
        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiThreeDSecureV2Request $request */
        $request = $this->threedv2Request->create();
        $request->setCres($cRes);

        $resultData = $this->threeDSecureSubmit($request, self::ACTION_SUBMIT_3Dv2, $vpsTxId);

        $this->validateThreeDSubmit($resultData);

        return $this->threeDSecureSubmitSetResultStatus($resultData);
    }

    private function threeDSecureSubmit(\Magento\Framework\Api\AbstractExtensibleObject $request, $url, $vpsTxId)
    {
        $jsonBody   = json_encode($request->__toArray());
        $result     = $this->executePostRequest($this->getServiceUrl($url, $vpsTxId), $jsonBody);
        return $this->processResponse($result);
    }

    /**
     * @param \stdClass $resultData
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    private function validateThreeDSubmit(\stdClass $resultData)
    {
        if (!isset($resultData->status)) {
            if (empty($resultData)) {
                $resultData->status = "Operation not allowed";
            } else {
                throw new ApiException(__('Invalid 3D secure response.'));
            }
        }
    }

    /**
     * @param string $resultData
     * @return PiTransactionResultThreeDInterface
     */
    private function threeDSecureSubmitSetResultStatus(\stdClass $resultData): PiTransactionResultThreeDInterface
    {
        $rest = $this->httpRestFactory->create();
        $rest->getLogger()->sageLog(\Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST, 'threeDSecureSubmitSetResultStatus', [__METHOD__, __LINE__]);
        $rest->getLogger()->sageLog(\Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST, json_encode($resultData), [__METHOD__, __LINE__]);

        /** @var PiTransactionResultThreeD $response */
        $response = $this->threedStatusResultFactory->create();
        $response->setStatus($resultData->status);

        return $response;
    }

    /**
     * @param $vendorTxCode
     * @param $refTransactionId
     * @param $amount
     * @param $currency
     * @param $description
     * @param $storeId
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface
     */
    public function refund($vendorTxCode, $refTransactionId, $amount, $description, $storeId)
    {
        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiRefundRequest $refundRequest */
        $refundRequest = $this->refundRequest->create();
        $refundRequest->setTransactionType();
        $refundRequest->setVendorTxCode($vendorTxCode);
        $refundRequest->setReferenceTransactionId($refTransactionId);
        $refundRequest->setAmount($amount);
        $refundRequest->setDescription($description);
        $this->config->setConfigurationScopeId($storeId);
        $jsonRequest = json_encode($refundRequest->__toArray());
        $result      = $this->executePostRequest($this->getServiceUrl(self::ACTION_TRANSACTIONS), $jsonRequest);

        return $this->getTransactionDetailsObject($this->processResponse($result));
    }

    /**
     * @param $transactionId
     * @return PiInstructionResponse
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    public function void($transactionId)
    {
        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionRequest $request */
        $request = $this->instructionRequest->create();
        $request->setInstructionType('void');

        $jsonRequest = json_encode($request->__toArray());
        $result = $this->executePostRequest(
            $this->getServiceUrl(self::ACTION_TRANSACTION_INSTRUCTIONS, $transactionId),
            $jsonRequest
        );

        return $this->processInstructionsResponse($result);
    }

    /**
     * @param $transactionId
     * @return PiInstructionResponse
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    public function abort($transactionId)
    {
        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionRequest $request */
        $request = $this->instructionRequest->create();
        $request->setInstructionType('abort');

        $jsonRequest = json_encode($request->__toArray());
        $result = $this->executePostRequest(
            $this->getServiceUrl(self::ACTION_TRANSACTION_INSTRUCTIONS, $transactionId),
            $jsonRequest
        );

        return $this->processInstructionsResponse($result);
    }

    /**
     * Make release request.
     *
     * @param string $transactionId
     * @param $amount
     * @param string $vpsProtocol
     * @return PiInstructionResponse
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    public function release(string $transactionId, $amount)
    {
        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionRequest $request */
        $request = $this->instructionRequest->create();
        $request->setInstructionType('release');
        $request->setAmount($amount * 100);

        $jsonRequest = json_encode($request->__toArray());
        $result = $this->executePostRequest(
            $this->getServiceUrl(self::ACTION_TRANSACTION_INSTRUCTIONS, $transactionId),
            $jsonRequest
        );

        return $this->processInstructionsResponse($result);
    }

    /**
     * @param $vendorTxCode
     * @param $refTransactionId
     * @param $amount
     * @param $currency
     * @param $description
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface
     */
    public function repeat(
        string $vendorTxCode,
        string $refTransactionId,
        string $currency,
        int $amount,
        string $description
    ) {
        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiRepeatRequest $repeatRequest */
        $repeatRequest = $this->repeatRequestFactory->create();
        $repeatRequest->setTransactionType(Config::ACTION_REPEAT_PI);
        $repeatRequest->setReferenceTransactionId($refTransactionId);
        $repeatRequest->setVendorTxCode($vendorTxCode);
        $repeatRequest->setAmount($amount);
        $repeatRequest->setCurrency($currency);
        $repeatRequest->setDescription($description);

        $jsonRequest = json_encode($repeatRequest->__toArray());
        $result      = $this->executePostRequest($this->getServiceUrl(self::ACTION_TRANSACTIONS), $jsonRequest);

        return $this->getTransactionDetailsObject($this->processResponse($result));
    }

    /**
     * GET transaction details
     *
     * @param $vpsTxId
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface
     * @throws ApiException
     */
    public function transactionDetails($vpsTxId)
    {
        $result = $this->executeRequest($this->getServiceUrl(self::ACTION_TRANSACTION_DETAILS, $vpsTxId));

        if ($result->getStatus() == 200) {
            return $this->getTransactionDetailsObject($result->getResponseData());
        } else {
            $error_code = $result->getResponseData()->code;
            $error_msg  = $result->getResponseData()->description;

            /** @var $exception ApiException */
            $exception = $this->apiExceptionFactory->create([
                'phrase' => __($error_msg),
                'code' => $error_code
            ]);

            throw $exception;
        }
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface $result
     * @return string
     * @throws ApiException
     */
    private function processResponse($result)
    {
        if ($result->getStatus() == 201) {
            //success
            return $result->getResponseData();
        } elseif ($result->getStatus() == 202) {
            //authentication required (3D secure)
            return $result->getResponseData();
        } else {
            $errorCode = 0;
            $errorMessage  = "Unable to capture Opayo transaction";

            $errors = $result->getResponseData();
            if (isset($errors->errors) && count($errors->errors) > 0) {
                $errors = $errors->errors[0];
            }

            if ($this->_isOperationNotAllowed($errors)) {
                $rest = $this->httpRestFactory->create();
                $rest->getLogger()->sageLog(\Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST, 'Operation not allowed detected', [__METHOD__, __LINE__]);
                $rest->getLogger()->sageLog(\Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST, json_encode($errors), [__METHOD__, __LINE__]);

                $errors->status = 'Operation not allowed';
                return $errors;
            }

            if (isset($errors->code)) {
                $errorCode = $errors->code;
            }
            if (isset($errors->description)) {
                $errorMessage = $errors->description;
            }
            if (isset($errors->property)) {
                $errorMessage .= ': ' . $errors->property;
            }

            if (isset($errors->statusDetail)) {
                $errorMessage = $errors->statusDetail;
            }

            /** @var ApiException $exception */
            $exception = $this->apiExceptionFactory->create(['phrase' => __($errorMessage), 'code' => $errorCode]);

            throw $exception;
        }
    }

    /**
     * @param \stdClass $errors
     * @return bool
     */
    private function _isOperationNotAllowed($errors)
    {
        $rest = $this->httpRestFactory->create();
        $rest->getLogger()->sageLog(\Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST, "Check if operation not allowed detected", [__METHOD__, __LINE__]);
        $rest->getLogger()->sageLog(\Ebizmarts\SagePaySuite\Model\Logger\Logger::LOG_REQUEST, json_encode($errors), [__METHOD__, __LINE__]);

        return (isset($errors->code) && $errors->code == 1017);
    }

    /**
     * @param \stdClass $captureResult
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface
     */
    private function getTransactionDetailsObject(\stdClass $captureResult)
    {
        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface $transaction */
        $transaction = $this->piCaptureResultFactory->create();
        $transaction->setStatusCode($captureResult->statusCode);
        $transaction->setStatusDetail($captureResult->statusDetail);
        $transaction->setTransactionId($captureResult->transactionId);
        $transaction->setStatus($captureResult->status);

        if ($captureResult->status == '3DAuth') {
            $transaction->setAcsUrl($captureResult->acsUrl);

            if (property_exists($captureResult, 'paReq') === true) {
                $transaction->setParEq($captureResult->paReq);
            } else {
                $transaction->setCReq($captureResult->cReq); //3Dv2
            }
        } else {
            $transaction->setTransactionType($captureResult->transactionType);

            if (isset($captureResult->retrievalReference)) {
                $transaction->setRetrievalReference($captureResult->retrievalReference);
            }

            if (isset($captureResult->bankAuthorisationCode)) {
                $transaction->setBankAuthCode($captureResult->bankAuthorisationCode);
            }

            if (isset($captureResult->retrievalReference)) {
                $transaction->setTxAuthNo($captureResult->retrievalReference);
            }

            if (isset($captureResult->currency)) {
                $transaction->setCurrency($captureResult->currency);
            }

            if (isset($captureResult->bankResponseCode)) {
                $transaction->setBankResponseCode($captureResult->bankResponseCode);
            }

            /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultCard $card */
            $card = $this->cardResultFactory->create();

            if (isset($captureResult->paymentMethod)) {
                if (isset($captureResult->paymentMethod->card->cardIdentifier)) {
                    $card->setCardIdentifier($captureResult->paymentMethod->card->cardIdentifier);
                }

                if (isset($captureResult->paymentMethod->card->reusable)) {
                    $card->setIsReusable($captureResult->paymentMethod->card->reusable);
                }

                $card->setCardType($captureResult->paymentMethod->card->cardType);
                $card->setLastFourDigits($captureResult->paymentMethod->card->lastFourDigits);
                $card->setExpiryDate($captureResult->paymentMethod->card->expiryDate);
            }

            /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultPaymentMethod $paymentMethod */
            $paymentMethod = $this->paymentMethodResultFactory->create();
            $paymentMethod->setCard($card);

            $transaction->setPaymentMethod($paymentMethod);

            if (isset($captureResult->{'3DSecure'})) {
                /** @var PiTransactionResultThreeD $threedstatus */
                $threedstatus = $this->threedStatusResultFactory->create();
                $threedstatus->setStatus($captureResult->{'3DSecure'}->status);
                $transaction->setThreeDSecure($threedstatus);
            }

            if (isset($captureResult->amount)) {
                /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAmountInterface $amount */
                $amount = $this->amountResultFactory->create();
                $amount->setSaleAmount($captureResult->amount->saleAmount);
                $amount->setTotalAmount($captureResult->amount->totalAmount);
                $amount->setSurchargeAmount($captureResult->amount->surchargeAmount);
                $transaction->setAmount($amount);
            }

            if (isset($captureResult->avsCvcCheck)) {
                /** @var PiTransactionResultAvsCvcCheck $avsCvcCheck */
                $avsCvcCheck = $this->avsCvcCheckResultFactory->create();
                $avsCvcCheck->setStatus($captureResult->avsCvcCheck->status);
                $avsCvcCheck->setAddress($captureResult->avsCvcCheck->address);
                $avsCvcCheck->setPostalCode($captureResult->avsCvcCheck->postalCode);
                $avsCvcCheck->setSecurityCode($captureResult->avsCvcCheck->securityCode);
                $transaction->setAvsCvcCheck($avsCvcCheck);
            }
        }

        return $transaction;
    }

    /**
     * @param $result
     * @return PiInstructionResponse
     * @throws ApiException
     */
    private function processInstructionsResponse($result): PiInstructionResponse
    {
        $apiResponse = $this->processResponse($result);

        /** @var PiInstructionResponse $response */
        $response = $this->instructionResponse->create();
        $response->setInstructionType($apiResponse->instructionType);
        $response->setDate($apiResponse->date);

        return $response;
    }
}
