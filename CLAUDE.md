# JorBill

A single-tenant ISP billing, CRM, and operations suite — built for one ISP (the operator running it), not a SaaS. Target: Splynx-tier feature breadth at taoki-tier price, with PH-specific compliance and workflows that foreign tools don't handle.

## Product positioning

Competitors and where each one wins:

- **[taokininam.com](https://taokininam.com)** — PH-focused, Mikrotik-only, ~₱365/yr. Surprisingly broad: PAYG/Piso, Agent commissions, VOD, GenieACS + Cacti integration, OLT/NAP fabric map with TR-069 overlay. Cheap, lean.
- **[oxaps.ph](https://oxaps.ph)** — Similar PH market, polished, white-label, mobile apps.
- **[phpnuxbill](https://github.com/hotspotbilling/phpnuxbill)** — Open source PHP kernel, FreeRADIUS, plugin system. Kernel is too thin to fork as a base for what we're building.
- **[Splynx](https://splynx.com)** — Enterprise. The moat: TR-069 ACS, IPAM, FSM + inventory, real CRM + quote-to-activation, accounting integrations.
- **[UISP](https://uisp.com)** (Ubiquiti) — Free. Auto-topology, native outage detection, FreeRADIUS, suspension at the router, open plugin SDK ([UCRM-plugins](https://github.com/Ubiquiti-App/UCRM-plugins)), [REST API](https://ucrm.docs.apiary.io/). CRM and FSM are weak; [multi-tax is broken](https://help.uisp.com/hc/en-us/articles/22590970611479-UISP-CRM-Pricing-Mode).

**Our differentiators (the gaps nobody has nailed):**

1. **BIR + PH compliance baked in** — OR/SI numbering, 12% VAT, 2307 withholding, ATP series rollover. Foreign tools don't try.
2. **One-pane outage RCA** — fuse UISP outage + RADIUS disconnects + OLT optical alarms + Cacti drops onto a single per-customer timeline.
3. **Lead → site survey → quote → install JO → activation** as one continuous workflow on the same record. Splynx fakes it; UISP doesn't try.
4. **Offline-capable tech mobile app** with signed customer acceptance, inventory sync, JO updates.
5. **Automation engine** — IFTTT for ISPs (e.g., "3 RADIUS disconnects + low ONU Rx in 1hr → auto-JO + SMS customer").

## Architecture: two layers

**Layer 1 — Network OS (off-the-shelf, free).** Don't rebuild what UISP/GenieACS already do well.

- **UISP** — Ubiquiti gear, topology, outage detection, suspension, FreeRADIUS bridge.
- **GenieACS** — TR-069 for non-UBNT ONUs (Huawei, VSOL, BDCOM, ZTE).
- **Cacti** — RRD graphing, embed via iframe + tag per customer.
- **FreeRADIUS** — single AAA source across Mikrotik + UISP + others, reads from our Postgres via SQL driver.

**Layer 2 — JorBill brain (greenfield, this repo).** The billing/CRM/FSM/automation logic that competitors are weakest at.

- **Laravel 11 + Filament 3** — admin in weeks, not months. Filament eats the CRUD that taoki visibly hand-rolled.
- **Customer portal** — Filament Pages (leaner) or thin Nuxt 3 frontend if we outgrow Filament.
- **Mobile (customer + tech)** — Flutter, one codebase.
- **Workers in Go** for Mikrotik API + OLT SNMP/CLI polling. PHP is fine until polling 50+ routers at 30s intervals.
- **Postgres + Redis + Laravel Horizon** for jobs.

**Bridges Layer 2 → Layer 1:** REST clients for UISP + GenieACS NBI; Mikrotik API client; OLT SNMP/SSH client. Each is a single class behind an interface so we can swap or mock.

## Build vs. use as-is

| Module | Off-the-shelf | Build in JorBill |
|---|---|---|
| Topology / outage map | UISP | overlay NAP/PON polygons |
| TR-069 / ACS | GenieACS | mass-config UI, scheduled jobs |
| Graphing | Cacti | iframe embed, tag-by-customer |
| RADIUS AAA | FreeRADIUS | session viewer, kick-user, Framed-IP |
| Mikrotik queues + PPPoE + Hotspot | — | full — abstract Simple Queue + PPPoE + Hotspot |
| OLT (Huawei/VSOL/BDCOM) | — | full — SNMP + CLI, ONU provisioning |
| Billing + invoicing | — | full — BIR OR/SI, 12% VAT, withholding, multi-gateway |
| CRM + sales pipeline | — | full — lead, quote-to-activation |
| FSM / Job Order | — | full — dispatch, mobile app, GPS, inventory link |
| Inventory | — | full — serialized assets, RMA, ONU-to-customer pairing |
| Agent commissions | — | full — taoki has it, others don't |
| PAYG / Piso WiFi | — | full — sachet, coin-op, IPoE/DHCP |
| Voucher / Hotspot | — | full — batch gen, SMS deliver, white-label landing |
| Customer portal | — | full — SOA, autopay, ticket, speedtest, plan change |
| Notifications | — | full — Semaphore, Viber, Telegram, email, WhatsApp Cloud API |
| Reporting / P&L | — | full — competitors are all weak here |
| Automation engine | — | full — the IFTTT layer |
| VOD | — | defer (low ROI) |

## Forum-validated must-haves

Lifted from [phpnuxbill Ideas](https://github.com/hotspotbilling/phpnuxbill/discussions/categories/ideas) and [Splynx's own 2025 automation checklist](https://splynx.com/blog/billing/fully-automated-isp-billing-in-2025-checklist-for-local-isps-and-wisps/):

- Mikrotik **Simple Queue** as a first-class bandwidth model (not just PPPoE rate-limit).
- **Framed-IP-Address** RADIUS attribute for static IPs over PPP.
- **Multiple payment gateways active simultaneously** — not switched one-at-a-time.
- **Expense tracking + P&L**, not just revenue.
- **Excel import/export** for migrations and bulk ops.
- **Active PPPoE / online-now monitoring** per NAS.
- **Smart cancellation, prorated refunds, promo + loyalty stacking**.
- **Auto-suspend / auto-restore idempotent** across router reboots.
- **Outage overlay on map**, not static NAP pins.

## Core entities (initial schema sketch)

`Customer` · `Service` (plan) · `Subscription` (customer × service) · `Invoice` · `Payment` · `Router` · `Nap` · `Onu` · `JobOrder` · `Ticket` · `Agent` · `Lead` · `Quote` · `InventoryItem` · `Notification` · `AutomationRule`.

Integration interfaces (one class each, mockable):

- `UispClient` — UISP REST.
- `GenieAcsClient` — GenieACS NBI.
- `MikrotikClient` — RouterOS API.
- `OltClient` (interface) → `HuaweiOlt`, `VsolOlt`, `BdcomOlt`, `ZteOlt` implementations.
- `PaymentGateway` (interface) → `Xendit`, `PayMongo`, `Maya`, `Dragonpay`, `Stripe`.
- `SmsGateway` (interface) → `Semaphore`, `MikrotikGsm`.

## Conventions

- **Single tenant.** No tenant scoping, no `tenant_id` columns. One ISP per install.
- **PH defaults**: 12% VAT inclusive pricing, PHP currency, Asia/Manila TZ, English UI with Tagalog/Bisaya optional later.
- **Idempotency** is a hard requirement on every router/OLT action — every job must be safe to retry.
- **Soft deletes** for Customer, Subscription, Invoice. Hard delete only for Lead, Quote, draft entities.
- **Money** — use `brick/money` or `akaunting/money`, never floats. Store as integer minor units (centavos).
- **Auditing** — every Customer/Service/Subscription/Invoice change is logged via `spatie/laravel-activitylog`.

## Decisions made, with reasoning

- **Greenfield over forking phpnuxbill** — its kernel is too thin to carry FSM/CRM/automation layers.
- **Laravel + Filament over Nuxt-first** — admin UI velocity matters more than frontend polish for a single-tenant tool.
- **UISP + GenieACS as Layer 1, not as competitors** — the topology/outage/TR-069 features are too expensive to rebuild and they're free.
- **Go workers for polling, PHP for everything else** — PHP's process model isn't great for sustained polling at scale.
- **No multi-tenancy** — explicitly out of scope per owner. Simpler schema, simpler ops, faster ship.

## Status

Pre-code. This doc is the north star. Next step: scaffold Laravel + Filament with the core entities and integration interface stubs.
