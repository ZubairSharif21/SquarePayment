<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\CommonMark\Environment\Environment;
use Square\Models\Builders\CreatePaymentRequestBuilder;
use Square\Models\Builders\MoneyBuilder;
use Square\Models\Currency;
use Square\SquareClientBuilder;

class GoogleController extends Controller
{
    public function processGooglePay(Request $request)
    {
        try {
            $paymentToken = $request->input('paymentToken');

            $accessToken = 'EAAAECwPlwKSoryPJBPR_7N2ippN_6Jhf5dbqKAPw9fWJUqFIe813bKLhWXbriNp'; // Replace with your Square access token

            $client = SquareClientBuilder::init()
                ->accessToken($accessToken)
                ->environment('sandbox') // Use 'sandbox' as the environment configuration
                ->build();
            $paymentsApi = $client->getPaymentsApi();

            $createPaymentRequest = CreatePaymentRequestBuilder::init(
                $paymentToken,
                $request->input('idempotencyKey')
            )
            ->amountMoney(
                MoneyBuilder::init()
                    ->amount(5000) // Amount in cents (50.00 USD)
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

        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment Error'], 500);
        }
    }
}
