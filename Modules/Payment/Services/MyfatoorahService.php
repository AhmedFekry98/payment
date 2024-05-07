<?php

namespace Modules\Payment\Services;

use Graphicode\Standard\TDO\TDO;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;

class MyfatoorahService
{

    private $user;
    private $mfConfig          = [];
    private $language          = 'en';
    private $success_url       = 'https://example.com/success';
    private $cancel_url        = 'https://example.com/cancel';

    private $mobileCountryCode = '+965' ;

    public function __construct()
    {
        $this->mfConfig = [
            'apiKey'      => config('payment.myfatoorah.api_key'),
            'isTest'      => config('payment.myfatoorah.test_mode'),
            'countryCode' => config('payment.myfatoorah.country_iso'),
        ];
        $this->user = Auth::user();
    }


    public function sendPayment(TDO $tdo)
    {
        try {
            //For example: pmid=0 for MyFatoorah invoice or pmid=1 for Knet in test mode
            $paymentId = $tdo->pmid ?: 0;
            $sessionId = $tdo->sid  ?: null;

            $curlData = $this->Payload($tdo);

            $mfObj   = new MyFatoorahPayment($this->mfConfig);
            $payment = $mfObj->getInvoiceURL($curlData, $paymentId, $orderId, $sessionId);

            return $payment['invoiceURL'];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }


    private function Payload(TDO $tdo)
    {
        return [
            'CustomerName'       => $this->user->name,
            'InvoiceValue'       => $tdo->total,
            'DisplayCurrencyIso' => $tdo->currency,
            'CustomerEmail'      => $this->user->email,
            'CallBackUrl'        => $this->success_url,
            'ErrorUrl'           => $this->cancel_url,
            'MobileCountryCode'  => $this->mobileCountryCode,
            'CustomerMobile'     => $this->user->phone,
            'Language'           => $this->language,
            'CustomerReference'  => $tdo->orderId,
            // 'SourceInfo'         => 'Laravel ' . app()::VERSION . ' - MyFatoorah Package ' . MYFATOORAH_LARAVEL_PACKAGE_VERSION
        ];
    }

    /**
     * Get MyFatoorah Payment Information
     * Provide the callback method with the paymentId
     * 
     * @return Response
     */
    public function applyPayment(TDO $paymentData)
    {
        try {
            $paymentId = $paymentData->paymentId;

            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data  = $mfObj->getPaymentStatus($paymentId, 'PaymentId');

            $response = $this->checkStatus($data);
        } catch (\Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return false;
        }
    }
    
            private function checkStatus($data): bool
            {
                if ( in_array($data->InvoiceStatus, ['Expired', 'Failed']) ) {
                    return false;
                }

                return true;
            }
}
