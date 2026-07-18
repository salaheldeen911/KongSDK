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
     * Start a conversion from an Office file.
     *
     * @param string $filePath
     * @return \PDFKong\PDFKongClient
     */
    public function office(string $filePath);

    /**
     * Start a conversion from an image file.
     *
     * @param string $filePath
     * @return \PDFKong\PDFKongClient
     */
    public function image(string $filePath);

    /**
     * Merge multiple PDFs.
     *
     * @param array $filePaths
     * @return \PDFKong\PDFKongClient
     */
    public function merge(array $filePaths);

    /**
     * Add a watermark to a PDF.
     *
     * @param string $filePath
     * @return \PDFKong\PDFKongClient
     */
    public function watermark(string $filePath);

    /**
     * Protect a PDF.
     *
     * @param string $filePath
     * @return \PDFKong\PDFKongClient
     */
    public function protect(string $filePath);

    /**
     * Start a raw payload request.
     *
     * @param array $payload
     * @param array $files
     * @return \PDFKong\PDFKongClient
     */
    public function raw(array $payload, array $files = []);

    /**
     * Get the parameters schema from the API.
     *
     * @return array
     */
    public function schema(): array;

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

    /**
     * Get the current user's usage and credits.
     *
     * @return array
     */
    public function usage(): array;

    /**
     * Get a paginated list of previously generated PDFs.
     *
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function list(int $page = 1, int $perPage = 15): array;

    /**
     * Delete a specific PDF file from the server.
     *
     * @param string $taskId
     * @return array
     */
    public function remove(string $taskId): array;

    /**
     * Check the status of an asynchronous batch job.
     *
     * @param string $batchId
     * @return array
     */
    public function batchStatus(string $batchId): array;

    /**
     * Download a completed batch job.
     *
     * @param string $batchId
     * @param string $savePath
     * @return bool
     */
    public function batchDownload(string $batchId, string $savePath): bool;

    /**
     * Run the conversion asynchronously in the background.
     *
     * @param bool $enable
     * @return \PDFKong\PDFKongClient
     */
    public function async(bool $enable = true);

    /**
     * Specify the number of times to retry the request if it fails.
     *
     * @param int $times
     * @param int $sleepMilliseconds
     * @return \PDFKong\PDFKongClient
     */
    public function retry(int $times, int $sleepMilliseconds = 0);

    /**
     * Add custom HTTP client options (e.g. proxy, verify).
     *
     * @param array $options
     * @return \PDFKong\PDFKongClient
     */
    public function withOptions(array $options);
}
