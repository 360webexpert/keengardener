<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

interface HttpResponseInterface
{
    const STATUS        = 'status';
    const RESPONSE_DATA = 'response_data';

    /**
     * @return integer
     */
    public function getStatus();

    /**
     * @param integer $httpStatusCode
     */
    public function setStatus($httpStatusCode);

    /**
     * @return string
     */
    public function getResponseData();

    /**
     * @param string $responseData
     */
    public function setResponseData($responseData);
}
