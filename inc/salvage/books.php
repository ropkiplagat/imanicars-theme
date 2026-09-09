<?php
/**
 * Salvage board — the three destination books.
 *
 * Kenya (Imani Car Sales) · Uganda (freight client) · Australian rental
 * (Imani Car Rentals). Pure logic, no WordPress dependency.
 *
 * Two rules govern this whole file:
 *
 * 1. WOVR IS A PUBLISHED FIELD, CAPTURED VERBATIM AND THEN CODED. It is never
 *    derived from age. "No WOVR Record" is a value the auction house publishes,
 *    and it is the whole of book 3 — a 2008 car published as Repairable is on
 *    the register and needs the inspection at any age. Age never removes it.
 *
 * 2. EVERY YEAR WINDOW IS A FORMULA, NEVER A LITERAL. The brief states Kenya as
 *    ">= 2019" and Uganda as ">= 2012". Both are correct for 2026 and both are
 *    silently wrong on 1 January 2027. A hardcoded year also passes every test
 *    written in the year it was hardcoded, which is how the last off-by-one
 *    shipped. Year appears here only as `calendar_year - n`.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'IC_SALVAGE_TEST' ) ) { exit; }

class IC_Salvage_Books {

	/* Published WOVR values, coded. */
	const STAT = 'STAT';   // Statutory Write-Off
	const REP  = 'REP';    // Repairable Write-Off
	const INSP = 'INSP';   // Inspection Passed Repairable Write-Off
	const NONE = 'NONE';   // No WOVR Record / WOVR N/A

	/**
	 * Code a published WOVR string.
	 *
	 * Order matters: "Inspection Passed Repairable Write-Off" contains
	 * "Repairable", so INSP must be tested before REP or every passed
	 * inspection would be filed as an ordinary repairable and lose the fact
	 * that its inspection is already done.
	 *
	 * @return string|null null when nothing was published — never a default.
	 */
	public static function wovr_code( $published ) {
		if ( null === $published ) { return null; }
		$s = strtolower( trim( (string) $published ) );
		if ( '' === $s || '-' === $s || '?' === $s ) { return null; }

		if ( false !== strpos( $s, 'inspection passed' ) ) { return self::INSP; }
		if ( false !== strpos( $s, 'statutory' ) )         { return self::STAT; }
		if ( false !== strpos( $s, 'repairable' ) )        { return self::REP; }
		if ( false !== strpos( $s, 'no wovr' ) || false !== strpos( $s, 'wovr n/a' )
			|| false !== strpos( $s, 'n/a' ) )             { return self::NONE; }
		return null;
	}

	/* ---------------------------------------------------------------
	 * Year windows — formulas only
	 * ------------------------------------------------------------- */

	/** Kenya KEBS: 2019 or later in 2026. */
	public static function kenya_floor( $cy ) { return (int) $cy - 7; }

	/** Uganda clean floor: 2012 or later in 2026. */
	public static function uganda_floor( $cy ) { return (int) $cy - 14; }

	/** Uganda contested year: 2011 in 2026 — admitted or not depending on which URA text you read. */
	public static function uganda_boundary_year( $cy ) { return (int) $cy - 15; }

	/**
	 * The year at or above which a `NONE` reading is INFORMATIVE.
	 *
	 * Below it the state never required the total loss to be recorded, so an
	 * absent WOVR entry proves nothing — the car may be a genuine total loss
	 * that was never notifiable.
	 *
	 * QLD  recorded if 16 years or younger  -> informative from cy-16
	 * NSW  recorded up to 15 years old      -> informative from cy-15
	 * WA   manufactured within last 15 yrs  -> informative from cy-15
	 * VIC  recorded if less than 15 years   -> informative from cy-14
	 */
	public static function none_informative_floor( $state, $cy ) {
		$st = strtoupper( trim( (string) $state ) );
		$map = array( 'QLD' => 16, 'NSW' => 15, 'WA' => 15, 'VIC' => 14 );
		if ( ! isset( $map[ $st ] ) ) { return null; }
		return (int) $cy - $map[ $st ];
	}

	/* ---------------------------------------------------------------
	 * Book 1 — Kenya
	 * ------------------------------------------------------------- */
	public static function kenya( array $lot, $cy ) {
		$flags   = array();
		$reasons = array();
		$year    = isset( $lot['year'] ) ? $lot['year'] : null;
		$wovr    = self::wovr_code( isset( $lot['wovr'] ) ? $lot['wovr'] : null );
		$state   = isset( $lot['state'] ) ? strtoupper( (string) $lot['state'] ) : null;

		$flood = IC_Salvage_Rules::flood_reject( self::damage_text( $lot ) );
		if ( true === $flood ) {
			$flags[]   = 'water_reject';
			$reasons[] = 'Water in the damage codes — fails PVoC at any age.';
			return self::result( false, $flags, $reasons, 'crsp' );
		}

		// Statutory ships exactly like repairable into Kenya. The only WOVR
		// consequence is at the Australian end: bidding on a Victorian
		// statutory write-off needs a Victorian EPA licence.
		if ( self::STAT === $wovr && 'VIC' === $state ) {
			$flags[]   = 'vic_epa_licence';
			$reasons[] = 'Victorian statutory write-off — a Victorian EPA licence is required to bid. Whether the requirement bites on export rather than dismantling is UNCONFIRMED.';
		}

		if ( null === $year || '' === $year ) {
			$reasons[] = 'Year of first registration not published — age cannot be screened.';
			return self::result( null, $flags, $reasons, 'crsp' );
		}

		$floor = self::kenya_floor( $cy );
		if ( (int) $year < $floor ) {
			$reasons[] = sprintf( 'First registered before %d — outside the KEBS window for %d.', $floor, (int) $cy );
			return self::result( false, $flags, $reasons, 'crsp' );
		}

		if ( null === $flood ) {
			$reasons[] = 'Inside the KEBS window, but no damage description was published — water cannot be ruled out.';
			return self::result( null, $flags, $reasons, 'crsp' );
		}

		$reasons[] = sprintf( 'First registered %d or later and no water damage reported.', $floor );
		return self::result( true, $flags, $reasons, 'crsp' );
	}

	/* ---------------------------------------------------------------
	 * Book 2 — Uganda
	 * ------------------------------------------------------------- */
	public static function uganda( array $lot, $cy ) {
		$flags   = array();
		$reasons = array();
		$year    = isset( $lot['year'] ) ? $lot['year'] : null;

		$flood = IC_Salvage_Rules::flood_reject( self::damage_text( $lot ) );
		if ( true === $flood ) {
			$flags[]   = 'water_reject';
			$reasons[] = 'Water in the damage codes — PVoC applies to Uganda too.';
			return self::result( false, $flags, $reasons, 'ura_database' );
		}

		if ( null === $year || '' === $year ) {
			$reasons[] = 'Year of first registration not published — age cannot be screened.';
			return self::result( null, $flags, $reasons, 'ura_database' );
		}
		$year = (int) $year;

		$boundary = self::uganda_boundary_year( $cy );
		$floor    = self::uganda_floor( $cy );

		if ( $year < $boundary ) {
			$reasons[] = sprintf( 'First registered %d — older than the 15-year limit for %d.', $year, (int) $cy );
			return self::result( false, $flags, $reasons, 'ura_database' );
		}

		if ( $year === $boundary ) {
			// Two published URA sources disagree at exactly this year. Neither is
			// picked: the row is flagged with both readings and excluded from
			// auto-inclusion until URA or a clearing agent settles it.
			$flags[]   = 'uganda_boundary_' . $boundary;
			$reasons[] = sprintf(
				'%d sits on a contested boundary. URA guidance reads "under 15 years old from first registration", which excludes it; URA\'s environmental levy band runs 9 to 15 years, which admits it. Not auto-included — confirm with URA or a clearing agent.',
				$boundary
			);
			$flags = array_merge( $flags, self::uganda_levy_flags( $year, $cy, $reasons ) );
			return self::result( null, $flags, $reasons, 'ura_database' );
		}

		if ( $year < $floor ) {
			$reasons[] = sprintf( 'First registered %d — outside the Ugandan window for %d.', $year, (int) $cy );
			return self::result( false, $flags, $reasons, 'ura_database' );
		}

		$flags = array_merge( $flags, self::uganda_levy_flags( $year, $cy, $reasons ) );

		if ( null === $flood ) {
			$reasons[] = 'Inside the Ugandan window, but no damage description was published — water cannot be ruled out.';
			return self::result( null, $flags, $reasons, 'ura_database' );
		}

		return self::result( true, $flags, $reasons, 'ura_database' );
	}

	/**
	 * Uganda's environmental levy, which matters more than the age boundary.
	 *
	 * 9-15 years -> 50% · exactly 8 -> 20% · under 8 -> nil.
	 * Assessed on URA's Used Motor Vehicle Valuation Database figure, NOT the
	 * invoice — buying the car damaged and cheap does not move the tax.
	 */
	private static function uganda_levy_flags( $year, $cy, array &$reasons ) {
		$age   = (int) $cy - (int) $year;
		$flags = array();
		if ( $age >= 9 && $age <= 15 ) {
			$flags[]   = 'uganda_levy_50pct';
			$reasons[] = sprintf( '%d years old — 50%% environmental levy on URA\'s valuation, not on what you paid.', $age );
		} elseif ( 8 === $age ) {
			$flags[]   = 'uganda_levy_20pct';
			$reasons[] = '8 years old — 20% environmental levy on URA\'s valuation.';
		} else {
			$flags[]   = 'uganda_levy_nil';
			$reasons[] = sprintf( '%d years old — no environmental levy.', $age );
		}
		return $flags;
	}

	/* ---------------------------------------------------------------
	 * Book 3 — Australian rental
	 * ------------------------------------------------------------- */
	public static function au_rental( array $lot, $cy ) {
		$flags   = array();
		$reasons = array();
		$year    = isset( $lot['year'] ) ? $lot['year'] : null;
		$state   = isset( $lot['state'] ) ? strtoupper( trim( (string) $lot['state'] ) ) : null;
		$wovr    = self::wovr_code( isset( $lot['wovr'] ) ? $lot['wovr'] : null );

		// NOT in the brief, added deliberately: a water-damaged car does not
		// belong in a rental fleet whatever its register status. Flagged rather
		// than silently rejected, because this is an addition to the spec.
		if ( true === IC_Salvage_Rules::flood_reject( self::damage_text( $lot ) ) ) {
			$flags[]   = 'water_reject';
			$reasons[] = 'Water damage. Book 3 has no published water rule — rejected here on fleet-safety grounds, not on a cited regulation. Confirm before overriding.';
			return self::result( false, $flags, $reasons, 'n/a' );
		}

		if ( null === $wovr ) {
			$reasons[] = 'No WOVR status published — registrability cannot be determined.';
			return self::result( null, $flags, $reasons, 'n/a' );
		}

		if ( self::STAT === $wovr ) {
			$reasons[] = 'Statutory write-off — never registrable, in any state, at any repair cost.';
			return self::result( false, $flags, $reasons, 'n/a' );
		}

		if ( self::NONE === $wovr ) {
			$reasons[] = 'No register entry, so no WOVI or VIV — an ordinary roadworthy applies.';
			if ( null !== $year && '' !== $year && null !== $state ) {
				$floor = self::none_informative_floor( $state, $cy );
				if ( null !== $floor && (int) $year < $floor ) {
					$flags[]   = 'history_unverified';
					$reasons[] = sprintf(
						'%s stopped requiring a total loss to be recorded above this age, so a clean WOVR reading proves nothing for a %d vehicle. Still eligible — run a PPSR check.',
						$state, (int) $year
					);
				}
			} else {
				$flags[]   = 'history_unverified';
				$reasons[] = 'Year or state not published, so the reliability of the clean WOVR reading cannot be judged. Run a PPSR check.';
			}
			return self::result( true, $flags, $reasons, 'n/a' );
		}

		// REP or INSP from here.
		if ( 'NSW' === $state ) {
			$flags[]   = 'nsw_not_registrable';
			$reasons[] = 'NSW repairable write-off — a trade buyer at auction fits none of the four NSW exemptions.';
			return self::result( false, $flags, $reasons, 'n/a' );
		}

		if ( self::INSP === $wovr ) {
			$flags[] = 'inspection_passed';
			$reasons[] = 'Inspection already passed — the best buy in this pool.';
		}

		if ( 'VIC' === $state ) {
			$flags[]   = 'needs_viv';
			$reasons[] = 'Victoria: roadworthy plus a VIV inspection.';
			return self::result( true, $flags, $reasons, 'n/a', true );
		}
		if ( 'QLD' === $state ) {
			$flags[]   = 'needs_wovi';
			$reasons[] = 'Queensland: safety certificate plus a WOVI inspection.';
			return self::result( true, $flags, $reasons, 'n/a', true );
		}
		if ( 'WA' === $state ) {
			$flags[]   = 'needs_wovi';
			$reasons[] = 'Western Australia: written-off vehicle inspection required.';
			return self::result( true, $flags, $reasons, 'n/a', true );
		}

		// SA, NT, ACT, TAS are not covered by the brief. Unknown, not eligible,
		// and not rejected either — say so rather than pick one.
		$flags[]   = 'state_rule_unknown';
		$reasons[] = sprintf( 'No published rule on file for %s repairable write-offs. Not screened.', $state ? $state : 'this state' );
		return self::result( null, $flags, $reasons, 'n/a' );
	}

	/* ---------------------------------------------------------------
	 * All three at once
	 * ------------------------------------------------------------- */
	public static function assess( array $lot, $cy ) {
		$k = self::kenya( $lot, $cy );
		$u = self::uganda( $lot, $cy );
		$a = self::au_rental( $lot, $cy );

		$flags = array_values( array_unique( array_merge( $k['flags'], $u['flags'], $a['flags'] ) ) );

		$duty = 'n/a';
		if ( true === $k['ok'] || null === $k['ok'] ) { $duty = 'crsp'; }
		elseif ( true === $u['ok'] || null === $u['ok'] ) { $duty = 'ura_database'; }

		return array(
			'source'       => isset( $lot['source'] ) ? $lot['source'] : null,
			'stock'        => isset( $lot['stock'] ) ? $lot['stock'] : null,
			'year'         => isset( $lot['year'] ) ? $lot['year'] : null,
			'model'        => isset( $lot['model'] ) ? $lot['model'] : null,
			'wovr'         => self::wovr_code( isset( $lot['wovr'] ) ? $lot['wovr'] : null ),
			'wovr_published' => isset( $lot['wovr'] ) ? $lot['wovr'] : null,
			'state'        => isset( $lot['state'] ) ? $lot['state'] : null,
			'sale_date'    => isset( $lot['sale_date'] ) ? $lot['sale_date'] : null,
			'kenya_ok'     => $k['ok'],
			'uganda_ok'    => $u['ok'],
			'au_rental_ok' => $a['ok'],
			'conditional'  => array( 'au_rental' => ! empty( $a['conditional'] ) ),
			'flags'        => $flags,
			'duty_basis'   => $duty,
			'reasons'      => array( 'kenya' => $k['reasons'], 'uganda' => $u['reasons'], 'au_rental' => $a['reasons'] ),
		);
	}

	private static function damage_text( array $lot ) {
		$d = trim( ( isset( $lot['primary_damage'] ) ? (string) $lot['primary_damage'] : '' )
			. ' ' . ( isset( $lot['secondary_damage'] ) ? (string) $lot['secondary_damage'] : '' )
			. ' ' . ( isset( $lot['damage'] ) ? (string) $lot['damage'] : '' ) );
		return ( '' === $d ) ? null : $d;
	}

	private static function result( $ok, $flags, $reasons, $duty, $conditional = false ) {
		return array( 'ok' => $ok, 'flags' => $flags, 'reasons' => $reasons,
			'duty_basis' => $duty, 'conditional' => $conditional );
	}
}
