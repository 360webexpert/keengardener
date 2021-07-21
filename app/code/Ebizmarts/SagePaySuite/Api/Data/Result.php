<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

class Result extends \Magento\Framework\Api\AbstractExtensibleObject implements ResultInterface
{

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->_get(self::SUCCESS);
    }

    /**
     * @return void
     */
    public function setSuccess($text)
    {
        $this->setData(self::SUCCESS, $text);
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->_get(self::RESPONSE);
    }

    /**
     * @return void
     */
    public function setResponse($text)
    {
        $this->setData(self::RESPONSE, $text);
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_get(self::ERROR_MESSAGE);
    }

    /**
     * @return void
     */
    public function setErrorMessage($text)
    {
        $this->setData(self::ERROR_MESSAGE, $text);
    }
}
