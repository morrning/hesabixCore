<?php

namespace App\Controller;

use App\Entity\Hook;
use App\Entity\User;
use App\Entity\Year;
use App\Service\Log;
use App\Entity\Money;
use App\Entity\Person;
use App\Entity\Plugin;
use App\Service\Jdate;
use App\Service\Access;
use App\Entity\APIToken;
use App\Entity\Business;
use ReflectionException;
use App\Entity\Commodity;
use App\Service\Provider;
use App\Entity\Permission;
use App\Entity\BankAccount;
use App\Entity\CommodityCat;
use App\Entity\HesabdariDoc;

use App\Entity\HesabdariRow;
use App\Entity\CommodityUnit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;


class HookController extends AbstractController
{
    #[Route('hooks/setting/SetChangeHook', name: 'api_hook_SetChangeHook')]
    public function api_hook_SetChangeHook(Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $api = $entityManager->getRepository(APIToken::class)->findOneBy([
            'token' => $request->headers->get('api-key'),
        ]);

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $hook = $entityManager->getRepository(Hook::class)->findOneBy([
            'url' => $params['url'],
            'password' => $params['hookPassword'],
            'bid' => $api->getBid(),
            'submitter' => $this->getUser()
        ]);
        if (!$hook) {
            $hook = new Hook();
            $hook->setBid($api->getBid());
            $hook->setSubmitter($this->getUser());
            $hook->setPassword($params['hookPassword']);
            $hook->setUrl($params['url']);
            $entityManager->persist($hook);
            $entityManager->flush();
        }

        $year = $entityManager->getRepository(Year::class)->findOneBy(['bid' => $api->getBid(), 'head' => true])->getId();
        return $this->json([
            'Success' => true,
            'bid' => $api->getBid()->getId(),
            'year' => $year,
            'money' => $api->getBid()->getMoney()->getId()
        ]);
    }

    #[Route('hooks/setting/getCurrency', name: 'api_hooks_getcurrency')]
    public function api_hooks_getcurrency(Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $api = $entityManager->getRepository(APIToken::class)->findOneBy([
            'token' => $request->headers->get('api-key'),
        ]);
        if (!$api)
            throw $this->createNotFoundException();

        return $this->json([
            'Success' => true,
            'ErrorCode' => 0,
            'ErrorMessage' => '',
            'Result' => [
                'moneyId' => $api->getBid()->getMoney()->getId(),
                'moneyName' => $api->getBid()->getMoney()->getName(),
                'moneylabel' => $api->getBid()->getMoney()->getLabel()
            ]
        ]);
    }

