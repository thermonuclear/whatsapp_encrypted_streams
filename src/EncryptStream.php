<?php

namespace Thermonuclear\WhatsappCipher;

use GuzzleHttp\Psr7\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator. Create encrypted file from stream using Whatsapp algoritm
 * @property StreamInterface $stream
 */
class EncryptStream implements StreamInterface
{
    use StreamDecoratorTrait;
    use CipherOptionsTrait;

    private StreamInterface $stream;

    public function __construct(StreamInterface $stream, string $mediaType, string $mediaKey = '')
    {
        $this->stream = $stream;
        $this->mediaType = $mediaType;
        $this->setCipherOptions($mediaKey);
    }

    /**
     * Create encrypted file
     * @param  string  $path  file path for save encrypted stream
     */
    public function createEncryptedFile(string $path): void
    {
        $originalText = $this->stream->getContents();
        $cipherText = openssl_encrypt($originalText, $this->method, $this->cipherKey, OPENSSL_RAW_DATA, $this->iv);

        file_put_contents($path, $cipherText.$this->getMac($cipherText));
    }
}
