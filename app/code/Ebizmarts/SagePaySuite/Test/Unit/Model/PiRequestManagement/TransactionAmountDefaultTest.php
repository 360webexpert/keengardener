<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\PiRequestManagement;

use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountDefaultPi;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountDefaultPost;

class TransactionAmountDefaultTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @dataProvider amountsProvider
     */
    public function testAmounts($expected, $amount)
    {
        $amountObject = new TransactionAmountDefaultPi($amount);

        $this->assertEquals($expected*100, $amountObject->execute());
    }

    /**
     * @dataProvider amountsProvider
     */
    public function testPostAmounts($expected, $amount)
    {
        $amountObject = new TransactionAmountDefaultPost($amount);

        $this->assertEquals($expected, $amountObject->execute());
    }

    public function amountsProvider()
    {
        return [
            [1, 1],
            [9539.84, 9539.84],
            [6469.20, 6469.20],
            [146.20, 146.20]
        ];
    }
}
