<?php

namespace App\Controller;

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
            $bs += $row->getBs();
            $bd += $row->getBd();
        }
        $response['bs'] = $bs;
        $response['bd'] = $bd;
        $response['balance'] = $bs - $bd;
        return $this->json($response);
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
            if ($person)
                return $this->json(['result' => 2]);
            $person = new Person();
            $person->setCode($provider->getAccountingCode($acc['bid'], 'person'));
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
        return $this->json(['result' => 1]);
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
                'id'    => $person->getId(),
                'nikename' => $person->getNikename(),
                'code'  => $person->getCode(),
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
                'id'    => $person->getId(),
                'nikename' => $person->getNikename(),
                'code'  => $person->getCode(),
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

    #[Route('/api/person/list', name: 'app_persons_list')]
    public function app_persons_list(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('person');
        if(!$acc)
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
        $response = Explore::ExplorePersons($persons, $entityManager->getRepository(PersonType::class)->findAll());
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
        return new Response(json_encode($response));
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
    public function app_persons_list_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): BinaryFileResponse | JsonResponse | StreamedResponse
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
    public function app_persons_card_list_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): BinaryFileResponse | JsonResponse | StreamedResponse
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
            $transactions = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bid' => $acc['bid'],
                'person' => $person
            ]);
        } else {
            $transactions = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'person' => $person
                ]);
                if ($prs) {
                    $transactions[] = $prs;
                }
            }
        }
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $arrayEntity = [[
            'شماره تراکنش',
            'تاریخ',
            'توضیحات',
            'تفضیل',
            'بستانکار',
            'بدهکار',
            'سال مالی',
        ]];
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
            $transactions = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bid' => $acc['bid'],
                'person' => $person
            ]);
        } else {
            $transactions = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'person' => $person
                ]);
                if ($prs) {
                    $transactions[] = $prs;
                }
            }
        }
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/person_card.html.twig', [
                'page_title' => 'کارت حساب'  . ' ' . $person->getNikename(),
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
        if (!array_key_exists('items', $params)) {
            $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid' => $acc['bid'],
                'type' => 'person_receive',
                'year' => $acc['year']
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'type' => 'person_receive',
                    'year' => $acc['year']
                ]);
                if ($prs)
                    $items[] = $prs;
            }
        }
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/persons_receive.html.twig', [
                'page_title' => 'لیست دریافت‌ها',
                'bid' => $acc['bid'],
                'items' => $items
            ])
        );
        return $this->json(['id' => $pid]);
    }

    #[Route('/api/person/receive/list/search', name: 'app_persons_receive_list_search')]
    public function app_persons_receive_list_search(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
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
                'type' => 'person_receive',
                'year'=>$acc['year']
            ],
            ['id' => 'DESC']
        );
        $res = [];
        foreach($items as $item){
            $temp = [
                'id'=>$item->getId(),
                'date'=>$item->getDate(),
                'code'=>$item->getCode(),
                'des'=>$item->getDes(),
                'amount'=>$item->getAmount()
            ];
            $persons = [];
            foreach($item->getHesabdariRows() as $row){
                if($row->getPerson()){
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
    #[Route('/api/person/receive/list/excel', name: 'app_persons_receive_list_excel')]
    public function app_persons_receive_list_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): BinaryFileResponse | JsonResponse | StreamedResponse
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
                'type' => 'person_receive',
                'year' => $acc['year']
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'type' => 'person_receive',
                    'year' => $acc['year']
                ]);
                if ($prs)
                    $items[] = $prs;
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
                'year' => $acc['year']
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'type' => 'person_send',
                    'year' => $acc['year']
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
                'year'=>$acc['year']
            ],
            ['id' => 'DESC']
        );
        $res = [];
        foreach($items as $item){
            $temp = [
                'id'=>$item->getId(),
                'date'=>$item->getDate(),
                'code'=>$item->getCode(),
                'des'=>$item->getDes(),
                'amount'=>$item->getAmount()
            ];
            $persons = [];
            foreach($item->getHesabdariRows() as $row){
                if($row->getPerson()){
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
    public function app_persons_send_list_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): BinaryFileResponse | JsonResponse | StreamedResponse
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
                'year' => $acc['year']
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'type' => 'person_send',
                    'year' => $acc['year']
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
}
