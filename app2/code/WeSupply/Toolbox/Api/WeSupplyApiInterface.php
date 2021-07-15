<?php
namespace WeSupply\Toolbox\Api;


interface WeSupplyApiInterface
{

    /**
     * @param $externalOrderIdString
     * @param bool $ignoreEmailConfirmation
     * @return mixed
     */
    function weSupplyInterogation($externalOrderIdString, $ignoreEmailConfirmation = false);

    /**
     * @param $orderNo
     * @param $phone
     * @param $prefix
     * @param $country
     * @param $unsubscribe
     * @return mixed
     */
    function notifyWeSupply($orderNo, $phone, $prefix, $country, $unsubscribe);

    /**
     * @param $ipAddress
     * @param $storeId
     * @param string $zipCode
     * @return mixed
     */
    function getEstimationsWeSupply($ipAddress, $storeId, $zipCode = '');

    /**
     * @param $protocol
     */
    public function setProtocol($protocol);

    /**
     * @param $apiPath
     * @return mixed
     */
    function setApiPath($apiPath);

    /**
     * @param $apiClientId
     * @return mixed
     */
    function setApiClientId($apiClientId);

    /**
     * @param $apiClientSecret
     * @return mixed
     */
    function setApiClientSecret($apiClientSecret);

    /**
     * @param $params
     * @param bool $multipleProducts
     * @return mixed
     */
    function getDeliveryEstimations($params, $multipleProducts = false);

    /**
     * @param $endpoint
     * @param $type
     * @param array $params
     * @return mixed
     */
    function grabUrl($endpoint, $type, $params = []);

    /**
     * @param $ipAddress
     * @param $storeId
     * @param $zipcode
     * @param $countryCode
     * @param $price
     * @param $currency
     * @param $shippers
     * @return mixed
     */
    function getShipperQuotes($ipAddress, $storeId, $zipcode, $countryCode, $price, $currency, $shippers);


    /**
     * @return mixed
     */
    function weSupplyAccountCredentialsCheck();

    /**
     * @param $serviceType
     * @return mixed
     */
    function checkServiceAvailability($serviceType);

    /**
     * @return mixed
     */
    function getWeSupplyAllowedCountries();
}
