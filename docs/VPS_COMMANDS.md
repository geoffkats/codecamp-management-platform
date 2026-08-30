# VPS commands — CodeCamp

Copy-paste cheat sheet for the Hostinger VPS. This is the live LMS server.

Do **not** put passwords, `APP_KEY`, or `.env` in git.

---

## Facts

| Item | Value |
|---|---|
| SSH | `ssh root@72.62.4.115` |
| App folder | `/var/www/codecamp` |
| GitHub | https://github.com/geoffkats/codecamp-management-platform |
| Branch | `main` |
| Working URL (until DNS is fixed) | http://72.62.4.115 |
| Intended URL | https://codecamp.codeacademyug.org |
| Nginx site | `/etc/nginx/sites-enabled/codecamp` |
| Database | local MySQL `codecamp` / user `codecamp` |
| PHP | 8.3 + php8.3-fpm |
| Web server | nginx |

The old shared-hosting database (`u622340404_*`) must **never** be used on this VPS.

---

## 1. Connect

**PowerShell on your PC**

```powershell
ssh root@72.62.4.115
```

Paste the Hostinger VPS root password. Nothing shows while you type. Then:

```bash
cd /var/www/codecamp
pwd
```

**WinSCP** (edit `.env` and upload files)

- Protocol: SFTP
- Host: `72.62.4.115`
- Port: `22`
- User: `root`
- Password: VPS root password
- App path: `/var/www/codecamp`
- Show hidden files: `Ctrl+Alt+H` (needed to see `.env`)

---

## 2. Update the code (do this most often)

On the VPS, after a commit was pushed to `main`:

```bash
cd /var/www/codecamp
git pull
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

Then hard-refresh the browser (`Ctrl+F5`).

If git says local files would be overwritten:

```bash
cd /var/www/codecamp
git status
git stash push -u -m "vps local"
git pull
php artisan view:clear
php artisan config:clear
```

---

## 3. Extra steps (only when needed)

Run these **after** `git pull` when the change matches.

**Database schema changed** (new/edited files in `database/migrations`)

```bash
cd /var/www/codecamp
php artisan migrate --force
```

**PHP packages changed** (`composer.json` / `composer.lock`)

```bash
cd /var/www/codecamp
composer install --no-dev --optimize-autoloader
```

**JS / CSS changed** (`resources/js`, `resources/css`, Vite)

```bash
cd /var/www/codecamp
npm ci
npm run build
```

**Permissions after uploads or artisan**

```bash
cd /var/www/codecamp
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

**Storage link missing** (images 404)

```bash
cd /var/www/codecamp
php artisan storage:link
```

**One seeder only** (example: React Web 2 lessons). Never run full `db:seed` on production.

```bash
cd /var/www/codecamp
php artisan db:seed --class=ReactWeb2LessonSeeder --force
```

**Robotics assignments** (question + file upload on every Robotics 1 / Robotics 2 lesson). Requires lessons already seeded.

```bash
cd /var/www/codecamp
php artisan db:seed --class=RoboticsAssignmentSeeder --force
```

Or re-run a full robotics course seeder (also refreshes assignments):

```bash
php artisan db:seed --class=MbotRoboticsLessonSeeder --force
php artisan db:seed --class=ArduinoAcebottRoboticsLessonSeeder --force
```

Type `yes` if it asks about production.

---

## 4. Full “safe update” block

Use this when you are not sure what changed:

```bash
cd /var/www/codecamp
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`optimize:clear` wipes caches. The `*:cache` lines rebuild them. Skip `config:cache` if you are about to edit `.env` — clear instead, edit, then cache.

---

## 5. `.env` rules (login / cookies)

Check current values (passwords hidden):

```bash
cd /var/www/codecamp
grep -E "^(APP_ENV|APP_DEBUG|APP_URL|SESSION_SECURE|SESSION_DOMAIN|DB_DATABASE|DB_USERNAME)=" .env
```

**While people use the IP (`http://72.62.4.115`)**

```env
APP_URL=http://72.62.4.115
SESSION_SECURE_COOKIE=false
SESSION_DOMAIN=
```

Then:

