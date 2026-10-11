# CakePHP TinyAuth backend
[![CI](https://github.com/dereuromark/cakephp-tinyauth-backend/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-tinyauth-backend/actions/workflows/ci.yml?query=branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-tinyauth-backend/master.svg)](https://codecov.io/github/dereuromark/cakephp-tinyauth-backend/branch/master)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)
[![License](https://poser.pugx.org/dereuromark/cakephp-tinyauth-backend/license.svg)](LICENSE)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-tinyauth-backend/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-tinyauth-backend)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-tinyauth-backend/d/total)](https://packagist.org/packages/dereuromark/cakephp-tinyauth-backend)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-purple.svg?style=flat-square)](https://github.com/php-fig-rectified/fig-rectified-standards)

A database backend and admin interface for [TinyAuth](https://dereuromark.github.io/cakephp-tinyauth/). Manage roles, public actions, controller permissions and optional resource permissions through `/admin/auth`.

The current branch targets CakePHP 5. Composer declares the supported PHP and framework ranges.

## Documentation

[Read the website documentation](https://dereuromark.github.io/cakephp-tinyauth-backend/).

- [Installation](https://dereuromark.github.io/cakephp-tinyauth-backend/guide/installation)
- [Getting started](https://dereuromark.github.io/cakephp-tinyauth-backend/guide/getting-started)
- [Admin access](https://dereuromark.github.io/cakephp-tinyauth-backend/guide/admin-access)
- [Runtime strategies](https://dereuromark.github.io/cakephp-tinyauth-backend/strategies/)
- [Authorization integration](https://dereuromark.github.io/cakephp-tinyauth-backend/authorization/)
- [Strict CSP](https://dereuromark.github.io/cakephp-tinyauth-backend/guide/strict-csp)

Admin access requires an explicit `TinyAuthBackend.adminAccess` Closure in every environment. The legacy `editorCheck` gate remains available for migration and emits a deprecation warning.

Markdown sources are in [docs](docs/). Run `npm ci` and `npm run docs:build` from that directory to build the site locally.
