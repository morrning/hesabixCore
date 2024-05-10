<?php

namespace App\Controller;

use App\Entity\ArchiveFile;
use App\Entity\ArchiveOrders;
use App\Entity\Settings;
use App\Service\Access;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\Notification;
use App\Service\Provider;
use App\Service\twigFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArchiveController extends AbstractController
{
    private function getArchiveInfo(EntityManagerInterface $entityManager,array $acc){
        $orders = $entityManager->getRepository(ArchiveOrders::class)->findBy([
            'bid'=>$acc['bid'],
            'status'=>100
        ]);
        $totalSize = 0;
        foreach ($orders as $order){
            if($order->getExpireDate()>= time())
                $totalSize += $order->getOrderSize();
        }
        $usedSize = 0;
        $files = $entityManager->getRepository(ArchiveFile::class)->findBy(['bid'=>$acc['bid']]);
        foreach ($files as $file)
            $usedSize += $file->getFileSize();
        return [
            'size' => $totalSize * 1024,
            'remain'=>($totalSize * 1024) - $usedSize,
            'used'=>$usedSize
        ];
    }
    #[Route('/api/archive/info', name: 'app_archive_info')]
    public function app_archive_info(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveInfo');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $resp = $this->getArchiveInfo($entityManager,$acc);
        return $this->json($resp);
    }

    #[Route('/api/archive/order/settings', name: 'app_archive_order_settings')]
    public function app_archive_order_settings(twigFunctions $functions,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveInfo');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $settings = $functions->systemSettings();
        return $this->json([
            'priceBase' => $settings->getStoragePrice()
        ]);
    }

    #[Route('/api/archive/order/submit', name: 'app_archive_order_submit')]
    public function app_archive_order_submit(twigFunctions $functions,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveInfo');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $order = new ArchiveOrders();
        $order->setBid($acc['bid']);
        $order->setSubmitter($this->getUser());
        $order->setDateSubmit(time());
        $order->setGatePay('zarinpal');
        $order->setDes('خرید سرویس فضای ابری به مقدار ' . $params['space'] . ' گیگابایت به مدت ' . $params['month'] . ' ماه ');

        $settings = $functions->systemSettings();
        if(array_key_exists('space',$params) && array_key_exists('month',$params)){
            $order->setPrice($params['space'] * $params['month'] * $settings->getStoragePrice());
            $order->setOrderSize($params['space']);
            $order->setMonth($params['month']);
        }
        else
            throw $this->createAccessDeniedException();
        $data = array("merchant_id" => $settings->getZarinpalMerchant(),
            "amount" => $order->getPrice(),
            "callback_url" => $this->generateUrl('api_archive_buy_verify',[],UrlGeneratorInterface::ABSOLUTE_URL),
            "description" => 'خرید سرویس فضای ابری',
        );
        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true, JSON_PRETTY_PRINT);
        curl_close($ch);
        if ($err) {
            throw $this->createAccessDeniedException($err);
        } else {
            if (empty($result['errors'])) {
                if ($result['data']['code'] == 100) {
                    $order->setStatus(0);
                    $order->setVerifyCode($result['data']['authority']);
                    $entityManager->persist($order);
                    $entityManager->flush();
                    $log->insert('سرویس فضای ابری','صدور فاکتور سرویس فضای ابری به مقدار ' . $params['space'] . ' گیگابایت به مدت ' . $params['month']. ' ماه ' ,$this->getUser(),$acc['bid']);
                    return $this->json([
                        'authority' => $result['data']["authority"]
                    ]);
                }
            }
        }
        throw $this->createAccessDeniedException();
    }

    #[Route('/api/archive/buy/verify', name: 'api_archive_buy_verify')]
    public function api_archive_buy_verify(twigFunctions $functions,Notification $notification,Request $request,EntityManagerInterface $entityManager,Log $log): Response
    {
        $Authority = $request->get('Authority');
        $req = $entityManager->getRepository(ArchiveOrders::class)->findOneBy(['verifyCode'=>$Authority]);
        //get system settings
        $settings = $functions->systemSettings();
        $data = array("merchant_id" => $settings->getZarinpalMerchant(), "authority" => $Authority, "amount" => $req->getPrice());
        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        //-----------------------------------

        //-----------------------------------
        if ($err) {
            $log->insert('سرویس فضای ابری','پرداخت ناموفق سرویس فضای ابری' ,$this->getUser(),$req->getBid());
            return $this->render('buy/fail.html.twig', ['results'=>$result]);
        } else {
            if(array_key_exists('code',$result['data'])){
                if ($result['data']['code'] == 100) {
                    $req->setStatus($request->get('Status'));
                    $req->setRefID($result['data']['ref_id']);
                    $req->setCardPan($result['data']['card_pan']);
                    $req->setExpireDate(time() + ($req->getMonth() * 30 * 24 * 60 * 60));
                    $entityManager->persist($req);
                    $entityManager->flush();
                    $log->insert(
                        'سرویس فضای ابری',
                        'پرداخت موفق فاکتور سرویس فضای ابری',
                        $req->getSubmitter(),
                        $req->getBid()
                    );
                    $notification->insert(' فاکتور فضای ابری پرداخت شد.','/acc/sms/panel',$req->getBid(),$req->getSubmitter());
                    return $this->render('buy/success.html.twig',['req'=>$req]);
                }
            }
            $notification->insert('پرداخت فاکتور فضای ابری ناموفق بود','/',$req->getBid(),$req->getSubmitter());
            $log->insert('سرویس پیامک','پرداخت ناموفق فاکتور فضای ابری' ,$this->getUser(),$req->getBid());
            return $this->render('buy/fail.html.twig', ['results'=>$result]);
        }
    }

    #[Route('/api/archive/list/{cat}', name: 'app_archive_list')]
    public function app_archive_list(string $cat,Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveUpload');
        if(!$acc)
            $acc = $access->hasRole('archiveMod');
        if(!$acc)
            $acc = $access->hasRole('archiveDelete');
        if(!$acc)
            throw $this->createAccessDeniedException();
        if($cat == 'all')
            $files = $entityManager->getRepository(ArchiveFile::class)->findBy(['bid'=>$acc['bid']]);
        else
            $files = $entityManager->getRepository(ArchiveFile::class)->findBy(['bid'=>$acc['bid'],'cat'=>$cat]);
        $resp = [];
        foreach ($files as $file){
            $temp = [];
            $temp['id']=$file->getId();
            $temp['filename']=$file->getFilename();
            $temp['fileType']=$file->getFileType();
            $temp['submitter']=$file->getSubmitter()->getFullName();
            $temp['dateSubmit']=$jdate->jdate('Y/n/d H:i',$file->getDateSubmit());
            $temp['filePublicls']=$file->isPublic();
            $temp['cat']=$file->getCat();
            $temp['filesize']=$file->getFileSize();
            $resp[] = $temp;
        }

        return $this->json($resp);
    }

    #[Route('/api/archive/orders/list', name: 'app_archive_orders_list')]
    public function app_archive_orders_list(Jdate $jdate, Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveInfo');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $orders = $entityManager->getRepository(ArchiveOrders::class)->findBy([
            'bid'=>$acc['bid']
        ],['id'=>'DESC']);
        $resp = $provider->ArrayEntity2Array($orders,0);
        foreach ($resp as &$item){
            $item['dateSubmit'] = $jdate->jdate('Y/n/d H:i',$item['dateSubmit']);
        }
        return $this->json($resp);
    }

    #[Route('/api/archive/file/upload', name: 'app_archive_file_upload')]
    public function app_archive_file_upload(Jdate $jdate, Provider $provider,SluggerInterface $slugger,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveUpload');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $info = $this->getArchiveInfo($entityManager,$acc);
        $uploadedFile = $request->files->get('image');
        if ($uploadedFile) {
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $uploadedFile->move(
                    $this->getParameter('archiveTempMediaDir'),
                    $newFilename
                );} catch (FileException $e) {
                // ... handle exception if something happens during file upload
                return $this->json("error");
            }

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            //$product->setBrochureFilename($newFilename);
            return $this->json(['name'=>$newFilename]);
        }
    }
    #[Route('/api/archive/file/save', name: 'app_archive_file_save')]
    public function app_archive_file_save(Jdate $jdate, Provider $provider,SluggerInterface $slugger,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveUpload');
        if (!$acc)
            throw $this->createAccessDeniedException();
        foreach ($request->get('added_media') as $item){
            if (file_exists(__DIR__ . '/../../../hesabixArchive/temp/'.$item) ){
                $size = ceil(filesize(__DIR__ . '/../../../hesabixArchive/temp/'.$item)/(1024*1024));
                $info = $this->getArchiveInfo($entityManager,$acc);
                if($info['size'] < ($info['used'] + $size))
                    return $this->json(['result'=>'nem']);
                $file = new ArchiveFile();
                $file->setBid($acc['bid']);
                $file->setDateSubmit(time());
                $file->setSubmitter($this->getUser());
                $file->setPublic(false);
                $file->setFilename($item);
                $file->setDes($request->get('des'));
                $file->setCat($request->get('cat'));
                //set file type
                $mimFile = mime_content_type(__DIR__ . '/../../../hesabixArchive/temp/'.$item);
                $file->setFileType($mimFile);
                $file->setFileSize(ceil(filesize(__DIR__ . '/../../../hesabixArchive/temp/'.$item)/(1024*1024)));
                rename(__DIR__ . '/../../../hesabixArchive/temp/'.$item,__DIR__ . '/../../../hesabixArchive/'.$item);
                $file->setRelatedDocType($request->get('doctype'));
                $file->setRelatedDocCode($request->get('docid'));
                $entityManager->persist($file);
                $entityManager->flush();
                $log->insert('آرشیو','فایل با نام ' . $file->getFilename() . ' افزوده شد.',$this->getUser(),$acc['bid']);

            }

        }
        return $this->json([
            'ok'=>'ok'
        ]);

    }

    #[Route('/api/archive/files/list', name: 'app_archive_file_list')]
    public function app_archive_file_list(Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveView');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $files = $entityManager->getRepository(ArchiveFile::class)->findBy([
            'bid'=>$acc['bid'],
            'relatedDocType'=>$params['type'],
            'relatedDocCode'=>$params['id']
        ]);
        echo $request->get('type');
        $resp = [];
        foreach ($files as $file){
            $temp = [];
            $temp['id']=$file->getId();
            $temp['filename']=$file->getFilename();
            $temp['fileType']=$file->getFileType();
            $temp['submitter']=$file->getSubmitter()->getFullName();
            $temp['dateSubmit']=$jdate->jdate('Y/n/d H:i',$file->getDateSubmit());
            $temp['filePublicls']=$file->isPublic();
            $temp['cat']=$file->getCat();
            $temp['filesize']=$file->getFileSize();
            $resp[] = $temp;
        }
        return $this->json($resp);
    }

    #[Route('/api/archive/file/get/{id}', name: 'app_archive_file_get')]
    public function app_archive_file_get(string $id,Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): BinaryFileResponse
    {
        $acc = $access->hasRole('archiveView');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $file = $entityManager->getRepository(ArchiveFile::class)->find($id);
        if(! $file)
            throw $this->createNotFoundException();
        if($acc['bid']->getId() != $file->getBid()->getId())
            throw $this->createAccessDeniedException();
        $fileAdr = __DIR__ . '/../../../hesabixArchive/'. $file->getFilename();
        $response = new BinaryFileResponse($fileAdr);
        return $response;

    }
    #[Route('/api/archive/file/remove/{id}', name: 'app_archive_file_remove')]
    public function app_archive_file_remove(string $id,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('archiveDelete');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $file = $entityManager->getRepository(ArchiveFile::class)->find($id);
        if(! $file)
            throw $this->createNotFoundException();
        if($acc['bid']->getId() != $file->getBid()->getId())
            throw $this->createAccessDeniedException();
        $fileAdr = __DIR__ . '/../../../hesabixArchive/'. $file->getFilename();
        unlink($fileAdr);
        $entityManager->remove($file);
        $entityManager->flush();
        $log->insert('آرشیو','فایل با نام ' . $file->getFilename() . ' حذف شد.',$this->getUser(),$acc['bid']);
        return $this->json(['result'=>1]);
    }
}
