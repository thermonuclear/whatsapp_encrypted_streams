# PSR-7 Stream Whatsapp Encryption Decorators

Декораторы для [PSR-7 потоков](https://github.com/php-fig/http-message/blob/14b9b813c5e36af4498ef38ef97938bf7090fd52
/src/StreamInterface.php), которые зашифровывают и расшифровывают их по алгоритмам, используемым WhatsApp.

## Алгоритм шифрования

1. Generate your own `mediaKey`, which needs to be 32 bytes, or use an existing one when available.
2. Expand it to 112 bytes using HKDF with SHA-256 and type-specific application info (see below). Call this value `mediaKeyExpanded`.
3. Split `mediaKeyExpanded` into:
	- `iv`: `mediaKeyExpanded[:16]`
	- `cipherKey`: `mediaKeyExpanded[16:48]`
	- `macKey`: `mediaKeyExpanded[48:80]`
	- `refKey`: `mediaKeyExpanded[80:]` (not used)
4. Encrypt the file with AES-CBC using `cipherKey` and `iv`, pad it and call it `enc`. 
5. Sign `iv + enc` with `macKey` using HMAC SHA-256 and store the first 10 bytes of the hash as `mac`.
6. Append `mac` to the `enc` to obtain the result.

## Алгоритм дешифрования

1. Obtain `mediaKey`.
2. Expand it to 112 bytes using HKDF with SHA-256 and type-specific application info (see below). Call this value `mediaKeyExpanded`.
3. Split `mediaKeyExpanded` into:
	- `iv`: `mediaKeyExpanded[:16]`
	- `cipherKey`: `mediaKeyExpanded[16:48]`
	- `macKey`: `mediaKeyExpanded[48:80]`
	- `refKey`: `mediaKeyExpanded[80:]` (not used)
4. Obtain encrypted media data and split it into:
	- `file`: `mediaData[:-10]`
	- `mac`: `mediaData[-10:]`
5. Validate media data with HMAC by signing `iv + file` with `macKey` using SHA-256. Take in mind that `mac` is truncated to 10 bytes, so you should compare only the first 10 bytes.
6. Decrypt `file` with AES-CBC using `cipherKey` and `iv`, and unpad it to obtain the result.

## Информационные строки для HKDF

HKDF позволяет указывать информационные строки, специфичные для контекста/приложения.
В данном случае контекстом является тип файла, для каждого из которых своя информационная строка:

| Media Type | Application Info         |
| ---------- | ------------------------ |
| IMAGE      | `WhatsApp Image Keys`    |
| VIDEO      | `WhatsApp Video Keys`    |
| AUDIO      | `WhatsApp Audio Keys`    |
| DOCUMENT   | `WhatsApp Document Keys` |


## Usage examples

```php
use GuzzleHttp\Psr7\Stream;
use Thermonuclear\WhatsappCipher\DecryptStream;
use Thermonuclear\WhatsappCipher\EncryptStream;

$stream = new Stream(fopen('./samples/AUDIO.original', 'r+'));
(new EncryptStream($stream, 'AUDIO', file_get_contents('./samples/AUDIO.key')))->createEncryptedFile('./samples/enc');

$stream = new Stream(fopen('./samples/enc', 'r+'));
(new DecryptStream($stream, 'AUDIO', file_get_contents('./samples/AUDIO.key')))->createDecryptedFile('./samples/dec');
