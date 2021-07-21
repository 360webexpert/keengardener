<?php

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;

class HttpText extends Http
{
    public function __construct(
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface $returnData,
        Logger $logger
    ) {
        parent::__construct($curl, $returnData, $logger);

        $this->setContentType("application/x-www-form-urlencoded");
    }

    /**
     * @return \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    public function processResponse()
    {
        $data = $this->getResponseData();

        $this->getLogger()->sageLog(Logger::LOG_REQUEST, $data, [__METHOD__, __LINE__]);

        /** @var \Ebizmarts\SagePaySuite\Api\Data\HttpResponse $return */
        $this->getReturnData()->setStatus($this->getResponseCode());
        $this->getReturnData()->setResponseData($data);

        return $this->getReturnData();
    }

    /**
     * @return array
     */
    public function rawResponseToArray()
    {
        $output = [];

        $responseString = preg_split('/^\r?$/m', $this->getResponseData(), 2);

        //Split response into name=value pairs
        $responseArray = explode("\n", $responseString[1]);

        // Tokenise the response
        $dataCnt = count($responseArray);
        for ($i = 0; $i < $dataCnt; $i++) {
            // Find position of first "=" character
            $splitAt = strpos($responseArray[$i], "=");

            // Create an associative (hash) array with key/value pairs ('trim' strips excess whitespace)
            if ($splitAt !== false) {
                $arVal = (string)trim(substr($responseArray[$i], ($splitAt + 1)));
                if (!empty($arVal)) {
                    $output[trim(substr($responseArray[$i], 0, $splitAt))] = $arVal;
                }
            }
        }

        return $output;
    }

    /**
     * @param array $postData
     * @return string
     */
    public function arrayToQueryParams($postData)
    {
        $post_data_string = '';
        foreach ($postData as $_key => $_val) {
            $post_data_string .= $_key . '=' . urlencode(mb_convert_encoding($_val, 'ISO-8859-1', 'UTF-8')) . '&';
        }
        return $post_data_string;
    }
}
