<?php

/**
 * Chain2Pay payment gateway for Paymenter.
 *
 * Clients pay their invoice by card through an on-ramp provider on the Chain2Pay checkout,
 * and you receive the payment in crypto in your own wallet.
 *
 * @see https://chain2pay.is
 * @see https://docs.chain2pay.is
 *
 * @license GPL-3.0-or-later
 */

namespace Paymenter\Extensions\Gateways\Chain2Pay;

use App\Attributes\ExtensionMeta;
use App\Classes\Extension\Gateway;
use App\Helpers\ExtensionHelper;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

#[ExtensionMeta(
    name: 'Chain2Pay',
    description: 'Card payments through Chain2Pay: clients pay by card through an on-ramp provider and you receive the payment in crypto in your own wallet.',
    version: '1.0.0',
    author: 'Chain2Pay',
    url: 'https://chain2pay.is',
    icon: 'data:image/png;base64,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'
)]
class Chain2Pay extends Gateway
{
    public const MODULE_VERSION = '1.0.0';
    private const NAME = 'Chain2Pay';
    private const CALLBACK_PATH = '/extensions/gateways/chain2pay/callback';
    private const CALLBACK_ROUTE = 'extensions.gateways.chain2pay.callback';

    public function boot()
    {
        Route::get(self::CALLBACK_PATH, [self::class, 'callback'])->name(self::CALLBACK_ROUTE);
    }

    public function getConfig($values = [])
    {
        return [
            [
                'name' => 'api_key',
                'label' => 'Chain2Pay API key',
                'type' => 'text',
                'encrypted' => true,
                'description' => 'Your key from the Chain2Pay dashboard (c2p_live_... or, for tests, c2p_sandbox_...).',
                'required' => true,
            ],
            [
                'name' => 'provider',
                'label' => 'Provider',
                'type' => 'select',
                'description' => 'Where clients are sent to pay. The hosted checkout lets the client choose among every provider available for their country and amount. To offer several providers, create one gateway per provider.',
                'options' => Chain2PayPaymenterCore::providerChoices(),
                'default' => 'multihosted',
                'required' => true,
            ],
            [
                'name' => 'custom_provider',
                'label' => 'Custom provider slug (optional)',
                'type' => 'text',
                'description' => 'Only used when Provider is set to "Custom provider": the Chain2Pay provider slug, in lowercase.',
                'required' => false,
            ],
        ];
    }

    /**
     * Invoice payment: creates the Chain2Pay payment and returns the checkout URL the client is sent to.
     */
    public function pay(Invoice $invoice, $total)
    {
        $apiKey = trim((string) $this->config('api_key'));
        $provider = trim((string) $this->config('provider'));
        if ($provider === '') {
            $provider = 'multihosted';
        }
        $slug = Chain2PayPaymenterCore::providerSlug($provider, (string) $this->config('custom_provider'));
        $total = (float) $total;
        $currency = strtoupper((string) $invoice->currency_code);
        if ($apiKey === '' || $slug === '' || $total <= 0) {
            throw new \App\Exceptions\DisplayException(__('This payment method is not available right now.'));
        }
        $problem = Chain2PayPaymenterCore::precheck($provider, $total, $currency);
        if ($problem !== '') {
            throw new \App\Exceptions\DisplayException($problem);
        }

        $invoiceId = (int) $invoice->id;
        $ref = Chain2PayPaymenterCore::orderRef('paymenter', $invoiceId, $total, $currency);
        $callback = url(self::CALLBACK_PATH)
            . '?invoice=' . $invoiceId
            . '&amt=' . rawurlencode(Chain2PayPaymenterCore::amount($total))
            . '&cur=' . rawurlencode($currency)
            . '&c2p_token=' . Chain2PayPaymenterCore::token(self::secret(), $ref);

        $created = Chain2PayPaymenterCore::createPayment(
            $apiKey,
            $total,
            $currency,
            $slug,
            (string) ($invoice->user->email ?? ''),
            $callback,
            ['order_ref' => $ref, 'platform' => 'paymenter', 'order_id' => (string) $invoiceId],
            'Paymenter/' . self::MODULE_VERSION
        );
        if (empty($created['success'])) {
            Log::warning(self::NAME . ': payment for invoice #' . $invoiceId . ' could not be started. ' . $created['error']);

            throw new \App\Exceptions\DisplayException(__('Payment could not be started. Please try again in a moment or choose another payment method.'));
        }

        Log::info(self::NAME . ': payment ' . $created['payment_id'] . ' created for invoice #' . $invoiceId . ' (provider ' . $slug . ').');

        return $created['checkout_url'];
    }

