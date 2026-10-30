# VMGT BOOKINGS — Full PHP Site + Admin Backend

A rebuilt version of your celebrity booking site, now a fully dynamic PHP
application with a complete admin panel. Plain PHP + PDO, no framework.
It runs two ways from the same code:

- **Render** (Docker, SQLite on a persistent disk) — auto-deploys on every git push
- **cPanel shared hosting** (MySQL) — upload the files and import the SQL

---

## Deploy on Render (auto-deploy)

1. **Push this folder to a GitHub repo** (the folder contents at the repo root, so
   `Dockerfile` and `render.yaml` sit at the top level).
2. In Render: **New → Blueprint**, pick the repo. Render reads `render.yaml` and
   creates the web service + 1 GB persistent disk.
3. When prompted, fill in:
   - `SITE_URL` — e.g. `https://vmgtbookings.com` (or your `*.onrender.com` URL)
   - `ADMIN_EMAIL` — where booking/contact notifications go
   - `SMTP_*` / `MAIL_FROM` — optional, see "Email" below (can leave blank)
4. Wait for the first deploy. On first boot the app **automatically**:
   creates the database, loads the starter content and the 11 celebrity photos, and
   creates your admin login.
5. Get your admin password: Render dashboard → service → **Environment** →
   `ADMIN_PASSWORD` (auto-generated). Username is `admin`. Log in at `/admin/login.php`,
   then change the password under **My Profile**.
6. From now on, every `git push` to the branch redeploys automatically.

**Good to know**
- Everything that must survive redeploys (database + uploaded images) lives on the
  disk at `/var/data`. Render only allows disks on paid instances, and a service with a
  disk runs as a single instance (fine for this site).
- Without a disk (free plan), the site works but the database and uploads reset on
  every deploy. Not suitable for real use.
- Changing `ADMIN_PASSWORD` in the dashboard later does **not** change the login: it is
  only read when no admin exists. Use **My Profile** / **Admin Users** instead.
- `/healthz.php` is the health check Render uses (it also confirms the database works).
- **Email:** Render blocks PHP `mail()`. Set `SMTP_HOST`, `SMTP_PORT` (587),
  `SMTP_USER`, `SMTP_PASS` (e.g. Brevo, Mailgun, Gmail app password) and `MAIL_FROM`.
  Without them the site still works; bookings and inquiries are saved in the admin
  panel, you just won't get the notification email.
- Optional: set `DB_DRIVER=mysql` plus `DB_HOST/DB_NAME/DB_USER/DB_PASS` to use an
  external MySQL database instead of SQLite (uploads still need the disk).

---

## What you get

**Public site**
- Home, About, Contact (working form), Celebrity list + individual profile pages
- Multi-step "Book Now" wizard (celebrity → event details → your info → review → submit)
- Blog / News section
- Newsletter signup (AJAX, no page reload)
- All content editable from the admin panel — no code edits needed for day-to-day changes

**Admin panel** (`/admin`)
- Dashboard with live stats (bookings, inquiries, subscribers, posts)
- Celebrities: add/edit/delete, photo upload, featured toggle, draft/published, ordering
- Bookings: view every request, filter by status, update status (pending → contacted →
  confirmed/declined), internal notes
- Inquiries: view contact form submissions, mark read/replied, one-click "reply by email"
- Blog: add/edit/delete posts, featured image, draft/published
- Subscribers: view newsletter list, export to CSV
- Site Settings: edit homepage text, about text, contact info, social links — no code
- Admin Users (super admin only): create additional admin/editor logins, disable/delete accounts
- My Profile: change your own name/email/password

## Deployment steps (cPanel / MySQL)

1. **Upload the files.** Upload everything in this ZIP to your domain's `public_html`
   (or a subdomain folder), keeping the folder structure intact.

2. **Create a MySQL database** in cPanel → MySQL Databases. Create a database user,
   attach it to the database with **all privileges**.

3. **Import the schema.** In phpMyAdmin, select your new database and import
   `sql/schema.sql`. This creates all tables and some starter site-settings text and
   seeds your 11 existing celebrities with their original photos (already included
   in `uploads/celebrities/`).

4. **Edit `config.php`** at the root and fill in:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — your cPanel database credentials
   - `SITE_URL` — your live domain, e.g. `https://vmgtbookings.com` (no trailing slash)
   - `ADMIN_EMAIL` — where booking/contact notifications get sent
   - `APP_SECRET` — replace with any long random string

5. **Create your admin login.** Visit `https://yourdomain.com/install/create_admin.php`
   in your browser once. Fill in your name, a username, email, and a password (min 8
   characters). This creates your first Super Admin account.

6. **Delete the `/install` folder** from the server immediately after step 5. The
   script locks itself once an admin exists, but removing the folder is best practice.

7. **Log in** at `https://yourdomain.com/admin/login.php` and you're in.

8. Set `display_errors` back to `'0'` in `config.php` if you turned it on while testing
   (it already defaults to off).

## Folder structure

```
/                       Public site (index.php, about.php, contact.php, etc.)
/admin/                 Admin panel
/assets/                Public CSS/JS/images
/uploads/celebrities/   Celebrity photos (uploaded via admin, or pre-seeded ones)
/uploads/blog/          Blog featured images
/includes/              Shared PHP includes (db, functions, templates) — not web-accessible
/sql/schema.sql         MySQL schema + starter content — not web-accessible
/sql/schema.sqlite.sql  SQLite version (used automatically on Render)
/bin/bootstrap.php      Render first-boot setup (schema + first admin) — CLI only
/Dockerfile, /docker/   Render container build + start-up script
/render.yaml            Render Blueprint (auto-deploy, disk, env vars)
/healthz.php            Render health check
/install/               One-time admin setup script — delete after first use
```

## Notes

- Email notifications use PHP's built-in `mail()`. If your host blocks it or you'd
  rather use proper SMTP (like we set up on Weboracle Shrine / RemitRova), that's a
  quick swap in `includes/functions.php` → `send_notification_email()`.
- Blog content is stored and displayed as plain text (line breaks preserved) rather
  than raw HTML, to keep it simple and safe by default. If you want a rich-text/HTML
  editor for blog posts later, that's an easy add-on.
- All forms have CSRF protection, honeypot spam traps, and basic rate limiting built in.
- Uploaded images are renamed to random filenames and only real image files (verified
  by content, not just extension) are accepted.
- The `/uploads` folder has its own `.htaccess` that blocks any script from ever
  executing there, even if someone tries to sneak one in via a file upload.
