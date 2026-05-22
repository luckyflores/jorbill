# Productionize notes

## DNS
- `jorbill.maltixtech.xyz`     → 160.250.21.46 (A)
- `accounting.maltixtech.xyz`  → 160.250.21.46 (A)

## Caddy
- Config: `/etc/caddy/Caddyfile` (mirror committed at `ops/Caddyfile.example`)
- Logs: journald (`sudo journalctl -u caddy`)
- Certs: auto-managed by Caddy via Let's Encrypt
- Service: `systemctl status caddy`

## JorBill app
- Bound to `127.0.0.1:8000` — only reachable via Caddy.
- Started via `nohup php artisan serve --host=127.0.0.1 --port=8000`.
- Reboot survival: NOT YET — see Phase 17B (systemd unit for artisan serve OR migrate to nginx+php-fpm).

## Odoo
- Bound to `127.0.0.1:8069`.
- `proxy_mode = True` in `/etc/odoo/odoo.conf`.
- `web.base.url = https://accounting.maltixtech.xyz` + `.freeze = True` in `ir_config_parameter`.

## Firewall
- Open: 22 · 80 · 443 · 1812-1813/udp (RADIUS) · 7547 · 7567 (TR-069)
- Closed: 8000 (was JorBill, now behind Caddy)

## URLs
- Operator:   https://jorbill.maltixtech.xyz/admin
- Accountant: https://accounting.maltixtech.xyz/web/login
- ssh:        ssh jorbill (port 22, public IP)


## Odoo custom addons (Phase 17B)

Custom community modules installed at `/opt/odoo-addons/` (owned by `odoo:odoo`):

- **base_accounting_kit** (Cybrosys) — Balance Sheet, P&L, Cash Flow, Trial Balance, General Ledger, Aged Partner Balance, asset management, budget, recurring entries, bank reconciliation. Fills the Enterprise-only accounting reports gap.
- **dynamic_accounts_report** (Cybrosys) — Interactive drill-down financial reports with PDF/XLSX export. Complements `base_accounting_kit`.

`addons_path` in `/etc/odoo/odoo.conf` includes `/opt/odoo-addons` so Odoo discovers these on startup.

### To upgrade / change modules

1. Stop Odoo: `sudo systemctl stop odoo`
2. Replace folder(s) under `/opt/odoo-addons/`
3. `sudo chown -R odoo:odoo /opt/odoo-addons`
4. Refresh apps list: `sudo -u odoo /usr/bin/odoo -d jorbill_accounting --update=base --no-http --stop-after-init`
5. Start Odoo: `sudo systemctl start odoo`
6. In Odoo UI (dev mode): Apps → Update Apps List → reinstall/upgrade


## Phase 18 — operations hardening

### systemd unit for JorBill
- Unit file: `/etc/systemd/system/jorbill.service`
- Replaces `nohup php artisan serve` — now restarts on failure, survives VM reboot.
- `sudo systemctl {status|restart|stop} jorbill`
- Logs: `sudo journalctl -u jorbill -f`

### Business Settings
- DB-backed `settings` table, `Setting::get()` / `Setting::put()` helpers (5-min cache).
- Filament page **System → Business Settings** for company name, TIN, address, VAT toggle + rate, currency.
- VAT toggle: when OFF, future invoices skip the 12% breakout.

### HitPay payment gateway
- `HitpayGateway implements PaymentGateway` (`app/Services/Payment/Hitpay/`).
- Webhook URL: `https://jorbill.maltixtech.xyz/webhooks/hitpay` (CSRF exempt via `webhooks/*` rule).
- HMAC-SHA256 validation on every incoming callback; rejects unsigned/forged payloads.
- Idempotent — duplicate `gateway_reference` lookups prevent double-recording on webhook retries.
- Sandbox vs live: `HITPAY_USE_LIVE` env flag toggles `api.hit-pay.com` vs `api.sandbox.hit-pay.com`.
- Filament Invoice "Send payment link" action — generates HitPay checkout URL, persistent notification you can copy + send to the customer.
- `.env` keys (gitignored): `HITPAY_API_KEY`, `HITPAY_SALT`, `HITPAY_USE_LIVE`, `HITPAY_CURRENCY`.
