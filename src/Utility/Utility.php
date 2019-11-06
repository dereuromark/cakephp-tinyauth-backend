<?php

namespace TinyAuthBackend\Utility;

use Cake\Utility\Inflector;

class Utility {

	/**
	 * @param string $word Word.
	 * @param string $separator Token separator char used.
	 *
	 * @return string
	 */
	public static function underscoreTokenString($word, $separator) {
		$pieces = explode($separator, $word);
		foreach ($pieces as $key => $piece) {
			$pieces[$key] = Inflector::underscore($piece);
		}

		return implode($separator, $pieces);
	}

	/**
	 * @param string $word Word.
	 * @param string $separator Token separator char used.
	 *
	 * @return string
	 */
	public static function camelizeTokenString($word, $separator) {
		$pieces = explode($separator, $word);
		foreach ($pieces as $key => $piece) {
			$pieces[$key] = Inflector::camelize($piece);
		}

		return implode($separator, $pieces);
	}

}
