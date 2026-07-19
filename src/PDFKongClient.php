<?php

namespace PDFKong;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PDFKong\Contracts\PDFKongClientInterface;
use PDFKong\Exceptions\PDFKongAuthenticationException;
use PDFKong\Exceptions\PDFKongException;
use PDFKong\Exceptions\PDFKongInsufficientCreditsException;
use PDFKong\Exceptions\PDFKongRateLimitException;
use PDFKong\Exceptions\PDFKongValidationException;

class PDFKongClient implements PDFKongClientInterface
{
    /**
     * The payload that will be sent to the API.
     */
    protected array $payload = [];

    /**
     * The file path if uploading an office file.
     */
    protected ?string $filePath = null;

    /**
     * Array of files for multi-file operations.
     */
    protected array $files = [];

    /**
     * Flag indicating a raw payload request.
     */
    protected bool $isRaw = false;

    /**
     * Retry times.
     */
    protected int $retryTimes = 0;

    /**
     * Retry sleep milliseconds.
     */
    protected int $retrySleep = 0;

    /**
     * Custom HTTP options.
     */
    protected array $httpOptions = [];

    /**
     * Specify the number of times to retry the request if it fails.
     *
     * @return $this
     */
    public function retry(int $times, int $sleepMilliseconds = 0): self
    {
        $this->retryTimes = $times;
        $this->retrySleep = $sleepMilliseconds;

        return $this;
    }

    /**
     * Add custom HTTP client options (e.g. proxy, verify).
     *
     * @return $this
     */
    public function withOptions(array $options): self
    {
        $this->httpOptions = $options;

        return $this;
    }

    /**
     * Start a conversion from a URL.
     *
     * @return $this
     */
    public function url(string $url): self
    {
        $this->payload['mode'] = 'url';
        $this->payload['url'] = $url;

        return $this;
    }

    /**
     * Start a conversion from HTML content.
     *
     * @return $this
     */
    public function html(string $html): self
    {
        $this->payload['mode'] = 'html';
        $this->payload['html'] = $html;

        return $this;
    }

