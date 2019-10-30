## Backend: Authentication

This is for "allow" management.

### Schema
The tables currently expect the following fields:
- type (`AllowRule::TYPE_ALLOW`, `AllowRule::TYPE_DENY`)
- path (`VendorName/PluginName.Prefix/ControllerName::actionName`)

### Important info
`deny` always trumps `allow` rules for the same path.

### TODO
Check if we can make a more normalized DB structure and a better UI selection for defining the rules.
