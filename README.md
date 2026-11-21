# bet19x.online

Laravel 12 (PHP 8.2+) casino / betting site with a player site, an admin panel and
white-label (multi-tenant) support. Currency: INR.

## Parts
- **Player site** – dashboard, wallet (deposit / withdraw requests), profile, in-play, games.
- **Games** – third-party AS Tech API (`app/Services/AsTechApi.php`, `AsTechController`).
  Provider calls `POST /api/astech/callback` (`getBalance`, `writeBet`, `rollback`),
  verified with an HMAC-SHA256 `X-Signature` header.
- **Admin panel** (`/admin`) – users, agents, bets, odds, matches, bonuses, promo codes, VIP,
  risk, reports, security, CMS, wallet approvals. Access is role based:
  - `super_admin` – everything (admin accounts, license, security, manual wallet adjust)
  - `manager` – wallet approvals, odds/matches, bonuses, risk, reports, site settings
  - `support` – users, notifications, CMS/banners and other content
- **White-label** – `ResolveTenant` middleware picks the tenant database by domain.

## Wallet rules
- **Deposit**: the user only creates a *pending* request. Balance is added when an admin approves it.
- **Withdraw**: only *winnings* are withdrawable. The amount is held immediately and a pending
  request is created; admin approval marks it paid, rejection refunds the held amount.

## Setup
```bash
composer install
cp .env.example .env      # then fill in DB_* and ASTECH_* values
php artisan key:generate
php artisan migrate --force
npm install && npm run build
```

## Tests
```bash
php artisan test
```
