## Backend: Authorization

This is for "acl" management.

### Schema
The tables currently expect the following fields:
- type (`AclRule::TYPE_ALLOW`, `AclRule::TYPE_DENY`)
- role (e.g. `admin`, `user`)
- path (`VendorName/PluginName.Prefix/ControllerName::actionName`)

### Important info
`deny` always trumps `allow` rules for the same path.

### TODO
Check if we can make a more normalized DB structure and a better UI selection for defining the rules.
