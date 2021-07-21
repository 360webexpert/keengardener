<?php

namespace Ebizmarts\SagePaySuite\Model\Api;

use Ebizmarts\SagePaySuite\Model\Logger\Logger;

class HttpRest extends Http
{
    public function __construct(
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface $returnData,
        Logger $logger
    ) {
        parent::__construct($curl, $returnData, $logger);

        $this->setContentType("application/json");
    }

    /**
     * @return \Ebizmarts\SagePaySuite\Api\Data\HttpResponseInterface
     * @throws \Ebizmarts\SagePaySuite\Model\Api\ApiException
     */
    public function processResponse()
    {
        $this->getLogger()->sageLog(Logger::LOG_REQUEST, $this->getResponseData(), [__METHOD__, __LINE__]);

        $data = preg_split('/^\r?$/m', $this->getResponseData(), 2);
        $data = json_decode(trim($data[1]));

        $this->getLogger()->sageLog(Logger::LOG_REQUEST, $data, [__METHOD__, __LINE__]);

        /** @var \Ebizmarts\SagePaySuite\Api\Data\HttpResponse $return */
        $this->getReturnData()->setStatus($this->getResponseCode());
        $this->getReturnData()->setResponseData($data);

        return $this->getReturnData();
    }
}
