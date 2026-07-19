<?php

namespace PDFKong\Contracts;

use PDFKong\PDFKongClient;

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
 * @method $this deliverToS3(array $config = [])
 * @method $this deliverToWebhook(?string $endpoint = null)
 * @method $this deliverToGoogleStorage(array $config = [])
 * @method $this returnAsBase64()
 * @method $this deliveryMode(string $mode)
 */
interface PDFKongClientInterface
{
    /**
     * Start a conversion from a URL.
     *
     * @return PDFKongClient
     */
    public function url(string $url);

    /**
     * Start a conversion from HTML content.
     *
     * @return PDFKongClient
     */
    public function html(string $html);

    /**
     * Start a conversion from an Office file.
     *
     * @return PDFKongClient
     */
    public function office(string $filePath);

    /**
     * Start a conversion from an image file.
     *
     * @return PDFKongClient
     */
    public function image(string $filePath);

    /**
     * Merge multiple PDFs.
     *
     * @return PDFKongClient
     */
    public function merge(array $filePaths);

    /**
     * Add a watermark to a PDF.
     *
     * @return PDFKongClient
     */
    public function watermark(string $filePath);

    /**
     * Protect a PDF.
     *
     * @return PDFKongClient
     */
    public function protect(string $filePath);

    /**
     * Start a raw payload request.
     *
     * @return PDFKongClient
     */
    public function raw(array $payload, array $files = []);

    /**
     * Get the parameters schema from the API.
     */
    public function schema(): array;

    /**
     * Start a conversion from Markdown content.
     *
     * @return PDFKongClient
     */
    public function markdown(string $markdown);

    /**
     * Send the request and save the output to the specified path.
     */
    public function save(string $path): bool;

    /**
     * Send the request and return the raw PDF bytes.
     */
    public function getAsBytes(): string;

    /**
     * Get the current user's usage and credits.
     */
    public function usage(): array;

    /**
     * Get a paginated list of previously generated PDFs.
     */
    public function list(int $page = 1, int $perPage = 15): array;

    /**
     * Delete a specific PDF file from the server.
     */
    public function remove(string $taskId): array;

    /**
     * Check the status of an asynchronous batch job.
     */
    public function batchStatus(string $batchId): array;

    /**
     * Download a completed batch job.
     */
    public function batchDownload(string $batchId, string $savePath): bool;

    /**
     * Run the conversion asynchronously in the background.
     *
     * @return PDFKongClient
     */
    public function async(bool $enable = true);

    /**
     * Specify the number of times to retry the request if it fails.
     *
     * @return PDFKongClient
     */
    public function retry(int $times, int $sleepMilliseconds = 0);

    /**
     * Add custom HTTP client options (e.g. proxy, verify).
     *
     * @return PDFKongClient
     */
    public function withOptions(array $options);
}
