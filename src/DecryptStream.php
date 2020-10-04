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
     * Create decrypted file
     * @param  string  $path  file path for save decrypted stream
     * @throws Exception
     */
    public function createDecryptedFile(string $path): void
    {
        $cipherTextMac = $this->stream->getContents();
        $cipherText = substr($cipherTextMac, 0, -$this->macSizeInFile);

        $this->checkMacKey($cipherTextMac, $cipherText);

        $originalText = openssl_decrypt($cipherText, $this->method, $this->cipherKey, OPENSSL_RAW_DATA, $this->iv);
        file_put_contents($path, $originalText);
    }

    /**
     * Check macKey from file sign
     * @param  string  $cipherTextMac  file with mac sign
     * @param  string  $cipherText  file without mac sign
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
