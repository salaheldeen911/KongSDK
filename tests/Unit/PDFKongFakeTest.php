<?php

namespace PDFKong\Tests\Unit;

use PDFKong\Facades\PDFKong;
use PDFKong\Tests\TestCase;

class PDFKongFakeTest extends TestCase
{
    public function test_fake_can_assert_conversion()
    {
        PDFKong::fake();

        PDFKong::url('https://example.com')->send();

        PDFKong::assertConverted('url');
    }

    public function test_fake_can_assert_conversion_with_specific_payload()
    {
        PDFKong::fake();

        PDFKong::html('<h1>Hello</h1>')
            ->landscape()
            ->deliverToWebhook('https://hook.com')
            ->send();

        PDFKong::assertConverted('html', function ($payload) {
            return $payload['html'] === '<h1>Hello</h1>' &&
                   $payload['landscape'] === true &&
                   $payload['delivery_mode'] === 'webhook';
        });
    }

    public function test_fake_assert_nothing_converted()
    {
        PDFKong::fake();

        PDFKong::assertNothingConverted();
    }
}
