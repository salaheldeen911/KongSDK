<?php

namespace PDFKong\Testing;

use PHPUnit\Framework\Assert as PHPUnit;
use PDFKong\Contracts\PDFKongClientInterface;

class PDFKongFake implements PDFKongClientInterface
{
    protected array $recordedPayloads = [];
    protected array $recordedModes = [];
    
    // Internal state to track the current chain
    protected array $currentPayload = [];

    public function url(string $url): self
    {
        $this->currentPayload['mode'] = 'url';
        $this->currentPayload['url'] = $url;
        return $this;
    }

    public function html(string $html): self
    {
        $this->currentPayload['mode'] = 'html';
        $this->currentPayload['html'] = $html;
        return $this;
    }

    public function office(string $filePath): self
    {
        $this->currentPayload['mode'] = 'office';
        $this->currentPayload['file'] = $filePath;
        return $this;
    }

    public function merge(array $filePaths): self
    {
        $this->currentPayload['mode'] = 'merge';
        $this->currentPayload['files'] = $filePaths;
        return $this;
    }

    public function watermark(string $filePath): self
    {
        $this->currentPayload['mode'] = 'watermark';
        $this->currentPayload['file'] = $filePath;
        return $this;
    }

    public function protect(string $filePath): self
    {
        $this->currentPayload['mode'] = 'protect';
        $this->currentPayload['file'] = $filePath;
        return $this;
    }

    public function raw(array $payload, array $files = []): self
    {
        $this->currentPayload = $payload;
        if (!empty($files)) {
            $this->currentPayload['files'] = $files;
        }
        return $this;
    }

    public function markdown(string $markdown): self
    {
        $this->currentPayload['mode'] = 'markdown';
        $this->currentPayload['markdown'] = $markdown;
        return $this;
    }

    public function save(string $path): bool
    {
        $this->recordCurrentCall();
        return true;
    }

    public function send(): array
    {
        $this->recordCurrentCall();
        return ['status' => 'success', 'message' => 'Faked response'];
    }

    public function getAsBytes(): string
    {
        $this->recordCurrentCall();
        return 'Faked PDF bytes';
    }

    public function schema(): array
    {
        return [];
    }

    public function usage(): array
    {
        return [];
    }

    public function list(int $page = 1, int $perPage = 15): array
    {
        return [];
    }

    public function remove(string $taskId): array
    {
        return [];
    }

    public function batchStatus(string $batchId): array
    {
        return [];
    }

    public function batchDownload(string $batchId, string $savePath): bool
    {
        return true;
    }

    public function async(bool $enable = true): self
    {
        $this->currentPayload['async'] = $enable;
        return $this;
    }

    public function retry(int $times, int $sleepMilliseconds = 0): self
    {
        $this->currentPayload['retry_times'] = $times;
        $this->currentPayload['retry_sleep'] = $sleepMilliseconds;
        return $this;
    }

    public function withOptions(array $options): self
    {
        $this->currentPayload['http_options'] = $options;
        return $this;
    }

    public function __call(string $method, array $parameters)
    {
        $key = \Illuminate\Support\Str::snake($method);
        $this->currentPayload[$key] = empty($parameters) ? true : $parameters[0];
        return $this;
    }

    protected function recordCurrentCall(): void
    {
        $this->recordedPayloads[] = $this->currentPayload;
        if (isset($this->currentPayload['mode'])) {
            $this->recordedModes[] = $this->currentPayload['mode'];
        }
        // Reset state after action
        $this->currentPayload = [];
    }

    /**
     * Assert that a conversion was requested for the given mode.
     */
    public function assertConverted(string $mode, callable $callback = null): void
    {
        PHPUnit::assertTrue(
            in_array($mode, $this->recordedModes),
            "The expected [{$mode}] conversion was not requested."
        );

        if ($callback) {
            $payloads = array_filter($this->recordedPayloads, function ($payload) use ($mode) {
                return isset($payload['mode']) && $payload['mode'] === $mode;
            });
            
            $passed = false;
            foreach ($payloads as $payload) {
                if ($callback($payload)) {
                    $passed = true;
                    break;
                }
            }
            
            PHPUnit::assertTrue(
                $passed,
                "The expected [{$mode}] conversion with specific payload was not requested."
            );
        }
    }

    /**
     * Assert that nothing was converted.
     */
    public function assertNothingConverted(): void
    {
        PHPUnit::assertEmpty(
            $this->recordedPayloads,
            'A conversion was requested unexpectedly.'
        );
    }
}
