<?php

namespace App\Service;

use App\Entity\PrinterQueue;
use App\Service\Twig;

use Twig\Environment;
class pdfMGR
{

    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function streamTwig2PDF(PrinterQueue $printQueue,$configs = []){
        // Load Twig File
        $template = $this->twig->load('pdf/footer.html.twig');

        // Render HTML
        $footer = $template->render([
            
        ]);
        $size = $printQueue->getPaperSize();
        if(!$size){ $size = 'A4-L'; }
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8', 'format' => $size,
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '../Fonts',
            ]),
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
            'autoArabic' => true,
        ]);
        $mpdf->AddFontDirectory(__DIR__ . '../Fonts');
        $mpdf->SetHTMLFooter($footer);
        $mpdf->WriteHTML($printQueue->getView());
        $mpdf->SetAutoPageBreak(true);
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