    /**
     * Chain2Pay's callback. The signed URL identifies the invoice and the amount; the Chain2Pay API
     * then confirms that the payment is paid before the payment is added to the invoice.
     */
    public function callback(Request $request)
    {
        $invoiceId = (int) $request->query('invoice', 0);
        $amount = (string) $request->query('amt', '');
        $currency = strtoupper((string) $request->query('cur', ''));
        $paymentId = trim((string) $request->query('chain2pay_order_id', ''));

        if ($invoiceId < 1 || !is_numeric($amount) || $currency === '') {
            return self::answer('bad request', 400);
        }
        $ref = Chain2PayPaymenterCore::orderRef('paymenter', $invoiceId, $amount, $currency);
        if (!Chain2PayPaymenterCore::checkToken(self::secret(), $ref, (string) $request->query('c2p_token', ''))) {
            return self::answer('invalid token', 403);
        }

        $check = Chain2PayPaymenterCore::verifyPaid((string) $this->config('api_key'), $paymentId, $ref, (float) $amount, $currency, '', 'Paymenter/' . self::MODULE_VERSION);
        if ($check['result'] === Chain2PayPaymenterCore::UNAVAILABLE) {
            Log::warning(self::NAME . ': callback for invoice #' . $invoiceId . ' could not be verified yet: ' . $check['reason']);

            return self::answer('verification unavailable, retry later', 503);
        }
        if ($check['result'] === Chain2PayPaymenterCore::NOT_PAID) {
            return self::answer('payment not paid', 409);
        }
        if ($check['result'] === Chain2PayPaymenterCore::MISMATCH) {
            Log::warning(self::NAME . ': callback for invoice #' . $invoiceId . ' ignored because ' . $check['reason']);

            return self::answer('payment does not match this invoice', 400);
        }
        $payment = $check['payment'];
        $txid = Chain2PayPaymenterCore::transactionId($payment, $paymentId);
        $summary = Chain2PayPaymenterCore::describe($payment);

        return DB::transaction(function () use ($invoiceId, $amount, $currency, $txid, $summary) {
            $invoice = Invoice::whereKey($invoiceId)->lockForUpdate()->first();
            if (!$invoice) {
                return self::answer('unknown invoice', 404);
            }
            if ($invoice->transactions()->where('transaction_id', $txid)->exists()) {
                return self::answer('*ok*', 200); // repeated callback, already recorded
            }
            if (strtoupper((string) $invoice->currency_code) !== $currency) {
                Log::warning(self::NAME . ': invoice #' . $invoiceId . ' is no longer in ' . $currency . ', payment not applied. ' . $summary);

                return self::answer('*ok*', 200);
            }
            if ((string) $invoice->status !== 'pending' || (float) $invoice->remaining <= 0) {
                Log::warning(self::NAME . ': invoice #' . $invoiceId . ' is ' . (string) $invoice->status . ', so the payment was not applied; refund the client or credit their account. ' . $summary);

                return self::answer('*ok*', 200);
            }
            // The amount that was signed and paid is what gets recorded, never more than what is still due.
            $paid = min((float) $amount, (float) $invoice->remaining);
            ExtensionHelper::addPayment($invoice->id, self::NAME, $paid, transactionId: $txid);

            return self::answer('*ok*', 200);
        });
    }

    /** Secret that signs callback URLs: the application key of this Paymenter installation. */
    private static function secret(): string
    {
        $key = (string) config('app.key');
        if ($key === '') {
            throw new \RuntimeException('APP_KEY is not set.');
        }

        return hash('sha256', 'chain2pay|' . self::class . '|' . $key);
    }

    private static function answer(string $text, int $status)
    {
        return response($text, $status)->header('Content-Type', 'text/plain');
    }
}

/**
 * Chain2Pay API client shared by the Chain2Pay plugins.
 *
 * Plain PHP (7.4+), no framework dependency. It creates a payment through the
 * Chain2Pay API, signs the callback URL of an order and, when the callback
 * arrives, asks the Chain2Pay API whether that payment is really paid before
 * the shop marks anything as paid.
 *
 * API documentation: https://docs.chain2pay.is
 *
 * @license GPL-3.0-or-later
 */
