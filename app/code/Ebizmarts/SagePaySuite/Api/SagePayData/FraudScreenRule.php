<?php
namespace Ebizmarts\SagePaySuite\Api\SagePayData;

class FraudScreenRule extends \Magento\Framework\Api\AbstractExtensibleObject implements FraudScreenRuleInterface
{

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function getScore()
    {
        return $this->_get(self::SCORE);
    }

    /**
     * @inheritDoc
     */
    public function setScore($score)
    {
        $this->setData(self::SCORE, $score);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);
    }
}
