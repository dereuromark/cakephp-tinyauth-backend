<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase;

use Cake\TestSuite\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Guard against re-introducing Alpine.js or other unsafe-eval / unsafe-inline
 * patterns in plugin templates. Strict CSP forbids these.
 */
class CspComplianceTest extends TestCase {

	/**
	 * Alpine.js attributes — they require the Function() evaluator, blocked by strict CSP.
     * @var string
	 */
	private const ALPINE_PATTERN = '/(\sx-(data|show|if|for|init|cloak|text|html|bind|on|model|collapse)\b'
		. '|\s@(click|change|submit|keydown|keyup|dragstart|dragend|dragover|drop|input|focus|blur)\b'
		. '|\s:(class|aria-label|aria-expanded|disabled|value|style)\s*=)/';

	/**
	 * Inline event handlers — onclick=, onchange=, etc. blocked by strict CSP.
     * @var string
	 */
	private const INLINE_HANDLER_PATTERN = '/\son(click|change|submit|load|keyup|keydown|mouseover|mouseout|focus|blur|input)\s*=/';

	/**
	 * @return void
	 */
	public function testNoAlpineDirectivesInTemplates(): void {
		$offenders = $this->scanTemplates(static::ALPINE_PATTERN);
		$this->assertSame([], $offenders, "Alpine directives found:\n" . implode("\n", $offenders));
	}

	/**
	 * @return void
	 */
	public function testNoInlineEventHandlersInTemplates(): void {
		$offenders = $this->scanTemplates(static::INLINE_HANDLER_PATTERN);
		$this->assertSame([], $offenders, "Inline event handlers found:\n" . implode("\n", $offenders));
	}

	/**
	 * @param string $pattern
	 * @return array<string>
	 */
	private function scanTemplates(string $pattern): array {
		$root = dirname(__DIR__, 2) . '/templates';
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
		$offenders = [];
		foreach ($iterator as $file) {
			if (!$file->isFile() || !str_ends_with((string)$file, '.php')) {
				continue;
			}
			$content = (string)file_get_contents((string)$file);
			$lines = preg_split('/\R/', $content) ?: [];
			foreach ($lines as $idx => $line) {
				if (preg_match($pattern, $line)) {
					$offenders[] = sprintf('%s:%d: %s', (string)$file, $idx + 1, trim($line));
				}
			}
		}

		return $offenders;
	}

}
