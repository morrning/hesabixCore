<?php

namespace App\Service;

use App\Entity\PrinterQueue;
use Twig\Environment;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class pdfMGR
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generateTwig2PDF(PrinterQueue $printQueue, $configs = []): string
    {
        $template = $this->twig->load('pdf/footer.html.twig');
        $footer = $template->render([]);
    
        $size = $printQueue->getPaperSize() ?: 'A4-L';
        
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
    
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
    
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mpdf';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
    
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $size,
            'fontDir' => array_merge($fontDirs, [dirname(__DIR__) . '/Fonts']),
            'fontdata' => $fontData + [
                'vazirmatn' => [
                    'R' => 'Vazir-Regular-FD.ttf',
                    'I' => 'Vazir-Regular-FD.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ]
            ],
            'default_font' => 'vazirmatn',
            'tempDir' => $tempDir,
            'autoArabic' => true,
        ]);
    
        if ($printQueue->isFooter()) {
            $mpdf->SetHTMLFooter($footer);
        }
    
        $htmlContent = $printQueue->getView() ?: '<p>محتوای PDF در دسترس نیست.</p>';
        $mpdf->WriteHTML($htmlContent);
        $mpdf->SetAutoPageBreak(true);
        $mpdf->SetTitle('حسابیکس');
    
        // به جای Output مستقیم، محتوا رو برگردونید
        return $mpdf->Output('', 'S'); // 'S' برای برگرداندن به صورت رشته
    }
    
    public function generateTwig2PDFInvoiceType(PrinterQueue $printQueue, $configs = []): string
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
    
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
    
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mpdf';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
    
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 300],
            'fontDir' => array_merge($fontDirs, [dirname(__DIR__) . '/Fonts']),
            'fontdata' => $fontData + [
                'vazirmatn' => [
                    'R' => 'Vazir-Regular-FD.ttf',
                    'I' => 'Vazir-Regular-FD.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ]
            ],
            'default_font' => 'vazirmatn',
            'tempDir' => $tempDir,
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
    
        $htmlContent = $printQueue->getView() ?: '<p>محتوای PDF در دسترس نیست.</p>';
        $mpdf->WriteHTML($htmlContent);
    
        return $mpdf->Output('', 'S'); // 'S' برای برگرداندن به صورت رشته
    }

    public function savePDF(PrinterQueue $printQueue, string $path)
    {
        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $htmlContent = $printQueue->getView() ?: '<p>محتوای PDF در دسترس نیست.</p>';
        $mpdf->WriteHTML($htmlContent);
        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);
    }

    private function imageToBase64($path)
    {
        if (!file_exists($path)) {
            return '';
        }
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}