    /**
     * Start a conversion from an Office file.
     *
     * @return $this
     */
    public function office(string $filePath): self
    {
        $this->payload['mode'] = 'office';
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Start a conversion from an image file.
     *
     * @return $this
     */
    public function image(string $filePath): self
    {
        $this->payload['mode'] = 'image';
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Merge multiple PDFs.
     *
     * @return $this
     */
    public function merge(array $filePaths): self
    {
        $this->payload['mode'] = 'merge';
        $this->files = $filePaths;

        return $this;
    }

    /**
     * Add a watermark to a PDF.
     *
     * @return $this
     */
    public function watermark(string $filePath): self
    {
        $this->payload['mode'] = 'watermark';
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Protect a PDF.
     *
     * @return $this
     */
    public function protect(string $filePath): self
    {
        $this->payload['mode'] = 'protect';
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Start a raw payload request.
     *
     * @return $this
     */
    public function raw(array $payload, array $files = []): self
    {
        $this->isRaw = true;
        $this->payload = $payload;
        $this->files = $files;

        return $this;
    }

    /**
     * Get the parameters schema from the API.
     */
    public function schema(): array
    {
        $baseUrl = config('pdfkong.base_url', 'https://pdfkong.online/api/v1');
        $endpoint = rtrim($baseUrl, '/').'/parameters';

        $request = $this->buildRequest();

        $response = $request->get($endpoint);

        if ($response->successful()) {
            return $response->json('data') ?? [];
        }

        return [];
    }

    /**
     * Start a conversion from Markdown content.
     *
     * @return $this
     */
    public function markdown(string $markdown): self
    {
        $this->payload['mode'] = 'markdown';
        $this->payload['markdown'] = $markdown;

        return $this;
    }

    /**
     * Set the margins.
     *
     * @return $this
     */
    public function margins(string $top = '0px', string $right = '0px', string $bottom = '0px', string $left = '0px'): self
    {
        $this->payload['margin_top'] = $top;
        $this->payload['margin_right'] = $right;
        $this->payload['margin_bottom'] = $bottom;
        $this->payload['margin_left'] = $left;

        return $this;
    }

    /**
     * Set a custom page size.
     *
     * @return $this
     */
    public function customSize(string $width, string $height): self
    {
        $this->payload['page_size'] = 'Custom';
        $this->payload['page_width'] = $width;
        $this->payload['page_height'] = $height;

        return $this;
    }

    /**
     * Set HTTP Basic Authentication credentials.
     *
     * @return $this
     */
    public function httpAuth(string $username, string $password): self
    {
        $this->payload['auth_user'] = $username;
        $this->payload['auth_password'] = $password;

        return $this;
    }

    /**
     * Emulate a geolocation for the browser.
     *
     * @param  int|float  $accuracy
     * @return $this
     */
    public function location(float $lat, float $lng, $accuracy = 100): self
    {
        $this->payload['location_lat'] = $lat;
        $this->payload['location_lng'] = $lng;
        $this->payload['location_accuracy'] = $accuracy;

        return $this;
    }

    /**
     * Add a text watermark to the PDF.
     *
     * @param  int  $opacity  (0-100)
     * @return $this
     */
    public function withTextWatermark(string $text, int $fontSize = 48, string $color = '#cccccc', int $opacity = 20): self
    {
        $this->payload['watermark'] = true;
        $this->payload['watermark_text'] = $text;
        $this->payload['watermark_font_size'] = $fontSize;
        $this->payload['watermark_font_color'] = $color;
        $this->payload['watermark_opacity'] = $opacity;

        return $this;
    }

    /**
     * Add an image watermark to the PDF.
     *
     * @param  int  $opacity  (0-100)
     * @return $this
     */
    public function withImageWatermark(string $url, ?string $width = null, ?string $height = null, int $opacity = 20): self
    {
        $this->payload['watermark'] = true;
        $this->payload['watermark_img'] = $url;
        $this->payload['watermark_opacity'] = $opacity;

        if ($width) {
            $this->payload['watermark_img_width'] = $width;
        }
        if ($height) {
            $this->payload['watermark_img_height'] = $height;
        }

        return $this;
    }

    /**
     * Deliver the result to S3.
     *
     * @param  array  $config  Pass an array of S3 config, or leave empty to use default config.
     * @return $this
     */
    public function deliverToS3(array $config = []): self
    {
        $this->payload['delivery_mode'] = 's3';
        $this->payload['async'] = true; // Auto-enable async for S3 delivery

        $this->payload['s3_bucket_name'] = $config['bucket_name'] ?? config('pdfkong.s3.bucket_name');
        $this->payload['s3_access_key_id'] = $config['access_key_id'] ?? config('pdfkong.s3.access_key_id');
        $this->payload['s3_secret_access_key'] = $config['secret_access_key'] ?? config('pdfkong.s3.secret_access_key');
        $this->payload['s3_region'] = $config['region'] ?? config('pdfkong.s3.region');

        $key = $config['bucket_key'] ?? null;
        if (! $key && config('pdfkong.s3.path_prefix')) {
            $key = config('pdfkong.s3.path_prefix').uniqid('pdf_').'.pdf';
        }
        $this->payload['s3_bucket_key'] = $key;

        return $this;
    }

    /**
     * Deliver the result to Google Cloud Storage.
     *
     * @param  array  $config  Pass an array of GCP config, or leave empty to use default config.
     * @return $this
     */
    public function deliverToGoogleStorage(array $config = []): self
    {
        $this->payload['delivery_mode'] = 'google_storage';
        $this->payload['async'] = true; // Auto-enable async for GCS delivery

        $this->payload['gcp_project_id'] = $config['project_id'] ?? config('pdfkong.google_storage.project_id');
        $this->payload['gcp_user_email'] = $config['user_email'] ?? config('pdfkong.google_storage.user_email');
        $this->payload['gcp_private_key'] = $config['private_key'] ?? config('pdfkong.google_storage.private_key');
        $this->payload['gcp_bucket_name'] = $config['bucket_name'] ?? config('pdfkong.google_storage.bucket_name');

        return $this;
    }

    /**
     * Request the API to return the PDF as a Base64 string.
     *
     * @return $this
     */
    public function returnAsBase64(): self
    {
        $this->payload['delivery_mode'] = 'base64';

        return $this;
    }

    /**
     * Deliver the result to a Webhook.
     *
     * @return $this
     */
    public function deliverToWebhook(?string $endpoint = null): self
    {
        $this->payload['delivery_mode'] = 'webhook';
        $this->payload['async'] = true; // Auto-enable async for Webhook delivery
        $this->payload['webhook_endpoint'] = $endpoint ?? config('pdfkong.webhook.default_endpoint');

        return $this;
    }

    /**
     * Append a custom parameter to the payload.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function with(string $key, $value): self
    {
        $this->payload[$key] = $value;

        return $this;
    }

    /**
     * Pre-validate the request before sending it.
     *
     * @throws PDFKongException
     */
    protected function preValidate(): void
    {
        if ($this->isRaw) {
            return;
        }

        $apiKey = config('pdfkong.api_key');

        if (empty($apiKey)) {
            throw new PDFKongException('PDFKong API key is missing. Please set PDFKONG_API_KEY in your .env file.');
        }

        if (! isset($this->payload['mode'])) {
            throw new PDFKongException('You must specify a conversion source (e.g., url(), html(), file(), or markdown()) before sending the request.');
        }

        if (in_array($this->payload['mode'], ['office', 'image', 'merge', 'watermark', 'protect']) && empty($this->filePath) && empty($this->files)) {
            throw new PDFKongException('File path(s) required for this conversion mode.');
        }

        if (config('pdfkong.store_file') && ! isset($this->payload['store_file'])) {
            $this->payload['store_file'] = true;
        }
    }

    /**
     * Build the HTTP request client with headers.
     *
     * @return PendingRequest
     */
    protected function buildRequest()
    {
        $apiKey = config('pdfkong.api_key');
        $secretKey = config('pdfkong.secret_key');
        $timeout = config('pdfkong.timeout', 30);

        $request = Http::timeout($timeout)
            ->withOptions($this->httpOptions)
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->withToken($apiKey);

        if ($this->retryTimes > 0) {
            $request->retry($this->retryTimes, $this->retrySleep);
        }

        // If the user has a secret key, we might need to handle it.
        // Usually, the API requires the secret key in the payload or header for hashing.
        // Assuming the API accepts it in the payload:
        if ($secretKey) {
            $this->payload['secret_key'] = $secretKey;
        }

        return $request;
    }

    /**
     * Handle failed API responses and throw specific exceptions.
     *
     * @param  Response  $response
     *
     * @throws PDFKongException
     */
    protected function handleFailedResponse($response): void
    {
        $status = $response->status();
        $message = 'PDFKong API Error: '.$response->body();

        switch ($status) {
            case 401:
            case 403:
                throw new PDFKongAuthenticationException($message, $status);
            case 402:
                throw new PDFKongInsufficientCreditsException($message, $status);
            case 422:
                throw new PDFKongValidationException($message, $status);
            case 429:
                throw new PDFKongRateLimitException($message, $status);
            default:
                throw new PDFKongException($message, $status);
        }
    }

    /**
     * Send the request and return the raw PDF bytes.
     *
     * @throws PDFKongException
     */
    public function getAsBytes(): string
    {
        $response = $this->executeConversionRequest();

        return $response->body();
    }

    /**
     * Send the request and save the output to the specified path.
     *
     * @throws PDFKongException
     */
    public function save(string $path): bool
    {
        $bytes = $this->getAsBytes();

        return file_put_contents($path, $bytes) !== false;
    }

    /**
     * Send the request (useful for webhook/S3 delivery where we don't need the bytes back).
     *
     * @return array The JSON response from the API.
     *
     * @throws PDFKongException
     */
    public function send(): array
    {
        $response = $this->executeConversionRequest();

        return $response->json() ?? [];
    }

    /**
     * Execute the conversion request to the API.
     *
     * @return Response
     *
     * @throws PDFKongException
     */
    protected function executeConversionRequest()
    {
        $this->preValidate();

        $baseUrl = config('pdfkong.base_url', 'https://pdfkong.online/api/v1');
        $endpoint = rtrim($baseUrl, '/').'/convert';

        $request = $this->buildRequest();

        if (! empty($this->files)) {
            $request = $request->asMultipart();
            foreach ($this->files as $key => $file) {
                if (is_array($file)) {
                    foreach ($file as $f) {
                        if ($f instanceof UploadedFile) {
                            $request->attach($key.'[]', file_get_contents($f->path()), $f->getClientOriginalName());
                        } else {
                            $request->attach($key.'[]', file_get_contents($f), basename($f));
                        }
                    }
                } else {
                    if ($file instanceof UploadedFile) {
                        $request->attach($key, file_get_contents($file->path()), $file->getClientOriginalName());
                    } else {
                        // Use a generic key 'files[]' if numeric index, else use the string key
                        $formKey = is_int($key) ? 'files[]' : $key;
                        $request->attach($formKey, file_get_contents($file), basename($file));
                    }
                }
            }
            $response = $request->post($endpoint, $this->payload);
        } elseif (! empty($this->filePath)) {
            $filename = basename($this->filePath);
            $response = $request->attach('file', file_get_contents($this->filePath), $filename)
                ->post($endpoint, $this->payload);
        } else {
            $response = $request->post($endpoint, $this->payload);
        }

        if ($response->failed()) {
            $this->handleFailedResponse($response);
        }

        return $response;
    }

    /**
     * Get the current user's usage and credits.
     *
     * @throws PDFKongException
     */
    public function usage(): array
    {
        $baseUrl = config('pdfkong.base_url', 'https://pdfkong.online/api/v1');
        $endpoint = rtrim($baseUrl, '/').'/usage';
        $response = $this->buildRequest()->get($endpoint);

        if ($response->failed()) {
            $this->handleFailedResponse($response);
        }

        return $response->json() ?? [];
    }

    /**
     * Get a paginated list of previously generated PDFs.
     *
     * @throws PDFKongException
     */
    public function list(int $page = 1, int $perPage = 15): array
    {
        $baseUrl = config('pdfkong.base_url', 'https://pdfkong.online/api/v1');
        $endpoint = rtrim($baseUrl, '/').'/list';
        $response = $this->buildRequest()->get($endpoint, [
            'page' => $page,
            'per_page' => $perPage,
        ]);

        if ($response->failed()) {
            $this->handleFailedResponse($response);
        }

        return $response->json() ?? [];
    }

    /**
     * Delete a specific PDF file from the server.
     *
     * @throws PDFKongException
     */
    public function remove(string $taskId): array
    {
        $baseUrl = config('pdfkong.base_url', 'https://pdfkong.online/api/v1');
        $endpoint = rtrim($baseUrl, '/').'/remove/'.$taskId;
        $response = $this->buildRequest()->delete($endpoint);

        if ($response->failed()) {
            $this->handleFailedResponse($response);
        }

        return $response->json() ?? [];
    }

    /**
     * Check the status of an asynchronous batch job.
     *
     * @throws PDFKongException
     */
    public function batchStatus(string $batchId): array
    {
        $baseUrl = config('pdfkong.base_url', 'https://pdfkong.online/api/v1');
        $endpoint = rtrim($baseUrl, '/').'/batch/'.$batchId.'/status';
        $response = $this->buildRequest()->get($endpoint);

        if ($response->failed()) {
            $this->handleFailedResponse($response);
        }

        return $response->json() ?? [];
    }

    /**
     * Download a completed batch job.
     *
     * @throws PDFKongException
     */
    public function batchDownload(string $batchId, string $savePath): bool
    {
        $baseUrl = config('pdfkong.base_url', 'https://pdfkong.online/api/v1');
        $endpoint = rtrim($baseUrl, '/').'/batch/'.$batchId.'/download';
        $response = $this->buildRequest()->get($endpoint);

        if ($response->failed()) {
            $this->handleFailedResponse($response);
        }

        return file_put_contents($savePath, $response->body()) !== false;
    }

    /**
     * Run the conversion asynchronously in the background.
     *
     * @return $this
     */
    public function async(bool $enable = true): self
    {
        $this->payload['async'] = $enable;

        return $this;
    }

    /**
     * Handle dynamic method calls for all API parameters.
     * Maps camelCase methods to snake_case API payload keys.
     *
     * @return $this
     */
    public function __call(string $method, array $parameters)
    {
        $key = Str::snake($method);

        // If no parameter is passed, assume it's a boolean true toggle (e.g., ->noImages())
        $this->payload[$key] = empty($parameters) ? true : $parameters[0];

        return $this;
    }
}
