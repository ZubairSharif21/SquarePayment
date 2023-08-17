<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" preload>
    <script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
</head>
<body>
    <div id="payment-form" style="padding: 13px 35px !important;">
        <div id="payment-status-container"></div>
        <div id="google-pay-button"></div>
    </div>
    <script type="module">

                // Initialize the Square Payments SDK
                const payments = Square.payments('sandbox-sq0idb-Ud3vkCKFahMIRX8u__Jdrw', 'L7T1449HFRAC1');

                const paymentRequest = payments.paymentRequest({
                    countryCode: 'GB',
                    currencyCode: 'GBP',
                    total: {
          amount: (22 * 1.27).toFixed(2), // Convert to cents
                        label: 'Total',
                    },
                });

                payments.googlePay(paymentRequest).then(async googlePay => {
                    await googlePay.attach('#google-pay-button');

                    const googlePayButton = document.getElementById('google-pay-button');
                    googlePayButton.addEventListener('click', async () => {
                        const statusContainer = document.getElementById('payment-status-container');

                        try {
                            const tokenResult = await googlePay.tokenize();
                            if (tokenResult.status === 'OK') {
                                console.log(`Payment token is ${tokenResult.token}`);
                                statusContainer.innerHTML = "Payment Successful";

                                // Send the token to the server
                                const response = await fetch('{{ route('process-google-pay') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        paymentToken: tokenResult.token,
                                        idempotencyKey: window.crypto.randomUUID(), // Generate a unique idempotency key
                                        Price: (22 * 1.27).toFixed(2),
                                        }),
                                });

                                const responseData = await response.json();

                                if (response.ok) {
                                    statusContainer.innerHTML = responseData.message;
                                } else {
                                    statusContainer.innerHTML = "Payment Failed";
                                }
                            } else {
                                let errorMessage = `Tokenization failed with status: ${tokenResult.status}`;
                                if (tokenResult.errors) {
                                    errorMessage += ` and errors: ${JSON.stringify(tokenResult.errors)}`;
                                }

                                throw new Error(errorMessage);
                            }
                        } catch (e) {
                            console.error(e.message);
                            statusContainer.innerHTML = "Payment Failed";
                        }
                    });
                });
            </script>
</body>
</html>
