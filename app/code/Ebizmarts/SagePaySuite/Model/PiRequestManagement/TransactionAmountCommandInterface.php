<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Model\PiRequestManagement;

interface TransactionAmountCommandInterface
{
    public function execute() : int;
}
