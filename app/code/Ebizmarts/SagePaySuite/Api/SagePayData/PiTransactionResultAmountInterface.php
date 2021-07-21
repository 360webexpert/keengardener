<?php
namespace Ebizmarts\SagePaySuite\Api\SagePayData;

/**
 * Interface PiTransactionResultAmountInterface
 *
 * The amount object provides information regarding the total, sale and surcharge amounts for the transaction.
 * The amount is only returned in response to GET requests to the transactions resource.
 *
 * @package Ebizmarts\SagePaySuite\Api\SagePayData
 */
interface PiTransactionResultAmountInterface
{
    const TOTAL_AMOUNT     = 'total_amount';
    const SALE_AMOUNT      = 'sale_amount';
    const SURCHARGE_AMOUNT = 'surcharge_amount';

    /**
     * The total amount for the transaction that includes any sale or surcharge values.
     * @return integer
     */
    public function getTotalAmount();

    /**
     * @param integer $amount
     * @return void
     */
    public function setTotalAmount($amount);

    /**
     * The sale amount associated with the cost of goods or services for the transaction.
     * @return integer
     */
    public function getSaleAmount();

    /**
     * @param integer $amount
     * @return void
     */
    public function setSaleAmount($amount);

    /**
     * The surcharge amount added to the transaction as per the settings of the account.
     * @return integer
     */
    public function getSurchargeAmount();

    /**
     * @param integer $amount
     * @return void
     */
    public function setSurchargeAmount($amount);
}
