# Installation

Install the plugin with Composer from your CakePHP project's ROOT directory
(where the `composer.json` file is located):

```sh
composer require dereuromark/cakephp-tinyauth-backend
```

This auto-requires the `dereuromark/cakephp-tinyauth` dependency.

## Load the plugin

Load the plugin in your application's `Application::bootstrap()`:

```php
public function bootstrap(): void
{
    parent::bootstrap();

    $this->addPlugin('TinyAuthBackend');
}
```

## Run the migrations

The plugin ships its database tables as migrations. Run them with the
[cakephp/migrations](https://github.com/cakephp/migrations) plugin:

```sh
bin/cake migrations migrate -p TinyAuthBackend
```

This creates the backend tables:

- `tinyauth_roles`
- `tinyauth_controllers`
- `tinyauth_actions`
- `tinyauth_acl_permissions`
- `tinyauth_resources`
- `tinyauth_resource_abilities`
- `tinyauth_scopes`
- `tinyauth_resource_acl`

## Sync controllers and actions

Before the backend can edit rules, it needs to know which controllers and
actions exist. Run the sync command once after install (and again after adding
new controllers):

```sh
bin/cake tiny_auth_backend sync
```

This is the CLI equivalent of clicking **Sync** in `/admin/auth/sync`. It walks
your application (and plugins), writes discovered rows into
`tinyauth_controllers` / `tinyauth_actions`, and is idempotent — re-running it
never clobbers existing grants.

You can scope the sync to controllers or resources only:

```sh
bin/cake tiny_auth_backend sync controllers
bin/cake tiny_auth_backend sync resources
```

## Initialize backend access

To grant a role access to the backend out of the box, initialize it for an
admin role:

```sh
bin/cake tiny_auth_backend init admin
```

## Import existing INI files

If you previously used TinyAuth's file-based rules, import them once:

```sh
bin/cake tiny_auth_backend import allow
bin/cake tiny_auth_backend import acl
```

## Next steps

::: warning Configure access before going live
The admin UI fails closed. Set an access gate before you rely on it — see
[Admin Access](/guide/admin-access).
:::

- [Admin Access](/guide/admin-access) — gate who may reach `/admin/auth`.
- [Strategies](/strategies/) — wire the backend into your runtime auth.
