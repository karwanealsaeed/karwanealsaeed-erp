# Karwan E Al Saeed ERP

Karwan E Al Saeed ERP is a modular Laravel and Filament platform for the public travel website, customer/agent portal, travel operations, accounting, HR, reporting, backups, audit trails and permission-aware Karwan AI.

## Current status

The application is under controlled completion and regression testing on the `website-modules-v1` branch. Passing automated tests do not by themselves authorize production deployment. Live deployment remains blocked until the master scope audit, cross-module browser UAT, production configuration, backup/restore verification and final sign-off are complete.

## Core modules

- Public website, bilingual content, SEO, legal pages and PWA
- Customer and agent portal
- Ticketing, visa, Umrah, hotel and travel-insurance operations
- Customer, supplier, agent, airline, airport, city, hotel and bank masters
- Double-entry accounting, ledgers, reconciliation, reports and reversals
- HR, attendance, leave, payroll, staff documents and performance records
- Marketing content, promotions, tasks, alerts and support tickets
- Local-first, permission-aware Karwan AI
- Database backup, Google Drive upload, restore safety and activity logging

## Local development

1. Install PHP, Composer, Node.js and a supported database.
2. Copy `.env.example` to `.env` and configure local credentials.
3. Run `composer install`, `npm install`, `php artisan key:generate` and `php artisan migrate`.
4. Build assets with `npm run build`.
5. Run `php artisan test` before and after every controlled change.

Never point PHPUnit at the live ERP database. Never commit `.env`, OAuth tokens, database dumps, account numbers, IBANs or other secrets.

## Documentation

- [System Requirements Specification](docs/system-requirements-specification.md)
- [Deployment Guide](docs/deployment-guide.md)
- [Backup and Restore Guide](docs/backup-restore-guide.md)
- [Admin User Manual](docs/admin-user-manual.md)
- [Customer and Agent User Guide](docs/customer-agent-user-guide.md)
- [Final UAT Checklist](docs/final-uat-checklist.md)
- [Role and Permission Mapping](docs/role-permission-mapping.md)
- [Karwan AI Final UAT](docs/karwan-ai-final-uat.md)

## Deployment gate

Production deployment is allowed only after every item in the final UAT checklist is checked, production environment values are reviewed, scheduler and queue execution are verified, an off-server backup is validated, and the project owner gives explicit sign-off.

**Live deployment is not authorized by this README or by an automated test result.**