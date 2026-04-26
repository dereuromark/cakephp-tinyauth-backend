<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TinyAuthBackend\Service\RoleSourceService;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

/**
 * Renders each admin page and asserts that the produced HTML does not contain
 * patterns that would violate a strict Content-Security-Policy header
 * (`script-src 'self' 'nonce-…'; style-src 'self';` — no `unsafe-eval`, no
 * `unsafe-inline`).
 *
 * Complements `tests/TestCase/CspComplianceTest.php`, which scans template
 * source files. This test catches Helper-emitted markup (e.g. inline styles
 * coming from `Form->postButton`) and any PHP-rendered strings that source
 * scanning cannot see.
 */
class RenderedCspComplianceTest extends TestCase {

	use DatabaseTestTrait;
	use IntegrationTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
		'plugin.TinyAuthBackend.TinyAuthControllers',
		'plugin.TinyAuthBackend.TinyAuthActions',
		'plugin.TinyAuthBackend.TinyAuthAclPermissions',
		'plugin.TinyAuthBackend.TinyAuthResources',
		'plugin.TinyAuthBackend.TinyAuthResourceAbilities',
		'plugin.TinyAuthBackend.TinyAuthResourceAcl',
		'plugin.TinyAuthBackend.TinyAuthScopes',
	];

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);
		Configure::write('TinyAuthBackend.roleSource', ['user' => 1, 'editor' => 2, 'admin' => 3]);
		(new RoleSourceService())->clearCache();
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function adminUrlProvider(): array {
		return [
			'dashboard' => ['/admin/auth/dashboard'],
			'acl' => ['/admin/auth/acl'],
			'allow' => ['/admin/auth/allow'],
			'roles' => ['/admin/auth/roles'],
			'resources' => ['/admin/auth/resources'],
			'scopes' => ['/admin/auth/scopes'],
		];
	}

	/**
	 * Every rendered admin page should be free of patterns blocked by strict CSP:
	 * inline `style="…"` attributes, inline event handlers (`onclick=`, etc.),
	 * `javascript:` URIs, Alpine.js directives, and unnonced `<script>` blocks.
	 *
	 * @param string $url
	 * @return void
	 */
	#[DataProvider('adminUrlProvider')]
	public function testAdminPageHtmlIsStrictCspClean(string $url): void {
		// The test_app's Application sets a fixed `cspNonce` request attribute
		// so the templates emit `nonce="…"` on inline `<script>` / `<style>`
		// blocks (mirroring what a real CSP middleware in the host app does).
		$this->get($url);

		$this->assertResponseOk();

		$body = (string)$this->_response->getBody();

		$this->assertStringNotContainsString(
			' style="',
			$body,
			"Rendered HTML for $url contains an inline `style=` attribute, which violates strict `style-src`. "
				. 'Convert to a CSS class or `data-style` + JS shim.',
		);

		// Inline event handlers.
		foreach (['onclick', 'onchange', 'onsubmit', 'onload', 'onkeyup', 'onkeydown', 'onmouseover', 'onmouseout', 'onfocus', 'onblur'] as $attr) {
			$this->assertStringNotContainsString(
				' ' . $attr . '=',
				$body,
				"Rendered HTML for $url contains inline `$attr` handler, which violates strict `script-src`. "
					. 'Use addEventListener in webroot/js/tinyauth.js instead.',
			);
		}

		// `javascript:` URIs.
		$this->assertStringNotContainsString(
			'javascript:',
			$body,
			"Rendered HTML for $url contains a `javascript:` URI. Use a real URL or `href=\"#\"` + preventDefault.",
		);

		// Alpine.js directives — defence in depth, also covered by CspComplianceTest source scan.
		$alpinePattern = '/\sx-(data|show|if|for|init|cloak|text|html|bind|on|model|collapse)\b|\s@(click|change|submit|keydown|keyup|dragstart|dragend|dragover|drop)\b|\s:(class|aria-label|aria-expanded|disabled|value|style)\s*=/';
		$this->assertSame(
			0,
			preg_match($alpinePattern, $body),
			"Rendered HTML for $url contains Alpine.js directive(s).",
		);

		// `<script>` blocks: anything not nonced is blocked under strict CSP.
		// Allow nonced inline scripts and `<script src="…">` external loads.
		$scriptOpens = preg_match_all('/<script(\s[^>]*)?>/i', $body, $matches);
		if ($scriptOpens > 0) {
			foreach ($matches[1] as $attrs) {
				$attrs = (string)$attrs;
				$isExternal = preg_match('/\ssrc\s*=/', $attrs) === 1;
				$isNonced = preg_match('/\snonce\s*=/', $attrs) === 1;
				$this->assertTrue(
					$isExternal || $isNonced,
					"Rendered HTML for $url contains an unnonced inline `<script>` opening tag <script$attrs>. "
						. 'Inline scripts must carry the request nonce.',
				);
			}
		}

		// `<style>` blocks: same rule — nonced or not present.
		$styleOpens = preg_match_all('/<style(\s[^>]*)?>/i', $body, $styleMatches);
		if ($styleOpens > 0) {
			foreach ($styleMatches[1] as $attrs) {
				$attrs = (string)$attrs;
				$isNonced = preg_match('/\snonce\s*=/', $attrs) === 1;
				$this->assertTrue(
					$isNonced,
					"Rendered HTML for $url contains an unnonced `<style>` block <style$attrs>. "
						. 'Move to webroot/css/ or carry the request nonce.',
				);
			}
		}
	}

}
