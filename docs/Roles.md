## Role Management

The roles section manages aliases, ordering, and optional hierarchy.

### Role Fields

| Field | Meaning |
|-------|---------|
| `name` | Human-readable label |
| `alias` | Runtime role key used by permission checks |
| `sort_order` | Display/order hint |
| `parent_id` | Higher role that this role rolls up into |

### Hierarchy Semantics

This plugin models hierarchy like this:

```text
admin
  └─ moderator
      └─ user
```

Meaning:

- `user.parent_id = moderator`
- `moderator.parent_id = admin`
- higher roles inherit lower-role permissions

So if `user` can `edit Article`, then `moderator` and `admin` also inherit that permission unless they have a direct rule of their own.

Direct rules win over inherited ones.

### External Role Sources

You do not have to manage roles in `tinyauth_roles`.

`TinyAuthBackend.roleSource` can be:

- `null`: use the plugin table
- a Configure path string
- an array of `alias => id`
- a callable returning that array

When an external source is used:

- the roles UI becomes read-only
- the backend still uses those aliases for ACL/resource assignments

### Example Config

```php
'TinyAuthBackend' => [
    'roleSource' => [
        'user' => 1,
        'moderator' => 2,
        'admin' => 3,
    ],
],
```
