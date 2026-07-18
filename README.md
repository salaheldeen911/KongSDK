# PDFKong Laravel SDK

A simple, fluent, and powerful Laravel package for interacting with the **PDFKong API**. Convert URLs, HTML, Office documents, and Markdown into high-quality PDF files with ease.

📚 **PDFKong Documentation:** [https://pdfkong.online/docs/intro](https://pdfkong.online/docs/intro)

## 🚀 Installation

Install the package via Composer:

```bash
composer require pdfkong/pdfkong-laravel
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=pdfkong-config
```

## ⚙️ Configuration

After publishing, you'll find the configuration file at `config/pdfkong.php`. Add your PDFKong API credentials to your `.env` file:

```env
PDFKONG_API_KEY="your-api-key-here"
PDFKONG_BASE_URL="https://pdfkong.online/api/v1"

# Optional Settings
PDFKONG_STORE_FILE=true # Set to true to retain generated PDFs on the server for 24 hours
PDFKONG_TIMEOUT=30 # Timeout for API requests in seconds
```

## 📖 Basic Usage

You can use the `PDFKong` facade anywhere in your Laravel application.

### 1. Convert URL to PDF
```php
use PDFKong\Facades\PDFKong;

PDFKong::url('https://google.com')
    ->save(storage_path('app/public/google.pdf'));
```

### 2. Convert HTML to PDF
```php
use PDFKong\Facades\PDFKong;

$htmlContent = '<h1>Hello, PDFKong!</h1><p>This is generated from raw HTML.</p>';

$pdfBytes = PDFKong::html($htmlContent)
    ->margins('20px', '20px', '20px', '20px')
    ->getAsBytes(); // Returns raw PDF string, perfect for returning to the browser

return response($pdfBytes, 200, [
    'Content-Type' => 'application/pdf',
    'Content-Disposition' => 'inline; filename="hello.pdf"'
]);
```

### 3. Convert Office Documents (Word, Excel, PPT) to PDF
```php
use PDFKong\Facades\PDFKong;

PDFKong::office(storage_path('app/documents/invoice.docx'))
    ->save(storage_path('app/public/invoice.pdf'));
```

### 4. Convert Markdown to PDF
```php
use PDFKong\Facades\PDFKong;

PDFKong::markdown('# Title \n\n This is a **markdown** text.')
    ->save(storage_path('app/public/markdown.pdf'));
```

### 5. Convert Image to PDF
```php
use PDFKong\Facades\PDFKong;

PDFKong::image(storage_path('app/images/photo.jpg'))
    ->save(storage_path('app/public/photo.pdf'));
```

---

## 🎨 Formatting and Customization

The SDK provides fluent methods to customize your PDF output easily.

### Margins and Page Size
```php
PDFKong::url('https://example.com')
    ->margins('10px', '10px', '10px', '10px')
    ->customSize('8.5in', '11in') // e.g. Custom width and height
    ->save('output.pdf');
```

### Authentication and Location Emulation
If you are converting a webpage that requires Basic HTTP Authentication or specific geolocation:
```php
PDFKong::url('https://restricted-area.com')
    ->httpAuth('username', 'password')
    ->location(30.0444, 31.2357, 100) // Lat, Lng, Accuracy
    ->save('output.pdf');
```

### Watermarks
You can secure your documents by adding Text or Image watermarks:

**Text Watermark:**
```php
PDFKong::url('https://example.com')
    ->withTextWatermark('CONFIDENTIAL', 48, '#FF0000', 30) // Text, Font Size, Color, Opacity (0-100)
    ->save('output.pdf');
```

**Image Watermark:**
```php
PDFKong::url('https://example.com')
    ->withImageWatermark('https://your-domain.com/logo.png', '200px', '200px', 20)
    ->save('output.pdf');
```

---

## 🚀 Advanced Delivery (Asynchronous)

If you are processing large files, you might want to offload the delivery using **Webhooks** or **Amazon S3**, so your server doesn't have to wait.

*Note: Calling `deliverToWebhook()` or `deliverToS3()` will automatically enable the `async` parameter in the background, making the API return immediately.*

### Deliver via Webhook
If you set `PDFKONG_WEBHOOK_URL` in your `.env`, the package will deduce the URL automatically:
```php
PDFKong::url('https://example.com')
    ->deliverToWebhook() // Reads default from config, or pass a custom URL here
    ->send(); // Use send() instead of save() to avoid downloading bytes. It will return a JSON with a task_id immediately.
```

### Deliver via Amazon S3
Ensure your S3 credentials are set in `.env` (like `PDFKONG_S3_BUCKET`).
```php
PDFKong::url('https://example.com')
    ->deliverToS3() // Uploads directly to your configured S3 Bucket
    ->send(); // Returns JSON with task_id immediately.
```

### Deliver via Google Cloud Storage
Ensure your GCP credentials are set in `.env` (like `PDFKONG_GCP_BUCKET`).
```php
PDFKong::url('https://example.com')
    ->deliverToGoogleStorage() // Uploads directly to your configured GCS Bucket
    ->send();
```

### Deliver via Base64
The API supports returning the PDF as a Base64 string directly inside the JSON response. You can use the `returnAsBase64()` method to trigger this.
```php
// Get PDF as a Base64 String
$response = PDFKong::url('https://example.com')
    ->returnAsBase64()
    ->send();
    
echo $response['base64_data']; // Contains the raw base64 string
```

### Manual Async Mode
If you want to run the job asynchronously without using Webhooks or S3 (perhaps you want to poll the status manually using `batchStatus` or checking the file later), you must explicitly tell the API to store the file on its server:
```php
PDFKong::url('https://example.com')
    ->async() // Forces the API to process this in the background
    ->storeFile() // Required when using async without Webhook/S3
    ->send(); 
```

---

---

## 🛠️ Management & Utility Methods

The SDK also supports the management endpoints available in the API, allowing you to check usage, manage generated PDFs, and handle batch downloads.

```php
use PDFKong\Facades\PDFKong;

// 1. Get Account Usage & Limits
$usage = PDFKong::usage();
print_r($usage);

// 2. List previously generated PDFs (Pagination: page, per_page)
$list = PDFKong::list(1, 15);

// 3. Remove a specific PDF from the server
$removed = PDFKong::remove('task_uuid_here');

// 4. Check the status of an asynchronous batch job
$status = PDFKong::batchStatus('batch_uuid_here');

// 5. Download a completed batch job (e.g. ZIP file)
PDFKong::batchDownload('batch_uuid_here', storage_path('app/public/batch_output.zip'));
```

---

## 🔧 Magic Methods for Extra Parameters
If the API supports a new parameter (e.g., `prefer_css_page_size` or `print_background`), you can call it using camelCase. The SDK will automatically convert it to `snake_case` for the API. 
📚 **View all supported parameters here:** [https://pdfkong.online/docs/intro](https://pdfkong.online/docs/intro)

```php
PDFKong::url('https://example.com')
    ->printBackground(true) // Maps to 'print_background' => true
    ->landscape()           // Maps to 'landscape' => true (if no arg provided, defaults to true)
    ->emulateMedia('screen') // Maps to 'emulate_media' => 'screen'
    ->save('output.pdf');
```

## 🛡️ Enterprise Features

We've built this SDK to be robust and ready for large-scale applications.

### 1. Testing (Fake)
When writing your tests, you shouldn't hit the real API. Use `PDFKong::fake()` to mock the SDK.
```php
use PDFKong\Facades\PDFKong;

PDFKong::fake();

// Run your application code...
PDFKong::url('https://example.com')->save('test.pdf');

// Assert that a conversion was requested
PDFKong::assertConverted('url');
```

### 2. Auto-Retry Mechanism
Network instability happens. You can tell the SDK to automatically retry failed requests:
```php
PDFKong::url('https://example.com')
    ->retry(3, 100) // Retry up to 3 times, with a 100ms delay between attempts
    ->save('output.pdf');
```

### 3. Custom Guzzle/HTTP Options
If your server sits behind a proxy, or you need to disable SSL verification locally, you can pass custom options to the underlying Laravel HTTP Client:
```php
PDFKong::url('https://example.com')
    ->withOptions(['verify' => false, 'proxy' => 'http://localhost:8080'])
    ->save('output.pdf');
```

### 4. Custom Exceptions
The SDK throws specific exceptions based on the HTTP status code, allowing you to catch and handle them gracefully:
- `PDFKongAuthenticationException` (401/403)
- `PDFKongInsufficientCreditsException` (402)
- `PDFKongValidationException` (422)
- `PDFKongRateLimitException` (429)
- `PDFKongException` (General)

```php
use PDFKong\Exceptions\PDFKongInsufficientCreditsException;

try {
    PDFKong::url('https://example.com')->save('output.pdf');
} catch (PDFKongInsufficientCreditsException $e) {
    // Notify user to top up balance
}
```

### 5. Type-Safe Enums
Instead of using raw strings for parameters, you can use the provided Enums for type safety:
```php
use PDFKong\Enums\PageSize;
use PDFKong\Enums\DeliveryMode;

PDFKong::url('https://example.com')
    ->deliveryMode(DeliveryMode::S3->value)
    ->pageFormat(PageSize::A4->value)
    ->send();
```

---

## 📊 Available API Parameters Reference

Below is a quick reference table of core parameters supported by the API.

| Parameter | Type | Default | Description |
|---|---|---|---|
| `mode` | string | *required* | Source type: `url`, `html`, `office`, `markdown`, `image` *(Set automatically by SDK)* |
| `url` | string | null | The webpage URL to convert (If `mode=url`). |
| `html` | string | null | Raw HTML string to convert (If `mode=html`). |
| `markdown` | string | null | Raw Markdown to convert (If `mode=markdown`). |
| `margin_top` / `bottom` / `right` / `left` | string | `0px` | Page margins (e.g. `10px`, `0.5in`, `1cm`). |
| `page_size` | string | `A4` | Page format: `A4`, `Letter`, `Legal`, `Custom`, etc. |
| `landscape` | boolean | `false` | Set to true to print in landscape orientation. |
| `print_background` | boolean | `true` | Print background graphics and colors. |
| `emulate_media` | string | `print` | CSS media emulation: `print` or `screen`. |
| `store_file` | boolean | `false` | Keeps the generated file on PDFKong servers for 24 hours. |
| `delivery_mode` | string | `json` | Where to deliver the output: `json`, `webhook`, `s3`, `base64`, `google_storage`, `inline`, `attachment` |
| `watermark` | boolean | `false` | Enables watermarking if set to true. |
| `watermark_text` | string | null | The text used for watermarking. |
| `watermark_img` | string | null | URL of the image used for watermarking. |

*(Note: Use the magic methods or the `->with('key', 'value')` method to pass any extra parameters directly).*

## 📄 License
The MIT License (MIT).
