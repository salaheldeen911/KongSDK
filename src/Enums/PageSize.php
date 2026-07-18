<?php

namespace PDFKong\Enums;

enum PageSize: string
{
    case A3 = 'A3';
    case A4 = 'A4';
    case A5 = 'A5';
    case A6 = 'A6';
    case Letter = 'Letter';
    case Legal = 'Legal';
    case Tabloid = 'Tabloid';
    case Custom = 'Custom';
}
