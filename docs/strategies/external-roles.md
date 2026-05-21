# External Role Source Strategy

Use this mode if role aliases and IDs already live outside TinyAuthBackend, for
example in:

- app config
- a custom role service
- another table managed by your app

## Config

```php
'TinyAuthBackend' => [
    'roleSource' => 'Roles',
],
```

Or:

```php
'TinyAuthBackend' => [
    'roleSource' => [
        'user' => 1,
        'moderator' => 2,
        'admin' => 3,
    ],
],
```

## How it works

- `RoleSourceService` reads aliases and IDs from the configured source.
- The roles page becomes read-only.
- External roles are mirrored into `tinyauth_roles` so ACL / resource permission
  rows can still use foreign keys safely.
- The mirror is authoritative: rows not present in the current external role set
  are pruned on sync.
- When a pruned role is still referenced by ACL / resource permission rows, those
  dependent rows are removed by the database foreign-key cascades too.

::: warning Role IDs must be integers
Whatever you put on the right-hand side of `alias => id` must be an integer or a
numeric string. UUIDs and opaque slugs cannot be used as IDs directly — see
[Roles](/permissions/roles#external-role-sources) for the full recipe.
:::

## Identity without cakephp/authentication

External role sources often come with custom identity resolution too — a JWT
claim, an SSO gateway, a session payload written by middleware the app already
owns. If you don't want to pull in `cakephp/authentication` just to satisfy the
Authorization plugin's `IdentityInterface` contract, the plugin ships
`TinyAuthBackend\Identity\EntityIdentity`: a minimal wrapper around any Cake
entity that implements `IdentityInterface` directly.

```php
use TinyAuthBackend\Identity\EntityIdentity;

$user = $this->Users->get($userIdFromSession);
$identity = new EntityIdentity($user, $authorizationService); // service is optional

$request = $request->withAttribute('identity', $identity);
```

See
[Authorization Integration](/authorization/#identity-without-cakephp-authentication)
for the full usage notes.

## Good fit

Choose this mode if:

- your app already owns role definitions elsewhere
- you still want to manage ACL / resource assignments in TinyAuthBackend
- you do not want admins changing role identity data from this plugin
