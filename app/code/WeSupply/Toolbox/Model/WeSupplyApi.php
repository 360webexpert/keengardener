<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model;

use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use WeSupply\Toolbox\Api\WeSupplyApiInterface;
use WeSupply\Toolbox\Logger\Logger;

/**
 * Class WeSupplyApi
 * @package WeSupply\Toolbox\Model
 */

class WeSupplyApi implements WeSupplyApiInterface
{
    const GRANT_TYPE = 'client_credentials';
    const TOKEN_PATH = 'oauth/token';
    const AUTH_ORDER_PATH = 'authLinks';
    const SUBSCRIBE_PATH = 'phone/enrol';
    const UNSUBSCRIBE_PATH = 'phone/unsubscribe';
    const ESTIMATIONS_PATH = 'estimations';
    const SHIPPING_QUOTES_PATH = 'shippingQuotes';
    const CHECK_SERVICE_PATH = 'permissions';

    /**
     * delivery estimation uri for a single product (product view page)
     */
    const DELIVERY_ESTIMATE_URI = 'shippingEstimate';

    /**
     * delivery estimations uri for multiple products (cart and checkout pages)
     */
    const DELIVERY_ESTIMATES_URI = 'shippingEstimates';

    /**
     * dynamic constants used for build api endpoints
     */
    const ALLOWED_COUNTRIES_URI = 'getShippingAllowedCountries';

    const ADMIN_KEY_URI = 'getAdminKey';

    /**
     * @var string
     */
    private $protocol = 'https';

    /**
     * path to wesupply api
     * @var string
     */
    private $apiPath;

    /**
     * wesupply api client id
     * @var string
     */
    private $apiClientId;

    /**
     * wesupply api client secret
     * @var string
     */
    private $apiClientSecret;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     *@var Curl
     */
    protected $curlClient;

