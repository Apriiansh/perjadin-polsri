<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class BlankoController extends BaseController
{
    /**
     * Show blanko preview
     */
    public function index(): string
    {
        return view('travel/blanko_view', [
            'title' => 'Blanko Kosong SPD',
        ]);
    }

    /**
     * Download blanko as PDF
     */
    public function download(): void
    {
        $html = view('travel/pdf/blanko_pdf');

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Times New Roman');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('Blanko_SPD_Kosong.pdf', ['Attachment' => 1]);
        exit;
    }
}
