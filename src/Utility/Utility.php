<?php

namespace TinyAuthBackend\Utility;

use Cake\Utility\Inflector;

class Utility {

	/**
	 * @phpstan-param non-empty-string $separator
	 *
	 * @param string $word Word.
	 * @param string $separator Token separator char used.
	 *
	 * @return string
	 */
	public static function underscoreTokenString(string $word, string $separator): string {
		$pieces = explode($separator, $word);
		foreach ($pieces as $key => $piece) {
			$pieces[$key] = Inflector::underscore($piece);
		}

		return implode($separator, $pieces);
	}

	/**
	 * @phpstan-param non-empty-string $separator
	 *
	 * @param string $word Word.
	 * @param string $separator Token separator char used.
	 *
	 * @return string
	 */
	public static function camelizeTokenString(string $word, string $separator): string {
		$pieces = explode($separator, $word);
		foreach ($pieces as $key => $piece) {
			$pieces[$key] = Inflector::camelize($piece);
		}

		return implode($separator, $pieces);
	}

}
