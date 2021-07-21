<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/24/17
 * Time: 3:14 PM
 */

namespace Ebizmarts\SagePaySuite\Api\Data;

class PiRequest extends \Magento\Framework\Api\AbstractExtensibleObject implements PiRequestInterface, PiScaRequestInterface
{

    /**
     * @return string
     */
    public function getCardIdentifier()
    {
        return $this->_get(self::CARD_ID);
    }

    /**
     * @param string $cardId Card identifier.
     * @return void
     */
    public function setCardIdentifier($cardId)
    {
        $this->setData(self::CARD_ID, $cardId);
    }

    /**
     * @return string
     */
    public function getMerchantSessionKey()
    {
        return $this->_get(self::MSK);
    }

    /**
     * @param string $msk
     * @return void
     */
    public function setMerchantSessionKey($msk)
    {
        $this->setData(self::MSK, $msk);
    }

    /**
     * @return int
     */
    public function getCcLastFour()
    {
        return $this->_get(self::CARD_LAST_FOUR);
    }

    /**
     * @param int $lastFour
     * @return void
     */
    public function setCcLastFour($lastFour)
    {
        $this->setData(self::CARD_LAST_FOUR, $lastFour);
    }

    /**
     * @return int
     */
    public function getCcExpMonth()
    {
        return $this->_get(self::CARD_EXP_MONTH);
    }

    /**
     * @param int $expiryMonth
     * @return void
     */
    public function setCcExpMonth($expiryMonth)
    {
        $this->setData(self::CARD_EXP_MONTH, $expiryMonth);
    }

    /**
     * @return int
     */
    public function getCcExpYear()
    {
        return $this->_get(self::CARD_EXP_YEAR);
    }

    /**
     * @param int $expiryYear
     * @return void
     */
    public function setCcExpYear($expiryYear)
    {
        $this->setData(self::CARD_EXP_YEAR, $expiryYear);
    }

    /**
     * @return string
     */
    public function getCcType()
    {
        return $this->_get(self::CARD_TYPE);
    }

    /**
     * @param string $cardType
     * @return void
     */
    public function setCcType($cardType)
    {
        $this->setData(self::CARD_TYPE, $cardType);
    }

    /**
     * @return int
     */
    public function getJavascriptEnabled(): int
    {
        return $this->_get(self::JS_ENABLED);
    }

    /**
     * Boolean that represents the ability of the cardholder browser to execute JavaScript.
     * @param int $enabled
     * @return void
     */
    public function setJavascriptEnabled(int $enabled): void
    {
        $this->setData(self::JS_ENABLED, $enabled);
    }

    /**
     * @return string
     */
    public function getAcceptHeaders(): string
    {
        return $this->_get(self::ACCEPT_HEADERS);
    }

    /**
     * Exact content of the HTTP accept headers as sent to the 3DS Requestor from the Cardholder’s browser.
     * @param string $headers
     * @return void
     */
    public function setAcceptHeaders(string $headers): void
    {
        $this->setData(self::ACCEPT_HEADERS, $headers);
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->_get(self::LANGUAGE);
    }

    /**
     * Value representing the browser language as defined in IETF BCP47. Returned from navigator.language property.
     * @param string $language
     * @return void
     */
    public function setLanguage(string $language): void
    {
        $this->setData(self::LANGUAGE, $language);
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->_get(self::USER_AGENT);
    }

    /**
     * Exact content of the HTTP user-agent header.
     * @param string $userAgent
     * @return void
     */
    public function setUserAgent(string $userAgent): void
    {
        $this->setData(self::USER_AGENT, $userAgent);
    }

    /**
     * @return int
     */
    public function getJavaEnabled(): int
    {
        return $this->_get(self::JAVA_ENABLED);
    }

    /**
     * Boolean that represents the ability of the cardholder browser to execute Java.
     * @param int $javaEnabled
     * @return void
     */
    public function setJavaEnabled(int $javaEnabled): void
    {
        $this->setData(self::JAVA_ENABLED, $javaEnabled);
    }

    /**
     * @return int
     */
    public function getColorDepth(): int
    {
        return $this->_get(self::COLOR_DEPTH);
    }

    /**
     * Exact content of the HTTP user-agent header.
     * @param int $colorDepth
     * @return void
     */
    public function setColorDepth(int $colorDepth): void
    {
        $this->setData(self::COLOR_DEPTH, $colorDepth);
    }

    /**
     * @return int
     */
    public function getScreenWidth(): int
    {
        return $this->_get(self::SCREEN_WIDTH);
    }

    /**
     * Exact content of the HTTP user-agent header.
     * @param int $screenWidth
     * @return void
     */
    public function setScreenWidth(int $screenWidth): void
    {
        $this->setData(self::SCREEN_WIDTH, $screenWidth);
    }

    /**
     * @return int
     */
    public function getScreenHeight(): int
    {
        return $this->_get(self::SCREEN_HEIGHT);
    }

    /**
     * Exact content of the HTTP user-agent header.
     * @param int $screenHeight
     * @return void
     */
    public function setScreenHeight(int $screenHeight): void
    {
        $this->setData(self::SCREEN_HEIGHT, $screenHeight);
    }

    /**
     * @return int
     */
    public function getTimezone(): int
    {
        return $this->_get(self::TIMEZONE);
    }

    /**
     * Exact content of the HTTP user-agent header.
     * @param int $timezone
     * @return void
     */
    public function setTimezone(int $timezone): void
    {
        $this->setData(self::TIMEZONE, $timezone);
    }
}
