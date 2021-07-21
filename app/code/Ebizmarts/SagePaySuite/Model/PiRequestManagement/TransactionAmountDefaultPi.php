<?php

declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Model\PiRequestManagement;

class TransactionAmountDefaultPi implements TransactionAmountCommandInterface
{
    /** @var float */
    private $amount;

    public function __construct(float $amount)
    {
        $this->amount = $amount;
    }

    public function execute(): int
    {
        return (int)(string)($this->amount * 100);
    }
}
