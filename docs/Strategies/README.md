## Strategies

These guides describe the supported ways to use TinyAuthBackend in a real app.

### Available Strategies

- [Adapter-Only TinyAuth](AdapterOnly.md)
  Keep classic TinyAuth `allow`/`acl` behavior and move rule storage from INI files to database-backed adapters.
- [Full TinyAuthBackend](FullBackend.md)
  Use the full backend: roles, ACL, resources, scopes, hierarchy, and CakePHP Authorization integration.
- [Native CakePHP Auth](NativeAuth.md)
  Keep CakePHP Authentication/Authorization as the runtime layer and use TinyAuthBackend mainly as a permission admin UI.
- [External Role Source](ExternalRoles.md)
  Read roles from app config or a callback while keeping backend-managed ACL/resource assignments usable.
