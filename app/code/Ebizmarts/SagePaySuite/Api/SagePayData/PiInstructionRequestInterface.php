<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface PiInstructionRequestInterface
{
    const INSTRUCTION_TYPE = 'instructionType';
    const AMOUNT           = 'amount';

    /**
     * The type of the instruction.
     * void abort release
     * @return string
     */
    public function getInstructionType();

    /**
     * @param string $instructionType
     * @return void
     */
    public function setInstructionType($instructionType);

    /**
     * The amount property is compulsory for a 'release’ instruction after a 'Deferred’ transaction.
     * The amount charged to the customer in the smallest currency unit.
     * (e.g 100 pence to charge £1.00, or 1 to charge ¥1 (0-decimal currency).
     * @return integer
     */
    public function getAmount();

    /**
     * @param integer $amount
     * @return void
     */
    public function setAmount($amount);
}
