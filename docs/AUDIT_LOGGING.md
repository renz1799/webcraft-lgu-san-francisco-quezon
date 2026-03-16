# Audit Logging Rules

## Purpose

Audit logs are for accountability and traceability of meaningful business actions.

They are not a full edit history, keystroke log, or replacement for workflow/task timelines.

Use audit logs so a reviewer can answer:

- what happened
- to what record or business object
- who triggered it
- when it happened
- what final payload or outcome was committed

## Scope

Audit logs should prioritize:

- workflow milestones
- lifecycle events
- cross-module or system-generated outcomes
- access and security changes
- master-data CRUD actions that affect future operations

Examples:

- `air.submitted`
- `air.inspection_finalized`
- `ris.issued`
- `wmr.disposed`
- `user.role.changed`
- `stock.adjusted`

## Where Audit Calls Belong

- Keep audit writes in the service layer.
- Record them where the business action is finalized, not in controllers or views.
- Prefer one primary audit record for one business outcome.

## What To Log

Record these kinds of actions:

- submission, approval, rejection, issuance, finalization, disposal, cancellation, reopening
- archive, restore, soft-delete, force-delete when applicable
- system-generated downstream outcomes
  - example: item promoted from AIR into inventory
- profile, permission, role, password, and status changes
- meaningful stock and inventory events
- master-data create, update, archive, and restore actions

## What Not To Log

Do not log low-value churn such as:

- draft header edits
- draft line-item add, update, delete, and bulk edit churn
- repeated corrections while a user is still preparing a draft
- per-field typing history
- lower-level support events when a higher-level audit already explains the accountable business outcome

Example:

- Keep `inventory_item.acquired_from_air`
- Avoid duplicating the same outcome in the global audit feed with a second lower-level event unless it answers a distinct accountability question

## Audit Vs Timeline

Use audit logs for:

- official business milestones
- accountability
- cross-module traceability
- security-sensitive changes

Use task or workflow timelines for:

- in-progress work detail
- draft save context
- operator notes
- repeated working edits during preparation

## Action Rules

- Use stable dot-notation action names.
- Keep action names short and domain-focused.
- Do not encode full prose in `action`.

Examples:

- `air.submitted`
- `inventory_item.acquired_from_air`
- `user.permissions.synced`

## Message Rules

`message` should be optimized for:

- table readability
- search
- exports
- quick SQL or admin review

Every message should stand on its own and include:

- the business event
- the key identifier

Preferred patterns:

- `Event: Identifier`
- `Event for Identifier`

Good examples:

- `AIR submitted for inspection: PO PO-2026-0004`
- `AIR inspection finalized: PO PO-2026-0004`
- `Inventory item acquired from AIR: Lenovo ThinkPad (PPE-001) from PO PO-2026-0004`
- `RIS issued: RIS-2026-0002`
- `Stock adjusted: Bond Paper (OFF-001)`

Avoid vague messages such as:

- `Updated successfully.`
- `AIR finalized.`
- `Inventory event recorded.`

unless there is no stronger identifier available.

## Display Payload Rules

Use `meta.display` for the user-facing audit modal.

Prefer this shape:

- `summary`
- `subject_label`
- `sections`
- `request_details`
- optional `system_notes`

The modal should lead with business-friendly sections, not raw JSON.

Use raw payloads only as fallback or under technical details.

## Payload Snapshot Rules

For milestone actions, capture the final committed payload snapshot that matters at that step.

Examples:

- submission logs should include the submitted item summaries
- issuance/finalization logs should include item counts and included items
- inventory promotion logs should include resulting item context
- AIR finalization should include accepted items and component details when components exist

When there are many items:

- show a readable summary in the modal
- cap the visible list if needed
- keep raw technical payloads in technical details

## Subject Rules

- Keep `subject_type` and `subject_id` for internal traceability and polymorphic linking.
- Do not depend on raw `subject_type` for user-facing meaning.
- User-facing meaning should come from `message`, `action`, and `meta.display.subject_label`.

## Duplicate Event Rules

Avoid double-logging the same outcome at different layers unless each record has a distinct purpose.

Preferred rule:

- global audit feed should favor the business-level event
- lower-level support events should only remain if they add separate accountability value

## Testing Rules

For high-value audit writers, test:

- action name
- message content
- presence of structured display payload
- important item or lifecycle summaries

## Review Checklist

Before adding a new audit write, confirm:

1. Is this a meaningful milestone, lifecycle event, cross-module outcome, or security/master-data change?
2. Is this better suited for a task timeline instead of the audit feed?
3. Does the message contain a useful business identifier?
4. Will the modal show a readable payload snapshot?
5. Are we accidentally duplicating a higher-level event that already explains the same outcome?

If the answer to `2` is yes, prefer timeline logging.

