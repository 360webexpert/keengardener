<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/27/17
 * Time: 10:11 AM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Api\AbstractExtensibleObject;

class PiInstructionRequest extends AbstractExtensibleObject implements PiInstructionRequestInterface
{
    /**
     * @inheritDoc
     */
    public function getInstructionType()
    {
        return $this->_get(self::INSTRUCTION_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setInstructionType($instructionType)
    {
        $this->setData(self::INSTRUCTION_TYPE, $instructionType);
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);
    }
}
