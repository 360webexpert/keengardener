<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface PiInstructionResponseInterface
{
    const INSTRUCTION_TYPE = 'instructionType';
    const DATE             = 'date';

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
     * Date/Time field is in ISO 8601 format.
     * @return string
     */
    public function getDate();

    /**
     * @param string $date
     * @return void
     */
    public function setDate($date);
}
