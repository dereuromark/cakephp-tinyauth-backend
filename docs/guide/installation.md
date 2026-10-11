# Installation

Install the plugin with Composer from your CakePHP project's ROOT directory
(where the `composer.json` file is located):

```sh
composer config minimum-stability dev
composer config prefer-stable true
composer require dereuromark/cakephp-tinyauth-backend:dev-cake6 cakephp/migrations:dev-fix/6.x-cakephp-compatibility
```

This branch requires PHP 8.5 and CakePHP 6. It requires the `cake6` branch of TinyAuth and the CakePHP 6 compatibility branch of Migrations. Configure development stability in the application because dependency stability flags are not inherited.

## Load the plugin

If Migrations is already loaded by your application, add only TinyAuthBackend. Otherwise load both plugins in your application's `Application::bootstrap()`:

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

Composer declares the supported PHP and CakePHP ranges. The supplied migrations use `Migrations\BaseMigration`, available from Migrations 4.5. Composer selects a compatible version for your CakePHP installation.

Continue with [Getting started](./getting-started.md) to configure local admin access, create a role and synchronize controllers. Existing installations should review the database changes before migrating: the initial migration drops the old `tiny_auth_acl_rules` and `tiny_auth_allow_rules` tables. Export those tables before migrating and manually transfer any rules you still need into the normalized schema; the INI importer does not import those legacy database tables.
