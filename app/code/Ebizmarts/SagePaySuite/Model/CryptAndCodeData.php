<?php
/**
 * Copyright © 2019 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Url\DecoderInterface;

class CryptAndCodeData
{
    /** @var EncryptorInterface */
    private $encryptor;

    /** @var EncoderInterface */
    private $encoder;

    /** @var DecoderInterface */
    private $decoder;

    /**
     * CryptAndCodeData constructor.
     * @param EncryptorInterface $encryptor
     * @param EncoderInterface $encoder
     * @param DecoderInterface $decoder
     */
    public function __construct(
        EncryptorInterface $encryptor,
        EncoderInterface $encoder,
        DecoderInterface $decoder
    ) {
        $this->encryptor = $encryptor;
        $this->encoder   = $encoder;
        $this->decoder   = $decoder;
    }

    /**
     * @param $data
     * @return string
     */
    public function encrypt($data)
    {
        return $this->encryptor->encrypt($data);
    }

    /**
     * @param $data
     * @return string
     */
    public function decrypt($data)
    {
        return $this->encryptor->decrypt($data);
    }

    /**
     * @param $data
     * @return string
     */
    public function encode($data)
    {
        return $this->encoder->encode($data);
    }

    /**
     * @param $data
     * @return string
     */
    public function decode($data)
    {
        return $this->decoder->decode($data);
    }

    /**
     * @param $data
     * @return string
     */
    public function encryptAndEncode($data)
    {
        $encryptedData = $this->encrypt($data);
        return $this->encode($encryptedData);
    }

    /**
     * @param $data
     * @return string
     */
    public function decodeAndDecrypt($data)
    {
        $decodedData = $this->decode($data);
        return $this->decrypt($decodedData);
    }
}
