# Chain2Pay for Paymenter

Accept card payments on your Paymenter invoices. Clients pay by card through an on-ramp provider on the Chain2Pay checkout, and you receive the payment in crypto in your own wallet.

## Requirements

- Paymenter 1.4 or newer
- The PHP cURL extension

## Before you start

You need three things:

1. A Chain2Pay account. Create one at https://dashboard.chain2pay.is.
2. A wallet address added and verified in the dashboard. Payments are sent to this wallet.
3. An API key linked to that wallet. Live keys start with `c2p_live_`, test keys start with `c2p_sandbox_`.

Your site must be reachable from the internet over HTTPS. Chain2Pay calls your site to confirm each payment, so a site on `localhost` or behind a password cannot receive confirmations.

## Install

Use one of the two methods.

**Upload from the admin area**

1. Download `Chain2Pay-V1.0.0-Paymenter.zip` from the [latest release](https://github.com/chain2pay-is/chain2pay-paymenter/releases/latest).
2. In the Paymenter admin area, open **Extensions > Available Extensions**, open **Ready to Install / Upload**, click **Upload Extension** and upload the zip file as it is.

**Copy the files**

1. Unzip `Chain2Pay-V1.0.0-Paymenter.zip`. You get a folder named `Chain2Pay`.
2. Upload it to `extensions/Gateways/` in your Paymenter installation. The file must end up at `extensions/Gateways/Chain2Pay/Chain2Pay.php`.

## Configure

1. Open **Gateways** and click **New gateway**.
2. Choose **Chain2Pay** as the gateway and set the **Name** your clients see, for example `Card payment`.
3. Paste your **Chain2Pay API key**.
4. Choose the **Provider**. Keep **Hosted checkout: all providers (recommended)** unless you want to send clients to one provider only.
5. Click **Create**.

To offer several providers side by side, create one gateway per provider with the same API key.

To use the custom provider, choose **Custom provider** as Provider and fill in **Custom provider slug**.

## Providers

| Provider | Name shown to the buyer | Indicative minimum (USD or EUR) |
|---|---|---|
| Hosted checkout (all providers) | Card Payment | 6 |
| Stripe | Card (Stripe) | 3, USD and EUR only |
| MoonPay | Pay with MoonPay | 20 |
| Revolut | Pay with Revolut | 6 |
| Coinbase | Pay with Coinbase | 10 |
| PayPal | Pay with PayPal | 5 |
| Banxa | Pay with Banxa | 17 |
| Ramp Network | Pay with Ramp | 7 |
| Robinhood (US only) | Pay with Robinhood | 5 |
| Skrill | Pay with Skrill | 22 |
| Alchemy Pay | Pay with Alchemy Pay | 17 |
| Alchemy Pay (local methods) | Local payment methods | 15 |
| Blockchain.com | Pay with Blockchain.com | 15 |
| BTC Direct | Pay with BTC Direct | 33 |
| Coindisco | Pay by card | 23 |
| Cash App (US only) | Pay with Cash App | 20 |
| Crypto | Pay with Crypto | 6 |
| Custom provider | Pay by Card (Chain2Pay) | 5 |

The hosted checkout is the recommended choice: the buyer picks among the providers available for their country and amount. Minimums and availability change over time; the Chain2Pay API always applies the current rules and the plugin shows its answer to the buyer.

The custom provider sends buyers to any other Chain2Pay provider: type its slug, in lowercase, in the plugin settings.

## How a payment is confirmed

1. The buyer chooses a Chain2Pay method. The plugin creates a payment through the Chain2Pay API and sends the buyer to the checkout page.
2. The buyer pays by card with the provider.
3. Chain2Pay calls your site on a URL that the plugin signed for this order and this amount.
4. The plugin asks the Chain2Pay API whether that payment is really paid, and only then marks the order as paid.

A call with a wrong signature, an unknown payment or a lower amount is refused and the order stays unpaid.

## Invoice statuses

| Situation | Result |
|---|---|
| Client sent to the checkout | Invoice stays pending |
| Payment confirmed | A payment with the transaction reference is added to the invoice, which becomes paid when nothing remains due |
| Payment received for an invoice that is no longer pending | Nothing is added; a warning is written in the log so that you can refund the client or credit their account |

## Troubleshooting

| Symptom | What to check |
|---|---|
| No Chain2Pay method at checkout | The API key is filled in, at least one provider is enabled, and the order currency and amount are accepted by that provider. |
| "Payment could not be started" | The API key is valid and linked to a verified wallet. The exact answer of the API is written in the log named below. |
| The buyer paid but the order is still unpaid | Your site must answer Chain2Pay's call: no maintenance page, no password protection, no firewall rule that blocks the callback URL. Chain2Pay repeats the call, so the order is updated once the site answers. |
| Stripe is not offered | Stripe takes USD and EUR orders only. |
| The order was paid after you cancelled it | The plugin does not reopen a closed order. The payment is noted on the order so that you can refund the buyer or reopen the order yourself. |

Documentation: https://docs.chain2pay.is. Support: support@chain2pay.is.

Log: `storage/logs/laravel.log`. Lines start with `Chain2Pay:`.

## Uninstall

Delete the Chain2Pay gateways in **Gateways**, then remove the folder `extensions/Gateways/Chain2Pay`.

## License

GPL-3.0-or-later. See the license file in this repository.
