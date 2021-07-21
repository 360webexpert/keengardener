<?php

namespace Ebizmarts\SagePaySuite\Model;

class FormCrypt implements \Ebizmarts\SagePaySuite\Api\RequestCryptInterface
{
    private $phpseclibCryptAes;

    public function __construct()
    {
        $this->phpseclibCryptAes = new \phpseclib\Crypt\AES(\phpseclib\Crypt\Base::MODE_CBC);
    }

    public function initInitializationVectorAndKey($key)
    {
        $this->phpseclibCryptAes->setKey($key);
        $this->phpseclibCryptAes->setIV($key);
    }

    public function encrypt($dataToEncrypt)
    {
        $binaryCipherText = $this->phpseclibCryptAes->encrypt($dataToEncrypt);
        $hexadecimalText   = bin2hex($binaryCipherText);
        $uppercaseHexadecimalText = strtoupper($hexadecimalText);

        return "@$uppercaseHexadecimalText";
    }

    public function decrypt($dataToDecrypt)
    {
        //** remove the first char which is @ to flag this is AES encrypted
        $hex = substr($dataToDecrypt, 1);

        // Throw exception if string is malformed
        if (!preg_match('/^[0-9a-fA-F]+$/', $hex)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid encryption string'));
        }

        //** HEX decoding
        $strIn = pack('H*', $hex);

        return $this->phpseclibCryptAes->decrypt($strIn);
    }
}
