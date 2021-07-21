<?php

declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Model\PiRequestManagement;

class TransactionAmountJapaneseYen implements TransactionAmountCommandInterface
{
    /** @var float */
    private $amount;

    public function __construct(float $amount)
    {
        $this->amount = $amount;
    }

    public function execute(): int
    {
        return (int)round($this->amount, 0, PHP_ROUND_HALF_EVEN);
    }
}
