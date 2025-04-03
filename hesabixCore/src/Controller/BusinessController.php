<?php

namespace App\Controller;

use App\Entity\APIToken;
use App\Entity\BankAccount;
use App\Entity\Commodity;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\Money;
use App\Entity\Permission;
use App\Entity\Person;
use App\Entity\Plugin;
use App\Entity\User;
use App\Entity\Business;
use App\Entity\Hook;
use App\Entity\Year;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Extractor;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\Provider;
use App\Service\registryMGR;
use Doctrine\ORM\EntityManagerInterface;

use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class BusinessController extends AbstractController
{
    #[Route('/api/business/delete', name: 'api_business_delete')]
    public function api_business_delete(Extractor $extractor, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $archiveUser = $entityManager->getRepository(User::class)->findOneBy([
            'email' => 'archive@hesabix.ir'
        ]);
        if (!$archiveUser) {
            $archiveUser = new User();
            $archiveUser->setEmail('archive@hesabix.ir');
            $archiveUser->setFullName('کاربر آرشیو و بایگانی');
            $archiveUser->setPassword(0);
            $archiveUser->setDateRegister(time());
            $archiveUser->setActive(false);
            $entityManager->persist($archiveUser);
            $entityManager->flush();
        }
        $acc['bid']->setOwner($archiveUser);
        $acc['bid']->setArchiveEmail($this->getUser()->getEmail());
        $entityManager->persist($acc['bid']);
        $entityManager->flush();

        //remove permissions
        $permissions = $entityManager->getRepository(Permission::class)->findBy([
            'bid' => $acc['bid']
        ]);
        foreach ($permissions as $perm) {
            $entityManager->remove($perm);
            $entityManager->flush();
        }
        return $this->json($extractor->operationSuccess());
    }
    #[Route('/api/business/list', name: 'api_bussiness_list')]
    public function api_bussiness_list(Extractor $extractor, Request $request, #[CurrentUser] ?User $user, Access $access, Explore $explore, EntityManagerInterface $entityManager, Provider $provider): Response
    {

        $buss = $entityManager->getRepository(Permission::class)->findBy([
            'user' => $user
        ]);
        $response = [];
        foreach ($buss as $bus) {
            $response[] = Explore::ExploreBusiness($bus->getBid());
        }
        $params = $request->getPayload()->all();
        if (array_key_exists('standard', $params)) {
            return $this->json($extractor->operationSuccess($response));
        }
        return $this->json($response);
    }

    /**
     * @throws ReflectionException
     */
    #[Route('/api/business/get/info/{bid}', name: 'api_business_get_info')]
    public function api_business_get_info($bid, #[CurrentUser] ?User $user, Access $access, Provider $provider, EntityManagerInterface $entityManager): Response
    {
        $bus = $entityManager->getRepository(Business::class)->findOneBy(['id' => $bid]);
        if (!$bus)
            throw $this->createNotFoundException();
        $perms = $entityManager->getRepository(Permission::class)->findOneBy([
            'bid' => $bus,
            'user' => $user
        ]);
        if (!$perms)
            throw $this->createAccessDeniedException();
        return $this->json(Explore::ExploreBusiness($bus));
    }

    #[Route('/api/business/list/count', name: 'api_bussiness_list_count')]
    public function api_bussiness_list_count(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        $buss = $entityManager->getRepository(Permission::class)->findBy(['user' => $user]);
        $response = ['count' => count($buss)];
        return $this->json($response);
    }

    #[Route('/api/business/insert', name: 'api_bussiness_insert')]
    public function api_bussiness_insert(Jdate $jdate, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager,registryMGR $registryMGR): Response
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        //check for that data is set
        if (
            trim($params['name']) != '' &&
            trim($params['legal_name']) != '' &&
            trim($params['maliyatafzode']) != ''
        ) {
            //submit business
            $isNew = false;
            if (array_key_exists('bid', $params)) {
                $business = $entityManager->getRepository(Business::class)->find($params['bid']);
                if (!$business) {
                    return $this->json(['result' => -1]);
                }
            } else {
                $business = new Business();
                $business->setPersonCode(1000);
                $business->setBankCode(1000);
                $business->setReceiveCode(1000);
                $isNew = true;
            }
            if (!$isNew && !$access->hasRole('settings'))
                throw $this->createAccessDeniedException();
            //check for that user register business before
            $oldBid = $entityManager->getRepository(Business::class)->findOneBy(['owner' => $this->getUser()], ['id' => 'DESC']);
            if ($oldBid && !$business->getId()) {
                if ($oldBid->getDateSubmit() > time() - 86400) {
                    return $this->json(['result' => 3]);
                }
            }
            $business->setName($params['name']);
            $business->setOwner($this->getUser());
            $business->setLegalName($params['legal_name']);
            $business->setMaliyatafzode($params['maliyatafzode']);

            if (array_key_exists(key: 'field', array: $params)) {
                $business->setField($params['field']);
            }

            if (array_key_exists(key: 'type', array: $params)) {
                $business->setType($params['type']);
            }
            if (array_key_exists(key: 'shenasemeli', array: $params)) {
                $business->setShenasemeli($params['shenasemeli']);
            }
            if (array_key_exists(key: 'codeeqtesadi', array: $params)) {
                $business->setCodeeghtesadi($params['codeeqtesadi']);
            }
            if (array_key_exists(key: 'shomaresabt', array: $params)) {
                $business->setShomaresabt($params['shomaresabt']);
            }
            if (array_key_exists(key: 'country', array: $params)) {
                $business->setCountry($params['country']);
            }
            if (array_key_exists(key: 'ostan', array: $params)) {
                $business->setOstan($params['ostan']);
            }
            if (array_key_exists(key: 'shahrestan', array: $params)) {
                $business->setShahrestan($params['shahrestan']);
            }
            if (array_key_exists(key: 'postalcode', array: $params)) {
                $business->setPostalcode($params['postalcode']);
            }
            if (array_key_exists(key: 'zarinpalCode', array: $params)) {
                $business->setZarinpalCode($params['zarinpalCode']);
            }
            if (array_key_exists(key: 'shortlinks', array: $params)) {
                $business->setShortlinks($params['shortlinks']);
            }
            if (array_key_exists(key: 'tel', array: $params)) {
                $business->setTel($params['tel']);
            }
            if (array_key_exists(key: 'mobile', array: $params)) {
                $business->setMobile($params['mobile']);
            }
            if (array_key_exists(key: 'address', array: $params)) {
                $business->setAddress($params['address']);
            }
            if (array_key_exists(key: 'website', array: $params)) {
                $business->setWesite($params['website']);
            }
            if (array_key_exists(key: 'email', array: $params)) {
                $business->setEmail($params['email']);
            }
            if (array_key_exists('commodityUpdateBuyPriceAuto', $params)) {
                if ($params['commodityUpdateBuyPriceAuto'] == true) {
                    $business->setCommodityUpdateBuyPriceAuto(true);
                } else {
                    $business->setCommodityUpdateBuyPriceAuto(false);
                }
            }
            if (array_key_exists('commodityUpdateSellPriceAuto', $params)) {
                if ($params['commodityUpdateSellPriceAuto'] == true) {
                    $business->setCommodityUpdateSellPriceAuto(true);
                } else {
                    $business->setCommodityUpdateSellPriceAuto(false);
                }
            }
            if (array_key_exists('profitCalcType', $params)) {
                if ($params['profitCalcType'] == 'lis' || $params['profitCalcType'] == 'avgis' || $params['profitCalcType'] == 'simple') {
                    $business->setProfitCalcType($params['profitCalcType']);
                }
            } else {
                $business->setProfitCalcType('lis');
            }
            if (array_key_exists('commodityUpdateSellPriceAuto', $params)) {
                $business->setCommodityUpdateSellPriceAuto($params['commodityUpdateSellPriceAuto']);
            } else {
                $business->setCommodityUpdateSellPriceAuto(true);
            }
            if (array_key_exists('walletEnabled', $params)) {
                if ($params['walletEnabled']) {
                    if (array_key_exists('walletMatchBank', $params)) {
                        $bank = $entityManager->getRepository(BankAccount::class)->findOneBy([
                            'bid' => $business->getId(),
                            'id' => $params['walletMatchBank']
                        ]);
                        if ($bank) {
                            $business->setWalletEnable($params['walletEnabled']);
                            $business->setWalletMatchBank($bank);
                        }
                    }
                } else {
                    $business->setWalletEnable(false);
                }
            }

            //get Money type
            if (!array_key_exists('arzmain', $params) && $isNew) {
                return $this->json(['result' => 2]);
            } elseif (array_key_exists('arzmain', $params) && $isNew) {
                $Arzmain = $entityManager->getRepository(Money::class)->findOneBy(['name' => $params['arzmain']]);
                if ($Arzmain)
                    $business->setMoney($Arzmain);
                else
                    return $this->json(['result' => 2]);
            }
            if (!$business->getDateSubmit())
                $business->setDateSubmit(time());
            $entityManager->persist($business);
            $entityManager->flush();
            if ($isNew) {
                $perms = new Permission();
                $giftCredit = (int) $registryMGR->get('system_settings', 'gift_credit', 0);
                $business->setSmsCharge($giftCredit);
                $perms->setBid($business);
                $perms->setUser($this->getUser());
                $perms->setOwner(true);
                $perms->setSettings(true);
                $perms->setPerson(true);
                $perms->setGetpay(true);
                $perms->setCommodity(true);
                $perms->setBanks(true);
                $perms->setBankTransfer(true);
                $perms->setBuy(true);
                $perms->setSell(true);
                $perms->setCost(true);
                $perms->setIncome(true);
                $perms->setCheque(true);
                $perms->setReport(true);
                $perms->setAccounting(true);
                $perms->setLog(true);
                $perms->setStore(true);
                $perms->setSalary(true);
                $perms->setPermission(true);
                $perms->setSalary(true);
                $perms->setCashdesk(true);
                $perms->setWallet(true);
                $entityManager->persist($perms);
                $entityManager->flush();
                //active Year
                $year = new Year();
                $year->setBid($business);
                $year->setHead(true);
                $startYearArray = explode('-', $params['year']['start']);
                if (count($startYearArray) == 1)
                    $startYearArray = explode('/', $params['year']['start']);

                $year->setStart($jdate->jmktime(0, 0, 0, $startYearArray[1], $startYearArray[2], $startYearArray[0]));
                $endYearArray = explode('-', $params['year']['end']);
                if (count($endYearArray) == 1)
                    $endYearArray = explode('/', $params['year']['end']);
                $year->setEnd($jdate->jmktime(0, 0, 0, $endYearArray[1], $endYearArray[2], $endYearArray[0]));
                $year->setLabel($params['year']['label']);
                $entityManager->persist($year);
                $entityManager->flush();
            } else {
                //not new business update business year
                $year = $entityManager->getRepository(Year::class)->findOneBy([
                    'bid' => $business,
                    'head' => true
                ]);
                if (!$year) {
                    $year = new Year;
                }
                $startYearArray = explode('-', $params['year']['startShamsi']);
                if (count($startYearArray) == 1)
                    $startYearArray = explode('/', $params['year']['startShamsi']);

                $year->setStart($jdate->jmktime(0, 0, 0, $startYearArray[1], $startYearArray[2], $startYearArray[0]));
                $endYearArray = explode('-', $params['year']['endShamsi']);
                if (count($endYearArray) == 1)
                    $endYearArray = explode('/', $params['year']['endShamsi']);
                $year->setEnd($jdate->jmktime(0, 0, 0, $endYearArray[1], $endYearArray[2], $endYearArray[0]));
                $year->setLabel($params['year']['label']);
                $entityManager->persist($year);
                $entityManager->flush();
            }
            //add log to system
            $log->insert('تنظیمات پایه', 'اطلاعات کسب و کار ایجاد/ویرایش شد.', $this->getUser(), $business);
        }
        return $this->json(['result' => 1]);
    }


    #[Route('/api/business/add/user', name: 'api_business_add_user')]
    public function api_business_add_user(Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$access->hasRole('permission'))
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        //check for that data is set
        if (
            trim($params['bid']) != '' &&
            trim($params['email']) != ''
        ) {
            $business = $entityManager->getRepository(Business::class)->find($params['bid']);
            if (is_null($business)) {
                return $this->json(['result' => -1]);
            }
            //echo $params['email'];
            $user = $entityManager->getRepository(User::class)->findOneBy([
                'email' => $params['email']
            ]);
            if (is_null($user)) {
                return $this->json(['result' => 0]);
            }
            $perm = $entityManager->getRepository(Permission::class)->findOneBy([
                'user' => $user,
                'bid' => $business
            ]);
            if ($perm) {
                //already added
                return $this->json(['result' => 1]);
            }
            $perm = new Permission();
            $perm->setBid($business);
            $perm->setUser($user);
            $perm->setOwner(false);
            $entityManager->persist($perm);
            $entityManager->flush();
            //add log to system
            $log->insert('تنظیمات پایه', 'کاربر با پست الکترونیکی ' . $params['email'] . ' به کسب و کار اضافه شد.', $this->getUser(), $business);
            return $this->json(
                [
                    'result' => 2,
                    'data' => [
                        'email' => $user->getEmail(),
                        'name' => $user->getFullName(),
                        'owner' => false
                    ]
                ]
            );
        }
        return $this->json(['result' => -1]);
    }

    #[Route('/api/business/delete/user', name: 'api_business_delete_user')]
    public function api_business_delete_user(Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$access->hasRole('permission'))
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        //check for that data is set
        if (
            trim($params['bid']) != '' &&
            trim($params['email']) != ''
        ) {
            $business = $entityManager->getRepository(Business::class)->find($params['bid']);
            if (is_null($business)) {
                return $this->json(['result' => -1]);
            }
            //echo $params['email'];
            $user = $entityManager->getRepository(User::class)->findOneBy([
                'email' => $params['email']
            ]);
            if (is_null($user)) {
                return $this->json(['result' => -1]);
            }
            $perm = $entityManager->getRepository(Permission::class)->findOneBy([
                'user' => $user,
                'bid' => $business
            ]);
            if ($perm && !$perm->isOwner()) {
                $entityManager->remove($perm);
                $entityManager->flush();
                //add log to system
                $log->insert('تنظیمات پایه', 'کاربر با پست الکترونیکی ' . $params['email'] . ' از کسب و کار حذف شد.', $this->getUser(), $business);
                return $this->json(['result' => 1]);
            }
        }
        return $this->json(['result' => -1]);
    }

    #[Route('/api/business/removeuser/me', name: 'api_business_remove_user_me')]
    public function api_business_remove_user_me(Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        if ($this->getUser()->getId() == $acc['bid']->getOwner()->getId()) {
            throw $this->createNotFoundException();
        }

        $perm = $entityManager->getRepository(Permission::class)->findOneBy([
            'user' => $this->getUser(),
            'bid' => $acc['bid']
        ]);
        if ($perm && !$perm->isOwner()) {
            $entityManager->remove($perm);
            $entityManager->flush();
            //add log to system
            $log->insert('تنظیمات پایه', 'کاربر با پست الکترونیکی ' . $this->getUser()->getEmail() . '  کسب و کار را ترک کرد..', $this->getUser(), $acc['bid']);
            return $this->json(['result' => 1]);
        }

        return $this->json(['result' => -1]);
    }

    #[Route('/api/business/my/permission/state', name: 'api_business_my_permission_state')]
    public function api_business_my_permission_state(Request $request, Access $access): Response
    {
        $reqdata = json_decode($request->getContent(), true);
        if (!array_key_exists('permission', $reqdata)) {
            throw $this->createNotFoundException();
        }
        $acc = $access->hasRole($reqdata['permission']);
        if ($acc)
            return $this->json(['state' => true]);
        return $this->json(['state' => false]);
    }
    #[Route('/api/business/get/user/permissions', name: 'api_business_get_user_permission')]
    public function api_business_get_user_permission(Access $access, Request $request, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        //check for that data is set
        if (array_key_exists('bid', $params) && array_key_exists('email', $params)) {
            $business = $entityManager->getRepository(Business::class)->find($params['bid']);
            if (is_null($business)) {
                return $this->json(['result' => -1]);
            }
            $user = $entityManager->getRepository(User::class)->findOneBy([
                'email' => $params['email']
            ]);
            if (is_null($user)) {
                return $this->json(['result' => -1]);
            }
        } else {
            $business = $entityManager->getRepository(Business::class)->find($acc['bid']);
            if (is_null($business)) {
                return $this->json(['result' => -1]);
            }
            $user = $this->getUser();
        }
        $perm = $entityManager->getRepository(Permission::class)->findOneBy([
            'bid' => $business,
            'user' => $user
        ]);
        $result = [];
        if ($business->getOwner() == $user) {
            $result = [
                'id' => $perm->getUser()->getId(),
                'user' => $perm->getUser()->getFullName(),
                'email' => $perm->getUser()->getEmail(),
                'settings' => true,
                'persons' => true,
                'commodity' => true,
                'cheque' => true,
                'getpay' => true,
                'store' => true,
                'bank' => true,
                'bankTransfer' => true,
                'cost' => true,
                'income' => true,
                'buy' => true,
                'sell' => true,
                'accounting' => true,
                'report' => true,
                'log' => true,
                'permission' => true,
                'salary' => true,
                'cashdesk' => true,
                'plugNoghreAdmin' => true,
                'plugNoghreSell' => true,
                'plugCCAdmin' => true,
                'wallet' => true,
                'owner' => true,
                'archiveUpload' => true,
                'archiveMod' => true,
                'archiveDelete' => true,
                'archiveView' => true,
                'active' => $perm->getUser()->isActive(),
                'shareholder' => true,
                'plugAccproAccounting' => true,
                'plugAccproRfsell' => true,
                'plugAccproRfbuy' => true,
                'plugAccproCloseYear' => true,
                'plugRepservice' => true,
            ];
        } elseif ($perm) {
            $result = [
                'id' => $perm->getUser()->getId(),
                'user' => $perm->getUser()->getFullName(),
                'email' => $perm->getUser()->getEmail(),
                'settings' => $perm->isSettings(),
                'persons' => $perm->isPerson(),
                'commodity' => $perm->isCommodity(),
                'getpay' => $perm->isGetpay(),
                'bank' => $perm->isBanks(),
                'bankTransfer' => $perm->isBankTransfer(),
                'cost' => $perm->isCost(),
                'income' => $perm->isIncome(),
                'buy' => $perm->isBuy(),
                'cheque' => $perm->isCheque(),
                'sell' => $perm->isSell(),
                'accounting' => $perm->isAccounting(),
                'report' => $perm->isReport(),
                'log' => $perm->isLog(),
                'store' => $perm->isStore(),
                'permission' => $perm->isPermission(),
                'salary' => $perm->isSalary(),
                'cashdesk' => $perm->isCashdesk(),
                'plugNoghreAdmin' => $perm->isPlugNoghreAdmin(),
                'plugNoghreSell' => $perm->isPlugNoghreSell(),
                'plugCCAdmin' => $perm->isPlugCCAdmin(),
                'wallet' => $perm->isWallet(),
                'owner' => false,
                'archiveUpload' => $perm->isArchiveUpload(),
                'archiveMod' => $perm->isArchiveMod(),
                'archiveDelete' => $perm->isArchiveDelete(),
                'archiveView' => $perm->isArchiveView(),
                'active' => $perm->getUser()->isActive(),
                'shareholder' => $perm->isShareholder(),
                'plugAccproAccounting' => $perm->isPlugAccproAccounting(),
                'plugAccproRfsell' => $perm->isPlugAccproRfsell(),
                'plugAccproRfbuy' => $perm->isPlugAccproRfbuy(),
                'plugAccproCloseYear' => $perm->isPlugAccproCloseYear(),
                'plugRepservice' => $perm->isPlugRepservice(),
            ];
        }
        return $this->json($result);
        return $this->json(['result' => -1]);
    }

    #[Route('/api/business/save/user/permissions', name: 'api_business_save_user_permission')]
    public function api_business_save_user_permission(Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$access->hasRole('permission'))
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        //check for that data is set
        if (
            trim($params['bid']) != '' &&
            trim($params['email']) != ''
        ) {
            $business = $entityManager->getRepository(Business::class)->find($params['bid']);
            if (is_null($business)) {
                return $this->json(['result' => -1]);
            }
            $user = $entityManager->getRepository(User::class)->findOneBy([
                'email' => $params['email']
            ]);
            if (is_null($user)) {
                return $this->json(['result' => -1]);
            }
            $perm = $entityManager->getRepository(Permission::class)->findOneBy([
                'bid' => $business,
                'user' => $user
            ]);
            if ($perm) {
                $perm->setSettings($params['settings']);
                $perm->setPerson($params['persons']);
                $perm->setGetpay($params['getpay']);
                $perm->setCommodity($params['commodity']);
                $perm->setBanks($params['bank']);
                $perm->setBankTransfer($params['bankTransfer']);
                $perm->setbuy($params['buy']);
                $perm->setSell($params['sell']);
                $perm->setStore($params['store']);
                $perm->setCost($params['cost']);
                $perm->setIncome($params['income']);
                $perm->setCheque($params['cheque']);
                $perm->setAccounting($params['accounting']);
                $perm->setReport($params['report']);
                $perm->setPermission($params['permission']);
                $perm->setSalary($params['salary']);
                $perm->setWallet($params['wallet']);
                $perm->setCashdesk($params['cashdesk']);
                $perm->setPlugNoghreAdmin($params['plugNoghreAdmin']);
                $perm->setPlugNoghreSell($params['plugNoghreSell']);
                $perm->setPlugCCAdmin($params['plugCCAdmin']);
                $perm->setLog($params['log']);
                $perm->setArchiveMod($params['archiveMod']);
                $perm->setArchiveDelete($params['archiveDelete']);
                $perm->setArchiveUpload($params['archiveUpload']);
                $perm->setArchiveView($params['archiveView']);
                $perm->setShareholder($params['shareholder']);
                $perm->setPlugAccproCloseYear($params['plugAccproCloseYear']);
                $perm->setPlugAccproRfbuy($params['plugAccproRfbuy']);
                $perm->setPlugAccproRfsell($params['plugAccproRfsell']);
                $perm->setPlugAccproAccounting($params['plugAccproAccounting']);
                $perm->setPlugRepservice($params['plugRepservice']);
                $entityManager->persist($perm);
                $entityManager->flush();
                $log->insert('تنظیمات پایه', 'ویرایش دسترسی‌های کاربر با پست الکترونیکی ' . $user->getEmail(), $this->getUser(), $business);

                return $this->json(['result' => 1]);
            }
        }
        return $this->json(['result' => -1]);
    }

    #[Route('/api/business/stat', name: 'api_business_stat')]
    public function api_business_stat(Access $access, Jdate $jdate, Request $request, #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $dateNow = $jdate->jdate('Y/m/d', time());
        $buss = $entityManager->getRepository(Business::class)->find(
            $request->headers->get('activeBid')
        );
        if (!$buss)
            throw $this->createNotFoundException();

        $year = $entityManager->getRepository(Year::class)->find(
            $request->headers->get('activeYear')
        );
        if (!$year)
            throw $this->createNotFoundException();
        $persons = $entityManager->getRepository(Person::class)->findBy([
            'bid' => $buss
        ]);
        $banks = $entityManager->getRepository(BankAccount::class)->findBy([
            'bid' => $buss
        ]);

        $docs = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid' => $buss,
            'year' => $year,
            'money' => $acc['money']
        ]);

        $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'bid' => $buss,
            'year' => $year
        ]);
        $bssum = 0;
        foreach ($rows as $row)
            $bssum += $row->getBs();
        $buys = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid' => $buss,
            'year' => $year,
            'type' => 'buy',
            'money' => $acc['money']

        ]);
        $buysTotal = 0;
        $buysToday = 0;
        foreach ($buys as $item) {
            $canAdd = false;
            foreach ($item->getHesabdariRows() as $row) {
                if ($row->getCommodity())
                    $canAdd = true;
            }
            if ($canAdd) {
                $buysTotal += $item->getAmount();
                if ($item->getDate() == $dateNow) {
                    $buysToday += $item->getAmount();
                }
            }
        }

        $sells = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid' => $buss,
            'year' => $year,
            'type' => 'sell',
            'money' => $acc['money']
        ]);
        $sellsTotal = 0;
        $sellsToday = 0;
        foreach ($sells as $item) {
            $canAdd = false;
            foreach ($item->getHesabdariRows() as $row) {
                if ($row->getCommodity())
                    $canAdd = true;
            }
            if ($canAdd) {
                $sellsTotal += $item->getAmount();
                if ($item->getDate() == $dateNow) {
                    $sellsToday += $item->getAmount();
                }
            }
        }

        $sends = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid' => $buss,
            'year' => $year,
            'type' => 'person_send',
            'money' => $acc['money']
        ]);
        $sendsTotal = 0;
        $sendsToday = 0;
        foreach ($sends as $item) {
            $canAdd = false;
            foreach ($item->getHesabdariRows() as $row) {
                if ($row->getPerson())
                    $canAdd = true;
            }
            if ($canAdd) {
                $sendsTotal += $item->getAmount();
                if ($item->getDate() == $dateNow) {
                    $sendsToday += $item->getAmount();
                }
            }
        }

        $recs = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid' => $buss,
            'year' => $year,
            'type' => 'person_receive',
            'money' => $acc['money']
        ]);
        $recsTotal = 0;
        $recsToday = 0;
        foreach ($recs as $item) {
            $canAdd = false;
            foreach ($item->getHesabdariRows() as $row) {
                if ($row->getPerson())
                    $canAdd = true;
            }
            if ($canAdd) {
                $recsTotal += $item->getAmount();
                if ($item->getDate() == $dateNow) {
                    $recsToday += $item->getAmount();
                }
            }
        }
        $response = [
            'personCount' => count($persons),
            'bankCount' => count($banks),
            'docCount' => count($docs),
            'income' => $bssum,
            'commodity' => count($entityManager->getRepository(Commodity::class)->findby([
                'bid' => $buss
            ])),
            'buys_total' => $buysTotal,
            'buys_today' => $buysToday,
            'sells_total' => $sellsTotal,
            'sells_today' => $sellsToday,
            'sends_total' => $sendsTotal,
            'sends_today' => $sendsToday,
            'recs_total' => $recsTotal,
            'recs_today' => $recsToday,
        ];
        return $this->json($response);
    }
}
