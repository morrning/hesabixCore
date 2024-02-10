<?php
namespace App\Service;

use App\Entity\BankAccount;
use App\Entity\User;
use App\Entity\Year;
use App\Entity\Business;
use App\Entity\Cashdesk;
use App\Entity\Commodity;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\Money;
use App\Entity\Person;
use App\Entity\Salary;

class Explore{

    public static function ExploreBuyDoc(HesabdariDoc $hesabdariDoc){
        $result = self::ExploreHesabdariDoc($hesabdariDoc);
        $person = [];
        $commodities = [];
        foreach($hesabdariDoc->getHesabdariRows() as $item){
            if($item->getPerson()){
                $person = self::ExplorePerson($item->getPerson());
            }
            elseif($item->getCommodity()){
                $commodities[] = Explore::ExploreCommodity($item->getCommodity(),$item->getCommdityCount());
            }
        }
        $result['person'] = $person;
        return $result;
    }
    public static function ExploreHesabdariDoc(HesabdariDoc $doc){
        return [
            'id'            => $doc->getId(),
            'update'        => $doc->getCode(),
            'rows'          => self::ExploreHesabdariRows($doc->getHesabdariRows()),
            'submiter'      => self::ExploreUser($doc->getSubmitter()),
            'year'          => self::ExploreYear($doc->getYear()),
            'money'         => self::ExploreMoney($doc->getMoney()),
            'date_submit'   => $doc->getDateSubmit(),
            'date'          => $doc->getDate(),
            'type'          => $doc->getType(),
            'code'          => $doc->getCode(),
            'des'           => $doc->getDes(),
            'amount'        => $doc->getAmount(),
            'mdate'         => '',
            'plugin'        => $doc->getPlugin()
        ];
    }

    public static function ExploreHesabdariRows($rows){
        $result = [];
        foreach($rows as $row){
            $result[] = self::ExploreHesabdariRow($row);
        }
        return $result;
    }
    public static function ExploreHesabdariRow(HesabdariRow $row){
        return [
            'id'                => $row->getId(),
            'bid'               => self::ExploreBid($row->getBid()),
            'year'              => self::ExploreYear($row->getYear()),
            'ref'               => self::ExploreHesabdariTable($row->getRef()),
            'person'            => self::ExplorePerson($row->getPerson()),
            'bank'              => self::ExploreBank($row->getBank()),
            'cashdesk'          => self::ExploreCashdesk($row->getCashdesk()),
            'salary'            => self::ExploreSalary($row->getSalary()),
            'bs'                => $row->getBs(),
            'bd'                => $row->getBd(),
            'des'               => $row->getDes(),
            'plugin'            => $row->getPlugin(),
            'commodity_count'   => $row->getCommdityCount(),
            'commodity'         => self::ExploreCommodity($row->getCommodity())
        ];
    }

    public static function ExploreCommodity(Commodity | null $item, int $count = 0){
        if($item)
            return [
                'id'            => $item->getId(),
                'code'          => $item->getCode(),
                'name'          => $item->getName(),
                'des'           => $item->getDes(),
                'price_buy'     => $item->getPriceBuy(),
                'price_sell'    => $item->getPriceSell(),
                'khadamat'      => $item->isKhadamat(),
                'speed_access'  => $item->isSpeedAccess(),
                //most be completed
                'count'         => $count
            ];
        return null;
    }

    public static function ExploreBank(BankAccount | null $item){
        if($item)
            return [
                'id'         => $item->getId(),
                'code'       => $item->getCode(),
                'name'       => $item->getName(),
                //most be completed
            ];
        return null;
    }

    public static function ExploreCashdesk(Cashdesk | null $item){
        if($item)
            return [
                'id'         => $item->getId(),
                'code'       => $item->getCode(),
                'name'       => $item->getName(),
                'des'        => $item->getDes()
            ];
        return null;
    }
    public static function ExploreSalary(Salary | null $item){
        if($item)
            return [
                'id'         => $item->getId(),
                'code'       => $item->getCode(),
                'name'       => $item->getName(),
                'des'        => $item->getDes()
            ];
        return null;
    }
    public static function ExplorePerson(Person | null $person){
        if($person)
            return [
                'id'         => $person->getId(),
                'code'       => $person->getCode(),
                'nikename'  => $person->getNikename(),
                'name'       => $person->getName(),
                //most be completed
            ];
        return null;
    }
    public static function ExploreHesabdariTable(HesabdariTable $table){
        return [
            'id'        => $table->getId(),
            'upper_id'  => $table->getUpper()->getId(),
            'name'      => $table->getName(),
            'type'      => $table->getType(),
            'code'      => $table->getCode()
        ];
    }
    public static function ExploreMoney(Money $money){
        return [
            'id'     => $money->getId(),
            'label'  => $money->getLabel(),
            'name'   => $money->getName(),
        ];
    }
    public static function ExploreYear(Year $year){
        return [
            'id'    => $year->getId(),
            'label' => $year->getLabel(),
            'head'  => $year->isHead(),
            'start' => $year->getStart(),
            'end'   => $year->getEnd(),
            'now'   => time()
        ];
    }

    public static function ExploreUser(User $user){
        return [
            'id'    => $user->getId(),
            'name'  => $user->getFullName()
        ];
    }

    public static function ExploreBid(Business $bid){
        return [
            'id'    => $bid->getId(),
            'name'  => $bid->getName(),
            'legal_name' => $bid->getLegalName()
        ];
    }

}