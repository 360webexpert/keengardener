<?php
namespace Ebizmarts\SagePaySuite\Api\SagePayData;

/**
 * Interface PiTransactionResultCardInterface
 *
 * The card object represents the credit or debit card details for this transaction.
 *
 * @package Ebizmarts\SagePaySuite\Api\SagePayData
 */
interface PiTransactionResultCardInterface
{
    const CARD_TYPE        = 'card_type';
    const LAST_FOUR_DIGITS = 'last_four_digits';
    const EXPIRY_DATE      = 'expiry_date';
    const CARD_IDENTIFIER  = 'card_identifier';
    const REUSABLE         = 'reusable';

    /**
     * The type of the card (Visa, MasterCard, American Express etc.).
     * @return string
     */
    public function getCardType();

    /**
     * @param string $ccType
     * @return void
     */
    public function setCardType($ccType);

    /**
     * The last 4 digits of the card.
     * @return string
     */
    public function getLastFourDigits();

    /**
     * @param string $digits
     * @return void
     */
    public function setLastFourDigits($digits);

    /**
     * The expiry date of the card in MMYY format.
     * @return string
     */
    public function getExpiryDate();

    /**
     * @param string $date
     * @return void
     */
    public function setExpiryDate($date);

    /**
     * The unique reference of the card that was used.
     * @return string
     */
    public function getCardIdentifier();

    /**
     * @param string $cardId
     * @return void
     */
    public function setCardIdentifier($cardId);

    /**
     * A flag to indicate the card identifier is reusable, i.e. it has been created previously.
     * @return boolean
     */
    public function getIsReusable();

    /**
     * @param boolean $usable
     * @return void
     */
    public function setIsReusable($usable);
}
