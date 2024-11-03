<?php

namespace App\Service;

use App\Entity\BankAccount;
use App\Entity\Project;
use App\Entity\Storeroom;
use App\Entity\User;
use App\Entity\Year;
use App\Entity\Business;
use App\Entity\Cashdesk;
use App\Entity\Cheque;
use App\Entity\Commodity;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\Money;
use App\Entity\Person;
use App\Entity\PersonType;
use App\Entity\PriceListDetail;
use App\Entity\Salary;

class Explore
{
    public static function ExploreCommodityPriceList($items)
    {
        $result = [];
        foreach ($items as $item)
            $result[] = self::ExploreCommodityPriceListOne($item);
        return $result;
    }
    public static function ExploreCommodityPriceListOne($item)
    {
        return [
            'id' => $item->getId(),
            'label' => $item->getLabel(),
        ];
    }
    public static function ExplorePersonType(PersonType $type)
    {
        return [
            'label' => $type->getLabel(),
            'code' => $type->getCode(),
            'checked' => false
        ];
    }

    public static function ExplorePersonTypes($types)
    {
        $result = [];
        foreach ($types as $type)
            $result[] = self::ExplorePersonType($type);
        return $result;
    }
    public static function ExploreSellDoc(HesabdariDoc $hesabdariDoc)
    {
        $result = self::ExploreHesabdariDoc($hesabdariDoc);
        $person = [];
        foreach ($hesabdariDoc->getHesabdariRows() as $item) {
            if ($item->getPerson()) {
                $person = self::ExplorePerson($item->getPerson());
            } elseif ($item->getRef()->getCode() == '104') {
                $result['discountAll'] = $item->getBd();
            } elseif ($item->getRef()->getCode() == '61') {
                $result['transferCost'] = $item->getBs();
            }
        }
        if (!array_key_exists('discountAll', $result))
            $result['discountAll'] = 0;
        if (!array_key_exists('transferCost', $result))
            $result['transferCost'] = 0;
        $result['person'] = $person;
        return $result;
    }

    public static function ExploreRfbuyDoc(HesabdariDoc $hesabdariDoc)
    {
        $result = self::ExploreHesabdariDoc($hesabdariDoc);
        $person = [];
        $commodities = [];
        foreach ($hesabdariDoc->getHesabdariRows() as $item) {
            if ($item->getPerson()) {
                $person = self::ExplorePerson($item->getPerson());
            } elseif ($item->getCommodity()) {
                $commodities[] = Explore::ExploreCommodity($item->getCommodity(), $item->getCommdityCount(), $item->getDes());
            }
        }
        $result['person'] = $person;
        return $result;
    }

    public static function ExploreBuyDoc(HesabdariDoc $hesabdariDoc)
    {
        $result = self::ExploreHesabdariDoc($hesabdariDoc);
        $person = [];
        foreach ($hesabdariDoc->getHesabdariRows() as $item) {
            if ($item->getPerson()) {
                $person = self::ExplorePerson($item->getPerson());
            } elseif ($item->getRef()->getCode() == '104') {
                $result['discountAll'] = $item->getBs();
            } elseif ($item->getRef()->getCode() == '90') {
                $result['transferCost'] = $item->getBd();
            }
        }
        if (!array_key_exists('discountAll', $result))
            $result['discountAll'] = 0;
        if (!array_key_exists('transferCost', $result))
            $result['transferCost'] = 0;
        $result['person'] = $person;
        return $result;
    }
    public static function ExploreRfsellDoc(HesabdariDoc $hesabdariDoc)
    {
        $result = self::ExploreHesabdariDoc($hesabdariDoc);
        $person = [];
        $commodities = [];
        foreach ($hesabdariDoc->getHesabdariRows() as $item) {
            if ($item->getPerson()) {
                $person = self::ExplorePerson($item->getPerson());
            } elseif ($item->getCommodity()) {
                $commodities[] = Explore::ExploreCommodity($item->getCommodity(), $item->getCommdityCount(), $item->getDes());
            }
        }
        $result['person'] = $person;
        return $result;
    }
    public static function ExploreHesabdariDoc(HesabdariDoc $doc)
    {
        return [
            'id' => $doc->getId(),
            'update' => $doc->getCode(),
            'rows' => self::ExploreHesabdariRows($doc->getHesabdariRows()),
            'submiter' => self::ExploreUser($doc->getSubmitter()),
            'year' => self::ExploreYear($doc->getYear()),
            'money' => self::ExploreMoney($doc->getMoney()),
            'date_submit' => $doc->getDateSubmit(),
            'date' => $doc->getDate(),
            'type' => $doc->getType(),
            'code' => $doc->getCode(),
            'des' => $doc->getDes(),
            'amount' => $doc->getAmount(),
            'mdate' => '',
            'plugin' => $doc->getPlugin(),
        ];
    }

