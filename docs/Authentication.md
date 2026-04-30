## Authentication And Public Actions

This section is about the backend's **Allow** feature: actions that should be reachable without authentication.

## Admin UI Access

This plugin ships an admin UI at `/admin/auth`. Because it manages
authorization rules, accidental exposure is **RCE-equivalent** — an
attacker who reaches the UI can grant themselves access to anything.

The plugin therefore **fails closed by default**: regardless of `debug`
mode, every request to the admin UI is rejected with `403` until the host
app explicitly configures a gate.

### Configuring access (`TinyAuthBackend.adminAccess`)

Set `TinyAuthBackend.adminAccess` to a `Closure` that receives the current
request and returns literal `true` to grant access. Anything else (unset,
non-`Closure`, returns `false`, returns a truthy non-bool, or throws)
yields a `403`.

```php
use Cake\Core\Configure;
use Cake\Http\ServerRequest;

Configure::write(
    'TinyAuthBackend.adminAccess',
    function (ServerRequest $request): bool {
        $identity = $request->getAttribute('identity');
        if ($identity === null) {
            return false;
        }

        return (int)($identity->get('role_id') ?? 0) === 3;
    },
);
```

For a local-only environment you may explicitly opt-in to wide-open access
in dev, but this is no longer the implicit default:

```php
if (Configure::read('debug') === true) {
    Configure::write('TinyAuthBackend.adminAccess', fn () => true);
}
```

### Deprecated: `TinyAuthBackend.editorCheck`

The legacy `TinyAuthBackend.editorCheck` callable (signature
`function ($identity, $request): bool`) is still honored when
`adminAccess` is unset, but is **deprecated** and emits a deprecation
warning. Migrate by:

1. Renaming the key from `editorCheck` to `adminAccess`.
2. Dropping the `$identity` parameter — fetch it via
   `$request->getAttribute('identity')` inside your Closure.

If both keys are configured, `adminAccess` wins; `editorCheck` is ignored.

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
