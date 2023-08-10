<!DOCTYPE html>
<html>
<head>
  <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
  <script
    type="text/javascript"
    src="https://sandbox.web.squarecdn.com/v1/square.js"
  ></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    const appId = 'sandbox-sq0idb-Ud3vkCKFahMIRX8u__Jdrw';
    const locationId = 'L7T1449HFRAC1';


    async function initializeCard(payments) {
      const card = await payments.card();
      await card.attach('#card-container');

      return card;
    }

    async function createPayment(token, verificationToken) {
      const body = JSON.stringify({
        locationId,
        sourceId: token,
        verificationToken,
        idempotencyKey: window.crypto.randomUUID(),
      });

      const paymentResponse = await fetch('{{ route("payment.process") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body,
      });

      if (paymentResponse.ok) {
        return paymentResponse.json();
      }

      const errorBody = await paymentResponse.text();
      throw new Error(errorBody);
    }

    async function tokenize(paymentMethod) {
      const tokenResult = await paymentMethod.tokenize();
      if (tokenResult.status === 'OK') {
        return tokenResult.token;
      } else {
        let errorMessage = `Tokenization failed with status: ${tokenResult.status}`;
        if (tokenResult.errors) {
          errorMessage += ` and errors: ${JSON.stringify(
            tokenResult.errors
          )}`;
        }

        throw new Error(errorMessage);
      }
    }

    // Required in SCA Mandated Regions: Learn more at https://developer.squareup.com/docs/sca-overview
    async function verifyBuyer(payments, token) {
      const verificationDetails = {
        amount: '1.00',
        billingContact: {
          addressLines: ['123 Main Street', 'Apartment 1'],
          familyName: 'Doe',
          givenName: 'John',
          email: 'jondoe@gmail.com',
          country: 'GB',
          phone: '3214563987',
          region: 'LND',
          city: 'London',
        },
        currencyCode: 'GBP',
        intent: 'CHARGE',
      };

      const verificationResults = await payments.verifyBuyer(
        token,
        verificationDetails
      );
      return verificationResults.token;
    }

    // status is either SUCCESS or FAILURE;
    function displayPaymentResults(status) {
      const statusContainer = document.getElementById('payment-status-container');
      if (status === 'SUCCESS') {
        statusContainer.classList.remove('is-failure');
        statusContainer.classList.add('is-success');
      } else {
        statusContainer.classList.remove('is-success');
        statusContainer.classList.add('is-failure');
      }

      statusContainer.style.visibility = 'visible';
    }

    document.addEventListener('DOMContentLoaded', async function () {
      if (!window.Square) {
        throw new Error('Square.js failed to load properly');
      }

      let payments;
      try {
        payments = window.Square.payments(appId, locationId);
      } catch {
        const statusContainer = document.getElementById('payment-status-container');
        statusContainer.className = 'missing-credentials';
        statusContainer.style.visibility = 'visible';
        return;
      }

      let card;
      try {
        card = await initializeCard(payments);
      } catch (e) {
        console.error('Initializing Card failed', e);
        return;
      }

      async function handlePaymentMethodSubmission(event, card) {
        event.preventDefault();

        try {
          // disable the submit button as we await tokenization and make a payment request.
          cardButton.disabled = true;
          const token = await tokenize(card);
          const verificationToken = await verifyBuyer(payments, token);
          const paymentResults = await createPayment(
            token,
            verificationToken
          );
          displayPaymentResults('SUCCESS');

          console.debug('Payment Success', paymentResults);
        } catch (e) {
          cardButton.disabled = false;
          displayPaymentResults('FAILURE');
          console.error(e.message);
        }
      }

      const cardButton = document.getElementById('card-button');
      cardButton.addEventListener('click', async function (event) {
        await handlePaymentMethodSubmission(event, card);
      });
    });
  </script>
</head>
<body>
  <form id="payment-form">
    <div id="card-container"></div>
    <button id="card-button" type="button">Pay $1.00</button>
  </form>
  <div id="payment-status-container"></div>
</body>
</html>
