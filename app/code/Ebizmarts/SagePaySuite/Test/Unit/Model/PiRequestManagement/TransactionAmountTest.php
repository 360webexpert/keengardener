<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\PiRequestManagement;

use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmount;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountDefaultPi;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountSouthKoreanWon;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountJapaneseYen;

class TransactionAmountTest extends \PHPUnit\Framework\TestCase
{

    public function testCommandsExist()
    {
        $amountObject = new TransactionAmount(1089);

        $this->assertInstanceOf(TransactionAmountSouthKoreanWon::class, $amountObject->getCommand('KRW'));
        $this->assertInstanceOf(TransactionAmountDefaultPi::class, $amountObject->getCommand('EUR'));
        $this->assertInstanceOf(TransactionAmountJapaneseYen::class, $amountObject->getCommand('JPY'));
        $this->assertInstanceOf(TransactionAmountDefaultPi::class, $amountObject->getCommand('GBP'));
    }
}
