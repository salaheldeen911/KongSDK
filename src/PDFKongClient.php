<?php

namespace PDFKong;

use Illuminate\Support\Facades\Http;
use PDFKong\Contracts\PDFKongClientInterface;
use PDFKong\Exceptions\PDFKongException;

class PDFKongClient implements PDFKongClientInterface
{
    /**
     * The payload that will be sent to the API.
     *
     * @var array
     */
    protected array $payload = [];

    /**
     * The file path if uploading an office file.
     *
     * @var string|null
     */
    protected ?string $filePath = null;

    /**
     * Start a conversion from a URL.
     *
     * @param string $url
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
     * @param string $html
     * @return $this
     */
    public function html(string $html): self
    {
        $this->payload['mode'] = 'html';
        $this->payload['html'] = $html;
        return $this;
    }

    /**
     * Start a conversion from a local Office/File path.
     *
     * @param string $filePath
     * @return $this
     */
    public function file(string $filePath): self
    {
        $this->payload['mode'] = 'office';
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * Start a conversion from Markdown content.
     *
     * @param string $markdown
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
     * @param string $top
     * @param string $right
     * @param string $bottom
     * @param string $left
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
     * @param string $width
     * @param string $height
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
     * @param string $username
     * @param string $password
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
     * @param float $lat
     * @param float $lng
     * @param int|float $accuracy
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
     * @param string $text
     * @param int $fontSize
     * @param string $color
     * @param int $opacity (0-100)
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
     * @param string $url
     * @param string|null $width
     * @param string|null $height
     * @param int $opacity (0-100)
     * @return $this
     */
    public function withImageWatermark(string $url, ?string $width = null, ?string $height = null, int $opacity = 20): self
    {
        $this->payload['watermark'] = true;
        $this->payload['watermark_img'] = $url;
        $this->payload['watermark_opacity'] = $opacity;
        
        if ($width) $this->payload['watermark_img_width'] = $width;
        if ($height) $this->payload['watermark_img_height'] = $height;
        
        return $this;
    }

    /**
     * Deliver the result to S3.
     *
     * @param array $config Pass an array of S3 config, or leave empty to use default config.
     * @return $this
     */
    public function deliverToS3(array $config = []): self
    {
        $this->payload['delivery_mode'] = 's3';
        
        $this->payload['s3_bucket_name'] = $config['bucket_name'] ?? config('pdfkong.s3.bucket_name');
        $this->payload['s3_access_key_id'] = $config['access_key_id'] ?? config('pdfkong.s3.access_key_id');
        $this->payload['s3_secret_access_key'] = $config['secret_access_key'] ?? config('pdfkong.s3.secret_access_key');
        $this->payload['s3_region'] = $config['region'] ?? config('pdfkong.s3.region');
        
        $key = $config['bucket_key'] ?? null;
        if (!$key && config('pdfkong.s3.path_prefix')) {
            $key = config('pdfkong.s3.path_prefix') . uniqid('pdf_') . '.pdf';
        }
        $this->payload['s3_bucket_key'] = $key;
        
        return $this;
    }

    /**
     * Deliver the result to a Webhook.
     *
     * @param string|null $endpoint
     * @return $this
     */
    public function deliverToWebhook(?string $endpoint = null): self
    {
        $this->payload['delivery_mode'] = 'webhook';
        $this->payload['webhook_endpoint'] = $endpoint ?? config('pdfkong.webhook.default_endpoint');
        return $this;
    }

    /**
     * Append a custom parameter to the payload.
     *
     * @param string $key
     * @param mixed $value
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
        $apiKey = config('pdfkong.api_key');

        if (empty($apiKey)) {
            throw new PDFKongException('PDFKong API key is missing. Please set PDFKONG_API_KEY in your .env file.');
        }

        if (!isset($this->payload['mode'])) {
            throw new PDFKongException('You must specify a conversion source (e.g., url(), html(), file(), or markdown()) before sending the request.');
        }

        if ($this->payload['mode'] === 'office' && empty($this->filePath)) {
            throw new PDFKongException('File path is required for office/file conversion mode.');
        }

        if (config('pdfkong.store_file') && !isset($this->payload['store_file'])) {
            $this->payload['store_file'] = true;
        }
    }

    /**
     * Build the HTTP request client with headers.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function buildRequest()
    {
        $apiKey = config('pdfkong.api_key');
        $secretKey = config('pdfkong.secret_key');
        $timeout = config('pdfkong.timeout', 30);

        $request = Http::timeout($timeout)
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->withToken($apiKey);
            
        // If the user has a secret key, we might need to handle it.
        // Usually, the API requires the secret key in the payload or header for hashing.
        // Assuming the API accepts it in the payload:
        if ($secretKey) {
            $this->payload['secret_key'] = $secretKey;
        }

        return $request;
    }

    /**
     * Send the request and return the raw PDF bytes.
     *
     * @return string
     * @throws PDFKongException
     */
    public function getAsBytes(): string
    {
        $this->preValidate();

        $baseUrl = config('pdfkong.base_url', 'https://pdfkong.maktaby.online/api/v1');
        $endpoint = rtrim($baseUrl, '/') . '/convert';

        $request = $this->buildRequest();

        if ($this->payload['mode'] === 'office') {
            $filename = basename($this->filePath);
            $response = $request->attach('file', file_get_contents($this->filePath), $filename)
                ->post($endpoint, $this->payload);
        } else {
            $response = $request->post($endpoint, $this->payload);
        }

        if ($response->failed()) {
            throw new PDFKongException('PDFKong API Error: ' . $response->body(), $response->status());
        }

        return $response->body();
    }

    /**
     * Send the request and save the output to the specified path.
     *
     * @param string $path
     * @return bool
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
     * @throws PDFKongException
     */
    public function send(): array
    {
        // Force delivery mode to JSON if not set, though the API defaults to JSON anyway.
        $this->preValidate();

        $baseUrl = config('pdfkong.base_url', 'https://pdfkong.maktaby.online/api/v1');
        $endpoint = rtrim($baseUrl, '/') . '/convert';

        $request = $this->buildRequest();

        if ($this->payload['mode'] === 'office') {
            $filename = basename($this->filePath);
            $response = $request->attach('file', file_get_contents($this->filePath), $filename)
                ->post($endpoint, $this->payload);
        } else {
            $response = $request->post($endpoint, $this->payload);
        }

        if ($response->failed()) {
            throw new PDFKongException('PDFKong API Error: ' . $response->body(), $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Handle dynamic method calls for all API parameters.
     * Maps camelCase methods to snake_case API payload keys.
     *
     * @param string $method
     * @param array $parameters
     * @return $this
     */
    public function __call(string $method, array $parameters)
    {
        $key = \Illuminate\Support\Str::snake($method);
        
        // If no parameter is passed, assume it's a boolean true toggle (e.g., ->noImages())
        $this->payload[$key] = empty($parameters) ? true : $parameters[0];
        
        return $this;
    }
}
