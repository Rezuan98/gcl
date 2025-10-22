<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrController extends Controller
{
    // Download the standard QR as SVG (same QR for all proposals)
    public function standard(Request $request)
    {
        $verifyUrl = route('verify.show');

        // You can change size via ?size=400 (defaults to 400)
        $size = (int) $request->query('size', 400);

        $renderer = new ImageRenderer(
            new RendererStyle($size, 2),   // size, quiet zone
            new SvgImageBackEnd()          // <-- SVG backend, no Imagick required
        );

        $svg = (new Writer($renderer))->writeString($verifyUrl);

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="gcl-standard-verify-qr.svg"');
    }

    // Inline preview for <img src="...">
    public function standardInline(Request $request)
    {
        $verifyUrl = route('verify.show');
        $size = (int) $request->query('size', 200);

        $renderer = new ImageRenderer(
            new RendererStyle($size, 2),
            new SvgImageBackEnd()
        );

        $svg = (new Writer($renderer))->writeString($verifyUrl);

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }
}
