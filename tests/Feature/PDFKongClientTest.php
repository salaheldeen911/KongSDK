<?php

namespace PDFKong\Tests\Feature;

use Illuminate\Support\Facades\Http;
use PDFKong\Exceptions\PDFKongException;
use PDFKong\Facades\PDFKong;
use PDFKong\Tests\TestCase;

class PDFKongClientTest extends TestCase
{
    public function test_it_throws_exception_if_api_key_is_missing()
    {
        config(['pdfkong.api_key' => null]);

        $this->expectException(PDFKongException::class);
        $this->expectExceptionMessage('PDFKong API key is missing');

        PDFKong::url('https://example.com')->send();
    }

    public function test_it_throws_exception_if_mode_is_missing()
    {
        $this->expectException(PDFKongException::class);
        $this->expectExceptionMessage('You must specify a conversion source');

        // Accessing the client directly without setting url(), html(), etc.
        app('pdfkong')->send();
    }

    public function test_it_sends_a_successful_url_conversion_request()
    {
        Http::fake([
            'pdfkong.online/api/v1/convert' => Http::response(['task_id' => '123', 'status' => 'processing'], 200)
        ]);

        $response = PDFKong::url('https://example.com')->send();

        $this->assertEquals('123', $response['task_id']);
        
        Http::assertSent(function ($request) {
            return $request->url() == 'https://pdfkong.online/api/v1/convert' &&
                   $request['mode'] == 'url' &&
                   $request['url'] == 'https://example.com';
        });
    }

    public function test_deliver_to_webhook_auto_enables_async()
    {
        Http::fake([
            '*' => Http::response(['task_id' => '456'], 200)
        ]);

        PDFKong::url('https://example.com')
            ->deliverToWebhook('https://myapp.com/webhook')
            ->send();

        Http::assertSent(function ($request) {
            return $request['async'] === true &&
                   $request['delivery_mode'] === 'webhook' &&
                   $request['webhook_endpoint'] === 'https://myapp.com/webhook';
        });
    }

    public function test_magic_methods_are_converted_to_snake_case()
    {
        Http::fake([
            '*' => Http::response([], 200)
        ]);

        PDFKong::url('https://example.com')
            ->preferCssPageSize()
            ->printBackground(false)
            ->send();

        Http::assertSent(function ($request) {
            return $request['prefer_css_page_size'] === true &&
                   $request['print_background'] === false;
        });
    }

    public function test_return_as_base64_sets_delivery_mode()
    {
        Http::fake([
            '*' => Http::response(['base64_data' => 'SGVsbG8='], 200)
        ]);

        $response = PDFKong::url('https://example.com')
            ->returnAsBase64()
            ->send();

        $this->assertEquals('SGVsbG8=', $response['base64_data']);
        
        Http::assertSent(function ($request) {
            return $request['delivery_mode'] === 'base64';
        });
    }
}
