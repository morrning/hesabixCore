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

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8', 'format' => 'A4-L',
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '../Fonts',
            ]),
            'fontdata' => $fontData + [ // lowercase letters only in font key
                    'Vazirmatn-Regular' => [
                        'R' => 'Vazirmatn-Regular.ttf',
                        'I' => 'Vazirmatn-Regular.ttf',
                    ]
                ],
            'default_font' => 'Vazirmatn-Regular',
            'tempDir' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'mpdf'
        ]);
        $mpdf->AddFontDirectory(__DIR__ . '../Fonts');
        $mpdf->setFooter('{PAGENO}');
        $stylesheet = file_get_contents(__DIR__ . '/../../../public_html/assets/css/dashmix.min.css');

        $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($printQueue->getView());
        $mpdf->Output();
    }

    public function streamTwig2PDFInvoiceType(PrinterQueue $printQueue, $configs = [])
    {

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8', 'format' => [80, 300],
            'fontdata' => [
                'vazirmatn' => [
                    'R' => 'Vazir-Regular-FD.ttf',
                    'I' => 'Vazir-Regular-FD.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ]
            ],
            'default_font' => 'vazirmatn',
            'tempDir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mpdf',
            'setAutoTopMargin' => true,
            'autoArabic' => true,
            'margin-collapse' => 'collapse|none'
        ]);
        $mpdf->AddPageByArray([
            'margin-left' => 0,
            'margin-right' => 0,
            'margin-top' => 0,
            'margin-bottom' => 0,
        ]);
        $mpdf->AddFontDirectory(__DIR__ . '../Fonts');
        $mpdf->WriteHTML($printQueue->getView());

        $mpdf->Output();
    }

    private function imageToBase64($path) {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}