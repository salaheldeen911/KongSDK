<?php

namespace PDFKong\Contracts;

/**
 * @method $this pageSize(string $size)
 * @method $this orientation(string $orientation)
 * @method $this filename(string $name)
 * @method $this customSize(string $width, string $height)
 * @method $this httpAuth(string $username, string $password)
 * @method $this location(float $lat, float $lng, $accuracy = 100)
 * @method $this withTextWatermark(string $text, int $fontSize = 48, string $color = '#cccccc', int $opacity = 20)
 * @method $this withImageWatermark(string $url, ?string $width = null, ?string $height = null, int $opacity = 20)
 * @method $this watermark(bool $enable = true)
 * @method $this watermarkText(string $text)
 * @method $this watermarkImg(string $url)
 * @method $this watermarkOpacity(int $opacity)
 * @method $this encrypt(bool $enable = true)
 * @method $this ownerPassword(string $password)
 * @method $this userPassword(string $password)
 * @method $this js(string $js)
 * @method $this css(string $css)
 * @method $this noImages(bool $enable = true)
 * @method $this noLinks(bool $enable = true)
 * @method $this noJavascript(bool $enable = true)
 * @method $this waitForSelector(string $selector)
 * @method $this waitForTimeout(int $milliseconds)
 * @method $this generateDocumentOutline(bool $enable = true)
 * @method $this headerHtml(string $html)
 * @method $this footerHtml(string $html)
 * @method $this parseLiquid(bool $enable = true)
 * @method $this liquidData(array|string $data)
 */
interface PDFKongClientInterface
{
    /**
     * Start a conversion from a URL.
     *
     * @param string $url
     * @return \PDFKong\PDFKongClient
     */
    public function url(string $url);

    /**
     * Start a conversion from HTML content.
     *
     * @param string $html
     * @return \PDFKong\PDFKongClient
     */
    public function html(string $html);

    /**
     * Start a conversion from a local Office/File path.
     *
     * @param string $filePath
     * @return \PDFKong\PDFKongClient
     */
    public function file(string $filePath);

    /**
     * Start a conversion from Markdown content.
     *
     * @param string $markdown
     * @return \PDFKong\PDFKongClient
     */
    public function markdown(string $markdown);

    /**
     * Send the request and save the output to the specified path.
     *
     * @param string $path
     * @return bool
     */
    public function save(string $path): bool;

    /**
     * Send the request and return the raw PDF bytes.
     *
     * @return string
     */
    public function getAsBytes(): string;
}
