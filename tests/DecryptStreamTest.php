<?php


namespace Thermonuclear\WhatsappCipher;

use \PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Utils;
use ReflectionClass;

class DecryptStreamTest extends TestCase
{
    public function testConstruct()
    {
        $stream = Utils::streamFor('some test data for stream');
        $mediaKey = random_bytes(32);
        $encStream = new DecryptStream($stream, 'AUDIO', $mediaKey);

        $this->assertSame($mediaKey, $encStream->getMediaKey());

        $class = new ReflectionClass('Thermonuclear\WhatsappCipher\DecryptStream');

        $prop = $class->getProperty('stream');
        $prop->setAccessible(true);
        $this->assertInstanceOf('Psr\Http\Message\StreamInterface', $prop->getValue($encStream));
    }

    public function testRead()
    {
        $originalText = 'some test data for stream';
        $mediaKey = random_bytes(32);

        $encStream = new EncryptStream(Utils::streamFor($originalText), 'AUDIO', $mediaKey);
        $cipherText = $encStream->read(2048);

        $decStream = new DecryptStream(Utils::streamFor($cipherText), 'AUDIO', $mediaKey);
        $decText = $decStream->read(2048);

        $this->assertSame($decText, $originalText);
    }
}
