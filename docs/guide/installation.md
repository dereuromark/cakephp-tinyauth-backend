# Installation

Install the plugin with Composer from your CakePHP project's ROOT directory
(where the `composer.json` file is located):

```sh
composer require dereuromark/cakephp-tinyauth-backend cakephp/migrations:^5
```

This auto-requires the `dereuromark/cakephp-tinyauth` dependency.

## Load the plugin

Load the plugin in your application's `Application::bootstrap()`:

```php
public function bootstrap(): void
{
    parent::bootstrap();

    $this->addPlugin('Migrations');
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

## Continue

Composer declares the supported PHP and CakePHP ranges. The supplied migrations use the Migrations 5 `BaseMigration` API.

Continue with [Getting started](./getting-started.md) to configure local admin access, create a role and synchronize controllers. Existing installations should review the database changes before migrating: the initial migration drops the old `tiny_auth_acl_rules` and `tiny_auth_allow_rules` tables. Preserve and migrate any rules you still need before running it.
