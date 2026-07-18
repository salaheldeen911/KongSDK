<?php

namespace PDFKong\Enums;

enum DeliveryMode: string
{
    case Json = 'json';
    case Base64 = 'base64';
    case Inline = 'inline';
    case Attachment = 'attachment';
    case Webhook = 'webhook';
    case S3 = 's3';
    case GoogleStorage = 'google_storage';
}