final class Chain2PayPaymenterCore
{
    const VERSION = '1.0.0';
    const API_BASE = 'https://chain2pay.is';

    /** Results of verifyPaid(). */
    const PAID = 'paid';
    const NOT_PAID = 'not_paid';
    const MISMATCH = 'mismatch';
    const UNAVAILABLE = 'unavailable';

    /**
     * Providers available through the Chain2Pay plugins.
     *
     * id => label shown to the buyer, description shown to the buyer, minimum amount
     * (checked here for USD and EUR orders, the API checks every currency) and, when
     * the provider only takes some currencies, that list.
     */
    public static function providers()
    {
        return array(
            'multihosted'  => array('label' => 'Card Payment', 'description' => 'Pay with Visa, Mastercard, Apple Pay and more via Chain2Pay', 'min' => 6, 'currencies' => null, 'admin' => 'Hosted checkout: all providers (recommended)'),
            'stripe'       => array('label' => 'Card (Stripe)', 'description' => 'Pay with credit or debit card via Stripe', 'min' => 3, 'currencies' => array('USD', 'EUR'), 'admin' => 'Stripe'),
            'moonpay'      => array('label' => 'Pay with MoonPay', 'description' => 'Card payment via MoonPay', 'min' => 20, 'currencies' => null, 'admin' => 'MoonPay'),
            'revolut'      => array('label' => 'Pay with Revolut', 'description' => 'Pay securely with Revolut', 'min' => 6, 'currencies' => null, 'admin' => 'Revolut'),
            'coinbase'     => array('label' => 'Pay with Coinbase', 'description' => 'Card payment via Coinbase', 'min' => 10, 'currencies' => null, 'admin' => 'Coinbase'),
            'paypal'       => array('label' => 'Pay with PayPal', 'description' => 'Pay securely with PayPal', 'min' => 5, 'currencies' => null, 'admin' => 'PayPal'),
            'banxa'        => array('label' => 'Pay with Banxa', 'description' => 'Card payment via Banxa', 'min' => 17, 'currencies' => null, 'admin' => 'Banxa'),
            'ramp'         => array('label' => 'Pay with Ramp', 'description' => 'Pay with card or bank via Ramp Network', 'min' => 7, 'currencies' => null, 'admin' => 'Ramp Network'),
            'robinhood'    => array('label' => 'Pay with Robinhood', 'description' => 'Debit card payment via Robinhood (US only)', 'min' => 5, 'currencies' => null, 'admin' => 'Robinhood (US only)'),
            'skrill'       => array('label' => 'Pay with Skrill', 'description' => 'Pay with your Skrill wallet', 'min' => 22, 'currencies' => null, 'admin' => 'Skrill'),
            'alchemy'      => array('label' => 'Pay with Alchemy Pay', 'description' => 'Card payment via Alchemy Pay', 'min' => 17, 'currencies' => null, 'admin' => 'Alchemy Pay'),
            'alchemylocal' => array('label' => 'Local payment methods', 'description' => 'Pay with local payment methods via Alchemy Pay (Local)', 'min' => 15, 'currencies' => null, 'admin' => 'Alchemy Pay (local methods)'),
            'blockchain'   => array('label' => 'Pay with Blockchain.com', 'description' => 'Pay via Blockchain.com', 'min' => 15, 'currencies' => null, 'admin' => 'Blockchain.com'),
            'btcdirect'    => array('label' => 'Pay with BTC Direct', 'description' => 'Pay with card or Apple Pay via BTC Direct', 'min' => 33, 'currencies' => null, 'admin' => 'BTC Direct'),
            'coindisco'    => array('label' => 'Pay by card', 'description' => 'Pay by card via Coindisco', 'min' => 23, 'currencies' => null, 'admin' => 'Coindisco'),
            'stixramp'     => array('label' => 'Pay with Cash App', 'description' => 'Pay with Cash App (US only)', 'min' => 20, 'currencies' => null, 'admin' => 'Cash App (US only)'),
            'crypto'       => array('label' => 'Pay with Crypto', 'description' => 'Pay with crypto (USDC, USDT, ETH, BNB and more)', 'min' => 6, 'currencies' => null, 'admin' => 'Crypto'),
            'custom'       => array('label' => 'Pay by Card (Chain2Pay)', 'description' => 'Pay securely with your card', 'min' => 5, 'currencies' => null, 'admin' => 'Custom provider (uses the provider slug below)'),
        );
    }

