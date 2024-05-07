<?php

namespace Modules\Payment\Services;

use Graphicode\Standard\TDO\TDO;
use Illuminate\Contracts\Auth\Authenticatable;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;

class MyfatoorahService
{

    /**
     * @var array
     */
    public $mfConfig = [];

    /**
     * Initiate MyFatoorah Configuration
     */
    public function __construct()
    {
        $this->mfConfig = [
            'apiKey'      => config('myfatoorah.api_key'),
            'isTest'      => config('myfatoorah.test_mode'),
            'countryCode' => config('myfatoorah.country_iso'),
        ];
    }


    public function sendPayment(Authenticatable $user, TDO $paymentData)
    {
        try {
            //For example: pmid=0 for MyFatoorah invoice or pmid=1 for Knet in test mode
            $paymentId = $paymentData->pmid ?: 0;
            $sessionId = $paymentData->sid  ?: null;

            $curlData = $this->getPayLoadData($user, $paymentData);

            $mfObj   = new MyFatoorahPayment($this->mfConfig);
            $payment = $mfObj->getInvoiceURL($curlData, $paymentId, $orderId, $sessionId);

            return $payment['invoiceURL'];
        } catch (\Exception $ex) {
            return null;
        }
    }


    private function getPayLoadData(Authenticatable$user, TDO $paymentData)
    {
        $callbackURL = route('myfatoorah.callback');


        return [
            'CustomerName'       => $user->extra->name,
            'InvoiceValue'       => $paymentData->total,
            'DisplayCurrencyIso' => $paymentData->currency,
            'CustomerEmail'      => $user->extra->email,
            'CallBackUrl'        => $callbackURL,
            'ErrorUrl'           => $callbackURL,
            // 'MobileCountryCode'  => '+965',
            // 'CustomerMobile'     => $user->extra->phone,
            'Language'           => 'en',
            'CustomerReference'  => $paymentData->orderId,
            'SourceInfo'         => 'Laravel ' . app()::VERSION . ' - MyFatoorah Package ' . MYFATOORAH_LARAVEL_PACKAGE_VERSION
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
