<?php
/**
 * Salvage board — sale date parsing and "sells soon" flags.
 *
 * Pure logic, no WordPress dependency.
 *
 * The source publishes sale times as "Wed 16/09 12:00PM" — no year. Rather than
 * assume the current year, we pick the year whose weekday MATCHES the published
 * weekday. If no nearby year matches, the string is returned unparsed instead of
 * being guessed: a sale date off by a year would put a lot in the wrong bidding
 * window, which is worse than showing nothing.
 *
 * Day boundaries are resolved in the site's local timezone, never UTC. A UTC
 * boundary on an Australian clock invents a day's difference either side of
 * midnight.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'IC_SALVAGE_TEST' ) ) { exit; }

class IC_Salvage_SaleDate {

	/** Auction times are published without a zone; treated as eastern Australian time. */
	const DEFAULT_TZ = 'Australia/Sydney';

	const TZ_NOTE = 'Auction times are published without a timezone and are read as eastern Australian time. WA and NT lots run on their own clock — confirm the local start time before travelling or bidding.';

	/**
	 * Parse a published sale-start string.
	 *
	 * @param string|null        $raw       e.g. "Wed 16/09 12:00PM", "-", "?"
	 * @param string             $reference Y-m-d the scan was taken; the year is chosen near this.
	 * @param string             $tz        Timezone name.
	 * @return array{datetime: ?string, weekday_ok: ?bool, unknown_reason: ?string, raw: ?string}
	 */
	public static function parse( $raw, $reference, $tz = self::DEFAULT_TZ ) {
		$out = array(
			'datetime'       => null,
			'weekday_ok'     => null,
			'unknown_reason' => null,
			'raw'            => ( null === $raw ? null : (string) $raw ),
		);

		if ( null === $raw ) {
			$out['unknown_reason'] = 'No sale time published for this lot.';
			return $out;
		}

		$s = trim( (string) $raw );
		if ( '' === $s || '-' === $s || '?' === $s || 'n/a' === strtolower( $s ) ) {
			$out['unknown_reason'] = 'No sale time published for this lot.';
			return $out;
		}

		// "Wed 16/09 12:00PM"  /  "16/09 12:00PM"  /  "Wed 16/09"
		$re = '/^(?:(?P<dow>Mon|Tue|Wed|Thu|Fri|Sat|Sun)[a-z]*\s+)?(?P<d>\d{1,2})\/(?P<m>\d{1,2})(?:\s+(?P<h>\d{1,2}):(?P<i>\d{2})\s*(?P<ap>AM|PM))?$/i';
		if ( ! preg_match( $re, $s, $m ) ) {
			$out['unknown_reason'] = sprintf( 'Sale time "%s" is not in a recognised format — left unparsed rather than guessed.', $s );
			return $out;
		}

		$day   = (int) $m['d'];
		$month = (int) $m['m'];
		$hour  = isset( $m['h'] ) && '' !== $m['h'] ? (int) $m['h'] : 0;
		$min   = isset( $m['i'] ) && '' !== $m['i'] ? (int) $m['i'] : 0;
		$ap    = isset( $m['ap'] ) ? strtoupper( $m['ap'] ) : '';

		if ( 'PM' === $ap && 12 !== $hour ) { $hour += 12; }
		if ( 'AM' === $ap && 12 === $hour ) { $hour = 0; }

		if ( $month < 1 || $month > 12 || $day < 1 || $day > 31 || $hour > 23 ) {
			$out['unknown_reason'] = sprintf( 'Sale time "%s" is not a valid date.', $s );
			return $out;
		}

		try {
			$zone    = new DateTimeZone( $tz );
			$ref     = new DateTimeImmutable( $reference . ' 00:00:00', $zone );
		} catch ( Exception $e ) {
			$out['unknown_reason'] = 'Invalid reference date or timezone.';
			return $out;
		}

		$ref_year = (int) $ref->format( 'Y' );
		$wanted   = isset( $m['dow'] ) && '' !== $m['dow'] ? ucfirst( strtolower( substr( $m['dow'], 0, 3 ) ) ) : null;

		// Try the reference year first, then its neighbours — a scan taken in late
		// December legitimately lists January sales.
		$candidates = array();
		foreach ( array( 0, 1, -1 ) as $delta ) {
			$y = $ref_year + $delta;
			if ( ! checkdate( $month, $day, $y ) ) { continue; }
			try {
				$dt = new DateTimeImmutable( sprintf( '%04d-%02d-%02d %02d:%02d:00', $y, $month, $day, $hour, $min ), $zone );
			} catch ( Exception $e ) {
				continue;
			}
			$candidates[] = $dt;
		}

		if ( ! $candidates ) {
			$out['unknown_reason'] = sprintf( 'Sale date "%s" is not a real calendar date.', $s );
			return $out;
		}

		if ( null === $wanted ) {
			// No weekday published — nothing to cross-check, so take the first
			// candidate on or after the scan date and say the check did not happen.
			$pick = null;
			foreach ( $candidates as $dt ) {
				if ( $dt >= $ref->modify( '-1 day' ) ) { $pick = $dt; break; }
			}
			if ( null === $pick ) { $pick = $candidates[0]; }
			$out['datetime']   = $pick->format( 'Y-m-d H:i:s' );
			$out['weekday_ok'] = null;
			return $out;
		}

		foreach ( $candidates as $dt ) {
			if ( $dt->format( 'D' ) === $wanted ) {
				$out['datetime']   = $dt->format( 'Y-m-d H:i:s' );
				$out['weekday_ok'] = true;
				return $out;
			}
		}

		$out['weekday_ok']     = false;
		$out['unknown_reason'] = sprintf(
			'Sale time "%s" names %s, but %02d/%02d is not a %s in any year near %d. Left unparsed rather than guessed.',
			$s, $wanted, $day, $month, $wanted, $ref_year
		);
		return $out;
	}

	/**
	 * Does this lot sell within the next N hours?
	 *
	 * @return bool|null null when the sale time is unknown — NOT false. "We do not
	 *                   know when this sells" must never render as "not selling soon".
	 */
	public static function sells_within_hours( $datetime, $now, $hours, $tz = self::DEFAULT_TZ ) {
		if ( null === $datetime || '' === $datetime ) { return null; }
		try {
			$zone = new DateTimeZone( $tz );
			$dt   = new DateTimeImmutable( $datetime, $zone );
			$n    = new DateTimeImmutable( $now, $zone );
		} catch ( Exception $e ) {
			return null;
		}
		if ( $dt < $n ) { return false; }
		$limit = $n->modify( sprintf( '+%d hours', (int) $hours ) );
		return $dt <= $limit;
	}

	/**
	 * Does this lot sell on tomorrow's local calendar day?
	 *
	 * Compared as local calendar dates, not as a 24-hour offset.
	 *
	 * @return bool|null
	 */
	public static function sells_tomorrow( $datetime, $now, $tz = self::DEFAULT_TZ ) {
		if ( null === $datetime || '' === $datetime ) { return null; }
		try {
			$zone     = new DateTimeZone( $tz );
			$dt       = new DateTimeImmutable( $datetime, $zone );
			$n        = new DateTimeImmutable( $now, $zone );
			$tomorrow = $n->modify( '+1 day' );
		} catch ( Exception $e ) {
			return null;
		}
		return $dt->format( 'Y-m-d' ) === $tomorrow->format( 'Y-m-d' );
	}

	/** Has the published sale time already passed? */
	public static function is_past( $datetime, $now, $tz = self::DEFAULT_TZ ) {
		if ( null === $datetime || '' === $datetime ) { return null; }
		try {
			$zone = new DateTimeZone( $tz );
			return ( new DateTimeImmutable( $datetime, $zone ) ) < ( new DateTimeImmutable( $now, $zone ) );
		} catch ( Exception $e ) {
			return null;
		}
	}
}
