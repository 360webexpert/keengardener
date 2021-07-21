<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Api\AbstractExtensibleObject;

class FraudScreenResponse extends AbstractExtensibleObject implements FraudScreenResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getTimestamp()
    {
        return $this->_get(self::TIMESTAMP);
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode()
    {
        return $this->_get(self::ERROR_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getFraudProviderName()
    {
        return $this->_get(self::FRAUD_PROVIDER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getFraudScreenRecommendation()
    {
        return $this->_get(self::FRAUD_SCREEN_RECOMMENDATION);
    }

    /**
     * @inheritDoc
     */
    public function getFraudId()
    {
        return $this->_get(self::FRAUD_ID);
    }

    /**
     * @inheritDoc
     */
    public function getFraudCode()
    {
        return $this->_get(self::FRAUD_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getFraudCodeDetail()
    {
        return $this->_get(self::FRAUD_CODE_DETAIL);
    }

    /**
     * @inheritDoc
     */
    public function getThirdmanRules()
    {
        return $this->_get(self::THIRDMAN_RULES);
    }

    /**
     * @inheritDoc
     */
    public function getThirdmanRulesAsArray()
    {
        $return = [];

        $data = $this->__toArray();

        if (isset($data[self::THIRDMAN_RULES])) {
            $return = $data[self::THIRDMAN_RULES];
        }

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function getThirdmanId()
    {
        return $this->_get(self::THIRDMAN_ID);
    }

    /**
     * @inheritDoc
     */
    public function getThirdmanScore()
    {
        return $this->_get(self::THIRDMAN_SCORE);
    }

    /**
     * @inheritDoc
     */
    public function getThirdmanAction()
    {
        return $this->_get(self::THIRDMAN_ACTION);
    }

    /**
     * @inheritDoc
     */
    public function setTimestamp($timestamp)
    {
        $this->setData(self::TIMESTAMP, $timestamp);
    }

    /**
     * @inheritDoc
     */
    public function setErrorCode($errorCode)
    {
        $this->setData(self::ERROR_CODE, $errorCode);
    }

    /**
     * @inheritDoc
     */
    public function setFraudProviderName($fraudProviderName)
    {
        $this->setData(self::FRAUD_PROVIDER_NAME, $fraudProviderName);
    }

    /**
     * @inheritDoc
     */
    public function setFraudScreenRecommendation($fraudScreenRecommendation)
    {
        $this->setData(self::FRAUD_SCREEN_RECOMMENDATION, $fraudScreenRecommendation);
    }

    /**
     * @inheritDoc
     */
    public function setFraudId($fraudId)
    {
        $this->setData(self::FRAUD_ID, $fraudId);
    }

    /**
     * @inheritDoc
     */
    public function setFraudCode($fraudCode)
    {
        $this->setData(self::FRAUD_CODE, $fraudCode);
    }

    /**
     * @inheritDoc
     */
    public function setFraudCodeDetail($fraudCodeDetail)
    {
        $this->setData(self::FRAUD_CODE_DETAIL, $fraudCodeDetail);
    }

    /**
     * @inheritDoc
     */
    public function setThirdmanRules($thirdmanRules)
    {
        $this->setData(self::THIRDMAN_RULES, $thirdmanRules);
    }

    /**
     * @inheritDoc
     */
    public function setThirdmanId($thirdmanId)
    {
        $this->setData(self::THIRDMAN_ID, $thirdmanId);
    }

    /**
     * @inheritDoc
     */
    public function setThirdmanScore($thirdmanScore)
    {
        $this->setData(self::THIRDMAN_SCORE, $thirdmanScore);
    }

    /**
     * @inheritDoc
     */
    public function setThirdmanAction($thirdmanAction)
    {
        $this->setData(self::THIRDMAN_ACTION, $thirdmanAction);
    }
}
