# Public Actions

This page is about the backend's **Allow** feature: actions that should be
reachable without authentication.

::: info Managing vs. understanding
This page covers the runtime concept and the TinyAuth wiring. To toggle actions
in the UI, see [Allow (Public Actions)](/permissions/allow).
:::

## What it controls

The normalized backend stores public action flags in:

- `tinyauth_controllers`
- `tinyauth_actions`

An action is public when `tinyauth_actions.is_public = true`.

## Runtime use

If you use TinyAuth at runtime, point it to the DB allow adapter:

```php
'TinyAuth' => [
    'allowAdapter' => \TinyAuthBackend\Auth\AllowAdapter\DbAllowAdapter::class,
],
```

`DbAllowAdapter` reads public actions from the database and feeds them into
TinyAuth's request-level allow logic.

## Backend UI

Manage public actions at:

```text
/admin/auth/allow
```

You can:

- toggle individual actions
- bulk-toggle all actions for one controller
- sync controllers/actions from code first, then mark public endpoints in the UI

## Migration from legacy INI

If you used TinyAuth's file-based allow rules before, import them once:

```sh
bin/cake tiny_auth_backend import allow
```

## Important note

This page is only about **public request access**.

- For role-based controller/action ACL, see [ACL Matrix](/permissions/acl).
- For entity/resource authorization, see [Resources](/permissions/resources).

::: danger Admin UI access is separate
Configuring public actions does **not** open the admin UI. The admin UI at
`/admin/auth` is gated independently and fails closed — see
[Admin Access](/guide/admin-access).
:::
