# Getting started

Complete [installation](./installation.md) first. This walkthrough opens the local admin interface and creates its first role. [TinyAuth's documentation](https://dereuromark.github.io/cakephp-tinyauth/) explains the request authorization layer used by the DB adapters.

## Configure local admin access

Merge this into `config/app_local.php` for a local application:

```php
'TinyAuthBackend' => [
    'adminAccess' => static function (\Cake\Http\ServerRequest $request): bool {
        return \Cake\Core\Configure::read('debug') === true
            && in_array($request->clientIp(), ['127.0.0.1', '::1'], true);
    },
],
```

The plugin denies access without a gate, including in debug mode. This example permits local requests while debug is enabled. Replace it with an application identity and permission check before allowing remote administration; see [Admin access](./admin-access.md).

## Open the dashboard and create a role

Open `/admin/auth` through your local application. Open **Roles** and add a role with the alias `admin` and name `Admin`. Role IDs belong to the backend database; use the saved ID when assigning that role to users.

Synchronize controllers and actions:

```bash
bin/cake tiny_auth_backend sync controllers
```

The ACL page now lists discovered controllers and actions. To create initial backend ACL entries for the saved role:

```bash
bin/cake tiny_auth_backend init admin
```

This command reads backend roles and writes ACL entries. The `adminAccess` gate still decides who can open the admin interface.

## Connect rules to your application

The admin UI edits database rules. Your runtime authorization layer must read those rules for them to affect application requests.

If your application already uses TinyAuth, merge these adapter settings into its configuration:

```php
'TinyAuth' => [
    'allowAdapter' => \TinyAuthBackend\Auth\AllowAdapter\DbAllowAdapter::class,
    'aclAdapter' => \TinyAuthBackend\Auth\AclAdapter\DbAclAdapter::class,
],
```

Keep your TinyAuth user-role configuration aligned with the backend role IDs. [Adapter-only setup](../strategies/adapter-only.md) covers this route; [native CakePHP auth](../strategies/native-auth.md) covers applications using Authentication and Authorization instead.

To migrate INI rules, use the import command after configuring the same role mapping in TinyAuth and the backend. Importing public flags does not disable an application's separate authentication middleware or controller checks.
