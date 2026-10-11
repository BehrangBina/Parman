<?php
/**
 * Persian date formatting without the intl extension (the live host may not have it).
 *
 * @package IMP
 */

namespace IMP\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Solar Hijri (Jalali) conversion, the Imperial (Shahanshahi) year used in the header,
 * and Gregorian dates written with Persian month names and digits (news/statement cards).
 */
final class Jalali_Date {

	/** Shahanshahi year = Jalali year + this. */
	const IMPERIAL_OFFSET = 1180;

	const JALALI_MONTHS = array( 1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );

	const GREGORIAN_MONTHS_FA = array( 1 => 'ژانویه', 'فوریه', 'مارس', 'آوریل', 'می', 'ژوئن', 'جولای', 'آگوست', 'سپتامبر', 'اکتبر', 'نوامبر', 'دسامبر' );

	/**
	 * Today (site timezone) as Jalali day/month and Imperial year — the header date.
	 *
	 * @return array{day:string,month:string,year:string}
	 */
	public static function today_imperial() {
		return self::parts( current_datetime(), true );
	}

	/**
	 * Jalali parts of a date.
	 *
	 * @param \DateTimeInterface $date     The date.
	 * @param bool               $imperial Use the Shahanshahi year instead of the Hijri year.
	 * @return array{day:string,month:string,year:string}
	 */
	public static function parts( \DateTimeInterface $date, $imperial = false ) {
		list( $year, $month, $day ) = self::from_gregorian( (int) $date->format( 'Y' ), (int) $date->format( 'n' ), (int) $date->format( 'j' ) );
		return array(
			'day'   => self::fa_digits( $day ),
			'month' => self::JALALI_MONTHS[ $month ],
			'year'  => self::fa_digits( $imperial ? $year + self::IMPERIAL_OFFSET : $year ),
		);
	}

	/**
	 * A post's date as Gregorian with Persian month and digits, e.g. ["۱۷ آگوست", "۲۰۲۶"].
	 *
	 * @param int|\WP_Post|null $post Post (default: current).
	 * @return array{0:string,1:string} Day + month, year. Empty strings if the post has no date.
	 */
	public static function post_gregorian_fa( $post = null ) {
		$date = get_post_datetime( $post );
		if ( ! $date ) {
			return array( '', '' );
		}
		return array(
			self::fa_digits( $date->format( 'j' ) ) . ' ' . self::GREGORIAN_MONTHS_FA[ (int) $date->format( 'n' ) ],
			self::fa_digits( $date->format( 'Y' ) ),
		);
	}

	/**
	 * Western digits → Persian digits.
	 *
	 * @param int|string $value Number or text.
	 * @return string
	 */
	public static function fa_digits( $value ) {
		return strtr( (string) $value, array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ) );
	}

	/**
	 * Gregorian → Jalali (the widely used "jdf" arithmetic algorithm).
	 *
	 * @param int $gy Year.
	 * @param int $gm Month 1–12.
	 * @param int $gd Day.
	 * @return int[] [ year, month, day ]
	 */
	public static function from_gregorian( $gy, $gm, $gd ) {
		$days_before_month = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
		$gy2               = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
		$days              = 355666 + ( 365 * $gy ) + intdiv( $gy2 + 3, 4 ) - intdiv( $gy2 + 99, 100 ) + intdiv( $gy2 + 399, 400 ) + $gd + $days_before_month[ $gm - 1 ];
		$jy                = -1595 + ( 33 * intdiv( $days, 12053 ) );
		$days             %= 12053;
		$jy               += 4 * intdiv( $days, 1461 );
		$days             %= 1461;
		if ( $days > 365 ) {
			$jy  += intdiv( $days - 1, 365 );
			$days = ( $days - 1 ) % 365;
		}
		if ( $days < 186 ) {
			return array( $jy, 1 + intdiv( $days, 31 ), 1 + ( $days % 31 ) );
		}
		return array( $jy, 7 + intdiv( $days - 186, 30 ), 1 + ( ( $days - 186 ) % 30 ) );
	}
}
