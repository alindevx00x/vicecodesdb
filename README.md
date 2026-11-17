# ViceCodes - GTA 6 Cheat Codes Website

Built to the full specifications in [GTA6-Cheat-Codes-Website-Full-Specifications.md](./GTA6-Cheat-Codes-Website-Full-Specifications.md) on **PHP 8.3+**, **MySQL 8.0+ (InnoDB / utf8mb4)**, **Vanilla JS**, and **self-hosted TinyMCE via cdnjs.cloudflare.com** (no API key required).

---

## 1. Local MAMP & phpMyAdmin Access

- **Public Website:** `http://localhost:8888/GTA6CheatCodes/`
- **Admin Panel:** `http://localhost:8888/GTA6CheatCodes/admin`
  - **Default Admin Email:** `admin@vicecodes.com`
  - **Default Admin Password:** `ViceCodes2026!` *(Change this immediately in production)*
- **MAMP phpMyAdmin:** `http://localhost:8888/phpMyAdmin5/`
  - **Database Name:** `gta6cheatcodes`
  - **MySQL Host / Port:** `127.0.0.1:8889` (or `/Applications/MAMP/tmp/mysql/mysql.sock`)
  - **MySQL User / Pass:** `root` / `root`

### Re-Importing Schema & Seed via phpMyAdmin
1. Open `http://localhost:8888/phpMyAdmin5/`.
2. Select or create the `gta6cheatcodes` database (`utf8mb4_unicode_ci`).
3. Click **Import** → upload `database/schema.sql` → click **Import**.
4. Click **Import** → upload `database/seed.sql` → click **Import**.

---

## 2. Production Deployment Checklist

1. Point your web server's `DocumentRoot` to `/path/to/GTA6CheatCodes/public`.
2. Copy `.env.example` to `.env` and configure:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://yourdomain.com`
   - `BASE_PATH=` (empty when `DocumentRoot` is `/public`)
   - Production `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
3. Enable HTTPS + HSTS and place behind **Cloudflare CDN** for sub-100ms TTFB worldwide.
4. Change the default admin password hash in `users` table (generate via `php -r 'echo password_hash("YOUR_STRONG_PASS", PASSWORD_BCRYPT);'`).
5. Submit `/sitemap.xml` to **Google Search Console** and **Bing Webmaster Tools** (Bing powers ChatGPT Search & Copilot!).

---

## 3. SEO / AEO / GEO / AISEO Architecture Included

- **Multi-Sitemap Index:** `/sitemap.xml`, `/sitemap-pages.xml`, `/sitemap-cheats.xml`, `/sitemap-blog.xml`
- **AI Crawler & GEO Discovery:** `/robots.txt` + `/llms.txt` + `/feed.xml`
- **Rich JSON-LD Structured Data:** `Organization`, `WebSite` + `SearchAction`, `VideoGame`, `BreadcrumbList`, `ItemList`, `HowTo`, `BlogPosting`, and `FAQPage`
- **AEO Quick Answer Boxes:** Dedicated 40–60 word direct answer fields on blog posts and static pages to win Google AI Overviews, Featured Snippets, Perplexity, and ChatGPT Search citations.
- **Launch-Day Switcher:** In **Admin → Site Settings**, switch **Launch Mode** from `Pre-Launch Countdown` to `Launched (Codes Live Mode)` on November 19, 2026, and bulk-publish verified codes in seconds.

---

## 4. Admin Panel Quick Guide

- **Cheats (`/admin/cheats`):** Add/edit cheats with PS5 DualSense, Xbox Series X|S, and PC button sequences (comma-separated inputs like `R1, R1, Circle, R2, Left, Right` automatically render as visual controller keycaps), phone numbers, per-cheat FAQs, and verification changelogs.
- **Blog (`/admin/blog`):** Full TinyMCE editor with direct image upload to `/public/uploads/editor/`, AEO Quick Answer box, reading time auto-calculation, and FAQPage schema builder.
- **Static Pages (`/admin/pages`):** Edit `/about`, `/how-we-verify`, `/disclaimer`, `/privacy-policy`, `/terms`, and `/contact`.
- **Newsletter (`/admin/newsletter`):** GDPR-compliant double opt-in subscribers list + one-click CSV export.
