<?php

namespace App\Services;

use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

class BarcodeService
{
    public function code128Svg(string $code, int $width = 2, int $height = 40): string
    {
        return app(DNS1D::class)->getBarcodeSVG($code, 'C128', $width, $height, 'black', true);
    }

    public function qrCodeSvg(string $data, int $size = 4): string
    {
        return app(DNS2D::class)->getBarcodeSVG($data, 'QRCODE', $size, $size);
    }

    public function code128Png(string $code, int $width = 2, int $height = 40): string
    {
        return app(DNS1D::class)->getBarcodePNG($code, 'C128', $width, $height);
    }

    public function code128Base64(string $code, int $width = 2, int $height = 40): string
    {
        return 'data:image/png;base64,'.$this->code128Png($code, $width, $height);
    }
}
