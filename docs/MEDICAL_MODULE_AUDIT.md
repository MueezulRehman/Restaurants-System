# Medical Module Audit

## Current state

The repository has no Doctor model, migration, controller, route, or view.

Existing medical and clinic models include `Medicine`, `Appointment`,
`MedicalRecord`, `InsuranceClaim`, `Prescription`, `MedicineBatch`,
`MedicineInteraction`, and `CustomerAllergy`.

## Established conventions

- Tenant-owned models generally use `App\Models\Concerns\BelongsToRestaurant`.
- Controllers resolve the active business with
  `auth()->user()->effectiveRestaurantId()` or the equivalent authenticated
  `User` helper.
- Related IDs are validated with restaurant-scoped `Rule::exists(...)` rules.
- Workflow states are plain string columns validated with `Rule::in(...)` or
  `in:` rules. The project does not use a shared status table or PHP enum for
  these medical workflows.
- Existing statuses include:
  - Appointments: `scheduled`, `confirmed`, `completed`, `cancelled`
  - Insurance claims: `draft`, `submitted`, `approved`, `partially_approved`,
    `rejected`, `paid`
  - Prescriptions: `pending`, `verified`, `used`, `expired`, `rejected`
  - Batch recalls: `issued`, `in_progress`, `completed`, `cancelled`

## Existing route and module structure

Medical routes are Manager routes under the `/manager` prefix. Medicines,
prescriptions, medical records, allergies, interactions, purchases, and
medical reports are grouped under `module:medical`. Controlled medicines,
insurance, follow-up reminders, and appointments use their own module keys.

The consistent initial placement for Doctors is therefore:

- Model: `App\Models\Doctor`
- Controller: `App\Http\Controllers\Admin\DoctorController`
- Views: `resources/views/manager/doctors/`
- Routes: `manager.doctors.*`
- Gate: existing `module:medical`

A separate `doctors` module key would require coordinated module seed,
business-type, navigation, and Super Admin configuration changes.

## Tenant migration note

Medical tables exist in both central migration and tenant-migration paths.
Doctor's migration must follow the active deployment convention and include a
restaurant-owned `restaurant_id` relationship/index.

## Implementation decision still requiring confirmation

The minimum requested Doctor statuses are `pending`, `active`, and `declined`.
The complete status list should be confirmed before implementation so the
migration, validation, UI labels, and transition tests all use the same set.
