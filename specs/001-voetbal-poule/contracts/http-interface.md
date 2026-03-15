# HTTP Interface Contract

## Contract Style

- Server-side rendered HTML for GET requests.
- Standard HTML form posts for mutating actions.
- Redirect-after-post for successful mutations.
- Validation failures return the same page with field-level and summary errors.
- Authorization is enforced server-side on every route.
- Every mutating request requires a valid CSRF token.

## Public Routes

| Method | Path | Auth | Purpose | Response Contract |
|--------|------|------|---------|-------------------|
| GET | `/` | Guest | Public landing page with active competitions | HTML page showing name, logo, description, dates, paid-participant count, entry totals, and prize distribution |
| GET | `/competitions/{slug}` | Guest | Public competition detail | HTML page with competition metadata, sections overview, and links to standings/results |
| GET | `/competitions/{slug}/results` | Guest | Public result overview | HTML page listing matches, results, groups, and venue information |
| GET | `/competitions/{slug}/standings` | Guest | Main standings | HTML page rendered from latest snapshot rows |
| GET | `/competitions/{slug}/sub-competitions/{subSlug}/standings` | Guest | Sub-competition standings | HTML page rendered from latest sub-competition snapshot rows |

## Authentication Routes

| Method | Path | Auth | Purpose | Request Contract | Success Contract |
|--------|------|------|---------|------------------|------------------|
| GET | `/register` | Guest | Registration form | N/A | SSR form |
| POST | `/register` | Guest | Create participant account | `first_name`, `last_name`, `email`, `phone_number`, `password`, `csrf_token` | Redirect to login or participant dashboard |
| GET | `/login` | Guest | Login form | N/A | SSR form |
| POST | `/login` | Guest | Authenticate user | `email`, `password`, `csrf_token` | Session established, redirect to intended page |
| POST | `/logout` | Participant/Admin | End session | `csrf_token` | Session destroyed, redirect to `/` |

## Participant Prediction Routes

| Method | Path | Auth | Purpose | Request Contract | Success Contract |
|--------|------|------|---------|------------------|------------------|
| GET | `/dashboard` | Participant/Admin | Personal competition overview | N/A | SSR page with competition links and payment indicators |
| GET | `/competitions/{slug}/prediction` | Participant/Admin | Prediction entry/review page | N/A | SSR form before deadline; read-only page after final submission or deadline |
| POST | `/competitions/{slug}/prediction/submit` | Participant/Admin | Final submission only | Full active-section payload + `csrf_token` | Transactional save of complete submission, redirect to read-only confirmation page |

## Participant Submission Rules

- The server must reject submissions when any required answer for an active section is missing.
- The server must reject submissions after the competition deadline even if the user bypasses the UI.
- The server must allow unpaid participants to submit while clearly marking them as unpaid elsewhere in the UI.
- The server must store the submission as one immutable record set; partial saves are not allowed.

## Administrator Routes

| Method | Path | Purpose | Request Contract | Success Contract |
|--------|------|---------|------------------|------------------|
| GET | `/admin` | Admin dashboard | N/A | SSR overview |
| GET | `/admin/competitions` | Competition listing | N/A | SSR list |
| GET | `/admin/competitions/create` | New competition form | N/A | SSR form |
| POST | `/admin/competitions` | Create competition | Competition fields + `csrf_token` | Redirect to edit page |
| GET | `/admin/competitions/{id}/edit` | Edit competition | N/A | SSR form |
| POST | `/admin/competitions/{id}` | Update competition | Competition fields + `csrf_token` | Redirect to edit page with success message |
| POST | `/admin/competitions/{id}/sections` | Update section activation/order | Section configuration + `csrf_token` | Redirect back |
| POST | `/admin/competitions/{id}/rules` | Update scoring rules | Rule payload + `csrf_token` | Redirect back |
| POST | `/admin/competitions/{id}/participants/{participantId}/payment` | Mark payment status | `payment_status`, `csrf_token` | Redirect back |
| POST | `/admin/competitions/{id}/results/{matchId}` | Save or update match result | Result payload + `csrf_token` | Persist result and trigger standings recomputation |
| POST | `/admin/competitions/{id}/recalculate` | Manual recalculation + snapshot | `csrf_token` | Recompute and create new snapshot |
| POST | `/admin/competitions/{id}/imports/entities` | Import CSV entities | Multipart upload + `entity_type` + `csrf_token` | Either full commit or full failure with row diagnostics |
| POST | `/admin/sub-competitions` | Create/update sub-competition | Name, parent competition, members, `csrf_token` | Redirect to sub-competition detail |
| POST | `/admin/users/{id}/role` | Change user role | `role`, `csrf_token` | Reject if action would remove/deactivate the last active admin |
| POST | `/admin/users/{id}/status` | Activate/deactivate user | `is_active`, `csrf_token` | Reject if action would remove/deactivate the last active admin |

## Error Contract

- Validation errors return HTTP 422 semantics at the application level, but can still be rendered as SSR HTML with previous input preserved.
- Authorization failures return HTTP 403 or redirect to login for anonymous users.
- Not-found resources return HTTP 404 pages.
- Deadline, integrity, and invariant violations return domain-specific error messages suitable for SSR rendering.

## Non-Functional Contract

- Public standings pages must read from the latest snapshot and not perform full live recomputation.
- Every POST route must be protected by CSRF and session-authenticated where applicable.
- All output originating from user or admin input must be HTML-escaped before rendering.
