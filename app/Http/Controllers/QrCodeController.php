<?php

// app/Http/Controllers/QrCodeController.php

namespace App\Http\Controllers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeController extends Controller
{
    public function index()
    {
        $qrExists = Storage::disk('public')->exists('qrcodes/site.png');

        return view('admin.qrcode.index', [
            'qrExists' => $qrExists,
        ]);
    }

    public function generate()
    {
        $logoPath = public_path('images/logo.png');

        $params = [
            'writer' => new PngWriter,
            'data' => url('/'),
            'encoding' => new Encoding('UTF-8'),
            'errorCorrectionLevel' => ErrorCorrectionLevel::High,
            'size' => 400,
            'margin' => 15,
            'roundBlockSizeMode' => RoundBlockSizeMode::Margin,
        ];

        if (file_exists($logoPath)) {
            $params['logoPath'] = $logoPath;
            $params['logoResizeToWidth'] = 90;
        }

        $result = (new Builder(...$params))->build();

        Storage::disk('public')->put('qrcodes/site.png', $result->getString());

        return back()->with('success', 'QR code généré avec succès.');
    }

    public function download()
    {
        $path = storage_path('app/public/qrcodes/site.png');

        if (! file_exists($path)) {
            return back()->with('error', 'Aucun QR code généré pour le moment.');
        }

        return response()->download($path, 'qr-code-site.png');
    }
}
