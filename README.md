# PDFKong Laravel SDK

A simple, fluent, and powerful Laravel package for interacting with the **PDFKong API**. Convert URLs, HTML, Office documents, and Markdown into high-quality PDF files with ease.

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

PDFKong::file(storage_path('app/documents/invoice.docx'))
    ->save(storage_path('app/public/invoice.pdf'));
```

### 4. Convert Markdown to PDF
```php
use PDFKong\Facades\PDFKong;

PDFKong::markdown('# Title \n\n This is a **markdown** text.')
    ->save(storage_path('app/public/markdown.pdf'));
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

### Deliver via Webhook
If you set `PDFKONG_WEBHOOK_URL` in your `.env`, the package will deduce the URL automatically:
```php
PDFKong::url('https://example.com')
    ->deliverToWebhook() // Reads default from config, or pass a custom URL here
    ->send(); // Use send() instead of save() to avoid downloading bytes
```

### Deliver via Amazon S3
Ensure your S3 credentials are set in `.env` (like `PDFKONG_S3_BUCKET`).
```php
PDFKong::url('https://example.com')
    ->deliverToS3() // Uploads directly to your configured S3 Bucket
    ->send();
```

---

## 🔧 Magic Methods for Extra Parameters
If the API supports a new parameter (e.g., `prefer_css_page_size` or `print_background`), you can call it using camelCase. The SDK will automatically convert it to `snake_case` for the API.

```php
PDFKong::url('https://example.com')
    ->printBackground(true) // Maps to 'print_background' => true
    ->landscape()           // Maps to 'landscape' => true (if no arg provided, defaults to true)
    ->emulateMedia('screen') // Maps to 'emulate_media' => 'screen'
    ->save('output.pdf');
```

---

## 📊 Available API Parameters Reference

Below is a quick reference table of core parameters supported by the API.

| Parameter | Type | Default | Description |
|---|---|---|---|
| `mode` | string | *required* | Source type: `url`, `html`, `office`, `markdown` |
| `url` | string | null | The webpage URL to convert (If `mode=url`). |
| `html` | string | null | Raw HTML string to convert (If `mode=html`). |
| `markdown` | string | null | Raw Markdown to convert (If `mode=markdown`). |
| `margin_top` / `bottom` / `right` / `left` | string | `0px` | Page margins (e.g. `10px`, `0.5in`, `1cm`). |
| `page_size` | string | `A4` | Page format: `A4`, `Letter`, `Legal`, `Custom`, etc. |
| `landscape` | boolean | `false` | Set to true to print in landscape orientation. |
| `print_background` | boolean | `true` | Print background graphics and colors. |
| `emulate_media` | string | `print` | CSS media emulation: `print` or `screen`. |
| `store_file` | boolean | `false` | Keeps the generated file on PDFKong servers for 24 hours. |
| `delivery_mode` | string | `json` | Where to deliver the output: `json`, `webhook`, `s3`. |
| `watermark` | boolean | `false` | Enables watermarking if set to true. |
| `watermark_text` | string | null | The text used for watermarking. |
| `watermark_img` | string | null | URL of the image used for watermarking. |

*(Note: Use the magic methods or the `->with('key', 'value')` method to pass any extra parameters directly).*

## 📄 License
The MIT License (MIT).
