<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

interface PiRequestInterface
{
    const CARD_ID        = 'card_identifier';
    const MSK            = 'merchant_session_key';
    const CARD_LAST_FOUR = 'card_last_four';
    const CARD_EXP_MONTH = 'card_exp_month';
    const CARD_EXP_YEAR  = 'card_exp_year';
    const CARD_TYPE      = 'card_type';

    /**
     * @return string
     */
    public function getCardIdentifier();

    /**
     * @param string $cardId Card identifier.
     * @return void
     */
    public function setCardIdentifier($cardId);

    /**
     * @return string
     */
    public function getMerchantSessionKey();

    /**
     * @param string $msk
     * @return void
     */
    public function setMerchantSessionKey($msk);

    /**
     * @return int
     */
    public function getCcLastFour();

    /**
     * @param int $lastFour
     * @return void
     */
    public function setCcLastFour($lastFour);

    /**
     * @return int
     */
    public function getCcExpMonth();

    /**
     * @param int $expiryMonth
     * @return void
     */
    public function setCcExpMonth($expiryMonth);

    /**
     * @return int
     */
    public function getCcExpYear();

    /**
     * @param int $expiryYear
     * @return void
     */
    public function setCcExpYear($expiryYear);

    /**
     * @return string
     */
    public function getCcType();

    /**
     * @param string $cardType
     * @return void
     */
    public function setCcType($cardType);
}