    /**
     * @var Session
     */
    protected $catalogSession;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * WeSupplyApi constructor.
     * @param Context $context
     * @param Curl $curl
     * @param Logger $logger
     * @param Session $catalogSession
     * @param CurlFactory $curlFactory
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        Curl $curl,
        Logger $logger,
        Session $catalogSession,
        CurlFactory $curlFactory,
        JsonHelper $jsonHelper
    ) {
        $this->curlClient = $curl;
        $this->logger = $logger;
        $this->catalogSession = $catalogSession;
        $this->curlFactory = $curlFactory;
        $this->messageManager = $context->getMessageManager();
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param $protocol
     *
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @param $apiPath
     */
    public function setApiPath($apiPath)
    {
        $this->apiPath = $apiPath;
    }

    /**
     * @param $apiClientId
     */
    public function setApiClientId($apiClientId)
    {
        $this->apiClientId = $apiClientId;
    }

    /**
     * @param $apiClientSecret
     */
    public function setApiClientSecret($apiClientSecret)
    {
        $this->apiClientSecret = $apiClientSecret;
    }

    /**
     * Used to get estimations for checkout page
     *
     * @param $ipAddress
     * @param $storeId
     * @param $zipcode
     * @param $countryCode
     * @param $price
     * @param $currency
     * @param $shippers
     * @return array|bool|mixed
     */
    public function getShipperQuotes($ipAddress, $storeId, $zipcode, $countryCode, $price, $currency, $shippers)
    {
        $accessToken = $this->getToken();

        if ($accessToken) {
            $params = [
                "ipAddress" => $ipAddress,
                'supplierId' => $storeId,
                'postcode' => $zipcode,
                'countrycode'=>$countryCode,
                'price' => $price,
                'currency' => $currency,
                'carriers' => $shippers];

            $buildQuery = http_build_query($params);

            $curlOptions = $this->getCurlOptions();
            $curlOptions[CURLOPT_HTTPHEADER] = ["Authorization: Bearer $accessToken"];
            $this->curlClient->setOptions($curlOptions);

            try {
                $url = $this->protocol . '://' . $this->apiPath . self::SHIPPING_QUOTES_PATH . '?' . $buildQuery;
                $this->curlClient->get($url);
                $response = $this->curlClient->getBody();
                $jsonDecoded = json_decode($response, true);

                if ($this->curlClient->getStatus() === Http::STATUS_CODE_403) {
                    return ['error' => 'Service not available'];
                } elseif ($this->curlClient->getStatus() === Http::STATUS_CODE_503) {
                    $this->logger->error('Error when checking for Shipper Quotes response: ' . $response);
                    return false;
                } elseif ($this->curlClient->getStatus() === Http::STATUS_CODE_200) {
                    return $jsonDecoded;
                }

                $this->logger->error('Error when sending Shipper Quote to Wesupply with status: ' . $this->curlClient->getStatus() . ' response: ' . $response);
                return  false;
            } catch (\Exception $e) {
                $this->logger->error("WeSupply Shipper Quotes API Error:" . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * @param string $ipAddress
     * @param string $zipCode
     * expected response
     * @return array | bool;
     *
     */
    public function getEstimationsWeSupply($ipAddress, $storeId, $zipCode = '')
    {
        $accessToken = $this->getToken();

        if ($accessToken) {
            $params = ["ipAddress" => $ipAddress, 'supplierId' => $storeId, 'postcode' => $zipCode];

            $buildQuery = http_build_query($params);

            $curlOptions = $this->getCurlOptions();
            $curlOptions[CURLOPT_HTTPHEADER] = ["Authorization: Bearer $accessToken"];
            $this->curlClient->setOptions($curlOptions);

            try {
                $url = $this->protocol . '://' . $this->apiPath . self::ESTIMATIONS_PATH . '?' . $buildQuery;
                $this->curlClient->get($url);
                $response = $this->curlClient->getBody();
                $jsonDecoded = json_decode($response, true);

                if ($this->curlClient->getStatus() === Http::STATUS_CODE_403) {
                    $this->logger->error('Estimations WeSupply - service not available');
                    return false;
                } elseif ($this->curlClient->getStatus() === Http::STATUS_CODE_503) {
                    $this->logger->error('Error when contacting Estimations Wesupply response: ' . $response);
                    return false;
                } elseif ($this->curlClient->getStatus() === Http::STATUS_CODE_200) {
                    return $jsonDecoded ?? false;
                }

                $this->logger->error('Error when contacting Estimations Wesupply with status: ' . $this->curlClient->getStatus() . ' response: ' . $response);
                return  false;
            } catch (\Exception $e) {
                $this->logger->error("WeSupply Estimations API Error:" . $e->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * @param $orderNo
     * @param $phone
     * @param $prefix
     * @param $country
     * @param $unsubscribe
     * @return array|bool|mixed
     */
    public function notifyWeSupply($orderNo, $phone, $prefix, $country, $unsubscribe)
    {
        $params = [
            "order" => $orderNo,
            "phone" => $phone,
            "prefix" => trim($prefix, '+'),
            "country" => strtolower($country),
            "unsubscribe" => (bool) $unsubscribe
        ];

        $buildQuery = http_build_query($params);

        $curlOptions = $this->getCurlOptions();
        $this->curlClient->setOptions($curlOptions);

        try {
            $url = $this->getNotificationUrlByType($params, $buildQuery);
            $this->curlClient->get($url);
            $response = $this->curlClient->getBody();

            $jsonDecoded = json_decode($response, true);

            if ($this->curlClient->getStatus() === Http::STATUS_CODE_403) {
                return ['error' => 'Service not available'];
            } elseif ($this->curlClient->getStatus() === Http::STATUS_CODE_503) {
                $this->logger->error('Error when sending SMS notif to Wesupply response: ' . $response);
                return $jsonDecoded;
            } elseif ($this->curlClient->getStatus() === Http::STATUS_CODE_200) {
                return true;
            }

            $this->logger->error('Error when sending SMS notif to Wesupply with status: ' . $this->curlClient->getStatus() . ' response: ' . $response);
            return  false;
        } catch (\Exception $e) {
            $this->logger->error("WeSupply Notification Error:" . $e->getMessage());
            return false;
        }
    }

    /**
     * @return bool|string
     */
    public function weSupplyAccountCredentialsCheck()
    {
        $this->unsetGeneratedToken();

        $accessToken = $this->getToken();
        return !empty($accessToken) ? true : false;
    }

    /**
     * @param $externalOrderIdString
     * @param bool $ignoreEmailConfirmation
     * @return bool|mixed
     */
    public function weSupplyInterogation($externalOrderIdString, $ignoreEmailConfirmation = false)
    {
        $accessToken = $this->getToken();

        if ($accessToken) {
            $params = ["orders"=>$externalOrderIdString];

            if ($ignoreEmailConfirmation) {
                $params['ignore_email_confirmation'] = $ignoreEmailConfirmation;
            }

            $buildQuery = http_build_query($params);

            $curlOptions = $this->getCurlOptions();
            $curlOptions[CURLOPT_HTTPHEADER] = ["Authorization: Bearer $accessToken"];
            $this->curlClient->setOptions($curlOptions);

            try {
                $url = $this->protocol . '://' . $this->apiPath . self::AUTH_ORDER_PATH . '?' . $buildQuery;
                $this->curlClient->get($url);
                $response = $this->curlClient->getBody();
                $jsonDecoded = json_decode($response, true);

                if ($this->curlClient->getStatus() === Http::STATUS_CODE_403) {
                    $this->logger->error('Wesupply Order Interogation - service not available');
                    return false;
                } elseif ($this->curlClient->getStatus() === Http::STATUS_CODE_503) {
                    $this->logger->error('Error when interogating orders at WeSupply response: ' . $response);
                    return false;
                } elseif ($this->curlClient->getStatus() === Http::STATUS_CODE_200) {
                    // force https for iframeResizer
                    foreach ($jsonDecoded as $orderId => $link) {
                        if (strpos($link, 'https') === false) {
                            $jsonDecoded[$orderId] = str_replace('http', 'https', $link);
                        }
                    }
                    return $jsonDecoded ?? false;
                }

                $this->logger->error('Wesupply Order Interogation error with status: ' . $this->curlClient->getStatus() . ' response: ' . $response);
                return  false;
            } catch (\Exception $e) {
                $this->logger->error("WeSupply Order Interogation API Error:" . $e->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * Check for a specific WeSupply service (feature) availability
     * @param $serviceType
     * @return bool|mixed
     */
    public function checkServiceAvailability($serviceType)
    {
        $accessToken = $this->getToken();
        if (!$accessToken) {
            $this->logger->error('Invalid access token error was generated while checking service availability.');
            return false;
        }

        $buildQuery = http_build_query(['type' => $serviceType]);
        $curlOptions = $this->getCurlOptions();
        $curlOptions[CURLOPT_HTTPHEADER] = ["Authorization: Bearer $accessToken"];
        $this->curlClient->setOptions($curlOptions);

        try {
            $url = $this->protocol . '://' . $this->apiPath . self::CHECK_SERVICE_PATH . '?' . $buildQuery;
            $this->curlClient->get($url);

            $response = json_decode($this->curlClient->getBody(), true);
            $responseStatus = $this->curlClient->getStatus();

            if ($responseStatus === Http::STATUS_CODE_401) {
                $this->messageManager->addErrorMessage($responseStatus . ' error. ' . __('Invalid token. Check WeSupply API credentials.'));
                $this->logger->error($responseStatus . 'Invalid token: access token is invalid.');
            } elseif ($responseStatus === Http::STATUS_CODE_503) {
                $this->messageManager->addErrorMessage($responseStatus . ' error. ' . __('WeSupply service unavailable. Please try again later.'));
                $this->logger->error($responseStatus . ' WeSupply service unavailable.');
            }

            return $response;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('WeSupply API Error: ') . $e->getMessage());
            $this->logger->error('WeSupply API Error: checkServiceAvailability(...) ' . $e->getMessage());

            return false;
        }
    }

    /**
     * @return bool
     */
    public function checkApiCredentials()
    {
        $this->unsetGeneratedToken();
        $accessToken = $this->getToken();

        if (!$accessToken) {
            $this->logger->error('Invalid WeSupply API credentials.');
            return false;
        }
        return true;
    }

    /**
     * Check if already have a previous token in session and return it,
     * otherwise generate a new one
     * @return mixed|string
     */
    private function getToken()
    {
        $generatedToken = $this->catalogSession->getGeneratedToken();

        if (is_array($generatedToken)) {
            $generatedTime = isset($generatedToken['created_at']) ? $generatedToken['created_at'] : '';
            $token = isset($generatedToken['token']) ? $generatedToken['token'] : '';

            if (empty($generatedTime) || empty($token)) {
                $this->unsetGeneratedToken();
                $token = $this->generateNewToken();
                if (!empty($token)) {
                    $this->setTokenInSession($token);
                }
                return $token;
            }

            if ((time() - $generatedTime) > 3500) {
                $this->unsetGeneratedToken();
                $token = $this->generateNewToken();
                if (!empty($token)) {
                    $this->setTokenInSession($token);
                }
                return $token;
            }

            return $token;
        }

        $token = $this->generateNewToken();
        if (!empty($token)) {
            $this->setTokenInSession($token);
        }
        return $token;
    }

    /**
     * Sets the token in session for further usage
     * @param $token
     */
    private function setTokenInSession($token)
    {
        $sessionToken = ['created_at'=> time(), 'token'=> $token];
        $this->catalogSession->setGeneratedToken($sessionToken);
    }

    /**
     * generates a new token from WeSupply
     * @return string
     */
    private function generateNewToken()
    {
        $authUrl = $this->protocol . '://' . $this->apiPath . self::TOKEN_PATH;

        $userData = [
            "grant_type"    => self::GRANT_TYPE,
            "client_id"     => $this->apiClientId,
            "client_secret" => $this->apiClientSecret
        ];

        $this->curlClient->setOptions(
            [
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false
            ]
        );

        $this->curlClient->setHeaders(['Content-Type: application/x-www-form-urlencoded']);

        try {
            $this->curlClient->post($authUrl, $userData);
            $response = $this->curlClient->getBody();
            $jsonDecoded = json_decode($response);

            if ($this->curlClient->getStatus() === Http::STATUS_CODE_403) {
                $this->logger->error("WeSupply Token Request Not Available");
                return '';
            } elseif ($this->curlClient->getStatus() === Http::STATUS_CODE_503) {
                $this->logger->error('Error while generating token from WeSupply. Response status ' . $this->curlClient->getStatus());
                return '';
            } elseif ($this->curlClient->getStatus() === Http::STATUS_CODE_200) {
                return $jsonDecoded->access_token ?? '';
            }

            $this->logger->error('Error when sending Token Request to Wesupply with status ' . $this->curlClient->getStatus());

            return  '';
        } catch (\Exception $e) {
            $this->logger->error("WeSupply API Error: generateNewToken() " . $e->getMessage());
            return '';
        }
    }

    /**
     * unset token
     */
    protected function unsetGeneratedToken()
    {
        $this->catalogSession->unsGeneratedToken();
    }

    /**
     * @return array
     */
    private function getCurlOptions()
    {
        return [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ];
    }

    public function getWeSupplyAdminKey()
    {
        $accessToken = $this->getToken();
        if (!$accessToken) {
            $this->logger->error('Access token not found or couldn\'t be generated!');
            return false;
        }

        $url = $this->getApiUrl($this->getEndpoint('admin_key'));
        $curlOptions = $this->setCurlGetOptions($accessToken);

        try {
            $this->curlClient->setOptions($curlOptions);
            $this->curlClient->get($url);

            $response = $this->curlClient->getBody();
            if ($this->isJson($response)) {
                $response = $this->jsonHelper->jsonDecode($response);
            }

            switch ($this->curlClient->getStatus()) {
                case Http::STATUS_CODE_401:
                case Http::STATUS_CODE_403:
                case Http::STATUS_CODE_503:
                    $this->logger->error('WeSupply response error with status ' . $this->curlClient->getStatus());
                    return false;
                default:
                    return $response ?? false;
            }
        } catch (\Exception $e) {
            $this->logger->error('WeSupply response error: ' . $e->getMessage());
            return false;
        }
    }

    /**  NEW ESTIMATION LOGIC */

    /**
     * @return bool|mixed|string
     */
    public function getWeSupplyAllowedCountries()
    {
        $accessToken = $this->getToken();
        if (!$accessToken) {
            $this->logger->error('Access token not found or couldn\'t be generated!');
            return false;
        }

        $url = $this->getApiUrl($this->getEndpoint('allowed_countries'));
        $curlOptions = $this->setCurlGetOptions($accessToken);

        try {
            $this->curlClient->setOptions($curlOptions);
            $this->curlClient->get($url);

            $response = $this->curlClient->getBody();
            if ($this->isJson($response)) {
                $response = $this->jsonHelper->jsonDecode($response);
            }

            switch ($this->curlClient->getStatus()) {
                case Http::STATUS_CODE_401:
                case Http::STATUS_CODE_403:
                case Http::STATUS_CODE_503:
                    $this->logger->error('WeSupply response error with status ' . $this->curlClient->getStatus());
                    return false;
                default:
                    return $response ?? false;
            }
        } catch (\Exception $e) {
            $this->logger->error('WeSupply response error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $params
     * @param bool $multipleProducts
     * @return bool|mixed|string
     */
    public function getDeliveryEstimations($params, $multipleProducts = false)
    {
        $accessToken = $this->getToken();
        if (!$accessToken) {
            $this->logger->error('Access token not found or couldn\'t be generated!');
            return false;
        }

        $curlOptions = $this->setCurlPostOptions($this->jsonHelper->jsonEncode($params), $accessToken);
        $url = $this->getApiUrl($this->getEndpoint('delivery_estimate'));
        if ($multipleProducts) {
            $url = $this->getApiUrl($this->getEndpoint('delivery_estimates'));
        }

        try {
            $this->curlClient->setOptions($curlOptions);
            $this->curlClient->post($url, $params);

            $response = $this->curlClient->getBody();
            if ($this->isJson($response)) {
                $response = $this->jsonHelper->jsonDecode($response);
            }

            switch ($this->curlClient->getStatus()) {
                case Http::STATUS_CODE_401:
                case Http::STATUS_CODE_403:
                    $this->logger->error('WeSupply response error with status ' . $this->curlClient->getStatus());
                    return false;
                default:
                    if (isset($response['error'])) {
                        $errorDesc = !empty($response['description']) ? ':  ' . $response['description'] : '';
                        $this->logger->error($this->curlClient->getStatus() . ' Error ' . $response['error'] . $errorDesc);
                    }

                    return $response ?? false;
            }
        } catch (\Exception $e) {
            $this->logger->error('WeSupply response error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $endpoint
     * @param $type
     * @param array $params
     * @return bool|mixed|string
     */
    public function grabUrl($endpoint, $type, $params = [])
    {
        $accessToken = $this->getToken();
        if (!$accessToken) {
            $this->messageManager->addErrorMessage('Please make sure the credentials and Access Key configured in WeSupply > Providers > Magento are correct.');
            $this->logger->error('Access token not found or couldn\'t be generated!');
            return false;
        }

        $curlOptions = [];
        $url = $this->getApiUrl($endpoint);
        switch (strtolower($type)) {
            case 'get':
                $curlOptions = $this->setCurlGetOptions($accessToken);
                $url .= '?' . http_build_query($params);
                break;
            case 'post':
                $curlOptions = $this->setCurlPostOptions($params, $accessToken);
                break;
        }

        try {
            $this->curlClient->setOptions($curlOptions);
            $this->curlClient->get($url);

            $response = $this->curlClient->getBody();
            if ($this->isJson($response)) {
                $response = $this->jsonHelper->jsonDecode($response);
            }

            switch ($this->curlClient->getStatus()) {
                case Http::STATUS_CODE_401:
                case Http::STATUS_CODE_403:
                case Http::STATUS_CODE_503:
                    $wsMessage = '';
                    if (is_array($response)) {
                        foreach ($response as $detail) {
                            $wsMessage .= $detail . '. ';
                        }
                    }
                    $this->messageManager->addErrorMessage('Please make sure the credentials and Access Key configured in WeSupply > Providers > Magento are correct.');
                    $this->logger->error('WeSupply response error with status ' . $this->curlClient->getStatus() . ' | ' . $wsMessage);
                    return false;
                case Http::STATUS_CODE_404:
                    $this->messageManager->addErrorMessage('Bed request. API url not found. Contact support!');
                    $this->logger->error('WeSupply ApiEndpoint not found: ' . $this->getApiUrl($endpoint));
                    return false;
                default:
                    return $response ?? false;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('The connection between WeSupply and Magento has timed out. Please try again in a few minutes.');
            $this->logger->error('WeSupply response error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $data
     * @param null|string $accessToken
     * @return array
     */
    private function setCurlPostOptions($data, $accessToken = null)
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ]
        ];

        if ($accessToken) {
            $options[CURLOPT_HTTPHEADER][] = "Authorization: Bearer $accessToken";
        }

        return $options;
    }

    /**
     * @param null $accessToken
     * @return array
     */
    private function setCurlGetOptions($accessToken = null)
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ];

        if ($accessToken) {
            $options[CURLOPT_HTTPHEADER] = ["Authorization: Bearer $accessToken"];
        }

        return $options;
    }

    /**
     * @param $endpoint
     * @return string
     */
    protected function getApiUrl($endpoint): string
    {
        return $this->protocol . '://' . $this->apiPath . $endpoint;
    }

    /**
     * @param string $string
     * @return mixed
     */
    private function getEndpoint($string)
    {
        $const = $this->getConvertToConst($string);
        return constant("self::$const");
    }

    /**
     * @param string $string
     * @return string
     */
    private function getConvertToConst($string)
    {
        return strtoupper($string) . '_URI';
    }

    /**
     * @param $string
     * @return bool
     */
    private function isJson($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    /**
     * @param array $params
     * @param $buildQuery
     * @return string
     */
    private function getNotificationUrlByType(array $params, $buildQuery)
    {
        if ($params['unsubscribe']) {
            return $this->protocol . '://' . $this->apiPath . self::UNSUBSCRIBE_PATH . '?' . $buildQuery;
        }
        return $this->protocol . '://' . $this->apiPath . self::SUBSCRIBE_PATH . '?' . $buildQuery;
    }
}
