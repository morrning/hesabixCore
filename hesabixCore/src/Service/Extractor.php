<?php

namespace App\Service;

use App\Entity\BankAccount;
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
use App\Entity\Salary;

class Extractor
{
    public function operationSuccess($data = '', $message = '')
    {
        if ($message == '') {
            return [
                'Success' => true,
                'code' => 0,
                'data' => $data,
                'message' => 'operation success',

            ];
        }
        return [
            'Success' => true,
            'code' => 0,
            'data' => $data,
            'message' => $message,
        ];

    }
    public function operationFail($message = 'operaition fail', $code = 404, $data = '')
    {
        return [
            'Success' => false,
            'code' => $code,
            'data' => $data,
            'message' => $message,
        ];
    }
    public function notFound($data = '')
    {
        return [
            'code' => 404,
            'data' => $data,
            'message' => 'item not found'
        ];
    }

    public function paramsNotSend()
    {
        return [
            'code' => 101,
            'data' => '',
            'message' => 'parameters not send currectly'
        ];
    }
}
