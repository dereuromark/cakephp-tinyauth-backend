## Backend: Authentication

This is for "allow" management.

### Schema
The tables currently expect the following fields:
- type (`AllowRule::TYPE_ALLOW`, `AllowRule::TYPE_DENY`)
- path (`VendorName/PluginName.Prefix/ControllerName::actionName`)

### Important info
`deny` always trumps `allow` rules for the same path.

### Prefix casing
In TinyAuth and for CakePHP 3 routing params multi-word prefixes are supposed to be dashed.
For a nested prefix for `\App\MyPrefix\MySubPrefix\MyTestController` controller class,
it would be `my-prefix/my-sub-prefix`.
For path syntax however, it is the namespace elements, thus `MyPrefix/MySubPrefix`.

For BC reasons and usability it will auto-inflect when saving.
So both cases are accepted as input.

### TODO
Check if we can make a more normalized DB structure and a better UI selection for defining the rules.
