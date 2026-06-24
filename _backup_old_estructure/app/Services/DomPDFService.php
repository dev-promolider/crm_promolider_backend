<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;

class DomPDFService
{
    private $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $this->dompdf = new Dompdf($options);
    }

    public function loadView($view, $data = [])
    {
        $html = view($view, $data)->render();
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'landscape');

        return $this;
    }

    public function stream($filename = 'document.pdf')
    {
        return $this->dompdf->stream($filename, ['Attachment' => false]);
    }

    public function download($filename = 'document.pdf')
    {
        return $this->dompdf->stream($filename, ['Attachment' => true]);
    }
}
