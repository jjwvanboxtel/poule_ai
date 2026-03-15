# CSV Import Contract

## Scope

This contract covers administrator-driven bulk import of catalog entities such as players, teams, countries, and referees.

## Upload Endpoint

- **Method**: `POST`
- **Path**: `/admin/competitions/{id}/imports/entities`
- **Content-Type**: `multipart/form-data`
- **Required fields**:
  - `entity_type`
  - `file`
  - `csrf_token`

## Supported Import Modes

| Entity Type | Required Columns | Suggested Uniqueness Rule |
|-------------|------------------|---------------------------|
| `country` | `name`, `short_code`, `is_active` | `short_code` |
| `team` | `name`, `short_code`, `country`, `is_active` | `name` within competition/global scope |
| `player` | `name`, `nationality`, `team`, `is_active` | `name + team` |
| `referee` | `name`, `nationality`, `is_active` | `name + nationality` |

## Processing Rules

1. Parse the full file before inserting anything.
2. Normalize whitespace, casing, booleans, and encodings consistently.
3. Validate every row for:
   - required columns
   - invalid values
   - duplicate rows in the upload itself
   - duplicates against existing records
   - invalid references to inactive or missing related entities
4. If any row fails, reject the entire import.
5. If all rows pass, insert/update the batch in a single transaction.

## Success Response Contract

- Redirect back to the import page or entity list.
- Show:
  - import type
  - number of rows inserted/updated
  - timestamp

## Failure Response Contract

- No row from the file may be committed.
- Return the user to the import page with:
  - a summary error message
  - row numbers that failed
  - per-row reasons
  - duplicate/conflict explanations where applicable

## Security Contract

- Only authenticated administrators may access this endpoint.
- Uploaded files must be size-limited and extension/MIME-validated.
- Temporary uploaded files must be deleted after processing.
- Parsing must treat uploaded content as data only; no formula or script execution.
