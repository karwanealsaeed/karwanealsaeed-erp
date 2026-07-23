# Final UAT Checklist

This checklist is the final user-acceptance gate. An automated pass does not replace browser verification or owner sign-off.

## A. Public website

- [ ] Home, services, promotions, tours, gallery, testimonials, FAQ and blog render correctly.
- [ ] English and Urdu switch correctly, including RTL presentation.
- [ ] Company identity, phone, address and WhatsApp links are consistent.
- [ ] SEO metadata, canonical URLs, hreflang, sitemap and robots are correct.
- [ ] Privacy, terms, disclaimer, refund and cancellation pages are approved.
- [ ] PWA manifest, icons, service worker and offline page work.
- [ ] Public Karwan AI stays within public-information scope.

## B. Customer and agent portal

- [ ] Login, logout, rate limits and deactivated-account handling work.
- [ ] Profile and password changes work safely.
- [ ] Each account sees only its own bookings, invoices, payments, documents and tickets.
- [ ] Private downloads reject guests and other customers.
- [ ] Payment upload, family members, notifications and support tickets work.
- [ ] Travel-insurance policy and receipt downloads are correctly scoped.

## C. Staff ERP and permissions

- [ ] Each of the ten proposal labels is tested through its mapped base role and permission bundle.
- [ ] Super Admin bypass is restricted.
- [ ] Director is read-only and has no user/system/restore rights.
- [ ] Departmental managers cannot access unrelated modules.
- [ ] Customer Support has no executive finance or delete rights.
- [ ] Inactive staff cannot access the panel.
- [ ] Activity and login logs record expected events.

## D. Travel operations

- [ ] Customer, supplier, agent, country, city, airport, airline and hotel masters are valid.
- [ ] Visa, ticketing, Umrah, hotel and insurance create/edit workflows work.
- [ ] Package, group-flight, transport, voucher and payment links are correct.
- [ ] Passport, visa and insurance expiry alerts are accurate.
- [ ] Documents remain private.

## E. Accounting

- [ ] Draft voucher creation works.
- [ ] Unbalanced posting shows visible validation and creates no ledger rows.
- [ ] Balanced posting creates exactly balanced general-ledger rows.
- [ ] Posted records respect immutability and period locks.
- [ ] Linked reversal works and preserves the original.
- [ ] Bank master and reconciliation show the correct difference.
- [ ] Trial balance, profit and loss, balance sheet, cash/bank book and party statements reconcile.
- [ ] No UAT/demo/orphan accounting rows remain.

## F. HR and reports

- [ ] HR masters, staff, attendance, leave, documents, ledgers, payroll and performance work.
- [ ] HR Reports requires the corrected `staff_view` and `reports_view` contract.
- [ ] Payroll printing and report exports respect permissions.
- [ ] Sensitive HR data is unavailable to Accountant and Sales Staff unless explicitly authorized.

## G. Backups, restore and operations

- [ ] A fresh local backup is completed and integrity verified.
- [ ] The off-server/Google Drive copy matches expected size and checksum.
- [ ] Restore readiness is reviewed; destructive restore is not tested casually.
- [ ] Scheduler heartbeat, queues, cron jobs and SMTP are healthy.
- [ ] Error monitoring and system-health checks are clear.
- [ ] Production `.env`, HTTPS, cache, sessions and storage permissions are reviewed.

## H. Final sign-off

- [ ] Full automated regression passes on the approved commit.
- [ ] Cross-module browser UAT evidence is attached.
- [ ] Known defects are fixed and retested.
- [ ] Deployment and rollback plan is approved.
- [ ] Project owner explicitly authorizes production deployment.

**Live deployment is not authorized until every applicable item is checked and signed.**