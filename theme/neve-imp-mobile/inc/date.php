<?php
/**
 * Solar Hijri (Jalali) dates without the intl extension.
 * The header shows the Imperial (Shahanshahi) year = Jalali year + 1180.
 */

defined( 'ABSPATH' ) || exit;

function imp_m_gregorian_to_jalali( $gy, $gm, $gd ) {
	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
	$days  = 355666 + ( 365 * $gy ) + intdiv( $gy2 + 3, 4 ) - intdiv( $gy2 + 99, 100 ) + intdiv( $gy2 + 399, 400 ) + $gd + $g_d_m[ $gm - 1 ];
	$jy    = -1595 + ( 33 * intdiv( $days, 12053 ) );
	$days %= 12053;
	$jy   += 4 * intdiv( $days, 1461 );
	$days %= 1461;
	if ( $days > 365 ) {
		$jy  += intdiv( $days - 1, 365 );
		$days = ( $days - 1 ) % 365;
	}
	if ( $days < 186 ) {
		$jm = 1 + intdiv( $days, 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + intdiv( $days - 186, 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}
	return array( $jy, $jm, $jd );
}

function imp_m_fa_digits( $value ) {
	return strtr( (string) $value, array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ) );
}

function imp_m_jalali_month( $m ) {
	$months = array( 1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );
	return $months[ $m ];
}

/**
 * @param DateTimeInterface|null $date     Defaults to "now" in the site timezone.
 * @param bool                   $imperial Use the Shahanshahi year (+1180) instead of the Hijri year.
 * @return array{day:string,month:string,year:string}
 */
function imp_m_jalali_parts( $date = null, $imperial = false ) {
	$date = $date ?: current_datetime();
	list( $jy, $jm, $jd ) = imp_m_gregorian_to_jalali( (int) $date->format( 'Y' ), (int) $date->format( 'n' ), (int) $date->format( 'j' ) );
	return array(
		'day'   => imp_m_fa_digits( $jd ),
		'month' => imp_m_jalali_month( $jm ),
		'year'  => imp_m_fa_digits( $imperial ? $jy + 1180 : $jy ),
	);
}

/** "۲۵ مرداد ۱۴۰۵" for a post. */
function imp_m_post_date( $post = null ) {
	$date = get_post_datetime( $post );
	if ( ! $date ) {
		return '';
	}
	$p = imp_m_jalali_parts( $date );
	return $p['day'] . ' ' . $p['month'] . ' ' . $p['year'];
}