    /** id => name for an admin drop-down or a list of checkboxes. */
    public static function providerChoices()
    {
        $choices = array();
        foreach (self::providers() as $id => $provider) {
            $choices[$id] = $provider['admin'];
        }

        return $choices;
    }

    public static function isProvider($id)
    {
        $providers = self::providers();

        return is_string($id) && isset($providers[$id]);
    }

    /**
     * The provider slug sent to the API: the id itself, or the merchant's slug for "custom".
     * Empty string when nothing valid is configured.
     */
    public static function providerSlug($id, $customSlug = '')
    {
        if (!self::isProvider($id)) {
            return '';
        }
        if ($id !== 'custom') {
            return $id;
        }
        $slug = strtolower(trim((string) $customSlug));

        return preg_match('/^[a-z0-9][a-z0-9_-]{0,39}$/', $slug) ? $slug : '';
    }

    /**
     * Checks done before calling the API. Returns an empty string when the order can go
     * ahead, or a message that can be shown to the buyer.
     */
    public static function precheck($id, $amount, $currency)
    {
        if (!self::isProvider($id)) {
            return 'This payment method is not available.';
        }
        $providers = self::providers();
        $provider = $providers[$id];
        $currency = strtoupper(trim((string) $currency));
        $amount = (float) $amount;
        if ($amount <= 0) {
            return 'There is nothing to pay on this order.';
        }
        if (is_array($provider['currencies']) && !in_array($currency, $provider['currencies'], true)) {
            return 'This payment method accepts ' . implode(' and ', $provider['currencies']) . ' only. This order is in ' . $currency . '.';
        }
        if (($currency === 'USD' || $currency === 'EUR') && $amount < (float) $provider['min']) {
            return 'The minimum amount for this payment method is ' . $provider['min'] . ' ' . $currency . '. This order is ' . self::amount($amount) . ' ' . $currency . '.';
        }

        return '';
    }

    public static function isSandboxKey($apiKey)
    {
        return strpos(trim((string) $apiKey), 'c2p_sandbox_') === 0;
    }

    /** Amount as the plain decimal string used in references and tokens: 12.5 => "12.50". */
    public static function amount($value)
    {
        return number_format(round((float) $value, 2), 2, '.', '');
    }

    /**
     * Reference of one order of one shop at one amount. It is stored in the payment
     * (metadata.order_ref) and signed in the callback URL.
     */
    public static function orderRef($platform, $orderId, $amount, $currency)
    {
        return strtolower((string) $platform) . ':' . (string) $orderId . ':' . self::amount($amount) . ':' . strtoupper(trim((string) $currency));
    }

    /** Signature of an order reference with the shop's own secret. */
    public static function token($secret, $orderRef)
    {
        return hash_hmac('sha256', 'chain2pay|' . (string) $orderRef, (string) $secret);
    }

    public static function checkToken($secret, $orderRef, $token)
    {
        if (strlen((string) $secret) < 16 || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals(self::token($secret, $orderRef), $token);
    }

    /**
     * Creates a payment.
     *
     * @return array success (bool), checkout_url, payment_id, error (message for logs and for the buyer)
     */
    public static function createPayment($apiKey, $amount, $currency, $provider, $email, $callbackUrl, array $metadata, $userAgent = '')
    {
        $apiKey = trim((string) $apiKey);
        if ($apiKey === '') {
            return array('success' => false, 'error' => 'The Chain2Pay API key is not configured.');
        }
        if (trim((string) $provider) === '') {
            return array('success' => false, 'error' => 'No Chain2Pay provider is configured.');
        }
        $payload = array(
            'amount' => (float) self::amount($amount),
            'currency' => strtoupper(trim((string) $currency)),
            'provider' => (string) $provider,
            'callback_url' => (string) $callbackUrl,
            'metadata' => $metadata,
        );
        $email = trim((string) $email);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $payload['customer_email'] = $email;
        }

        // A transport error or a 5xx answer is retried; 2xx and 4xx answers are final.
        $response = array('http' => 0, 'body' => '', 'error' => 'no attempt');
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $response = self::http('POST', '/api/v2/payments', $apiKey, $payload, $userAgent);
            if ($response['error'] === '' && $response['http'] > 0 && $response['http'] < 500) {
                break;
            }
            if ($attempt < 3) {
                usleep(400000 * $attempt);
            }
        }
        if ($response['error'] !== '') {
            return array('success' => false, 'error' => 'Could not reach Chain2Pay: ' . $response['error']);
        }
        $data = json_decode($response['body'], true);
        if ($response['http'] !== 200 && $response['http'] !== 201) {
            return array('success' => false, 'error' => 'Chain2Pay answered HTTP ' . $response['http'] . self::apiMessage($data));
        }
        $checkoutUrl = is_array($data) && isset($data['checkout_url']) && is_string($data['checkout_url']) ? $data['checkout_url'] : '';
        $paymentId = is_array($data) && isset($data['id']) && is_scalar($data['id']) ? (string) $data['id'] : '';
        if ($paymentId === '' || !preg_match('#^https://[^\s"\'<>]+$#i', $checkoutUrl)) {
            return array('success' => false, 'error' => 'Chain2Pay returned an answer without a checkout link.');
        }

