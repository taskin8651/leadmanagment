# LeadFlow CRM — Laravel 10 (Single Admin)

A Laravel 10 Lead Management CRM for a single company/admin — leads, follow-ups, staff, attendance.

## Included

- Laravel 10
- Single Admin login (Spatie Permission role: `Client`, plus `Staff` / `Telecaller` sub-roles)
- Dashboard with analytics
- Lead CRUD, status/priority, notes, interactions, attachments, email, import (CSV/XLSX), export
- Duplicate-lead detection, lead merge
- Follow-up listing and completion
- Staff management (add/edit/deactivate)
- Attendance (check-in/check-out)
- Custom fields, tags
- Public lead-capture form (for embedding on a website)
- API token issuing + REST API (`/api/v1/leads`)
- Responsive Bootstrap 5.3 UI, search/filter/pagination, soft deletes
- Lead scoring (auto-computed 0–100 from source, value, engagement, response speed) with a suggested priority
- Account-wide audit log (logins, staff changes, API tokens, lead/invoice deletions, merges) — Admin-only
- Staff performance and lead-source ROI breakdown on the dashboard — Admin-only
- Follow-up calendar view (month grid), alongside the existing list view
- GST-ready invoices: customer GSTIN, place of supply, HSN/SAC per line item, CGST/SGST vs IGST split
- Outbound WhatsApp messages to leads via 11za, and Facebook Lead Ads / Google Ads lead form webhooks (see below — both need your own provider credentials)

## Install

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database named `lead_crm`, then configure `.env`.

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Login:

Email: `admin@example.com`
Password: `password`

Change the demo password immediately in a real deployment.

## URLs

All paths below are relative to `APP_URL` (default `http://localhost`).

| Purpose | URL |
|---|---|
| Login | `/login` |
| Forgot password | `/forgot-password` |
| Admin dashboard | `/client/dashboard` |
| Leads | `/client/leads` |
| Add lead | `/client/leads/create` |
| Import leads (CSV/XLSX) | `/client/leads/import` |
| Follow-ups | `/client/follow-ups` |
| Follow-up calendar | `/client/follow-ups/calendar` |
| Attendance | `/client/attendances` |
| Staff | `/client/staff` |
| Analytics | `/client/analytics` |
| Audit log | `/client/audit-logs` |
| Search | `/client/search` |
| Custom fields | `/client/settings/custom-fields` |
| API tokens | `/client/api-tokens` |
| Public lead-capture form (embed on your website) | `/lead-form/{client_code}` — this install's code: `/lead-form/CL-EF4SHF4X` |
| REST API — leads (needs a Sanctum token from API Tokens page) | `/api/v1/leads` |
| WhatsApp lead webhook (e.g. 11za — see below) | `/api/webhooks/whatsapp` |
| Facebook Lead Ads webhook (see below) | `/api/webhooks/facebook-leads` |
| Google Ads lead form webhook (see below) | `/api/webhooks/google-ads-leads` |

## WhatsApp lead webhook (incoming)

`POST /api/webhooks/whatsapp` auto-creates a Lead (source "WhatsApp Ads") from an incoming WhatsApp message, with duplicate-phone detection and auto-assignment — same behavior as the public lead form.

**Status: dummy field mapping.** The real payload format from your WhatsApp provider (11za) hasn't been confirmed yet, so `app/Http/Controllers/Api/WhatsAppWebhookController.php` currently reads generic field names (`phone`/`from`/`sender`/`mobile`, `name`/`sender_name`/`pushname`, `message`/`text`/`body`). Once you share a real sample payload from 11za's dashboard, update those field names to match exactly.

Setup:
1. Set `WHATSAPP_WEBHOOK_SECRET` in `.env` to a random string.
2. Give your provider this URL: `{APP_URL}/api/webhooks/whatsapp?token=<that same secret>`.
3. Without the secret set, the endpoint accepts requests unauthenticated — fine for testing, not for production.

## WhatsApp outbound messages (11za)

Every lead's page has a "Send WhatsApp" button that messages the lead directly via 11za, logged to the lead's activity timeline.

**Status: dummy request shape.** `app/Services/ElevenZaWhatsAppService.php` posts to `{ELEVENZA_API_URL}/send-message` with a generic `{sender, phone, message}` body and a `Bearer` API key — this hasn't been confirmed against 11za's real API docs yet. Update `buildPayload`/the request in that file once you have real credentials and a sample request.

Setup: set `ELEVENZA_API_URL`, `ELEVENZA_API_KEY`, `ELEVENZA_SENDER_ID` in `.env`. Until these are set, the "Send WhatsApp" button shows an error instead of silently failing.

## Facebook & Google Ads lead sync

Two webhook endpoints auto-create leads from ad platforms, same duplicate-phone detection and auto-assignment as the other lead sources.

**Facebook (Meta) Lead Ads** — `app/Http/Controllers/Api/FacebookLeadsWebhookController.php`. Meta's webhook only sends a `leadgen_id`; the controller then calls the Graph API to fetch the actual answers, so it needs a Page access token with `leads_retrieval` permission. The question→field mapping (`full_name`, `phone_number`, `email`) is a placeholder — check it against your actual lead form's question names.

Setup:
1. Create a Meta app with the Webhooks + Lead Ads products, subscribe your Page to `leadgen` events, point the webhook at `{APP_URL}/api/webhooks/facebook-leads`.
2. Set `FACEBOOK_LEADS_VERIFY_TOKEN` (any random string — also enter it in Meta's webhook setup screen), `FACEBOOK_LEADS_APP_SECRET` (from the app dashboard, used to verify request signatures), and `FACEBOOK_LEADS_PAGE_ACCESS_TOKEN`.

**Google Ads lead form extension** — `app/Http/Controllers/Api/GoogleAdsLeadsWebhookController.php`. Follows Google's documented webhook payload (`google_key`, `user_column_data[]`); the built-in `FULL_NAME`/`PHONE_NUMBER`/`EMAIL` column IDs are handled — extend `mapColumns()`-equivalent logic in the controller if your form has custom questions.

Setup: in Google Ads → Lead form extension → Webhook, set the URL to `{APP_URL}/api/webhooks/google-ads-leads` and the "webhook key" to the same value as `GOOGLE_ADS_LEADS_WEBHOOK_SECRET` in `.env`.

## Important

For production, add email integrations, queue jobs, backups and production hardening before launch.
