<?php


namespace Thermonuclear\WhatsappCipher;

use \PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Utils;
use ReflectionClass;

class EncryptStreamTest extends TestCase
{
    public function testConstruct()
    {
        $stream = Utils::streamFor('some test data for stream');
        $mediaKey = random_bytes(32);
        $encStream = new EncryptStream($stream, 'AUDIO', $mediaKey);

        $this->assertSame($mediaKey, $encStream->getMediaKey());

        $class = new ReflectionClass('Thermonuclear\WhatsappCipher\EncryptStream');

        $prop = $class->getProperty('stream');
        $prop->setAccessible(true);
        $this->assertInstanceOf('Psr\Http\Message\StreamInterface', $prop->getValue($encStream));
    }

    public function testRead()
    {
        $originalText = 'some test data for stream';
        $mediaKey = random_bytes(32);
        $stream = Utils::streamFor($originalText);
        $encStream = new EncryptStream($stream, 'AUDIO', $mediaKey);
        $cipherText = $encStream->read(2048);

        $ref = new ReflectionClass('Thermonuclear\WhatsappCipher\EncryptStream');
        $method = $ref->getProperty('method');
        $method->setAccessible(true);
        $iv = $ref->getProperty('iv');
        $iv->setAccessible(true);
        $cipherKey = $ref->getProperty('cipherKey');
        $cipherKey->setAccessible(true);

        $cipherTextTest = openssl_encrypt(
            $originalText,
            $method->getValue($encStream),
            $cipherKey->getValue($encStream),
            OPENSSL_RAW_DATA,
            $iv->getValue($encStream)
        );

        $getMac = $ref->getMethod('getMac');
        $getMac->setAccessible(true);
        $mac = $getMac->invoke($encStream, $cipherTextTest);

        $this->assertSame($cipherTextTest.$mac, $cipherText);
    }
}
