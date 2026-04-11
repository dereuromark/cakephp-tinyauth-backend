## Authentication And Public Actions

This section is about the backend's **Allow** feature: actions that should be reachable without authentication.

## Admin UI Access

This plugin also ships an admin UI at `/admin/auth`.

That admin UI is separate from the **Allow** feature documented below.

By default:

- `debug = true`: `/admin/auth` is accessible for local/dev setup convenience
- `debug = false`: `/admin/auth` is denied unless your app sets `TinyAuthBackend.editorCheck`

Configure that callable in your app to decide who may manage TinyAuth rules:

```php
use Cake\Core\Configure;
use Psr\Http\Message\ServerRequestInterface;

Configure::write(
    'TinyAuthBackend.editorCheck',
    function (mixed $identity, ServerRequestInterface $request): bool {
        if ($identity === null) {
            return false;
        }

        return (int)($identity->get('role_id') ?? 0) === 3;
    },
);
```

Treat the debug-mode default as a local-development convenience only.

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
