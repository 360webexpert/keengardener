<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

class HttpResponse extends \Magento\Framework\Api\AbstractExtensibleObject implements HttpResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($httpStatusCode)
    {
        $this->setData(self::STATUS, $httpStatusCode);
    }

    /**
     * @inheritDoc
     */
    public function getResponseData()
    {
        return $this->_get(self::RESPONSE_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setResponseData($responseData)
    {
        $this->setData(self::RESPONSE_DATA, $responseData);
    }
}
