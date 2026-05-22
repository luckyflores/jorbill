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
