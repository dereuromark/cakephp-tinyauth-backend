# Strategies

These guides describe the supported ways to use TinyAuthBackend in a real app.
They form a ladder: start at the bottom and climb only as far as your project
needs.

## Available strategies

| Strategy | Runtime auth | Backend role |
|----------|--------------|--------------|
| [Adapter-Only](/strategies/adapter-only) | TinyAuth | Edit `allow` / `acl` rules stored in the DB |
| [Full Backend](/strategies/full-backend) | TinyAuth + Authorization | Single source of truth for request and entity authorization |
| [Native CakePHP Auth](/strategies/native-auth) | cakephp/authentication + authorization | Permission admin UI; you consume the data |
| [External Role Source](/strategies/external-roles) | Any | Roles come from outside; ACL/resources still in the backend |

## Quick descriptions

- **[Adapter-Only TinyAuth](/strategies/adapter-only)** — keep classic TinyAuth
  `allow` / `acl` behavior and move rule storage from INI files to database-backed
  adapters.
- **[Full TinyAuthBackend](/strategies/full-backend)** — use the full backend:
  roles, ACL, resources, scopes, hierarchy, and CakePHP Authorization
  integration.
- **[Native CakePHP Auth](/strategies/native-auth)** — keep CakePHP
  Authentication / Authorization as the runtime layer and use TinyAuthBackend
  mainly as a permission admin UI.
- **[External Role Source](/strategies/external-roles)** — read roles from app
  config or a callback while keeping backend-managed ACL / resource assignments
  usable.

::: tip Not sure which to pick?
If you already use TinyAuth INI files today, start with
[Adapter-Only](/strategies/adapter-only). If you want entity-level checks like
"users can edit only their own records", you want
[Full Backend](/strategies/full-backend).
:::
