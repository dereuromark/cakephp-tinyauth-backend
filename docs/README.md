## TinyAuth Backend

DB backend and adapters for TinyAuth.

These adapters are very simple and in line with the INI files in terms of denormalized structure. 
If you need a more normalized DB table approach, create your own ones.

### Enable the plugin

Load the plugin by running
```
bin/cake plugin load TinyAuthBackend
```

or manually add this into your `Application::bootstrap()` method:
```php
    $this->addPlugin('TinyAuthBackend');
```


### Enable the adapters
If you need only Authentication or Authorization or both, add those as needed in your global config:
```php
    'allowAdapter' => \TinyAuth\Auth\AllowAdapter\DbAllowAdapter::class,
    'aclAdapter' => \TinyAuth\Auth\AclAdapter\DbAclAdapter::class,
```


### Run migration 
or provide your own tables.

This uses Migrations plugin:
```
bin/cake migrations migrate -p TinyAuthBackend
```


### Test run
Navigate to `/admin/auth` backend. It should show the dashboard. 

If you didn't include the plugin's default routes, you need to define them similarly on app level.

You can now start adding rules for authentication and authorization.

Note: Every change automatically busts the internal TinyAuth cache.

### Custom theme/template
TODO