    #[Route('hooks/setting/getAccounts', name: 'api_hooks_getAccounts')]
    public function api_hooks_getAccounts(Provider $provider, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (array_key_exists('speedAccess', $params)) {
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid' => $request->headers->get('activeBid'),
                'speedAccess' => true
            ]);
        } else {
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid' => $request->headers->get('activeBid')
            ]);
        }

        $response = $provider->ArrayEntity2Array($persons, 0);
        foreach ($persons as $key => $person) {
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'person' => $person
            ]);
            $bs = 0;
            $bd = 0;
            foreach ($rows as $row) {
                $bs += $row->getBs();
                $bd += $row->getBd();
            }
            $response[$key]['bs'] = $bs;
            $response[$key]['bd'] = $bd;
            $response[$key]['balance'] = $bs - $bd;
        }
        return $this->json([
            'Success' => true,
            'ErrorCode' => 0,
            'ErrorMessage' => '',
            'Result' => $response
        ]);
    }

    #[Route('hooks/setting/getBanks', name: 'api_hooks_getBanks')]
    public function api_hooks_getBanks(Provider $provider, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $datas = $entityManager->getRepository(BankAccount::class)->findBy([
            'bid' => $request->headers->get('activeBid')
        ]);
        foreach ($datas as $data) {
            $bs = 0;
            $bd = 0;
            $items = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bank' => $data
            ]);
            foreach ($items as $item) {
                $bs += $item->getBs();
                $bd += $item->getBd();
            }
            $data->setBalance($bd - $bs);
        }
        return $this->json([
            'Success' => true,
            'ErrorCode' => 0,
            'ErrorMessage' => '',
            'Result' => $provider->ArrayEntity2Array($datas, 0)
        ]);
    }


    #[Route('hooks/item/getitems', name: 'api_hooks_item_getitems')]
    public function api_hooks_item_getitems(Provider $provider, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $items = $entityManager->getRepository(Commodity::class)->findBy([
            'bid' => $request->headers->get('activeBid')
        ]);
        $res = [];
        foreach ($items as $item) {
            $temp = [];
            $temp['id'] = $item->getId();
            $temp['name'] = $item->getName();
            $temp['unit'] = $item->getUnit()->getName();
            $temp['des'] = $item->getDes();
            $temp['priceBuy'] = $item->getPriceBuy();
            $temp['speedAccess'] = $item->isSpeedAccess();
            $temp['priceSell'] = $item->getPriceSell();
            $temp['code'] = $item->getCode();
            $temp['cat'] = null;
            if ($item->getCat())
                $temp['cat'] = $item->getCat()->getName();
            $temp['khadamat'] = false;
            if ($item->isKhadamat())
                $temp['khadamat'] = true;

            $temp['commodityCountCheck'] = $item->isCommodityCountCheck();
            $temp['minOrderCount'] = $item->getMinOrderCount();
            $temp['dayLoading'] = $item->getDayLoading();
            $temp['orderPoint'] = $item->getOrderPoint();
            $res[] = $temp;
        }
        return $this->json([
            'Success' => true,
            'ErrorCode' => 0,
            'ErrorMessage' => '',
            'Result' => $res
        ]);
    }

    #[Route('hooks/setting/getBusinessInfo ', name: 'api_hooks_setting_getBusinessInfo')]
    public function api_hooks_setting_getBusinessInfo(Provider $provider, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $bus = $entityManager->getRepository(Business::class)->findOneBy(['id' => $request->headers->get('activeBid')]);
        $response = [];
        $response['id'] = $bus->getId();
        $response['name'] = $bus->getName();
        $response['owner'] = $bus->getOwner()->getFullName();
        $response['legal_name'] = $bus->getLegalName();
        $response['field'] = $bus->getField();
        $response['shenasemeli'] = $bus->getShenasemeli();
        $response['codeeqtesadi'] = $bus->getCodeeghtesadi();
        $response['shomaresabt'] = $bus->getShomaresabt();
        $response['country'] = $bus->getCountry();
        $response['ostan'] = $bus->getOstan();
        $response['shahrestan'] = $bus->getShahrestan();
        $response['postalcode'] = $bus->getPostalcode();
        $response['tel'] = $bus->getTel();
        $response['mobile'] = $bus->getMobile();
        $response['address'] = $bus->getAddress();
        $response['website'] = $bus->getWesite();
        $response['email'] = $bus->getEmail();
        $response['maliyatafzode'] = $bus->getMaliyatafzode();
        $response['arzmain'] = $bus->getMoney()->getName();
        $response['type'] = $bus->getType();
        $response['zarinpalCode'] = $bus->getZarinpalCode();
        $response['smsCharge'] = $bus->getSmsCharge();
        $response['shortlinks'] = $bus->isShortLinks();
        $response['walletEnabled'] = $bus->isWalletEnable();
        $response['walletMatchBank'] = null;
        if ($bus->isWalletEnable())
            $response['walletMatchBank'] = $provider->Entity2Array($bus->getWalletMatchBank(), 0);
        return $this->json([
            'Success' => true,
            'ErrorCode' => 0,
            'ErrorMessage' => '',
            'Result' => $response
        ]);
    }

    #[Route('hooks/commodity/import ', name: 'api_hooks_products_import')]
    public function api_hooks_products_import(Provider $provider, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        foreach ($params['data'] as $item) {

            $data = $entityManager->getRepository(Commodity::class)->findOneBy([
                'name' => $item['post_title'],
                'bid' => $acc['bid']
            ]);
            //check exist before
            if ($data)
                continue;
            $data = new Commodity();
            $data->setCode($provider->getAccountingCode($request->headers->get('activeBid'), 'Commodity'));
            $unit = $entityManager->getRepository(CommodityUnit::class)->find(1);
            if (!$unit)
                throw $this->createNotFoundException('unit not fount!');
            $data->setUnit($unit);
            $data->setBid($acc['bid']);
            $data->setname($item['post_title']);
            $data->setKhadamat(false);
            $data->setDes($item['post_content']);
            $data->setPriceSell(0);
            $data->setPriceBuy(0);
            $data->setCommodityCountCheck(false);
            $data->setMinOrderCount(0);
            $data->setSpeedAccess(false);
            $data->setDayLoading(1);
            $data->setOrderPoint(0);
            $entityManager->persist($data);
            $entityManager->flush();
            $log->insert('کالا و خدمات', 'کالا / خدمات  از طریق hook با نام  ' . $item['name'] . ' افزوده/ویرایش شد.', $this->getUser(), $request->headers->get('activeBid'));

        }
        return $this->json([
            'Success' => true,
            'ErrorCode' => 0,
            'ErrorMessage' => '',
            'Result' => 'ok'
        ]);
    }

    #[Route('hooks/person/import ', name: 'api_hooks_person_import')]
    public function api_hooks_person_import(Provider $provider, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        foreach ($params['data'] as $item) {
            $person = $entityManager->getRepository(Person::class)->findOneBy([
                'nikename' => $item['nickname'],
                'bid' => $acc['bid']
            ]);
            if ($person)
                continue;
            $person = new Person();
            $person->setCode($provider->getAccountingCode($request->headers->get('activeBid'), 'person'));
            $person->setName($item['nickname']);
            $person->setNikename($item['nickname']);
            $person->setBid($acc['bid']);
            $entityManager->persist($person);
            $entityManager->flush();
            $log->insert('اشخاص', 'شخص با نام مستعار ' . $item['nickname'] . '  از طریق hook افزوده/ویرایش شد.', $this->getUser(), $request->headers->get('activeBid'));
        }
        return $this->json([
            'Success' => true,
            'ErrorCode' => 0,
            'ErrorMessage' => '',
            'Result' => 'ok'
        ]);
    }

    #[Route('hooks/setting/GetFiscalYear', name: 'api_hooks_fiscal_year')]
    public function api_hooks_fiscal_year(Provider $provider, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $bus = $entityManager->getRepository(Business::class)->findOneBy(['id' => $request->headers->get('activeBid')]);
        $year = $entityManager->getRepository(Year::class)->findOneBy(['bid' => $bus, 'head' => true]);

        $response = [
            'Name' => $year->getLabel(),
            'StartDate' => date(DATE_ATOM, $year->getStart()),
            'EndDate' => date(DATE_ATOM, $year->getEnd()),
        ];

        return $this->json([
            'Success' => true,
            'ErrorCode' => 0,
            'ErrorMessage' => '',
            'Result' => $response
        ]);
    }
}
