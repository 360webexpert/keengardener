<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\PiRequestManagement;

use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountPost;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountDefaultPost;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountPostSouthKoreanWon;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountPostJapaneseYen;

class TransactionAmountPostTest extends \PHPUnit\Framework\TestCase
{

    public function testCommandsExist()
    {
        $amountObject = new TransactionAmountPost(1089);

        $this->assertInstanceOf(TransactionAmountPostSouthKoreanWon::class, $amountObject->getCommand('KRW'));
        $this->assertInstanceOf(TransactionAmountDefaultPost::class, $amountObject->getCommand('EUR'));
        $this->assertInstanceOf(TransactionAmountPostJapaneseYen::class, $amountObject->getCommand('JPY'));
        $this->assertInstanceOf(TransactionAmountDefaultPost::class, $amountObject->getCommand('GBP'));
    }
}