```bash
php artisan config:clear
php artisan cache:clear
```

If Sign in does nothing, that cookie setting is wrong, or the browser still has old cookies. Use a private window.

**Only after HTTPS on the real domain works**

```env
APP_URL=https://codecamp.codeacademyug.org
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=
```

No trailing slash on `APP_URL`.

```bash
php artisan config:clear
php artisan config:cache
```

Never replace the VPS `.env` with the Windows one. Never point `DB_*` at `u622340404_*`.

---

## 6. DNS — is the domain on the VPS yet?

**On your PC (PowerShell)**

```powershell
Resolve-DnsName codecamp.codeacademyug.org
```

Healthy: **A record = `72.62.4.115`**.  
Broken: `191.96.56.89`, `89.116...`, `91.108...`, or any `2a02:4780:...` (old Hostinger / CDN).

**On the VPS**

```bash
getent hosts codecamp.codeacademyug.org
dig +short A codecamp.codeacademyug.org
dig +short AAAA codecamp.codeacademyug.org
curl -I http://72.62.4.115/up
curl -I http://codecamp.codeacademyug.org/up
```

`/up` on the VPS should say **Application up** and `Server: nginx`.

If the domain response says `LiteSpeed` / `platform: hostinger` / `panel: hpanel`, DNS still points at shared hosting.

**Fix in hPanel DNS for `codeacademyug.org`**

1. `codecamp` **A** → `72.62.4.115`
2. Delete `codecamp` **AAAA**
3. Turn **CDN off** for the `codecamp` subdomain
4. Do **not** change `@`, `www`, MX, or email records (those are the public website)

Wait, then run `Resolve-DnsName` again.

**SSL — only after A record is `72.62.4.115`**

```bash
certbot --nginx -d codecamp.codeacademyug.org
```

Then set `APP_URL` and `SESSION_SECURE_COOKIE=true` as in section 5.

---

## 7. Services

```bash
systemctl is-active nginx php8.3-fpm mysql
systemctl status nginx --no-pager -l
systemctl reload nginx
systemctl restart php8.3-fpm
nginx -t
```

---

## 8. Logs and health

```bash
cd /var/www/codecamp
tail -n 80 storage/logs/laravel.log
ls -lh /var/www/codecamp
php artisan about
curl -I http://127.0.0.1/up
```

Nginx error log:

```bash
tail -n 50 /var/log/nginx/error.log
```

---

## 9. Database (VPS MySQL only)

```bash
mysql -e "SHOW DATABASES;"
mysql codecamp -e "SHOW TABLES;" | head
mysql codecamp -e "SELECT COUNT(*) AS users FROM users;"
```

Backup:

```bash
mysqldump codecamp > /root/codecamp-$(date +%F).sql
ls -lh /root/codecamp-*.sql
```

Import a dump (this overwrites data — be sure):

```bash
mysql codecamp < /root/codecamp-old.sql
cd /var/www/codecamp
php artisan migrate --force
php artisan config:clear
```

---

## 10. First-time stack (already done — keep for rebuild)

Only if the server is wiped and you must start over.

```bash
apt update && apt upgrade -y
apt install -y nginx mysql-server unzip git curl composer \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd \
  php8.3-sqlite3 php8.3-readline php8.3-opcache
```

Node (for `npm run build`):

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

App:

```bash
mkdir -p /var/www
cd /var/www
git clone https://github.com/geoffkats/codecamp-management-platform.git codecamp
cd codecamp
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
npm ci
npm run build
```

Then edit `.env` (WinSCP), create the MySQL user, `php artisan migrate --force`, point nginx `root` at `/var/www/codecamp/public`, `chown` storage, `systemctl reload nginx`.

---

## 11. Do not

- Do not `git push --force` to `main`
- Do not commit `.env`
- Do not run `php artisan db:seed` with no class on production
- Do not set `SESSION_SECURE_COOKIE=true` while using `http://`
- Do not point this VPS at the old shared-hosting database
- Do not change DNS for `@` / `www` unless you mean to move the marketing site too
- Do not run `certbot` until `codecamp.codeacademyug.org` resolves to `72.62.4.115`
