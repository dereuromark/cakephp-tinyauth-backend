# Admin Access

This plugin ships an admin UI at `/admin/auth`. Because it manages authorization
rules, accidental exposure is **RCE-equivalent** — an attacker who reaches the UI
can grant themselves access to anything.

::: danger Fails closed by default
Regardless of `debug` mode, every request to the admin UI is rejected with `403`
until the host app explicitly configures a gate. There is no implicit "open in
dev" default.
:::

The plugin expects the host app to decide who may manage `/admin/auth`.

## Configuring access

Set `TinyAuthBackend.adminAccess` to a `Closure` that receives the current
request and returns literal `true` to grant access. Anything else (unset,
non-`Closure`, returns `false`, returns a truthy non-bool, or throws) yields a
`403`.

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

## Opting into open access in development

For a local-only environment you may explicitly opt in to wide-open access in
dev, but this is no longer the implicit default:

```php
if (Configure::read('debug') === true) {
    Configure::write('TinyAuthBackend.adminAccess', fn () => true);
}
```

## Deprecated: `TinyAuthBackend.editorCheck`

The legacy `TinyAuthBackend.editorCheck` callable (signature
`function ($identity, $request): bool`) is still honored when `adminAccess` is
unset, but is **deprecated** and emits a deprecation warning. Migrate by:

1. Renaming the key from `editorCheck` to `adminAccess`.
2. Dropping the `$identity` parameter — fetch it via
   `$request->getAttribute('identity')` inside your `Closure`.

::: tip Precedence
If both keys are configured, `adminAccess` wins; `editorCheck` is ignored.
:::

## Next steps

- [Strict CSP](/guide/strict-csp) — run the admin UI under a strict
  Content-Security-Policy header.
- [Feature Flags](/guide/feature-flags) — choose which backend sections appear.
