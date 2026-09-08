<?php
/**
 * Salvage board — Kenya export screening rules.
 *
 * Pure logic, no WordPress dependency.
 *
 * Every rule here returns one of THREE states: true, false, or null (unknown).
 * Null is not a nuisance value to be collapsed into false. "We do not know
 * whether this lot is eligible" and "this lot is not eligible" lead to opposite
 * bidding decisions, and the source data genuinely lacks fields for some lots.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'IC_SALVAGE_TEST' ) ) { exit; }

class IC_Salvage_Rules {

	/**
	 * KEBS age window, KS 1515:2000.
	 *
	 * Canon: "From 1 Jan 2026 KEBS clears only RHD vehicles first registered
	 * 2019 or later." That is an eight-year window counted inclusively, so the
	 * cutoff is (year - 7), NOT (year - 8).
	 *
	 * Expressed as a formula on purpose. A hardcoded 2019 silently becomes wrong
	 * on 1 January 2027, and a stale cutoff would pass every test that only
	 * checked the 2019 string.
	 */
	const KEBS_WINDOW_YEARS = 8;

	/** Earliest first-registration year KEBS will clear, for a given calendar year. */
	public static function kebs_cutoff_year( $calendar_year ) {
		return (int) $calendar_year - ( self::KEBS_WINDOW_YEARS - 1 );
	}

	/**
	 * Is the vehicle inside the KEBS age window?
	 *
	 * @return bool|null null when the year is unknown.
	 */
	public static function kebs_year_ok( $year, $calendar_year ) {
		if ( null === $year || '' === $year ) { return null; }
		$year = (int) $year;
		if ( $year < 1900 || $year > (int) $calendar_year + 1 ) { return null; }
		return $year >= self::kebs_cutoff_year( $calendar_year );
	}

	/**
	 * Flood / water damage fails PVoC outright, at any age.
	 *
	 * Deliberately independent of the age rule: a 2025 flood car is rejected for
	 * a different reason than a 2015 dry car, and collapsing the two would let a
	 * flood lot reappear the moment the age window moved.
	 *
	 * @return bool|null null when no damage text was published.
	 */
	public static function flood_reject( $damage_text ) {
		if ( null === $damage_text ) { return null; }
		$d = strtolower( trim( (string) $damage_text ) );
		if ( '' === $d || '-' === $d || '?' === $d ) { return null; }
		foreach ( array( 'water', 'flood', 'submerg' ) as $needle ) {
			if ( false !== strpos( $d, $needle ) ) { return true; }
		}
		return false;
	}

	/**
	 * Overall Kenya eligibility.
	 *
	 * @return array{eligible: bool|null, reasons: string[]}
	 */
	public static function kenya_eligible( $year, $damage_text, $calendar_year ) {
		$reasons = array();
		$flood   = self::flood_reject( $damage_text );
		$age_ok  = self::kebs_year_ok( $year, $calendar_year );

		// Flood is decisive and age-independent — check it first.
		if ( true === $flood ) {
			$reasons[] = 'Water damage — fails PVoC at any age.';
			return array( 'eligible' => false, 'reasons' => $reasons );
		}

		if ( null === $age_ok ) {
			$reasons[] = 'Year of first registration unknown — age cannot be screened.';
			return array( 'eligible' => null, 'reasons' => $reasons );
		}

		if ( false === $age_ok ) {
			$reasons[] = sprintf(
				'First registered before %d — outside the KEBS %d-year window for %d.',
				self::kebs_cutoff_year( $calendar_year ),
				self::KEBS_WINDOW_YEARS,
				(int) $calendar_year
			);
			return array( 'eligible' => false, 'reasons' => $reasons );
		}

		if ( null === $flood ) {
			$reasons[] = 'Inside the KEBS age window, but no damage description was published — water damage cannot be ruled out.';
			return array( 'eligible' => null, 'reasons' => $reasons );
		}

		$reasons[] = sprintf( 'Inside the KEBS age window (%d or later) and no water damage reported.', self::kebs_cutoff_year( $calendar_year ) );
		return array( 'eligible' => true, 'reasons' => $reasons );
	}

	/**
	 * Victorian statutory write-offs require a Victorian EPA licence to bid at
	 * both Pickles and Manheim.
	 *
	 * We flag it. We do NOT assert whether the rule bites on export rather than
	 * dismantling — that is unconfirmed, and asserting it either way would be
	 * inventing a legal position.
	 *
	 * @return bool|null
	 */
	public static function vic_statutory_epa( $state, $wovr ) {
		if ( null === $state || null === $wovr ) { return null; }
		$st = strtoupper( trim( (string) $state ) );
		$wv = strtolower( trim( (string) $wovr ) );
		if ( '' === $st || '' === $wv ) { return null; }
		if ( 'VIC' !== $st ) { return false; }
		return false !== strpos( $wv, 'statutory' );
	}

	const VIC_EPA_NOTE = 'Victorian statutory write-off — a Victorian EPA licence is required to bid at Pickles and Manheim. Whether the licence requirement bites on export rather than dismantling is UNCONFIRMED; verify before bidding.';

	/** Extract an Australian state code from a free-text location. */
	public static function state_from_location( $location ) {
		if ( null === $location ) { return null; }
		$loc = strtoupper( trim( (string) $location ) );
		if ( '' === $loc ) { return null; }
		if ( preg_match( '/\b(NSW|VIC|QLD|WA|SA|TAS|NT|ACT)\b/', $loc, $m ) ) {
			return $m[1];
		}
		return null;
	}

	/**
	 * Data-integrity guard: there is no V8 Prado.
	 *
	 * Prado is a 2.8/3.0 four-cylinder turbo-diesel or a 4.0 V6 petrol. The 4.5
	 * V8 turbo-diesel is the LandCruiser 200 and 70 Series. A row asserting both
	 * is a mis-scrape, and a mis-scraped drivetrain would be bid on as if real.
	 *
	 * @return string|null Warning text, or null when the row is consistent.
	 */
	public static function drivetrain_conflict( $model, $variant ) {
		$hay = strtolower( trim( (string) $model . ' ' . (string) $variant ) );
		if ( '' === trim( $hay ) ) { return null; }
		if ( false === strpos( $hay, 'prado' ) ) { return null; }
		// "4.5" must match in "4.5L" too, where a trailing \b fails against the
		// letter. Guard against 14.55 and 4.55 instead of relying on word breaks.
		if ( preg_match( '/\bv8\b|(?<![\d.])4\.5(?!\d)/', $hay ) ) {
			return 'Listing describes a V8 or 4.5L Prado. No such vehicle exists — Prado is a 2.8/3.0 four-cylinder turbo-diesel or a 4.0 V6 petrol; the 4.5 V8 turbo-diesel is the LandCruiser 200/70 Series. Treat this row as a mis-scrape and verify against the lot page.';
		}
		return null;
	}
}
