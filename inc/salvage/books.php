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

		// "Not inspected" is the ABSENCE of an inspection and must never be read
		// as one. Tested before the positive forms because both contain the word.
		if ( false !== strpos( $s, 'not inspected' ) || false !== strpos( $s, 'uninspected' ) ) { return null; }

		// Two spellings of the same thing. "Inspection Passed Repairable
		// Write-Off" is Pickles' wording; "Inspected Write-Off" is IAA's, found
		// in the live 11 Sep 2026 sale on 11 lots — until then this coder
		// returned null for every one of them and they read as "not published".
		if ( false !== strpos( $s, 'inspection passed' ) ) { return self::INSP; }
		if ( false !== strpos( $s, 'inspected' ) )         { return self::INSP; }
		if ( false !== strpos( $s, 'statutory' ) )         { return self::STAT; }
		if ( false !== strpos( $s, 'repairable' ) )        { return self::REP; }
		if ( false !== strpos( $s, 'no wovr' ) || false !== strpos( $s, 'wovr n/a' )
			|| false !== strpos( $s, 'n/a' ) )             { return self::NONE; }
		return null;
	}

	/* ---------------------------------------------------------------
	 * Year windows — formulas only
	 * ------------------------------------------------------------- */

	/*
	 * The three books are CLOSED, DISJOINT year bands, set by Rop on 10 Sep 2026:
	 *
	 *   Imani Car Rentals  2008 - 2010   (cy-18 .. cy-16)  WOVR N/A only
	 *   Uganda             2011 - 2018   (cy-15 .. cy-8)
	 *   Kenya              2019 +        (cy-7 ..)
	 *
	 * Disjoint on purpose: three different buyers, and an overlap means two of
	 * them bid against each other and inflate a price they both pay. 2011 was
	 * assigned to Uganda deliberately; it is the contested URA year and stays
	 * flagged as such.
	 */

	/** Kenya KEBS: 2019 or later in 2026. */
	public static function kenya_floor( $cy ) { return (int) $cy - 7; }

	/** Uganda band opens at the contested year: 2011 in 2026. */
	public static function uganda_floor( $cy ) { return (int) $cy - 15; }

	/** Uganda band closes at 2018 in 2026 — the only year in the band paying 20% levy rather than 50%. */
	public static function uganda_ceiling( $cy ) { return (int) $cy - 8; }

	/** Uganda contested year: 2011 in 2026 — admitted or not depending on which URA text you read. */
	public static function uganda_boundary_year( $cy ) { return (int) $cy - 15; }

	/** Australian rental band: 2008 - 2010 in 2026. */
	public static function rental_floor( $cy ) { return (int) $cy - 18; }
	public static function rental_ceiling( $cy ) { return (int) $cy - 16; }

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

		$floor   = self::uganda_floor( $cy );
		$ceiling = self::uganda_ceiling( $cy );

		if ( $year < $floor ) {
			$reasons[] = sprintf( 'First registered %d — older than the 15-year limit for %d.', $year, (int) $cy );
			return self::result( false, $flags, $reasons, 'ura_database' );
		}

		// Above the ceiling the car belongs to the Kenya book, not this one. The
		// bands are disjoint so two buyers never bid against each other.
		if ( $year > $ceiling ) {
			$reasons[] = sprintf(
				'First registered %d — newer than the Uganda band (%d-%d). This is Kenya-book stock; the Uganda buyer does not bid on it.',
				$year, $floor, $ceiling
			);
			return self::result( false, $flags, $reasons, 'ura_database' );
		}

		if ( $year === $floor ) {
			// Rop assigned this year to Uganda on 10 Sep 2026, so it IS eligible —
			// but two published URA sources still disagree about it and the flag
			// stays so nobody spends money on it without knowing.
			$flags[]   = 'uganda_boundary_contested_' . $floor;
			$reasons[] = sprintf(
				'%d is the contested boundary year. URA guidance reads "under 15 years old from first registration", which excludes it; URA\'s own environmental levy band runs 9 to 15 years, which admits it. Assigned to the Uganda book by decision, not by resolution — confirm with URA or a clearing agent before committing.',
				$floor
			);
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

		// The rental book is a CLOSED year band, 2008-2010 in 2026.
		if ( null === $year || '' === $year ) {
			$reasons[] = 'Year not published — cannot place this lot in the rental band.';
			return self::result( null, $flags, $reasons, 'n/a' );
		}
		$year  = (int) $year;
		$floor = self::rental_floor( $cy );
		$ceil  = self::rental_ceiling( $cy );

		if ( $year < $floor || $year > $ceil ) {
			$reasons[] = sprintf(
				'First registered %d — outside the rental band (%d-%d) for %d.%s',
				$year, $floor, $ceil, (int) $cy,
				$year > $ceil ? ' Newer stock belongs to the Uganda or Kenya books; the rental buyer does not bid on it.' : ''
			);
			return self::result( false, $flags, $reasons, 'n/a' );
		}

		// Inside the band, the book takes WOVR N/A only. Anything on the register
		// needs a WOVI or VIV and is out of scope by Rop's decision of 10 Sep 2026 —
		// including repairable write-offs, which earlier versions admitted.
		if ( self::STAT === $wovr ) {
			$reasons[] = 'Statutory write-off — never registrable, in any state, at any repair cost.';
			return self::result( false, $flags, $reasons, 'n/a' );
		}
		if ( self::NONE !== $wovr ) {
			$reasons[] = sprintf(
				'WOVR reads %s. The rental book takes WOVR N/A only — anything on the register carries a WOVI or VIV at any age, and age never removes it.',
				$wovr
			);
			return self::result( false, $flags, $reasons, 'n/a' );
		}

		$reasons[] = 'No register entry, so no WOVI or VIV — an ordinary roadworthy applies.';

		// EVERY car in the 2008-2010 band sits below every state's recording
		// threshold (QLD cy-16, NSW/WA cy-15, VIC cy-14), so a clean WOVR reading
		// proves nothing here. That is not a defect of the band — it is why PPSR
		// is mandatory on it rather than advisable.
		$flags[]   = 'history_unverified';
		$flags[]   = 'ppsr_mandatory';
		$info      = self::none_informative_floor( $state, $cy );
		$reasons[] = ( null !== $info )
			? sprintf( '%s only recorded total losses on vehicles newer than %d, so a %d car could be a genuine write-off that was never notifiable. PPSR check is mandatory, not optional.', $state, $info, $year )
			: 'No recording threshold on file for this state, so the clean WOVR reading cannot be relied on. PPSR check is mandatory.';

		return self::result( true, $flags, $reasons, 'n/a' );
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

	/**
	 * Build the lot array from a stored row (or any object/array with the same
	 * field names).
	 *
	 * There is ONE mapping and it lives here. The import writes the book columns
	 * and the board recomputes them on read; if those two used separate mappings
	 * they would drift, and the drift would show as a lot that filters into a
	 * book but displays out of it.
	 *
	 * @param object|array $row
	 */
	public static function from_row( $row ) {
		$r   = (array) $row;
		$get = static function ( $k ) use ( $r ) {
			return ( array_key_exists( $k, $r ) && '' !== $r[ $k ] ) ? $r[ $k ] : null;
		};
		return array(
			'source'           => $get( 'source' ),
			'stock'            => $get( 'stock' ),
			'year'             => null === $get( 'year' ) ? null : (int) $get( 'year' ),
			'make'             => $get( 'make' ),
			'model'            => $get( 'model' ),
			'wovr'             => $get( 'wovr' ),
			'state'            => $get( 'state' ),
			'sale_date'        => $get( 'sale_datetime' ),
			'primary_damage'   => $get( 'primary_damage' ),
			'secondary_damage' => $get( 'secondary_damage' ),
		);
	}

	/**
	 * The calendar year every band is measured against.
	 *
	 * Read from the site's timezone, never from UTC. On 1 January a UTC clock is
	 * still in the previous year for ten hours of the Australian day, and every
	 * band would be off by one for exactly as long.
	 */
	public static function calendar_year() {
		if ( function_exists( 'wp_timezone' ) ) {
			return (int) ( new DateTimeImmutable( 'now', wp_timezone() ) )->format( 'Y' );
		}
		return (int) gmdate( 'Y' );
	}

	/** Human label for a book key. */
	public static function label( $book ) {
		$map = array(
			'kenya'  => 'Kenya',
			'uganda' => 'Uganda',
			'rental' => 'Imani Car Rentals',
		);
		return isset( $map[ $book ] ) ? $map[ $book ] : $book;
	}

	/** The band a book covers in a given year, as a display string. */
	public static function band_label( $book, $cy ) {
		if ( 'kenya' === $book )  { return self::kenya_floor( $cy ) . '+'; }
		if ( 'uganda' === $book ) { return self::uganda_floor( $cy ) . '–' . self::uganda_ceiling( $cy ); }
		if ( 'rental' === $book ) { return self::rental_floor( $cy ) . '–' . self::rental_ceiling( $cy ); }
		return '';
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
