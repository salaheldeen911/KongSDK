<?php

namespace PDFKong\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \PDFKong\Contracts\PDFKongClientInterface url(string $url)
 * @method static \PDFKong\Contracts\PDFKongClientInterface html(string $html)
 * @method static \PDFKong\Contracts\PDFKongClientInterface file(string $filePath)
 * @method static \PDFKong\Contracts\PDFKongClientInterface markdown(string $markdown)
 * @method static \PDFKong\Contracts\PDFKongClientInterface pageSize(string $size)
 * @method static \PDFKong\Contracts\PDFKongClientInterface orientation(string $orientation)
 * @method static \PDFKong\Contracts\PDFKongClientInterface filename(string $name)
 * @method static \PDFKong\Contracts\PDFKongClientInterface customSize(string $width, string $height)
 * @method static \PDFKong\Contracts\PDFKongClientInterface httpAuth(string $username, string $password)
 * @method static \PDFKong\Contracts\PDFKongClientInterface location(float $lat, float $lng, $accuracy = 100)
 * @method static \PDFKong\Contracts\PDFKongClientInterface withTextWatermark(string $text, int $fontSize = 48, string $color = '#cccccc', int $opacity = 20)
 * @method static \PDFKong\Contracts\PDFKongClientInterface withImageWatermark(string $url, ?string $width = null, ?string $height = null, int $opacity = 20)
 * @method static \PDFKong\Contracts\PDFKongClientInterface watermark(bool $enable = true)
 * @method static \PDFKong\Contracts\PDFKongClientInterface watermarkText(string $text)
 * @method static \PDFKong\Contracts\PDFKongClientInterface watermarkImg(string $url)
 * @method static \PDFKong\Contracts\PDFKongClientInterface watermarkOpacity(int $opacity)
 * @method static \PDFKong\Contracts\PDFKongClientInterface encrypt(bool $enable = true)
 * @method static \PDFKong\Contracts\PDFKongClientInterface ownerPassword(string $password)
 * @method static \PDFKong\Contracts\PDFKongClientInterface userPassword(string $password)
 * @method static \PDFKong\Contracts\PDFKongClientInterface js(string $js)
 * @method static \PDFKong\Contracts\PDFKongClientInterface css(string $css)
 * @method static \PDFKong\Contracts\PDFKongClientInterface noImages(bool $enable = true)
 * @method static \PDFKong\Contracts\PDFKongClientInterface noLinks(bool $enable = true)
 * @method static \PDFKong\Contracts\PDFKongClientInterface noJavascript(bool $enable = true)
 * @method static \PDFKong\Contracts\PDFKongClientInterface waitForSelector(string $selector)
 * @method static \PDFKong\Contracts\PDFKongClientInterface waitForTimeout(int $milliseconds)
 * @method static \PDFKong\Contracts\PDFKongClientInterface generateDocumentOutline(bool $enable = true)
 * @method static \PDFKong\Contracts\PDFKongClientInterface headerHtml(string $html)
 * @method static \PDFKong\Contracts\PDFKongClientInterface footerHtml(string $html)
 * @method static \PDFKong\Contracts\PDFKongClientInterface parseLiquid(bool $enable = true)
 * @method static \PDFKong\Contracts\PDFKongClientInterface liquidData(array|string $data)
 * 
 * @see \PDFKong\PDFKongClient
 */
class PDFKong extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pdfkong';
    }
}
