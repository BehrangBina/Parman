<?php
/**
 * Splits statement titles for the Bayanie cards.
 *
 * @package IMP
 */

namespace IMP\Services;

use IMP\Core\Config;

defined( 'ABSPATH' ) || exit;

/**
 * Admins write full titles ("بیانیه پارمان پادشاهی ایرانیان درباره اعدام …"); the card shows a
 * fixed small prefix and only the subject ("اعدام …") in large type (Figma Bayanie).
 */
final class Statement_Title {

	/**
	 * The subject part of a statement title (the whole title if no known prefix matches).
	 *
	 * @param string $title Full post title.
	 * @return string
	 */
	public static function subject( $title ) {
		foreach ( (array) Config::get( 'statement_prefixes', array() ) as $prefix ) {
			if ( 0 !== mb_strpos( $title, $prefix ) ) {
				continue;
			}
			$rest = trim( mb_substr( $title, mb_strlen( $prefix ) ) );
			$rest = preg_replace( '/^درباره(ی)?\s*:?\s*/u', '', $rest );
			return '' !== $rest ? $rest : $title;
		}
		return $title;
	}
}
