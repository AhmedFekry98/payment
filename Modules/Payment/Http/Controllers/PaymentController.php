<?php

namespace Modules\Payment\Http\Controllers;

use Graphicode\Standard\Facades\TDOFacade;
use Graphicode\Standard\Traits\ApiResponses;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payment\Http\Requests\SendPaymentRequest;
use Modules\Payment\Services\StripeService;

class PaymentController extends Controller
{
    use ApiResponses;

    private static $paymentType;

    public function __construct()
    {
        self::$paymentType = new StripeService();
    }



    public function sendPayment(SendPaymentRequest $request)
    {
        $payment = self::$paymentType->sendPayment(TDOFacade::make($request));
       
        if (isset($payment['error'])) {
            return $this->badResponse(message:'invaled payment');
        }
     
        
        return $this->okResponse($payment,message:'success payment');
        
    }
}
