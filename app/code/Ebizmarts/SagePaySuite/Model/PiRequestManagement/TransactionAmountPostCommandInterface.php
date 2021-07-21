<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Model\PiRequestManagement;

interface TransactionAmountPostCommandInterface
{
    public function execute() : string;
}
