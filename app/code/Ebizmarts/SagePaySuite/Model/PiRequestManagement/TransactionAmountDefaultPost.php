<?php

declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Model\PiRequestManagement;

class TransactionAmountDefaultPost implements TransactionAmountPostCommandInterface
{
    /** @var float */
    private $amount;

    public function __construct(float $amount)
    {
        $this->amount = $amount;
    }

    public function execute(): string
    {
        return number_format($this->amount, 2, '.', '');
    }
}
