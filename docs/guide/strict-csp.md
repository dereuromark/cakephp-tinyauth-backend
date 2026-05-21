# Strict Content-Security-Policy

The plugin's admin UI is built to run under a strict Content-Security-Policy
header — no `script-src 'unsafe-eval'`, no `style-src 'unsafe-inline'`.

Inline `<script>` blocks in the layout carry a per-request nonce read from
`$this->getRequest()->getAttribute('cspNonce')`, so any host-app middleware that
sets that attribute and emits a matching `Content-Security-Policy` header will
work out of the box.

There are two host-app concerns to be aware of.

## 1. CSP middleware

The plugin does **not** ship its own CSP middleware. Add a small middleware to
your app that:

- generates a per-request nonce,
- exposes it as the `cspNonce` request attribute, and
- emits a `Content-Security-Policy` header with `'nonce-…'` in `script-src`.

::: tip Reference implementation
The companion
[cakephp-tinyauth-demo](https://github.com/dereuromark/cakephp-tinyauth-demo)
shows a roughly 50-line implementation in
`src/Middleware/StrictCspMiddleware.php`.
:::

## 2. FormHelper `hiddenBlock` template

Out of the box, CakePHP wraps every CSRF token in
`<div style="display:none;">…</div>`, which violates strict `style-src`.
Override the template once in your `AppView::initialize()`:

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

This swaps the inline style for the HTML5 `hidden` attribute, which needs no CSS.
A single override eliminates one CSP violation per `Form->postLink()` /
`Form->postButton()` on every page.

## Why precompiled assets

The admin UI ships precompiled Tailwind CSS rather than relying on the Tailwind
Play CDN, which cannot run under strict CSP because it JIT-compiles utility
classes in the browser via `Function()` (`unsafe-eval`). See
[Frontend Assets](/reference/assets) for the contributor workflow.

## Regression guards

The plugin includes two CSP guard tests:

- `tests/TestCase/CspComplianceTest.php` — scans the template source for Alpine.js
  directives and inline event handlers.
- `tests/TestCase/Controller/Admin/RenderedCspComplianceTest.php` — checks the
  rendered HTML.

Both fail if a CSP-violating pattern reappears.