        return array('success' => true, 'checkout_url' => $checkoutUrl, 'payment_id' => $paymentId, 'error' => '');
    }

    /**
     * Asks the API whether a payment is paid and belongs to the expected order.
     *
     * @param string $paymentId        id sent by Chain2Pay in the callback (chain2pay_order_id)
     * @param string $expectedRef      order reference signed in the callback URL
     * @param float  $expectedAmount   amount signed in the callback URL
     * @param string $expectedCurrency currency signed in the callback URL
     * @param string|array $storedPaymentId payment id(s) saved by the shop when it created the payment(s), '' when none
     *
     * @return array result (PAID, NOT_PAID, MISMATCH or UNAVAILABLE), reason, payment (API data)
     */
    public static function verifyPaid($apiKey, $paymentId, $expectedRef, $expectedAmount, $expectedCurrency, $storedPaymentId = '', $userAgent = '')
    {
        $paymentId = trim((string) $paymentId);
        if (!preg_match('/^[A-Za-z0-9_-]{1,100}$/', $paymentId)) {
            return array('result' => self::MISMATCH, 'reason' => 'the callback carries no valid payment id', 'payment' => array());
        }
        // One order can have several payments (the buyer went back and started again): any of them counts.
        $known = array();
        foreach ((array) $storedPaymentId as $stored) {
            if (is_scalar($stored) && trim((string) $stored) !== '') {
                $known[] = trim((string) $stored);
            }
        }
        if ($known && !in_array($paymentId, $known, true)) {
            return array('result' => self::MISMATCH, 'reason' => 'the payment id is not one created for this order', 'payment' => array());
        }
        if (trim((string) $apiKey) === '') {
            return array('result' => self::UNAVAILABLE, 'reason' => 'the Chain2Pay API key is not configured', 'payment' => array());
        }
        // A transport error or a 5xx answer is retried twice before the callback is asked to come back later.
        $response = array('http' => 0, 'body' => '', 'error' => 'no attempt');
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $response = self::http('GET', '/api/v2/payments/' . rawurlencode($paymentId), trim((string) $apiKey), null, $userAgent);
            if ($response['error'] === '' && $response['http'] > 0 && $response['http'] < 500) {
                break;
            }
            if ($attempt < 3) {
                usleep(700000 * $attempt);
            }
        }
        if ($response['error'] !== '') {
            return array('result' => self::UNAVAILABLE, 'reason' => 'could not reach Chain2Pay: ' . $response['error'], 'payment' => array());
        }
        if ($response['http'] === 404) {
            return array('result' => self::MISMATCH, 'reason' => 'Chain2Pay does not know this payment for this API key', 'payment' => array());
        }
        $data = json_decode($response['body'], true);
        if ($response['http'] !== 200 || !is_array($data)) {
            return array('result' => self::UNAVAILABLE, 'reason' => 'Chain2Pay answered HTTP ' . $response['http'] . self::apiMessage($data), 'payment' => array());
        }
        if (isset($data['id']) && is_scalar($data['id']) && (string) $data['id'] !== $paymentId) {
            return array('result' => self::MISMATCH, 'reason' => 'Chain2Pay returned another payment', 'payment' => $data);
        }
        $status = isset($data['status']) && is_string($data['status']) ? strtolower($data['status']) : '';
        if ($status !== 'paid') {
            return array('result' => self::NOT_PAID, 'reason' => 'the payment status is "' . $status . '"', 'payment' => $data);
        }
        if (isset($data['metadata']) && is_array($data['metadata']) && isset($data['metadata']['order_ref'])) {
            if (!is_string($data['metadata']['order_ref']) || !hash_equals((string) $expectedRef, $data['metadata']['order_ref'])) {
                return array('result' => self::MISMATCH, 'reason' => 'the payment was created for another order or another amount', 'payment' => $data);
            }
        }
        if (isset($data['currency'], $data['amount']) && is_string($data['currency']) && is_numeric($data['amount'])
            && strtoupper($data['currency']) === strtoupper(trim((string) $expectedCurrency))
            && (float) $data['amount'] + 0.01 < (float) $expectedAmount) {
            return array('result' => self::MISMATCH, 'reason' => 'the payment amount (' . self::amount($data['amount']) . ') is lower than the order amount (' . self::amount($expectedAmount) . ')', 'payment' => $data);
        }

        return array('result' => self::PAID, 'reason' => '', 'payment' => $data);
    }

    /** Short text for an order note: transaction hash, amount and coin when the API gives them. */
    public static function describe(array $payment)
    {
        $parts = array();
        if (isset($payment['id']) && is_scalar($payment['id'])) {
            $parts[] = 'payment ' . $payment['id'];
        }
        if (isset($payment['provider']) && is_string($payment['provider']) && $payment['provider'] !== '') {
            $parts[] = 'provider ' . $payment['provider'];
        }
        if (isset($payment['paid_amount']) && is_numeric($payment['paid_amount'])) {
            $parts[] = 'received ' . $payment['paid_amount'] . (isset($payment['paid_coin']) && is_string($payment['paid_coin']) ? ' ' . $payment['paid_coin'] : '');
        }
        if (isset($payment['tx_hash']) && is_string($payment['tx_hash']) && $payment['tx_hash'] !== '') {
            $parts[] = 'transaction ' . $payment['tx_hash'];
        }
        if (!empty($payment['sandbox'])) {
            $parts[] = 'SANDBOX (test payment)';
        }

        return implode(', ', $parts);
    }

    /** Transaction reference to store on the order: the transaction hash, or the payment id. */
    public static function transactionId(array $payment, $paymentId)
    {
        if (isset($payment['tx_hash']) && is_string($payment['tx_hash']) && $payment['tx_hash'] !== '') {
            return substr($payment['tx_hash'], 0, 190);
        }

        return 'chain2pay_' . $paymentId;
    }

    private static function apiBase()
    {
        // Only used by the automated tests of the plugins.
        if (defined('CHAIN2PAY_TEST_API_BASE') && is_string(CHAIN2PAY_TEST_API_BASE) && CHAIN2PAY_TEST_API_BASE !== '') {
            return rtrim(CHAIN2PAY_TEST_API_BASE, '/');
        }

        return self::API_BASE;
    }

    private static function apiMessage($data)
    {
        if (!is_array($data) || !isset($data['error'])) {
            return '';
        }
        if (is_array($data['error']) && isset($data['error']['message']) && is_string($data['error']['message'])) {
            return ': ' . substr($data['error']['message'], 0, 300);
        }
        if (is_string($data['error'])) {
            return ': ' . substr($data['error'], 0, 300);
        }

        return '';
    }

    /** @return array http (int), body (string), error (string, empty when the request went through) */
    private static function http($method, $path, $apiKey, $json, $userAgent)
    {
        if (!function_exists('curl_init')) {
            return array('http' => 0, 'body' => '', 'error' => 'the PHP cURL extension is not installed');
        }
        $headers = array(
            'Accept: application/json',
            'Authorization: Bearer ' . $apiKey,
        );
        $options = array(
            CURLOPT_URL => self::apiBase() . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Chain2Pay-Plugin/' . self::VERSION . ($userAgent !== '' ? ' ' . preg_replace('/[^A-Za-z0-9._\/ -]/', '', (string) $userAgent) : ''),
        );
        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($json);
        }
        $options[CURLOPT_HTTPHEADER] = $headers;
        $handle = curl_init();
        curl_setopt_array($handle, $options);
        $body = curl_exec($handle);
        $error = curl_error($handle);
        $http = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        if ($body === false || $error !== '') {
            return array('http' => 0, 'body' => '', 'error' => $error !== '' ? $error : 'empty answer');
        }

        return array('http' => $http, 'body' => (string) $body, 'error' => '');
    }
}

