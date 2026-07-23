# Deployment Guide

## 1. Deployment policy

Deployment is the final project phase. Do not deploy while the master scope checklist, final browser UAT, production security review or documentation sign-off contains an open blocker. **Live deployment is not authorized until explicit owner approval is recorded.**

## 2. Production prerequisites

Use a supported PHP 8.3 environment, Composer, a supported MySQL/MariaDB database, Node-built production assets, HTTPS, cron access, private storage outside public web paths where possible, SMTP credentials and an off-server backup destination.

Create production credentials separately. Never copy local passwords, OAuth tokens or test data into shared notes or source control.

## 3. Pre-deployment checks

1. Confirm the approved branch and commit.
2. Confirm `git status` is clean.
3. Run the full automated test suite.
4. Review `.env` values for production, debug mode, app URL, database, mail, queue, cache, session and backup configuration.
5. Create and verify a fresh backup.
6. Confirm no UAT, demo or orphan accounting records remain.
7. Confirm company identity, contact information, legal pages and bilingual content.

## 4. cPanel-style release sequence

1. Upload or pull the approved source outside the public document root.
2. Point the domain document root to the Laravel `public` directory.
3. Install production dependencies with `composer install --no-dev --optimize-autoloader`.
4. Provide the reviewed production `.env`.
5. Run `php artisan key:generate` only for a new installation.
6. Run `php artisan migrate --force` after a verified backup.
7. Run `php artisan storage:link` only when the production storage design requires it; sensitive documents must remain private.
8. Build or upload production assets.
9. Run `php artisan optimize:clear`, then production cache commands after validation.
10. Set writable permissions for `storage` and `bootstrap/cache` without granting broad public write access.

## 5. Scheduler, queue and mail

Configure cron to run Laravel scheduling every minute. Configure a supervised queue worker or a hosting-compatible recurring worker. Verify scheduler heartbeat, queued notifications, passport/visa/insurance alerts, task reminders, backup jobs and SMTP delivery.

## 6. Smoke tests

Test the public website, Urdu switch, sitemap, robots, legal pages, portal login, staff login, permission boundaries, one non-destructive record view per module, private file download authorization, reports, backup status, error monitoring and Karwan AI scope.

## 7. Rollback

Stop user writes if integrity is at risk. Record the failing commit and error. Restore the previous approved source, clear caches and roll back only migrations whose rollback contract is verified. For data corruption, use the documented restore procedure and preserve a pre-restore safety backup.

## 8. Post-deployment sign-off

Record deployed commit, migration status, backup identifier, scheduler/queue health, SMTP result, smoke-test result and approver. Keep deployment unauthorized until every sign-off field is complete.