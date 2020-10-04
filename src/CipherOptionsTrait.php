<?php

namespace Thermonuclear\WhatsappCipher;

/**
 * Encrypte/Decrypte stream decorator trait
 *
 * @property  string $mediaKey initial key 32 bytes
 * @property  string $iv initialization vector for cipher method
 * @property  string $cipherKey cipher key for cipher method
 * @property  string $macKey cipher key for cipher method
 * @property  int $macSizeInFile size of HMAC hash part
 * @property  int $mediaKeySize size of key for encrypte/decrypte
 * @property  int $mediaKeyExpandedSize size of expanded key for encrypte/decrypte
 * @property  string $method encrypte/decrypte algoritm
 * @property  string $mediaType type for HKDF context
 * @property  array $contextHKDF information lines for HKDF
 * @property  array $cipherOptionsSize length cipher options
 */
trait CipherOptionsTrait
{
    private string $mediaKey;
    private string $iv;
    private string $cipherKey;
    private string $macKey;
    private int $macSizeInFile = 10;
    private int $mediaKeySize = 32;
    private int $mediaKeyExpandedSize = 112;
    private string $method = 'aes-256-cbc';
    private string $mediaType;
    private array $contextHKDF = [
        'IMAGE' => 'WhatsApp Image Keys',
        'VIDEO' => 'WhatsApp Video Keys',
        'AUDIO' => 'WhatsApp Audio Keys',
        'DOCUMENT' => 'WhatsApp Document Keys',
    ];
    private array $cipherOptionsSize = [
        'iv' => 16,
        'cipherKey' => 32,
        'macKey' => 32,
    ];

    /**
     * set initial key 32 bytes
     * @param  string  $mediaKey  initial key 32 bytes
     */
    private function setMediaKey(string $mediaKey): void
    {
        $this->mediaKey = (strlen($mediaKey) == $this->mediaKeySize) ? $mediaKey : random_bytes($this->mediaKeySize);
    }

    /**
     * get expanded mediaKey
     *
     * @return string
     */
    private function getMediaKeyExpanded(): string
    {
        return hash_hkdf('sha256', $this->mediaKey, $this->mediaKeyExpandedSize, $this->contextHKDF[$this->mediaType]);
    }

    /**
     * set cipher options
     * @param  string  $mediaKey  extended key
     */
    private function setCipherOptions(string $mediaKey): void
    {
        $this->setMediaKey($mediaKey);
        $mediaKeyExpanded = $this->getMediaKeyExpanded();

        $this->iv = substr($mediaKeyExpanded, 0, $this->cipherOptionsSize['iv']);
        $this->cipherKey = substr($mediaKeyExpanded, 16, $this->cipherOptionsSize['cipherKey']);
        $this->macKey = substr($mediaKeyExpanded, 48, $this->cipherOptionsSize['macKey']);
    }

    /**
     * get 'mac' param for sign
     * @param  string  $cipherText  encrypted data
     *
     * @return string
     */
    private function getMac(string $cipherText): string
    {
        $sign = hash_hmac('sha256', $this->iv.$cipherText, $this->macKey);

        return substr($sign, 0, $this->macSizeInFile);
    }

    public function getMediaKey(): string
    {
        return $this->mediaKey;
    }
}
