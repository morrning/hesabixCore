<?php

namespace App\Controller;

use App\Entity\PersonPrelabel;
use App\Service\Extractor;
use App\Service\Jdate;
use App\Service\Log;
use App\Entity\Person;
use App\Service\Access;
use App\Entity\Business;
use App\Service\Provider;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\PersonCard;
use App\Entity\PersonType;
use App\Entity\Storeroom;
use App\Entity\StoreroomItem;
use App\Entity\StoreroomTicket;
use App\Service\Explore;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PersonsController extends AbstractController
{

    /**
     * function to generate random strings
     * @param 		int 	$length 	number of characters in the generated string
     * @return 		string	a new string is created with random characters of the desired length
     */
    private function RandomString($length = 32)
    {
        return substr(str_shuffle(str_repeat($x = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }

    /**
     * @throws \ReflectionException
     */
    #[Route('/api/person/types/get', name: 'app_persons_types_get')]
    public function app_persons_types_get(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(PersonType::class)->findAll();
        return $this->json(Explore::ExplorePersonTypes($items));
    }

    /**
     * @throws \ReflectionException
     */
    #[Route('/api/person/info/{code}', name: 'app_persons_info')]
    public function app_persons_info($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $person = $entityManager->getRepository(Person::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $code
        ]);
        $types = $entityManager->getRepository(PersonType::class)->findAll();
        $response = Explore::ExplorePerson($person, $types);
        $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'person' => $person
        ]);
        $bs = 0;
        $bd = 0;
        foreach ($rows as $row) {
            if ($row->getDoc()->getMoney() == $acc['money']) {
                $bs += $row->getBs();
                $bd += $row->getBd();
            }
        }
        $response['bs'] = $bs;
        $response['bd'] = $bd;
        $response['balance'] = $bs - $bd;
        return $this->json($response);
    }

    #[Route('/api/person/group/mod', name: 'app_persons_group_mod')]
    public function app_persons_group_mod(Provider $provider, Extractor $extractor, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $paramsAll = [];
        if ($content = $request->getContent()) {
            $paramsAll = json_decode($content, true);
        }
        if (!array_key_exists('items', $paramsAll))
            return $this->json($extractor->paramsNotSend());
        foreach ($paramsAll['items'] as $params) {
            if (!array_key_exists('nikename', $params))
                return $this->json(['result' => -1]);
            if (count_chars(trim($params['nikename'])) == 0)
                return $this->json(['result' => 3]);
            if ($code == 0) {
                $person = $entityManager->getRepository(Person::class)->findOneBy([
                    'nikename' => $params['nikename'],
                    'bid' => $acc['bid']
                ]);
                //check exist before
                if (!$person) {
                    $person = new Person();
                    $person->setCode($provider->getAccountingCode($acc['bid'], 'person'));
                }

            } else {
                $person = $entityManager->getRepository(Person::class)->findOneBy([
                    'bid' => $acc['bid'],
                    'code' => $code
                ]);
                if (!$person)
                    throw $this->createNotFoundException();
            }
            $person->setBid($acc['bid']);
            $person->setNikename($params['nikename']);
            if (array_key_exists('name', $params))
                $person->setName($params['name']);
            if (array_key_exists('birthday', $params))
                $person->setBirthday($params['birthday']);
            if (array_key_exists('tel', $params))
                $person->setTel($params['tel']);
            if (array_key_exists('speedAccess', $params))
                $person->setSpeedAccess($params['speedAccess']);
            if (array_key_exists('address', $params))
                $person->setAddress($params['address']);
            if (array_key_exists('des', $params))
                $person->setDes($params['des']);
            if (array_key_exists('mobile', $params))
                $person->setMobile($params['mobile']);
            if (array_key_exists('mobile2', $params))
                $person->setMobile2($params['mobile2']);
            if (array_key_exists('fax', $params))
                $person->setFax($params['fax']);
            if (array_key_exists('website', $params))
                $person->setWebsite($params['website']);
            if (array_key_exists('email', $params))
                $person->setEmail($params['email']);
            if (array_key_exists('postalcode', $params))
                $person->setPostalcode($params['postalcode']);
            if (array_key_exists('shahr', $params))
                $person->setShahr($params['shahr']);
            if (array_key_exists('ostan', $params))
                $person->setOstan($params['ostan']);
            if (array_key_exists('keshvar', $params))
                $person->setKeshvar($params['keshvar']);
            if (array_key_exists('sabt', $params))
                $person->setSabt($params['sabt']);
            if (array_key_exists('codeeghtesadi', $params))
                $person->setCodeeghtesadi($params['codeeghtesadi']);
            if (array_key_exists('shenasemeli', $params))
                $person->setShenasemeli($params['shenasemeli']);
            if (array_key_exists('company', $params))
                $person->setCompany($params['company']);

            //inset cards
            if (array_key_exists('accounts', $params)) {
                foreach ($params['accounts'] as $item) {
                    $card = $entityManager->getRepository(PersonCard::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'person' => $person,
                        'bank' => $item['bank']
                    ]);
                    if (!$card)
                        $card = new PersonCard();

                    $card->setPerson($person);
                    $card->setBid($acc['bid']);
                    $card->setShabaNum($item['shabaNum']);
                    $card->setCardNum($item['cardNum']);
                    $card->setAccountNum($item['accountNum']);
                    $card->setBank($item['bank']);
                    $entityManager->persist($card);
                }
            }
            //remove not sended accounts
            $accounts = $entityManager->getRepository(PersonCard::class)->findBy([
                'bid' => $acc['bid'],
                'person' => $person,
            ]);
            foreach ($accounts as $item) {
                $deleted = true;
                foreach ($params['accounts'] as $param) {
                    if ($item->getBank() == $param['bank']) {
                        $deleted = false;
                    }
                }
                if ($deleted) {
                    $entityManager->remove($item);
                }
            }
            $entityManager->persist($person);

            //insert new types
            $types = $entityManager->getRepository(PersonType::class)->findAll();
            foreach ($params['types'] as $item) {
                if ($item['checked'] == true)
                    $person->addType($entityManager->getRepository(PersonType::class)->findOneBy([
                        'code' => $item['code']
                    ]));
                elseif ($item['checked'] == false) {
                    $person->removeType($entityManager->getRepository(PersonType::class)->findOneBy([
                        'code' => $item['code']
                    ]));
                }
            }
            $entityManager->flush();
            $log->insert('اشخاص', 'شخص با نام مستعار ' . $params['nikename'] . ' افزوده/ویرایش شد.', $this->getUser(), $acc['bid']);
        }

        return $this->json([
            'Success' => true,
            'result' => 1,
        ]);
    }

    #[Route('/api/person/mod/{code}', name: 'app_persons_mod')]
    public function app_persons_mod(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('nikename', $params))
            return $this->json(['result' => -1]);
        if (count_chars(trim($params['nikename'])) == 0)
            return $this->json(['result' => 3]);

        if ($code == 0) {
            $person = $entityManager->getRepository(Person::class)->findOneBy([
                'nikename' => $params['nikename'],
                'bid' => $acc['bid']
            ]);
            //check exist before
            if (!$person) {
                $person = new Person();
                $person->setCode($provider->getAccountingCode($acc['bid'], 'person'));
            }

        } else {
            $person = $entityManager->getRepository(Person::class)->findOneBy([
                'bid' => $acc['bid'],
                'code' => $code
            ]);
            if (!$person)
                throw $this->createNotFoundException();
        }
        $person->setBid($acc['bid']);
        $person->setNikename($params['nikename']);
        if (array_key_exists('name', $params))
            $person->setName($params['name']);
        if (array_key_exists('birthday', $params))
            $person->setBirthday($params['birthday']);
        if (array_key_exists('tel', $params))
            $person->setTel($params['tel']);
        if (array_key_exists('speedAccess', $params))
            $person->setSpeedAccess($params['speedAccess']);
        if (array_key_exists('address', $params))
            $person->setAddress($params['address']);
        if (array_key_exists('des', $params))
            $person->setDes($params['des']);
        if (array_key_exists('mobile', $params))
            $person->setMobile($params['mobile']);
        if (array_key_exists('mobile2', $params))
            $person->setMobile2($params['mobile2']);
        if (array_key_exists('fax', $params))
            $person->setFax($params['fax']);
        if (array_key_exists('website', $params))
            $person->setWebsite($params['website']);
        if (array_key_exists('email', $params))
            $person->setEmail($params['email']);
        if (array_key_exists('postalcode', $params))
            $person->setPostalcode($params['postalcode']);
        if (array_key_exists('shahr', $params))
            $person->setShahr($params['shahr']);
        if (array_key_exists('ostan', $params))
            $person->setOstan($params['ostan']);
        if (array_key_exists('keshvar', $params))
            $person->setKeshvar($params['keshvar']);
        if (array_key_exists('sabt', $params))
            $person->setSabt($params['sabt']);
        if (array_key_exists('codeeghtesadi', $params))
            $person->setCodeeghtesadi($params['codeeghtesadi']);
        if (array_key_exists('shenasemeli', $params))
            $person->setShenasemeli($params['shenasemeli']);
        if (array_key_exists('company', $params))
            $person->setCompany($params['company']);
        if (array_key_exists('prelabel', $params)) {
            if ($params['prelabel'] != '') {
                $prelabel = $entityManager->getRepository(PersonPrelabel::class)->findOneBy(['label' => $params['prelabel']]);
                if ($prelabel) {
                    $person->setPrelabel($prelabel);
                }
            }
        }
        //inset cards
        if (array_key_exists('accounts', $params)) {
            foreach ($params['accounts'] as $item) {
                $card = $entityManager->getRepository(PersonCard::class)->findOneBy([
                    'bid' => $acc['bid'],
                    'person' => $person,
                    'bank' => $item['bank']
                ]);
                if (!$card)
                    $card = new PersonCard();

                $card->setPerson($person);
                $card->setBid($acc['bid']);
                $card->setShabaNum($item['shabaNum']);
                $card->setCardNum($item['cardNum']);
                $card->setAccountNum($item['accountNum']);
                $card->setBank($item['bank']);
                $entityManager->persist($card);
            }
        }
        //remove not sended accounts
        $accounts = $entityManager->getRepository(PersonCard::class)->findBy([
            'bid' => $acc['bid'],
            'person' => $person,
        ]);
        foreach ($accounts as $item) {
            $deleted = true;
            foreach ($params['accounts'] as $param) {
                if ($item->getBank() == $param['bank']) {
                    $deleted = false;
                }
            }
            if ($deleted) {
                $entityManager->remove($item);
            }
        }
        $entityManager->persist($person);

        //insert new types
        $types = $entityManager->getRepository(PersonType::class)->findAll();
        foreach ($params['types'] as $item) {
            if ($item['checked'] == true)
                $person->addType($entityManager->getRepository(PersonType::class)->findOneBy([
                    'code' => $item['code']
                ]));
            elseif ($item['checked'] == false) {
                $person->removeType($entityManager->getRepository(PersonType::class)->findOneBy([
                    'code' => $item['code']
                ]));
            }
        }
        $entityManager->flush();
        $log->insert('اشخاص', 'شخص با نام مستعار ' . $params['nikename'] . ' افزوده/ویرایش شد.', $this->getUser(), $acc['bid']);
        return $this->json([
            'Success' => true,
            'result' => 1,
        ]);
    }

    #[Route('/api/person/list/search', name: 'app_persons_list_search')]
    public function app_persons_list_search(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (array_key_exists('search', $params))
            $persons = $entityManager->getRepository(Person::class)->searchByNikename($acc['bid'], $params['search'], 10);
        else
            $persons = $entityManager->getRepository(Person::class)->getLasts($acc['bid'], 10);
        $response = [];
        foreach ($persons as $key => $person) {
            $temp = [
                'id' => $person->getId(),
                'nikename' => $person->getNikename(),
                'code' => $person->getCode(),
                'mobile' => $person->getMobile()
            ];
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'person' => $person,
            ]);
            $bs = 0;
            $bd = 0;
            foreach ($rows as $row) {
                //check for that calulate is in match money type
                if ($row->getDoc()->getMoney() == $acc['money']) {
                    $bs += $row->getBs();
                    $bd += $row->getBd();
                }
            }
            $temp['bs'] = $bs;
            $temp['bd'] = $bd;
            $temp['balance'] = $bs - $bd;
            $response[] = $temp;
        }
        return $this->json($response);
    }

    #[Route('/api/person/list/limit', name: 'app_persons_list_limit')]
    public function app_persons_list_limit(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (array_key_exists('speedAccess', $params)) {
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid' => $acc['bid'],
                'speedAccess' => true
            ]);
        } else {
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid' => $acc['bid']
            ]);
        }
        $response = [];
        foreach ($persons as $key => $person) {
            $temp = [
                'id' => $person->getId(),
                'nikename' => $person->getNikename(),
                'code' => $person->getCode(),
                'mobile' => $person->getMobile()
            ];
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'person' => $person
            ]);
            $bs = 0;
            $bd = 0;
            foreach ($rows as $row) {
                $bs += $row->getBs();
                $bd += $row->getBd();
            }
            $temp['bs'] = $bs;
            $temp['bd'] = $bd;
            $temp['balance'] = $bs - $bd;
            $response[] = $temp;
        }
        return $this->json($response);
    }

    #[Route('/api/person/list', name: 'app_persons_list', methods: ['POST'])]
    public function app_persons_list(
        Provider $provider,
        Request $request,
        Access $access,
        Log $log,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $acc = $access->hasRole('person');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = json_decode($request->getContent(), true) ?? [];
        $page = $params['page'] ?? 1;
        $itemsPerPage = $params['itemsPerPage'] ?? 10;
        $search = $params['search'] ?? '';
        $types = $params['types'] ?? null;
        $transactionFilters = $params['transactionFilters'] ?? null;

        // کوئری اصلی برای گرفتن همه اشخاص
        $queryBuilder = $entityManager->getRepository(\App\Entity\Person::class)
            ->createQueryBuilder('p')
            ->where('p.bid = :bid')
            ->setParameter('bid', $acc['bid']);

        // جست‌وجو (بهبود داده‌شده)
        if (!empty($search) || $search === '0') { // برای اطمینان از کار با "0" یا خالی
            $search = trim($search); // حذف فضای خالی اضافی
            $queryBuilder->andWhere('p.nikename LIKE :search OR p.name LIKE :search OR p.code LIKE :search')
                ->setParameter('search', "%$search%");
        }

        // فیلتر نوع اشخاص
        if ($types && !empty($types)) {
            $queryBuilder->leftJoin('p.type', 't')
                ->andWhere('t.code IN (:types)')
                ->setParameter('types', $types);
        }

        // تعداد کل (قبل از فیلتر تراکنش‌ها)
        $totalItems = (clone $queryBuilder)
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // گرفتن اشخاص با صفحه‌بندی
        $persons = $queryBuilder
            ->select('p')
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();

        // محاسبه تراکنش‌ها و اعمال فیلتر تراکنش‌ها
        $response = [];
        foreach ($persons as $person) {
            $rows = $entityManager->getRepository(\App\Entity\HesabdariRow::class)->findBy([
                'person' => $person,
                'bid' => $acc['bid'],
            ]);
            $bs = 0; // بستانکار
            $bd = 0; // بدهکار
            foreach ($rows as $row) {
                if ($row->getDoc()->getMoney()->getId() == $acc['money']->getId() &&
                    $row->getDoc()->getYear()->getId() == $acc['year']->getId()) {
                    $bs += (float) $row->getBs(); // بستانکار
                    $bd += (float) $row->getBd(); // بدهکار
                }
            }
            $balance = $bs - $bd; // تراز = بستانکار - بدهکار

            // اعمال فیلتر transactionFilters
            $include = true;
            if ($transactionFilters && !empty($transactionFilters)) {
                $include = false;
                if (in_array('debtors', $transactionFilters) && $balance < 0) { // بدهکارها (تراز منفی)
                    $include = true;
                }
                if (in_array('creditors', $transactionFilters) && $balance > 0) { // بستانکارها (تراز مثبت)
                    $include = true;
                }
                if (in_array('zero', $transactionFilters) && $balance == 0) { // تسویه‌شده‌ها
                    $include = true;
                }
            }

            if ($include) {
                $result =  Explore::ExplorePerson($person, $entityManager->getRepository(PersonType::class)->findAll());
                $result['bs'] = $bs;
                $result['bd'] = $bd;
                $result['balance'] = $balance;
                $response[] = $result;
            }
        }

        // تعداد آیتم‌های فیلترشده
        $filteredTotal = count($response);

        return new JsonResponse([
            'items' => array_slice($response, 0, $itemsPerPage), // فقط تعداد درخواستی
            'total' => $filteredTotal, // تعداد کل فیلترشده
            'unfilteredTotal' => $totalItems, // تعداد کل بدون فیلتر (اختیاری)
        ]);
    }

    #[Route('/api/person/list/debtors/{amount}', name: 'app_persons_list_debtors')]
    public function app_persons_list_debtors(string $amount, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (array_key_exists('speedAccess', $params)) {
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid' => $acc['bid'],
                'speedAccess' => true
            ]);
        } else {
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid' => $acc['bid']
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
        $result = [];
        foreach ($response as $key => $person) {
            if ($person['bd'] - $person['bs'] > $amount) {
                array_push($result, $person);
            }
        }
        return $this->json($result);
    }
    #[Route('/api/person/list/debtors/print/{amount}', name: 'app_persons_debtors_list_print')]
    public function app_persons_debtors_list_print(string $amount, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $persons = $entityManager->getRepository(Person::class)->findBy([
            'bid' => $acc['bid']
        ]);
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
        $result = [];
        foreach ($response as $key => $person) {
            if ($person['bd'] - $person['bs'] > $amount) {
                array_push($result, $person);
            }
        }
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/personsDebtors.html.twig', [
                'page_title' => 'فهرست بدهکاران',
                'bid' => $acc['bid'],
                'persons' => $result
            ])
        );
        return $this->json(['id' => $pid]);
    }

    #[Route('/api/person/list/depositors/{amount}', name: 'app_persons_list_depoistors')]
    public function app_persons_list_depoistors(string $amount, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (array_key_exists('speedAccess', $params)) {
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid' => $acc['bid'],
                'speedAccess' => true
            ]);
        } else {
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid' => $acc['bid']
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
        $result = [];
        foreach ($response as $key => $person) {
            if ($person['bs'] - $person['bd'] > $amount) {
                array_push($result, $person);
            }
        }
        return $this->json($result);
    }

    #[Route('/api/person/list/salesmen', name: 'app_persons_list_salesmen')]
    public function app_persons_list_salesmen(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $personType = $entityManager->getRepository(PersonType::class)->findOneBy([
            'code' => 'salesman',
        ]);
        $persons = $entityManager->getRepository(Person::class)->findBy([
            'bid' => $acc['bid'],
        ]);
        $res = [];
        foreach ($persons as $key => $person) {
            foreach ($person->getType() as $type) {
                if ($type->getCode() == $personType->getCode()) {
                    $res[] = $person;
                }
            }
        }
        $response = Explore::ExplorePersons($res, $entityManager->getRepository(PersonType::class)->findAll());
        foreach ($res as $key => $person) {
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
        return new Response(json_encode([
            'Success' => true,
            'result' => $response
        ]));
    }

    #[Route('/api/person/list/depositors/print/{amount}', name: 'app_persons_depositors_list_print')]
    public function app_persons_depositors_list_print(string $amount, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $persons = $entityManager->getRepository(Person::class)->findBy([
            'bid' => $acc['bid']
        ]);
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
        $result = [];
        foreach ($response as $key => $person) {
            if ($person['bs'] - $person['bd'] > $amount) {
                array_push($result, $person);
            }
        }
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/personsDepositors.html.twig', [
                'page_title' => 'فهرست بستانکاران',
                'bid' => $acc['bid'],
                'persons' => $result
            ])
        );
        return $this->json(['id' => $pid]);
    }

    #[Route('/api/person/list/print', name: 'app_persons_list_print')]
    public function app_persons_list_print(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('items', $params)) {
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid' => $acc['bid']
            ]);
        } else {
            $persons = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(Person::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid']
                ]);
                if ($prs)
                    $persons[] = $prs;
            }
        }
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/persons.html.twig', [
                'page_title' => 'فهرست اشخاص',
                'bid' => $acc['bid'],
                'persons' => $persons
            ])
        );
        return $this->json(['id' => $pid]);
    }

    /**
     * @throws Exception
     */
    #[Route('/api/person/list/excel', name: 'app_persons_list_excel')]
    public function app_persons_list_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): BinaryFileResponse|JsonResponse|StreamedResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('items', $params)) {
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid' => $acc['bid']
            ]);
        } else {
            $persons = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(Person::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid']
                ]);
                if ($prs)
                    $persons[] = $prs;
            }
        }
        return new BinaryFileResponse($provider->createExcell($persons));
    }

    /**
     * @throws Exception
     */
    #[Route('/api/person/card/list/excel', name: 'app_persons_card_list_excel')]
    public function app_persons_card_list_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): BinaryFileResponse|JsonResponse|StreamedResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('code', $params))
            throw $this->createNotFoundException();
        $person = $entityManager->getRepository(Person::class)->findOneBy(['bid' => $acc['bid'], 'code' => $params['code']]);
        if (!$person)
            throw $this->createNotFoundException();
        if (!array_key_exists('items', $params)) {
            $transactions = $entityManager->getRepository(HesabdariRow::class)->findByJoinMoney([
                'bid' => $acc['bid'],
                'person' => $person,
                'year' => $acc['year'],
            ], $acc['money']);
        } else {
            $transactions = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariRow::class)->findByJoinMoney([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'person' => $person,
                    'year' => $acc['year'],
                ], $acc['money']);
                if (count($prs) != 0) {
                    $transactions[] = $prs[0];
                }
            }
        }
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $arrayEntity = [
            [
                'شماره تراکنش',
                'تاریخ',
                'توضیحات',
                'تفضیل',
                'بستانکار',
                'بدهکار',
                'سال مالی',
            ]
        ];
        foreach ($transactions as $transaction) {
            $arrayEntity[] = [
                $transaction->getId(),
                $transaction->getDoc()->getDate(),
                $transaction->getDes(),
                $transaction->getRef()->getName(),
                $transaction->getBs(),
                $transaction->getBd(),
                $transaction->getYear()->getlabel()
            ];
        }
        $activeWorksheet->fromArray($arrayEntity, null, 'A1');
        $activeWorksheet->setRightToLeft(true);
        $writer = new Xlsx($spreadsheet);
        $filePath = __DIR__ . '/../../var/' . $this->RandomString(12) . '.xlsx';
        $writer->save($filePath);
        return new BinaryFileResponse($filePath);
    }

    #[Route('/api/person/card/list/print', name: 'app_persons_card_list_print')]
    public function app_persons_card_list_print(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('code', $params))
            throw $this->createNotFoundException();
        $person = $entityManager->getRepository(Person::class)->findOneBy(['bid' => $acc['bid'], 'code' => $params['code']]);
        if (!$person)
            throw $this->createNotFoundException();

        if (!array_key_exists('items', $params)) {
            $transactions = $entityManager->getRepository(HesabdariRow::class)->findByJoinMoney([
                'bid' => $acc['bid'],
                'person' => $person,
                'year' => $acc['year'],
            ], $acc['money']);
        } else {
            $transactions = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariRow::class)->findByJoinMoney([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'person' => $person,
                    'year' => $acc['year'],
                ], $acc['money']);
                if (count($prs) != 0) {
                    $transactions[] = $prs[0];
                }
            }
        }
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/person_card.html.twig', [
                'page_title' => 'کارت حساب' . ' ' . $person->getNikename(),
                'bid' => $acc['bid'],
                'items' => $transactions,
                'person' => $person
            ])
        );
        return $this->json(['id' => $pid]);
    }

    #[Route('/api/person/receive/list/print', name: 'app_persons_receive_list_print')]
    public function app_persons_receive_list_print(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('getpay');
        if (!$acc)
            throw $this->createAccessDeniedException();
        
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        // پارامترهای صفحه‌بندی
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $offset = ($page - 1) * $limit;

        $queryBuilder = $entityManager->getRepository(HesabdariDoc::class)->createQueryBuilder('d')
            ->where('d.bid = :bid')
            ->andWhere('d.type = :type')
            ->andWhere('d.year = :year')
            ->andWhere('d.money = :money')
            ->setParameter('bid', $acc['bid'])
            ->setParameter('type', 'person_receive')
            ->setParameter('year', $acc['year'])
            ->setParameter('money', $acc['money']);

        // اگر آیتم‌های خاصی درخواست شده‌اند
        if (array_key_exists('items', $params)) {
            $ids = array_map(function($item) { return $item['id']; }, $params['items']);
            $queryBuilder->andWhere('d.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        // دریافت تعداد کل رکوردها
        $totalItems = $queryBuilder->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // دریافت داده‌های صفحه فعلی
        $items = $queryBuilder->select('d')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // اضافه کردن اطلاعات اشخاص به هر آیتم
        foreach ($items as $item) {
            $personNames = [];
            foreach ($item->getHesabdariRows() as $row) {
                if ($row->getPerson()) {
                    $personNames[] = $row->getPerson()->getNikename();
                }
            }
            $item->personNames = implode('، ', array_unique($personNames));
        }

        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/persons_receive.html.twig', [
                'page_title' => 'لیست دریافت‌ها',
                'bid' => $acc['bid'],
                'items' => $items,
                'totalItems' => $totalItems,
                'currentPage' => $page,
                'totalPages' => ceil($totalItems / $limit)
            ])
        );

        return $this->json([
            'id' => $pid,
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'totalPages' => ceil($totalItems / $limit)
        ]);
    }
    

    #[Route('/api/person/receive/list/search', name: 'app_persons_receive_list_search', methods: ['POST'])]
    public function app_persons_receive_list_search(
        Request $request,
        Access $access,
        EntityManagerInterface $entityManager,
        Jdate $jdate
    ): JsonResponse {
        $acc = $access->hasRole('getpay');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        // دریافت پارامترها
        $params = json_decode($request->getContent(), true) ?? [];
        $page = (int) ($params['page'] ?? 1);
        $itemsPerPage = (int) ($params['itemsPerPage'] ?? 10);
        $search = $params['search'] ?? '';
        $dateFilter = $params['dateFilter'] ?? 'all';

        // کوئری پایه برای اسناد
        $queryBuilder = $entityManager->getRepository(HesabdariDoc::class)
            ->createQueryBuilder('d')
            ->select('DISTINCT d.id, d.date, d.code, d.des, d.amount')
            ->leftJoin('d.hesabdariRows', 'hr')
            ->leftJoin('hr.person', 'p')
            ->where('d.bid = :bid')
            ->andWhere('d.type = :type')
            ->andWhere('d.year = :year')
            ->andWhere('d.money = :money')
            ->setParameters([
                    'bid' => $acc['bid'],
                    'type' => 'person_receive',
                    'year' => $acc['year'],
                    'money' => $acc['money'],
                ])
            ->orderBy('d.id', 'DESC');

        // جست‌وجو
        if (!empty($search)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    'd.code LIKE :search',
                    'd.des LIKE :search',
                    'p.nikename LIKE :search'
                )
            )->setParameter('search', "%$search%");
        }

        // فیلتر تاریخ
        $today = $jdate->GetTodayDate();
        switch ($dateFilter) {
            case 'today':
                $queryBuilder->andWhere('d.date = :today')
                    ->setParameter('today', $today);
                break;
            case 'thisWeek':
                $dayOfWeek = (int) $jdate->jdate('w', time());
                $startOfWeek = $jdate->shamsiDate(0, 0, -$dayOfWeek);
                $endOfWeek = $jdate->shamsiDate(0, 0, 6 - $dayOfWeek);
                $queryBuilder->andWhere('d.date BETWEEN :start AND :end')
                    ->setParameters(['start' => $startOfWeek, 'end' => $endOfWeek]);
                break;
            case 'thisMonth':
                $currentYear = (int) $jdate->jdate('Y', time());
                $currentMonth = (int) $jdate->jdate('n', time());
                $daysInMonth = (int) $jdate->jdate('t', time());
                $startOfMonth = sprintf('%d/%02d/01', $currentYear, $currentMonth);
                $endOfMonth = sprintf('%d/%02d/%02d', $currentYear, $currentMonth, $daysInMonth);
                $queryBuilder->andWhere('d.date BETWEEN :start AND :end')
                    ->setParameters(['start' => $startOfMonth, 'end' => $endOfMonth]);
                break;
            case 'all':
            default:
                break;
        }

        // محاسبه تعداد کل
        $totalQuery = (clone $queryBuilder)
            ->select('COUNT(DISTINCT d.id) as total')
            ->getQuery()
            ->getSingleResult();
        $total = (int) $totalQuery['total'];

        // گرفتن اسناد با صفحه‌بندی
        $docs = $queryBuilder
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getArrayResult();
    
        // گرفتن اشخاص مرتبط
        $docIds = array_column($docs, 'id');
        $persons = [];
        if (!empty($docIds)) {
            $personQuery = $entityManager->createQueryBuilder()
                ->select('IDENTITY(hr.doc) as doc_id, p.code as person_code, p.nikename as person_nikename')
                ->from('App\Entity\HesabdariRow', 'hr')
                ->leftJoin('hr.person', 'p')
                ->where('hr.doc IN (:docIds)')
                ->setParameter('docIds', $docIds)
                ->getQuery()
                ->getArrayResult();
    
            foreach ($personQuery as $row) {
                if (!empty($row['person_code'])) {
                    $persons[$row['doc_id']][] = [
                        'code' => $row['person_code'],
                        'nikename' => $row['person_nikename'],
                    ];
                }
            }
        }
    
        // ساختاردهی خروجی
        $items = [];
        foreach ($docs as $doc) {
            $items[] = [
                'id' => $doc['id'],
                'date' => $doc['date'],
                'code' => $doc['code'],
                'des' => $doc['des'],
                'amount' => $doc['amount'],
                'persons' => $persons[$doc['id']] ?? [],
            ];
        }

        return $this->json([
            'items' => $items,
            'total' => $total,
        ]);
    }

    #[Route('/api/person/receive/list/excel', name: 'app_persons_receive_list_excel', methods: ['POST'])]
    public function app_persons_receive_list_excel(
        Provider $provider,
        Request $request,
        Access $access,
        Log $log,
        EntityManagerInterface $entityManager
    ): BinaryFileResponse {
        $acc = $access->hasRole('getpay');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = json_decode($request->getContent(), true) ?? [];
        if (!array_key_exists('items', $params) || empty($params['items'])) {
            $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid' => $acc['bid'],
                'type' => 'person_receive',
                'year' => $acc['year'],
                'money' => $acc['money'],
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                if (!is_array($param) || !isset($param['id'])) {
                    throw new \InvalidArgumentException('Invalid item format in request');
                }
                $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'type' => 'person_receive',
                    'year' => $acc['year'],
                    'money' => $acc['money'],
                ]);
                if ($doc) {
                    // اضافه کردن اطلاعات اشخاص
                    $personNames = [];
                    foreach ($doc->getHesabdariRows() as $row) {
                        if ($row->getPerson()) {
                            $personNames[] = $row->getPerson()->getNikename();
                        }
                    }
                    $doc->personNames = implode('، ', array_unique($personNames));
                    $items[] = $doc;
                }
            }
        }

        return new BinaryFileResponse($provider->createExcell($items, ['type', 'dateSubmit']));
    }

    #[Route('/api/person/send/list/print', name: 'app_persons_send_list_print')]
    public function app_persons_send_list_print(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('getpay');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('items', $params)) {
            $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid' => $acc['bid'],
                'type' => 'person_send',
                'year' => $acc['year'],
                'money' => $acc['money']
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'type' => 'person_send',
                    'year' => $acc['year'],
                    'money' => $acc['money']
                ]);
                if ($prs)
                    $items[] = $prs;
            }
        }
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/persons_receive.html.twig', [
                'page_title' => 'لیست پرداخت‌ها',
                'bid' => $acc['bid'],
                'items' => $items
            ])
        );
        return $this->json(['id' => $pid]);
    }

    #[Route('/api/person/send/list/search', name: 'app_persons_send_list_search')]
    public function app_persons_send_list_search(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('getpay');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $items = $entityManager->getRepository(HesabdariDoc::class)->findBy(
            [
                'bid' => $acc['bid'],
                'type' => 'person_send',
                'year' => $acc['year'],
                'money' => $acc['money']
            ],
            ['id' => 'DESC']
        );
        $res = [];
        foreach ($items as $item) {
            $temp = [
                'id' => $item->getId(),
                'date' => $item->getDate(),
                'code' => $item->getCode(),
                'des' => $item->getDes(),
                'amount' => $item->getAmount()
            ];
            $persons = [];
            foreach ($item->getHesabdariRows() as $row) {
                if ($row->getPerson()) {
                    $persons[] = Explore::ExplorePerson($row->getPerson());
                }
            }
            $temp['persons'] = $persons;
            $res[] = $temp;
        }

        return $this->json($res);
    }

    /**
     * @throws Exception
     */
    #[Route('/api/person/send/list/excel', name: 'app_persons_send_list_excel')]
    public function app_persons_send_list_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): BinaryFileResponse|JsonResponse|StreamedResponse
    {
        $acc = $access->hasRole('getpay');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('items', $params)) {
            $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid' => $acc['bid'],
                'type' => 'person_send',
                'year' => $acc['year'],
                'money' => $acc['money']
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'type' => 'person_send',
                    'year' => $acc['year'],
                    'money' => $acc['money']
                ]);
                if ($prs)
                    $items[] = $prs;
            }
        }
        return new BinaryFileResponse($provider->createExcell($items, ['type', 'dateSubmit']));
    }

    #[Route('/api/person/import/excel', name: 'app_persons_import_excel')]
    public function app_persons_import_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $file = $request->files->get('file');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $data = $sheet->toArray();
        unset($data[0]);
        foreach ($data as $item) {
            $person = $entityManager->getRepository(Person::class)->findOneBy([
                'nikename' => $item[0],
                'bid' => $acc['bid']
            ]);
            //check exist before
            if (!$person) {
                $person = new Person();
                $person->setCode($provider->getAccountingCode($acc['bid'], 'person'));
                $person->setNikename($item[0]);
                $person->setBid($acc['bid']);

                if (array_key_exists(1, $item))
                    $person->setName($item[1]);
                if (array_key_exists(4, $item))
                    $person->setBirthday($item[4]);
                if (array_key_exists(10, $item))
                    $person->setTel($item[10]);
                if (array_key_exists(2, $item))
                    $person->setSpeedAccess($item[2]);
                if (array_key_exists(18, $item))
                    $person->setAddress($item[18]);
                if (array_key_exists(5, $item))
                    $person->setDes($item[5]);
                if (array_key_exists(9, $item))
                    $person->setMobile($item[9]);
                if (array_key_exists(11, $item))
                    $person->setFax($item[11]);
                if (array_key_exists(13, $item))
                    $person->setWebsite($item[13]);
                if (array_key_exists(12, $item))
                    $person->setEmail($item[12]);
                if (array_key_exists(17, $item))
                    $person->setPostalcode($item[17]);
                if (array_key_exists(16, $item))
                    $person->setShahr($item[16]);
                if (array_key_exists(15, $item))
                    $person->setOstan($item[15]);
                if (array_key_exists(14, $item))
                    $person->setKeshvar($item[14]);
                if (array_key_exists(7, $item))
                    $person->setSabt($item[7]);
                if (array_key_exists(8, $item))
                    $person->setCodeeghtesadi($item[8]);
                if (array_key_exists(6, $item))
                    $person->setShenasemeli($item[6]);
                if (array_key_exists(3, $item))
                    $person->setCompany($item[3]);
                $entityManager->persist($person);
            }
            $entityManager->flush();
        }
        $log->insert('اشخاص', 'تعداد ' . count($data) . ' شخص به صورت گروهی وارد شد.', $this->getUser(), $acc['bid']);
        return $this->json(['result' => 1]);
    }

    #[Route('/api/person/delete/{code}', name: 'app_person_delete')]
    public function app_person_delete(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $person = $entityManager->getRepository(Person::class)->findOneBy(['bid' => $acc['bid'], 'code' => $code]);
        if (!$person)
            throw $this->createNotFoundException();
        //check accounting docs
        $docs = $entityManager->getRepository(HesabdariRow::class)->findby(['bid' => $acc['bid'], 'person' => $person]);
        if (count($docs) > 0)
            return $this->json(['result' => 2]);
        //check for storeroom docs
        $storeDocs = $entityManager->getRepository(StoreroomTicket::class)->findby(['bid' => $acc['bid'], 'Person' => $person]);
        if (count($storeDocs) > 0)
            return $this->json(['result' => 2]);
        //check in repservice

        $comName = $person->getName();
        try {
            $entityManager->remove($person);
        } catch (Exception $e) {
            return $this->json(['result' => 2]);
        }
        $log->insert('اشخاص', '  شخص  با نام ' . $comName . ' حذف شد. ', $this->getUser(), $acc['bid']->getId());
        return $this->json(['result' => 1]);
    }

    #[Route('/api/person/prelabels/list', name: 'app_persons_prelabels_list')]
    public function app_persons_prelabels_list(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('person');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $labels = $entityManager->getRepository(PersonPrelabel::class)->findAll();
        $rows = [];
        foreach ($labels as $key => $label) {
            $rows[] = [
                'label' => $label->getLabel(),
                'code' => $label->getCode(),
            ];
        }
        return new Response(json_encode($rows));
    }

    #[Route('/api/person/deletegroup', name: 'app_persons_delete_group', methods: ['POST'])]
    public function app_persons_delete_group(
        Extractor $extractor,
        Request $request,
        Access $access,
        Log $log,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $acc = $access->hasRole('person');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = json_decode($request->getContent(), true);
        if (!isset($params['codes']) || !is_array($params['codes'])) {
            return $this->json(['Success' => false, 'message' => 'لیست کدهای اشخاص ارسال نشده است'], 400);
        }

        $hasIgnored = false;
        $deletedCount = 0;

        foreach ($params['codes'] as $code) {
            $person = $entityManager->getRepository(Person::class)->findOneBy([
                'bid' => $acc['bid'],
                'code' => $code
            ]);

            if (!$person) {
                $hasIgnored = true;
                continue;
            }

            // بررسی اسناد حسابداری
            $docs = $entityManager->getRepository(HesabdariRow::class)->findBy(['bid' => $acc['bid'], 'person' => $person]);
            if (count($docs) > 0) {
                $hasIgnored = true;
                continue;
            }

            // بررسی اسناد انبار
            $storeDocs = $entityManager->getRepository(StoreroomTicket::class)->findBy(['bid' => $acc['bid'], 'Person' => $person]);
            if (count($storeDocs) > 0) {
                $hasIgnored = true;
                continue;
            }

            $personName = $person->getNikename();
            $entityManager->remove($person);
            $log->insert('اشخاص', 'شخص با نام ' . $personName . ' حذف شد.', $this->getUser(), $acc['bid']->getId());
            $deletedCount++;
        }

        $entityManager->flush();

        return $this->json([
            'Success' => true,
            'result' => [
                'ignored' => $hasIgnored,
                'deletedCount' => $deletedCount,
                'message' => $hasIgnored ? 'برخی اشخاص به دلیل استفاده در اسناد حذف نشدند' : 'همه اشخاص با موفقیت حذف شدند'
            ]
        ]);
    }
}
