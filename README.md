# CakePHP TinyAuth backend
[![CI](https://github.com/dereuromark/cakephp-tinyauth-backend/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-tinyauth-backend/actions/workflows/ci.yml?query=branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-tinyauth-backend/master.svg)](https://codecov.io/github/dereuromark/cakephp-tinyauth-backend/branch/master)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-tinyauth-backend/license.svg)](LICENSE)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-tinyauth-backend/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-tinyauth-backend)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-tinyauth-backend/d/total)](https://packagist.org/packages/dereuromark/cakephp-tinyauth-backend)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

A database driven backend for CakePHP [TinyAuth plugin](https://github.com/dereuromark/cakephp-tinyauth).
This replaces the native INI file approach.

This branch is for use with **CakePHP 5.1+**. For details see [version map](https://github.com/dereuromark/cakephp-tinyauth-backend/wiki#cakephp-version-map).

## Installation

Install the plugin with composer from your CakePHP project's ROOT directory
(where composer.json file is located)

```sh
composer require dereuromark/cakephp-tinyauth-backend
```

It will auto-require `dereuromark/cakephp-tinyauth` dependency.

### Admin Access Requirement

The plugin mounts its admin UI under `/admin/auth`.

As of the current `master`, admin access is **fail-closed outside debug mode**:

- `debug = true`: the admin UI is accessible by default for local setup and demos
- `debug = false`: the admin UI returns `403` unless your app explicitly configures `TinyAuthBackend.editorCheck`

Production apps should always set `TinyAuthBackend.editorCheck` to a callable that decides who may edit TinyAuth rules:

```php
use Cake\Core\Configure;
use Psr\Http\Message\ServerRequestInterface;

Configure::write(
    'TinyAuthBackend.editorCheck',
    function (mixed $identity, ServerRequestInterface $request): bool {
        if ($identity === null) {
            return false;
        }

        $roleId = is_object($identity) && method_exists($identity, 'get')
            ? $identity->get('role_id')
            : ($identity['role_id'] ?? null);

        return (int)$roleId === 3;
    },
);
```

### Strict Content-Security-Policy (optional)

The plugin's admin UI is built to run under a strict Content-Security-Policy header — no `script-src 'unsafe-eval'`, no `style-src 'unsafe-inline'`. Inline `<script>` blocks in the layout carry a per-request nonce read from `$this->getRequest()->getAttribute('cspNonce')`, so any host-app middleware that sets that attribute and emits a matching `Content-Security-Policy` header will Just Work.

Two host-app concerns to be aware of:

1. **CSP middleware** — the plugin does not ship its own. Add a small middleware to your app that generates a per-request nonce, exposes it as the `cspNonce` request attribute, and emits a `Content-Security-Policy` header with `'nonce-…'` in `script-src`. The companion [cakephp-tinyauth-demo](https://github.com/dereuromark/cakephp-tinyauth-demo) shows a ~50-line implementation in `src/Middleware/StrictCspMiddleware.php`.

2. **FormHelper `hiddenBlock` template** — out of the box, CakePHP wraps every CSRF token in `<div style="display:none;">…</div>`, which violates strict `style-src`. Override the template once in your `AppView::initialize()`:

   ```php
   public function initialize(): void
   {
       $this->loadHelper('Form', [
           'templates' => [
               'hiddenBlock' => '<div hidden>{{content}}</div>',
           ],
       ]);
   }
   ```

   This swaps the inline style for the HTML5 `hidden` attribute, which needs no CSS. A single override eliminates one CSP violation per `Form->postLink()` / `Form->postButton()` on every page.

The included `tests/TestCase/CspComplianceTest.php` (template-source scan) and `tests/TestCase/Controller/Admin/RenderedCspComplianceTest.php` (rendered-HTML check) guard against regressions.

## Usage
See [Docs](/docs/README.md).
