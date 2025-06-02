<?php

namespace App\Service;

use App\Entity\APIToken;
use App\Entity\Business;
use App\Entity\Money;
use App\Entity\Permission;
use App\Entity\UserToken;
use App\Entity\Year;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PayMGR
{

    protected Business|string $bid;

    function __construct(
        private EntityManagerInterface $entityManager,
        private registryMGR $registry
    ) {
    }

    /**
     * function to generate random strings
     * @param 		int 	$length 	number of characters in the generated string
     * @return 		string	a new string is created with random characters of the desired length
     */
    private function RandomString($length = 32)
    {
        return substr(str_shuffle(str_repeat($x = '123456789', ceil($length / strlen($x)))), 1, $length);
    }

    public function createRequest($price, $callback_url, $des = '', $orderID = 0): array
    {
        $res = [
            'Success' => false,
        ];
        if ($orderID == 0)
            $orderID = $this->RandomString(15);
        $activeGateway = $this->registry->get('system', 'activeGateway');
        if ($activeGateway == 'zarinpal') {
            $data = array(
                "merchant_id" => $this->registry->get('system', 'zarinpalKey'),
                "amount" => $price,
                "callback_url" => $callback_url,
                "description" => $des,
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

            } else {
                if (empty($result['errors'])) {
                    if ($result['data']['code'] == 100) {
                        $res['code'] = 100;
                        $res['Success'] = true;
                        $res['gate'] = 'zarinpal';
                        $res['message'] = $result['data']['message'];
                        $res['authkey'] = $result['data']['authority'];
                        $res['targetURL'] = 'https://www.zarinpal.com/pg/StartPay/' . $result['data']['authority'];
                    }
                }
            }
        } elseif ($activeGateway == 'payping') {
            $data = array(
                'amount' => $price,
                'returnUrl' => $callback_url,
                'description' => $des,
                'clientRefId' => $orderID
            );

            $ch = curl_init('https://api.payping.ir/v2/pay');
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 45,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    "accept: application/json",
                    "authorization: Bearer " . $this->registry->get('system', 'paypingKey'),
                    "cache-control: no-cache",
                    "content-type: application/json",
                ),
            ));

            $response = curl_exec($ch);
            $err = curl_error($ch);
            $header = curl_getinfo($ch);
            curl_close($ch);

            if ($err) {
                $res['message'] = 'خطا در ارتباط با پی‌پینگ: ' . $err;
                return $res;
            }

            if ($header['http_code'] == 200) {
                $response = json_decode($response, true);
                if (isset($response['code'])) {
                    $res['code'] = 100;
                    $res['Success'] = true;
                    $res['gate'] = 'payping';
                    $res['message'] = 'OK';
                    $res['authkey'] = $response['code'];
                    $res['targetURL'] = 'https://api.payping.ir/v2/pay/gotoipg/' . $response['code'];
                } else {
                    $res['message'] = 'خطا در دریافت کد پرداخت از پی‌پینگ';
                }
            } elseif ($header['http_code'] == 400) {
                $res['message'] = 'خطا در درخواست پرداخت: ' . $response;
            } else {
                $res['message'] = 'خطا در ارتباط با پی‌پینگ. کد خطا: ' . $header['http_code'];
            }
        } elseif ($activeGateway == 'pec') {
            ini_set("soap.wsdl_cache_enabled", "0");
            $url = "https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?WSDL";
            $params = array(
                "LoginAccount" => $this->registry->get('system', 'parsianGatewayAPI'),
                "Amount" => $price,
                "OrderId" => $orderID,
                "CallBackUrl" => $callback_url,
                "AdditionalData" => '',
                "Originator" => ''
            );
            $client = new \SoapClient($url);

            $result = $client->SalePaymentRequest(array(
                "requestData" => $params
            ));
            if ($result->SalePaymentRequestResult->Token && $result->SalePaymentRequestResult->Status === 0) {
                $res['code'] = 100;
                $res['Success'] = true;
                $res['gate'] = 'pec';
                $res['message'] = 'OK';
                $res['authkey'] = $result->SalePaymentRequestResult->Token;
                $res['targetURL'] = 'https://pec.shaparak.ir/NewIPG/?Token=' . $result->SalePaymentRequestResult->Token;
            }
            try {
            } catch (\Exception $ex) {

            }
        } elseif ($activeGateway == 'bitpay') {
            $url = 'https://bitpay.ir/payment/gateway-send';
            $api = $this->registry->get('system', 'bitpayKey');
            $amount = $price;
            $redirect = $callback_url;
            $factorId = $orderID;
            $name = '';
            $email = '';
            $description = $des;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "api=$api&amount=$amount&redirect=$redirect&factorId=$factorId&name=$name&email=$email&description=$description");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            if ($result > 0 && is_numeric($result)) {
                $res['code'] = 100;
                $res['Success'] = true;
                $res['gate'] = 'bitpay';
                $res['message'] = 'OK';
                $res['authkey'] = $result;
                $res['targetURL'] = "https://bitpay.ir/payment/gateway-$result-get";
            }
            else{
                var_dump($result);
            }
        }
        return $res;
    }


    public function verify($price, $token, Request $request): array
    {
        $res = [
            'Success' => false
        ];
        $activeGateway = $this->registry->get('system', 'activeGateway');
        if ($activeGateway == 'zarinpal') {
            $Authority = $request->get('Authority');
            $data = array("merchant_id" => $this->registry->get('system', 'zarinpalKey'), "authority" => $Authority, "amount" => $price);
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

            if ($err) {
                return $res;
            } else {
                if (array_key_exists('code', $result['data'])) {
                    if ($result['data']['code'] == 100) {
                        $res['Success'] = true;
                        $res['status'] = 100;
                        $res['refID'] = $result['data']['ref_id'];
                        $res['card_pan'] = $result['data']['card_pan'];
                        return $res;
                    }
                }
            }
        } elseif ($activeGateway == 'payping') {
            $refid = $request->get('refid');
            if (!$refid) {
                return $res;
            }

            $data = array(
                'amount' => $price,
                'refId' => $refid
            );

            $ch = curl_init('https://api.payping.ir/v2/pay/verify');
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 45,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    "accept: application/json",
                    "authorization: Bearer " . $this->registry->get('system', 'paypingKey'),
                    "cache-control: no-cache",
                    "content-type: application/json",
                ),
            ));

            $response = curl_exec($ch);
            $err = curl_error($ch);
            $header = curl_getinfo($ch);
            curl_close($ch);

            if ($err) {
                return $res;
            }

            if ($header['http_code'] == 200) {
                $response = json_decode($response, true);
                if (isset($refid) && $refid != '') {
                    $res['Success'] = true;
                    $res['status'] = 100;
                    $res['refID'] = $refid;
                    $res['card_pan'] = ''; // PayPing این اطلاعات را برنمی‌گرداند
                    return $res;
                }
            }
        } elseif ($activeGateway == 'pec') {
            $confirmUrl = 'https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?WSDL';
            $params = array(
                "LoginAccount" => $this->registry->get('system', 'parsianGatewayAPI'),
                "Token" => $token
            );

            $client = new \SoapClient($confirmUrl);

            $result = $client->ConfirmPayment(array(
                "requestData" => $params
            ));
            if ($result->ConfirmPaymentResult->Status == '0') {
                $res['Success'] = true;
                $res['status'] = 100;
                $res['refID'] = $result->ConfirmPaymentResult->RRN;
                $res['card_pan'] = $result->ConfirmPaymentResult->CardNumberMasked;
            }
        } elseif ($activeGateway == 'bitpay') {
            $url = 'https://bitpay.ir/payment/gateway-result-second';
            $api = $this->registry->get('system', 'bitpayKey');
            $trans_id = $request->get('trans_id');
            $id_get = $request->get('id_get');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "api=$api&id_get=$id_get&trans_id=$trans_id&json=1");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);

            $parseDecode = json_decode($result);
            if ($parseDecode->status == 1) {
                $res['Success'] = true;
                $res['status'] = 100;
                $res['refID'] = $trans_id;
                $res['card_pan'] = $parseDecode->cardNum;
            }
        }
        return $res;
    }
}