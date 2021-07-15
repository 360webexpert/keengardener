<?php

namespace WeSupply\Toolbox\Api;

use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;

class Authorize
{
    /**
     * #@+.
     * Cipher method
     * @var string
     */
    const CIPHER_METHOD = 'AES-256-CTR'; // is implemented below

    /**
     * @var $error
     */
    public $error;

    /**
     * @var WeSupplyHelper
     */
    private $wsHelper;

    /**
     * Authorize constructor.
     * @param WeSupplyHelper $wsHelper
     */
    public function __construct(
        WeSupplyHelper $wsHelper
    )
    {
        $this->wsHelper = $wsHelper;
    }

    /**
     * Authorize code.
     *
     * @param string $authCode
     * @param string $encKey
     *
     * @return mixed array|false
     */
    public function authorize($encKey, $authCode)
    {
        $code = base64_decode($authCode, TRUE) ?: '';
        $ivLen = openssl_cipher_iv_length($this->getChiperMethode());

        list($knownHmac, $ciphertextRaw, $initVector) = $this->getCipherRaw($code);

        if (strlen($initVector) != $ivLen) {
            $this->error = __('Invalid vector size( %1) : %2 for method %3', strlen($initVector), base64_encode($initVector), $this->getChiperMethode());

            return false;
        }

        $originalPlaintext = openssl_decrypt(
            $ciphertextRaw,
            $this->getChiperMethode(),
            $encKey,
            OPENSSL_RAW_DATA,
            $initVector
        );

        $userHmac = hash_hmac('sha512', $ciphertextRaw, $encKey, TRUE);

        if (!hash_equals($knownHmac, $userHmac)) { // PHP 5.6+ timing attack safe comparison
            $this->error = __('Timing attack safe comparison failed.');

            return false;
        }

        if (is_string($knownHmac)) {
            return $originalPlaintext;
        }

        $this->error = __('Unknown error occurred!');

        return false;
    }

    /**
     * Retrieve cipher raw and initialization vector.
     *
     * @param string $encKeyodedText Encoded text to get cipher of
     *
     * @return array
     */
    private static function getCipherRaw(string $encKeyodedText)
    {
        $ivLen = openssl_cipher_iv_length(self::CIPHER_METHOD);
        $ivect = substr($encKeyodedText, 0, $ivLen);
        $hmac = substr($encKeyodedText, $ivLen, $sha2len = 64);
        $ciphertextRaw = substr($encKeyodedText, $ivLen + $sha2len);

        return [$hmac, $ciphertextRaw, $ivect];
    }

    /**
     * @return string
     */
    private function getChiperMethode()
    {
        return self::CIPHER_METHOD;
    }

    /**
     * @param $params
     * @return string
     */
    public function authenticate($params)
    {
        $ivect = $this->getIv();
        $encKey = $this->wsHelper->getWeSupplyApiClientSecret();

        $ciphertextRaw = openssl_encrypt(
            $params,
            $this->getChiperMethode(),
            $encKey,
            OPENSSL_RAW_DATA,
            $ivect
        );

        $hmac = hash_hmac('sha512', $ciphertextRaw, $encKey, TRUE);

        return urlencode(base64_encode($ivect . $hmac . $ciphertextRaw));
    }

    /**
     * Generates the initialization vector
     * @return string
     */
    private function getIv()
    {
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->getChiperMethode()));
    }
}
