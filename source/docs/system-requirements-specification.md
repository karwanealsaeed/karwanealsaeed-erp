# System Requirements Specification

## 1. Purpose

Karwan E Al Saeed ERP provides one controlled platform for the public travel website, customer and agent self-service, internal travel operations, accounting, HR, reporting, support, backups and permission-aware AI assistance.

## 2. System boundaries

The platform contains a public website, a protected customer/agent portal and a protected staff administration panel. Public users may access published information and safe public Karwan AI answers. Portal users may access only their own records. Staff access is limited by active status, base role and explicit permissions. Cross-customer, cross-agent and unauthorized staff data must never be exposed.

## 3. Actors

The proposal labels are Super Admin, Director, Accountant, Ticketing Manager, Visa Manager, Umrah Manager, HR Manager, Marketing Manager, Customer Support and Customer. Staff labels map to the four implemented base roles Admin, Manager, Accountant and Sales Staff through `App\Support\ProposalRoleMapping`. Customer is a separate portal identity and is not a staff role.

## 4. Functional scope

The required scope includes public pages, English and Urdu content, SEO, sitemap, legal pages, PWA, customer registration and login, customer profile, family members, documents, booking tracking, payments, notifications and support tickets.

Internal modules include customers, agents, suppliers, countries, cities, airports, airlines, global hotels, Umrah hotels, visa types, ticketing, visa, Umrah, hotel bookings, travel insurance, invoices, refunds, expenses, tasks, accounting masters, vouchers, ledgers, reversals, reconciliation, financial reports, HR, payroll, website content, backups, restore controls, audit logs, error monitoring and Karwan AI administration.

## 5. Accounting requirements

Posted vouchers must satisfy double-entry equality. Draft vouchers must not create general-ledger rows. Posted vouchers and lines are immutable in locked periods. Reversals must preserve the original voucher and create a linked mirrored posting. Bank reconciliation must use a verified bank-account master and produce a traceable difference. Sensitive bank identifiers must be redacted in audit logs.

## 6. Security requirements

Authentication, active-status checks, role checks and exact permissions are mandatory. Sensitive files use private storage and authorization-controlled download routes. Public and portal routes require rate limits where appropriate. Secrets must remain outside source control. Logs and AI usage records must not store raw sensitive content unnecessarily.

Karwan AI is local-first. Public AI may use only current public website knowledge and safe basic travel guidance. Portal AI may use only the authenticated party's authorized records. Staff AI must respect the user's permissions. Web-based latest-news capability is admin-only, disabled by default and requires separate implementation and testing.

## 7. Non-functional requirements

The system must be modular, maintainable, mobile responsive, SEO friendly, printable, auditable and suitable for cPanel-style production hosting. Automated tests, controlled migrations, backup verification, activity logging, error monitoring, queue/scheduler health and rollback procedures are mandatory.

## 8. Acceptance and deployment

A feature is complete only when implementation, automated regression, security checks, data integrity and browser UAT are complete. Production deployment is not authorized until the final master audit and owner sign-off are complete.