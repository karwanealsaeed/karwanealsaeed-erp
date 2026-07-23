# Backup and Restore Guide

## 1. Objectives

Backups protect the ERP database and support controlled recovery. A backup is trusted only when its file exists, size is plausible, SHA-256 integrity is verified and any remote copy matches the local evidence.

## 2. Backup operation

Use the ERP backup service or approved command. Store local backups in private storage. Where configured, upload to the approved Google Drive folder through the connected OAuth account. Do not expose backup paths or provider errors to unauthorized users.

For every backup, record type, database name, start and completion times, file size, checksum, integrity status, remote-upload status and audit activity.

## 3. Retention and off-server copies

Keep a minimum recent retention set and at least one validated off-server copy. Production retention must follow business and legal requirements. Never delete the only known-good backup.

## 4. Verification

Verification must confirm the database identity, file checksum, expected SQL structure and absence of unsafe cross-database statements. A modified or mismatched file is corrupt and must not be restored.

## 5. Restore controls

Restore is a high-risk administrative action. It requires the dedicated restore permission, enabled production configuration, exact confirmation, a filesystem lock and a pre-restore safety backup. Passwords must be supplied through process environment handling rather than command-line disclosure.

## 6. Restore procedure

1. Place the verified backup in private storage.
2. Confirm the target database and maintenance window.
3. Create and verify a pre-restore backup.
4. Review the restore readiness result and audit metadata.
5. Stop conflicting workers and writes where required.
6. Execute the approved restore action.
7. If import fails, run the automatic rollback path.
8. Clear caches, restart workers and run integrity checks.
9. Validate authentication, critical masters, voucher balance, ledger totals and recent records.

## 7. Recovery evidence

Record the operator, backup identifier, target database, safety backup, checksum, start/end time, result, rollback result and post-restore validation. Do not claim recovery success until application and accounting integrity checks pass.