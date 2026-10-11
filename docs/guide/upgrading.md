# Upgrading to CakePHP 6

The `cake6` development branch requires PHP 8.5 and CakePHP 6. Follow the [installation guide](./installation.md) for the TinyAuth and Migrations development dependencies. Authentication and authorization integrations use Authentication 5 and Authorization 4.

Custom table and entity extensions must follow the CakePHP 6 configuration and patchable-field property names. Controller hooks use the new event callback signatures; command extensions implement `execute(): int` and use the inherited console and argument properties. The test suite uses PHPUnit 13.

The published documentation website covers stable CakePHP 5 releases. Use the documentation in this branch while developing on CakePHP 6.
