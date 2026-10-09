# Deployment Guide (Production VPS)

**Last updated:** 6 October 2026
**Server:** Ubuntu 24.04, Nginx, PHP 8.3-FPM, MySQL, Node 22
**App folder:** `/var/www/codecamp`
**Web server user:** `www-data`
**Database / DB user:** `codecamp` / `codecamp` (password is `DB_PASSWORD` in `/var/www/codecamp/.env`)

---

## 1. Before you deploy

On your PC:

1. Make sure the tests pass: `php artisan test`
2. Commit and push to `main`.
3. Check whether the release contains migrations:
   ```bash
   git diff --stat <last-deployed-commit>..HEAD -- database/migrations
   ```
   If it does, the database backup in step 2 below is mandatory.

Deploy when few students are online. The site is in maintenance mode for roughly one to two minutes.

---

## 2. Deploy

SSH into the server:

```bash
ssh root@<vps-ip>
cd /var/www/codecamp
```

Back up the database (asks for `DB_PASSWORD`; nothing shows while typing):

```bash
mysqldump --no-tablespaces -u codecamp -p codecamp > ~/codecamp-$(date +%F-%H%M).sql
ls -lh ~/codecamp-*.sql   # must be tens of MB, not 0 bytes
```

Deploy:

```bash
php artisan down --retry=60

git pull origin main

composer install --no-dev --optimize-autoloader
php artisan migrate --force

npm ci
npm run build

php artisan optimize:clear
php artisan optimize
php artisan queue:restart

sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache

php artisan up
```

Why each step matters:

| Step | Why |
|------|-----|
| `npm ci` + `npm run build` | `public/build` is git-ignored, so CSS/JS must be built on the server. Skipping it leaves new pages unstyled. |
| `migrate --force` | Required in production; without `--force` Laravel refuses to run migrations. |
| `optimize` | Caches config, routes, events and views. Run `optimize:clear` first so stale caches never survive a deploy. |
| `chown` | Commands run as root create root-owned cache files that `www-data` cannot overwrite, which causes 500 errors later. |
| `queue:restart` | Makes queue workers load the new code. Harmless if no workers run. |

---

## 3. One-off steps for specific releases

Only run these when the release notes call for them.

### Question-set snapshots (October 2026 question bank release)

Attempts created before snapshots existed need their question set saved. It never changes scores, answers or status. Always dry-run first:

```bash
sudo -u www-data php artisan assessments:snapshot-attempts --dry-run
sudo -u www-data php artisan assessments:snapshot-attempts
```

"Flagged" attempts could not be reconstructed exactly, usually because a trainer edited or deleted questions after the attempt. Their scores stand; only the review screen may differ from what the student originally saw. A second dry run should report `No attempts need a question-set snapshot.`

Status: run on 6 October 2026 (2,168 attempts snapshotted, 293 flagged).

---

## 4. Check the release

In the browser:

- [ ] Log in as a supervisor: Students, Code Camps and Content Approval open without a 403.
- [ ] Log in as a trainer: dashboard, Assessments and Question Bank load.
- [ ] Log in as a student: dashboard shows badges as icons and the Continue button opens the course.
- [ ] Pages are styled (if not, the asset build did not run).

On the server:

```bash
php artisan migrate:status | tail -5
tail -n 50 storage/logs/laravel.log
```

---

## 5. Rollback

**Code only (no migrations in the release):**

```bash
php artisan down --retry=60
git log --oneline -5                 # find the previous good commit
git reset --hard <good-commit>
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan optimize:clear && php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
php artisan up
```

**Code and database:** restore the backup taken in step 2. This overwrites all data written since the backup, so use it only as a last resort.

```bash
php artisan down --retry=60
mysql -u codecamp -p codecamp < ~/codecamp-YYYY-MM-DD-HHMM.sql
# then roll back the code as above
php artisan up
```

---

## 6. Troubleshooting

| Symptom | Fix |
|---------|-----|
| 500 error right after deploy | `tail -n 100 storage/logs/laravel.log`; usually permissions, so rerun the `chown`/`chmod` lines. |
| Pages unstyled or old design | `npm ci && npm run build`, then hard refresh the browser. |
| `EBADENGINE` warnings during `npm ci` | Node is too old. Vite 7 needs Node 20.19+ (server runs Node 22). Check with `node -v`. |
| A role gets 403 on a sidebar link | Check the route's `can:` gate in `routes/web.php` and the gate in `AppServiceProvider`. `tests/Feature/SidebarAccessTest.php` catches these. |
| Changes to `.env` not taking effect | `php artisan optimize:clear && php artisan optimize` |
| `mysqldump: Access denied ... PROCESS privilege` | Add `--no-tablespaces` (already in the command above). |
| Composer warns about running as root | Safe to ignore for `install --no-dev`; or run it as `sudo -u www-data composer install ...`. |

---

## 7. Server tools

### phpMyAdmin (private)

Installed on the server but only listens on `127.0.0.1:8081`, so it is not reachable from the internet. Open it through an SSH tunnel from your PC:

```powershell
ssh -L 8081:127.0.0.1:8081 root@<vps-ip>
```

Keep that window open and browse to `http://localhost:8081`. Log in as `codecamp` with `DB_PASSWORD`.

Edits in phpMyAdmin hit the live database immediately and cannot be undone. Take a backup first. `mysqldump` is a shell command; it does not work in phpMyAdmin's SQL box (use the **Export** tab there instead).

Nginx config: `/etc/nginx/sites-available/phpmyadmin`.

### Copy a backup to your PC

From PowerShell on your PC:

```powershell
scp root@<vps-ip>:/root/codecamp-YYYY-MM-DD-HHMM.sql $HOME\Desktop\
```

### Node

Installed from NodeSource (Node 22). To upgrade to a newer major version later:

```bash
curl -fsSL https://deb.nodesource.com/setup_24.x -o /tmp/nodesource_setup.sh
sudo bash /tmp/nodesource_setup.sh
sudo apt install nodejs
cd /var/www/codecamp && rm -rf node_modules && npm ci && npm run build
```

### Kernel updates

When SSH shows `*** System restart required ***`, run `sudo reboot` at a quiet time. The site comes back on its own in about a minute.

---

## 8. Production `.env` essentials

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

Never commit `.env`. After editing it, run `php artisan optimize:clear && php artisan optimize`.

---

## Deployment log

| Date | Commit | Notes |
|------|--------|-------|
| 2026-10-06 | `b469538` | Question bank, attempt snapshots, help manual, dashboard redesigns, supervisor access fixes. Three migrations; snapshot command run. Node upgraded 18 → 22. phpMyAdmin installed (private). |
