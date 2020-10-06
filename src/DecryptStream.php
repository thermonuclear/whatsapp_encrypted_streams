<?php

namespace Thermonuclear\WhatsappCipher;

use Exception;
use GuzzleHttp\Psr7\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator. Create decrypted file from encrypted stream using Whatsapp algoritm
 * @property StreamInterface $stream
 */
class DecryptStream implements StreamInterface
{
    use StreamDecoratorTrait;
    use CipherOptionsTrait;

    private StreamInterface $stream;

    public function __construct(StreamInterface $stream, string $mediaType, string $mediaKey)
    {
        $this->stream = $stream;
        $this->mediaType = $mediaType;
        $this->setCipherOptions($mediaKey);
    }

    /**
     * Create decrypted data
     * @param  int  $length  Read up to $length bytes from the object
     * @return string
     */
    public function read($length): string
    {
        $cipherTextMac = $this->stream->read($length);
        $cipherText = substr($cipherTextMac, 0, -$this->macSizeInFile);

        $this->checkMacKey($cipherTextMac, $cipherText);

        return openssl_decrypt($cipherText, $this->method, $this->cipherKey, OPENSSL_RAW_DATA, $this->iv);
    }

    /**
     * Check macKey from data sign
     * @param  string  $cipherTextMac  data with mac sign
     * @param  string  $cipherText  data without mac sign
     * @throws Exception
     */
    private function checkMacKey(string $cipherTextMac, string $cipherText): void
    {
        $receivedMac = substr($cipherTextMac, -$this->macSizeInFile);
        $calculatedMac = $this->getMac($cipherText);

        if ($receivedMac != $calculatedMac) {
            throw new Exception('received mac not equal calculated mac');
        }
    }
}
