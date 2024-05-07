<?php


namespace Modules\Payment\Services;

use Graphicode\Standard\TDO\TDO;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
class StripeService
{
    private $SecretKey;
    private $payment_method_types  = ['card'];
    private $currency              = 'usd';
    private $mode                  = 'payment';
    private $success_url           = 'https://example.com/success';
    private $cancel_url            = 'https://example.com/cancel';
    
    public function __construct() {
        $this->SecretKey = config('payment.stripe.secret-key');
    }


    public function sendPayment(TDO $tdo)
    {
        try{
            $items = $this->payload($tdo);
            Stripe::setApiKey($this->SecretKey);
            $session = Session::create([
                'payment_method_types' => $this->payment_method_types,
                'line_items' => $items,
                'mode' => $this->mode,
                'success_url' => $this->success_url,
                'cancel_url' => $this->cancel_url,
            ]);
            
            return $session->url;
        }catch(ApiErrorException  $e){
            return ['error' => $e->getMessage()];
        }
    }

    private function payload($tdo)
    {
        try{
            $items = [
                [
                    'price_data' => [
                        'currency' => $this->currency, 
                        'unit_amount' => $tdo->amount * 100 , 
                        'product_data' => [
                            'name' => $tdo->name, 
                        ],
                    ],
                    'quantity' => $tdo->quantity,
                ]
            ];
        
            return $items;
        }catch(\Throwable $e){
            return $e;
        }

    }
    

}