<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Credentials & Endpoint
    |--------------------------------------------------------------------------
    | Here you may specify your API key and Secret key for the PDFKong API.
    | You can find these in your PDFKong dashboard.
    */
    'api_key' => env('PDFKONG_API_KEY'),
    
    // The Secret Key from the dashboard, used for payload hashing
    // or URL signing to ensure your API Key is protected.
    'secret_key' => env('PDFKONG_SECRET_KEY'),
    
    'base_url' => env('PDFKONG_BASE_URL', 'https://pdfkong.online/api/v1'),
    
    /*
    |--------------------------------------------------------------------------
    | Retention Policy (Store File)
    |--------------------------------------------------------------------------
    | If enabled, the API will keep the generated PDF on the server for 24 hours.
    | Useful for async processing or if you want to provide a download link.
    */
    'store_file' => env('PDFKONG_STORE_FILE', false),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    | The timeout in seconds for the API requests. Since PDF generation
    | can sometimes take a while for large files, the default is set to 30.
    */
    'timeout' => env('PDFKONG_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    | If you use delivery_mode = 'webhook', you can specify the default endpoint here.
    */
    'webhook' => [
        'default_endpoint' => env('PDFKONG_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | S3 Delivery Configuration
    |--------------------------------------------------------------------------
    | If you plan to use delivery_mode = 's3', you can define your S3 credentials
    | here. The package will automatically attach these to your request when 
    | S3 delivery is requested, keeping your controllers clean.
    */
    's3' => [
        'bucket_name'        => env('PDFKONG_S3_BUCKET'),
        'access_key_id'      => env('PDFKONG_S3_ACCESS_KEY'),
        'secret_access_key'  => env('PDFKONG_S3_SECRET_KEY'),
        'region'             => env('PDFKONG_S3_REGION', 'us-east-1'),
        'path_prefix'        => env('PDFKONG_S3_PATH_PREFIX', ''), // e.g. 'exports/pdfs/'
    ],
];
