<?php

namespace App\Http\Controllers;

use App\Models\Order_Transaction;
use Illuminate\Http\Request;
use Square\Models\Builders\AddressBuilder;
use Square\Models\Builders\ChargeRequestAdditionalRecipientBuilder;
use Square\Models\Builders\CreateCheckoutRequestBuilder;
use Square\Models\Builders\MoneyBuilder;
use Square\Models\Country;
use Square\Models\Currency;
use Square\SquareClientBuilder;
use Square\Environment;
use Square\Models\Builders\CreateOrderRequestBuilder;
use Square\Models\Builders\CreatePaymentRequestBuilder;
use Square\Models\Builders\OrderBuilder;

class PaymentController extends Controller
{
    public function showPaymentForm()
    {
        return view('payment_form');
    }

    public function processPayment(Request $request)
    {
        $appId = 'sandbox-sq0idb-Ud3vkCKFahMIRX8u__Jdrw';
        $locationId = 'L7T1449HFRAC1';
        $accessToken = 'EAAAECwPlwKSoryPJBPR_7N2ippN_6Jhf5dbqKAPw9fWJUqFIe813bKLhWXbriNp';

        $client = SquareClientBuilder::init()
            ->accessToken($accessToken)
            ->environment(Environment::SANDBOX)
            ->build();

        $paymentsApi = $client->getPaymentsApi();

        $createPaymentRequest = CreatePaymentRequestBuilder::init(
            $request->input('sourceId'),
            $request->input('idempotencyKey')
        )
        ->amountMoney(
            MoneyBuilder::init()
                ->amount(10000)
                ->currency(Currency::USD)
                ->build()
        )
        ->build();

        try {
            $response = $paymentsApi->createPayment($createPaymentRequest);

            return response()->json($response->getResult());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




}