    public static function ExploreHesabdariRows($rows)
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = self::ExploreHesabdariRow($row);
        }
        return $result;
    }
    public static function ExploreHesabdariRow(HesabdariRow $row)
    {
        $temp = [
            'id' => $row->getId(),
            'bid' => self::ExploreBid($row->getBid()),
            'year' => self::ExploreYear($row->getYear()),
            'ref' => self::ExploreHesabdariTable($row->getRef()),
            'person' => self::ExplorePerson($row->getPerson()),
            'bank' => self::ExploreBank($row->getBank()),
            'cashdesk' => self::ExploreCashdesk($row->getCashdesk()),
            'salary' => self::ExploreSalary($row->getSalary()),
            'bs' => $row->getBs(),
            'bd' => $row->getBd(),
            'des' => $row->getDes(),
            'plugin' => $row->getPlugin(),
            'commodity_count' => $row->getCommdityCount(),
            'commodity' => self::ExploreCommodity($row->getCommodity(), $row->getCommdityCount(), $row->getDes()),
        ];
        if (!$row->getTax())
            $row->setTax(0);
        if (!$row->getDiscount())
            $row->setDiscount(0);
        $temp['tax'] = $row->getTax();
        $temp['discount'] = $row->getDiscount();
        return $temp;
    }

    public static function ExploreCommodity(Commodity|null $item, int|null $count = 0, string|null $des = '')
    {
        if ($item) {
            $result = [
                'id' => $item->getId(),
                'code' => $item->getCode(),
                'name' => $item->getName(),
                'des' => $item->getDes(),
                'priceBuy' => $item->getPriceBuy(),
                'priceSell' => $item->getPriceSell(),
                'khadamat' => $item->isKhadamat(),
                'count' => $count,
                'unit' => $item->getUnit()->getName(),
                'withoutTax' => $item->isWithoutTax(),
                'barcodes' => $item->getBarcodes(),
                'commodityCountCheck' => $item->isCommodityCountCheck(),
                'speedAccess' => $item->isSpeedAccess(),
                'orderPoint' => $item->getOrderPoint(),
                'dayLoading' => $item->getDayLoading(),
                'minOrderCount' => $item->getMinOrderCount(),
                'unitData' => [
                    'name' => $item->getUnit()->getName(),
                    'floatNumber' => $item->getUnit()->getFloatNumber(),
                ],
            ];
            if ($des) {
                $result['des'] = $des;
            }
            return $result;
        }

        return null;
    }

    public static function ExploreCommodityPriceListDetail(PriceListDetail|null $item)
    {
        return [
            'id' => $item->getId(),
            'list' => self::ExploreCommodityPriceListOne($item->getList()),
            'priceBuy' => $item->getPriceBuy(),
            'priceSell' => $item->getPriceSell(),
        ];
    }
    public static function ExploreCommodityPriceListDetails($items)
    {
        $result = [];
        foreach ($items as $item)
            $result[] = self::ExploreCommodityPriceListDetail($item);
        return $result;
    }
    public static function ExploreBank(BankAccount|null $item)
    {
        if ($item)
            return [
                'id' => $item->getId(),
                'code' => $item->getCode(),
                'name' => $item->getName(),
            ];
        return null;
    }

    public static function ExploreCashdesk(Cashdesk|null $item)
    {
        if ($item)
            return [
                'id' => $item->getId(),
                'code' => $item->getCode(),
                'name' => $item->getName(),
                'des' => $item->getDes()
            ];
        return null;
    }
    public static function ExploreSalary(Salary|null $item)
    {
        if ($item)
            return [
                'id' => $item->getId(),
                'code' => $item->getCode(),
                'name' => $item->getName(),
                'des' => $item->getDes()
            ];
        return null;
    }
    public static function ExplorePerson(Person|null $person, array $typesAll = [])
    {
        if ($person) {
            $res = [
                'id' => $person->getId(),
                'code' => $person->getCode(),
                'nikename' => $person->getNikename(),
                'name' => $person->getName(),
                'tel' => $person->getTel(),
                'mobile' => $person->getmobile(),
                'mobile2' => $person->getMobile2(),
                'des' => $person->getDes(),
                'company' => $person->getCompany(),
                'shenasemeli' => $person->getShenasemeli(),
                'sabt' => $person->getSabt(),
                'shahr' => $person->getShahr(),
                'keshvar' => $person->getKeshvar(),
                'ostan' => $person->getOstan(),
                'postalcode' => $person->getPostalcode(),
                'codeeghtesadi' => $person->getCodeeghtesadi(),
                'email' => $person->getEmail(),
                'website' => $person->getWebsite(),
                'fax' => $person->getFax(),
                'birthday' => $person->getBirthday(),
                'speedAccess' => $person->isSpeedAccess(),
                'address' => $person->getAddress(),
            ];
            $res['accounts'] = self::ExplorePersonCards($person);
            if (count($typesAll) != 0) {
                $res['types'] = self::ExplorePersonTypes($typesAll);
                foreach ($res['types'] as $key => $item) {
                    foreach ($person->getType() as $type) {
                        if ($item['code'] == $type->getCode())
                            $res['types'][$key]['checked'] = true;
                    }
                }
            }
            return $res;
        }
        return null;
    }
    public static function ExplorePersons($items, $types)
    {
        $result = [];
        foreach ($items as $item)
            $result[] = self::ExplorePerson($item, $types);
        return $result;
    }

    public static function ExplorePersonCards(Person $person)
    {
        $res = [];
        foreach ($person->getPersonCards() as $item) {
            $res[] = [
                'bank' => $item->getBank(),
                'shabaNum' => $item->getShabaNum(),
                'cardNum' => $item->getCardNum(),
                'accountNum' => $item->getAccountNum()
            ];
        }
        return $res;
    }

    public static function ExploreHesabdariTable(HesabdariTable $table)
    {
        return [
            'id' => $table->getId(),
            'upper_id' => $table->getUpper()->getId(),
            'name' => $table->getName(),
            'type' => $table->getType(),
            'code' => $table->getCode()
        ];
    }
    public static function ExploreMoney(Money $money)
    {
        return [
            'id' => $money->getId(),
            'label' => $money->getLabel(),
            'name' => $money->getName(),
        ];
    }
    public static function ExploreYear(Year|null $year)
    {
        $jdate = new Jdate();
        if (!$year) {
            $year = new Year();
            $year->setHead(true);
            $year->setLabel('سال مالی اول');
            $year->setStart(time());
            $year->setEnd(time() + 31536000);
        }
        return [
            'id' => $year->getId(),
            'label' => $year->getLabel(),
            'head' => $year->isHead(),
            'start' => $year->getStart(),
            'end' => $year->getEnd(),
            'now' => time(),
            'startShamsi' => $jdate->jdate('Y/n/d', $year->getStart()),
            'endShamsi' => $jdate->jdate('Y/n/d', $year->getEnd()),
        ];
    }

    public static function ExploreUser(User $user)
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getFullName()
        ];
    }

    public static function ExploreBid(Business $bid)
    {
        return [
            'id' => $bid->getId(),
            'name' => $bid->getName(),
            'legal_name' => $bid->getLegalName()
        ];
    }

    public static function ExploreBuyDocsList(array $items)
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id' => $item->getId(),
                'date' => $item->getDate(),
                'des' => $item->getDes(),
            ];
        }
        return $result;
    }

    public static function SerializeCheque(Cheque|null $cheque)
    {
        if (!$cheque)
            return null;
        $jdate = new Jdate;
        $label = '';
        if ($cheque->getType() == 'input')
            $label = 'چک شماره ' . $cheque->getNumber() . ' مربوط به ' . $cheque->getPerson()->getNikename() . ' با مبلغ ' . $cheque->getAmount();
        return [
            'id' => $cheque->getId(),
            'number' => $cheque->getNumber(),
            'sayadNum' => $cheque->getSayadNum(),
            'chequeBank' => $cheque->getBankOncheque(),
            'person' => self::ExplorePerson($cheque->getPerson()),
            'bank' => self::ExploreBank($cheque->getBank()),
            'des' => $cheque->getDes(),
            'datePay' => $cheque->getPayDate(),
            'type' => $cheque->getType(),
            'amount' => $cheque->getAmount(),
            'status' => $cheque->getStatus(),
            'date' => $cheque->getDate(),
            'locked' => $cheque->isLocked(),
            'rejected' => $cheque->isRejected(),
            'label' => $label
        ];
    }

    public static function SerializeCheques(array $cheques)
    {
        $result = [];
        foreach ($cheques as $cheque)
            $result[] = self::SerializeCheque($cheque);
        return $result;
    }

    public static function ExploreProject(Project $item)
    {
        return [
            'id' => $item->getId(),
            'name' => $item->getName()
        ];
    }

    public static function ExploreProjects(array $items)
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = self::ExploreProject($item);
        }
        return $result;
    }

    public static function ExploreStoreroom(Storeroom $item)
    {
        return [
            'id' => $item->getId(),
            'code' => $item->getId(),
            'name' => $item->getName(),
            'tel' => $item->getTel(),
            'address' => $item->getAdr(),
            'manager' => $item->getManager()
        ];
    }

    public static function ExploreStorerooms(array $items)
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = self::ExploreStoreroom($item);
        }
        return $result;
    }
}
