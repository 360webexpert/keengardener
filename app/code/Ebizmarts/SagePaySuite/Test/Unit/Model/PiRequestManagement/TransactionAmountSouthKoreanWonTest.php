<?php
declare(strict_types=1);
namespace Ebizmarts\SagePaySuite\Test\Unit\Model\PiRequestManagement;

use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountSouthKoreanWon;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountPostSouthKoreanWon;

class TransactionAmountSouthKoreanWonTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @dataProvider amountsProvider
     */
    public function testAmounts($expected, $amount)
    {
        $amountObject = new TransactionAmountSouthKoreanWon($amount);

        $this->assertEquals($expected, $amountObject->execute());
    }

    /**
     * @dataProvider amountsProvider
     */
    public function testPostAmounts($expected, $amount)
    {
        $amountObject = new TransactionAmountPostSouthKoreanWon($amount);

        $this->assertEquals($expected, $amountObject->execute());
    }

    public function amountsProvider()
    {
        return [
            [1, 1],
            [64731, 64731.10],
        ];
    }
}
