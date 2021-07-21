<?php
namespace Ebizmarts\SagePaySuite\Api\SagePayData;

/**
 * Interface PiTransactionPaymentMethodInterface
 *
 * The paymentMethod object specifies the payment method for the transaction.
 *
 * @package Ebizmarts\SagePaySuite\Api\SagePayData
 */
interface PiTransactionResultPaymentMethodInterface
{
    const CARD = 'card';

    /**
     * Details of the customer’s credit or debit card.
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultCardInterface
     */
    public function getCard();

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultCardInterface $card
     * @return void
     */
    public function setCard($card);
}
