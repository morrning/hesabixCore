<?php

namespace App\Service;

use App\Entity\PrinterQueue;
use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
class pdfMGR
{

    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function streamTwig2PDF(PrinterQueue $printQueue,$configs = []){
        $data = [

        ];

        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->setDefaultFont('DejaVuSans');
        $dompdf->setOptions($options);
        $dompdf->loadHtml($printQueue->getView());
        $dompdf->render();

        return new Response (
            $dompdf->stream('resume', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function imageToBase64($path) {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}