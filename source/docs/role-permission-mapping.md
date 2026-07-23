# Role and Permission Mapping

## 1. Architecture

The ERP keeps four staff base roles: Admin, Manager, Accountant and Sales Staff. Proposal labels are implemented as documented mappings with explicit permissions. Specialized Manager mappings do **not** inherit the broad Manager preset; their explicit bundle must be assigned. Customer is a separate portal identity.

The executable contract is `App\Support\ProposalRoleMapping`.

## 2. Proposal labels

| Proposal label | Base role | Permission mode | Bundle |
|---|---|---|---|
| Super Admin | Admin | Admin bypass | No stored bundle required |
| Director | Manager | Explicit | 30 read-only/executive permissions |
| Accountant | Accountant | Existing default | Tested Accountant preset |
| Ticketing Manager | Manager | Explicit | 35 ticketing permissions |
| Visa Manager | Manager | Explicit | 23 visa permissions |
| Umrah Manager | Manager | Explicit | 39 Umrah permissions |
| HR Manager | Manager | Explicit | 45 HR permissions plus `reports_view` |
| Marketing Manager | Manager | Explicit | 24 website-content permissions |
| Customer Support | Sales Staff | Explicit | 14 customer/support/task permissions |
| Customer | None | Portal identity | No staff permission bundle |

## 3. Safety policy

Director has no create, edit, delete, approve, manage, restore or system-setting permission. Ticketing, Visa, Umrah and Customer Support bundles have no delete permission. HR Manager has explicit HR deletion rights because HR lifecycle ownership is part of the role. Marketing Manager has explicit content deletion rights for the six website-content resources. Those deletion rights must be documented in the user approval record.

Super Admin must be assigned only to the principal administrator. Customer must never be stored as a staff role.

## 4. HR Reports correction

The HR Reports page is a Manager-role page whose central permission list is `staff_view` and `reports_view`. Legacy `users_view` and `users_manage` must not grant HR Reports access. HR Manager's explicit mapping includes all dedicated HR permissions and `reports_view`.

## 5. Assignment procedure

1. Select the proposal label approved for the staff member.
2. Read its mapping from `ProposalRoleMapping`.
3. Store the mapped base role.
4. For `explicit` mode, store exactly the mapped permission list and do not merge the Manager default.
5. For Accountant, use the tested Accountant default.
6. For Super Admin, verify administrative approval.
7. Re-authenticate and test the user's permitted and denied modules.

## 6. Change control

Permission changes require a reviewed code change, tests and documentation update. Do not add broad wildcard permissions. Do not grant delete, restore, settings or user-management access through an unrelated departmental bundle.