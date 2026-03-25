## Authentication And Public Actions

This section is about the backend's **Allow** feature: actions that should be reachable without authentication.

### What It Controls

The normalized backend stores public action flags in:

- `tinyauth_controllers`
- `tinyauth_actions`

An action is public when `tinyauth_actions.is_public = true`.

### Runtime Use

If you use TinyAuth at runtime, point it to the DB allow adapter:

```php
'TinyAuth' => [
    'allowAdapter' => \TinyAuthBackend\Auth\AllowAdapter\DbAllowAdapter::class,
],
```

`DbAllowAdapter` reads public actions from the database and feeds them into TinyAuth's request-level allow logic.

### Backend UI

Manage public actions at:

```text
/admin/auth/allow
```

You can:

- toggle individual actions
- bulk-toggle all actions for one controller
- sync controllers/actions from code first, then mark public endpoints in the UI

### Migration From Legacy INI

If you used TinyAuth's file-based allow rules before, import them once:

```bash
bin/cake tiny_auth_backend import allow
```

### Important Note

This doc is only about **public request access**.

For role-based controller/action ACL use [Acl.md](Acl.md).
For entity/resource authorization use [Resources.md](Resources.md).
