<?php
/**
 * Salvage board — source row normalisation.
 *
 * Pure logic, no WordPress dependency.
 *
 * The three auction houses publish three different column sets. This layer maps
 * each into one canonical lot record so the board can show them side by side.
 *
 * The governing rule: a field absent from a source becomes NULL, never a
 * plausible default. Half these columns exist for only one of the three houses,
 * and a defaulted value is indistinguishable from a real one once it is in the
 * table.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'IC_SALVAGE_TEST' ) ) { exit; }

class IC_Salvage_Normalise {

	/** Canonical, empty lot record. Every importer starts here. */
	public static function blank() {
		return array(
			'source'              => null,
			'stock'               => null,
			'lot_ref'             => null,
			'sale_event'          => null,
			'sale_datetime'       => null,
			'sale_datetime_raw'   => null,
			'sale_time_note'      => null,
			'make'                => null,
			'model'               => null,
			'variant'             => null,
			'year'                => null,
			'wovr'                => null,
			'odometer_km'         => null,
			'primary_damage'      => null,
			'secondary_damage'    => null,
			'damage_published'    => null,
			'keys_present'        => null,
			'drives'              => null,
			'starts'              => null,
			'airbags'             => null,
			'start_code'          => null,
			'colour'              => null,
			'compliance_date'     => null,
			'location'            => null,
			'state'               => null,
			'kebs_eligible'       => null,
			'kebs_reasons'        => array(),
			'book_kenya'          => null,
			'book_uganda'         => null,
			'book_rental'         => null,
			'book_flags'          => array(),
			'book_cy'             => null,
			'flood_pvoc_reject'   => null,
			'vic_statutory_epa'   => null,
			'detail_url'          => null,
			'photo_url'           => null,
			'est_repair'          => null,
			'est_au_value'        => null,
			'est_kenya_resale'    => null,
			'verdict'             => null,
			'assessment'          => null,
			'high_pre_bid'        => null,
			'data_warnings'       => array(),
		);
	}

	/** Model → make. Marque facts, not inferences; unknown models return null. */
	public static function make_for_model( $model ) {
		$m = strtolower( trim( (string) $model ) );
		if ( '' === $m ) { return null; }
		$map = array(
			'rav4'        => 'Toyota',
			'prado'       => 'Toyota',
			'landcruiser' => 'Toyota',
			'land cruiser' => 'Toyota',
			'hilux'       => 'Toyota',
			'ranger'      => 'Ford',
			'everest'     => 'Ford',
		);
		foreach ( $map as $needle => $make ) {
			if ( false !== strpos( $m, $needle ) ) { return $make; }
		}
		return null;
	}

	/** "13,597 km" | "8608" | "?" | "" => int|null */
	public static function km( $raw ) {
		if ( null === $raw ) { return null; }
		$s = strtolower( trim( (string) $raw ) );
		if ( '' === $s || '?' === $s || '-' === $s || 'n/a' === $s ) { return null; }
		$s = str_replace( array( ',', ' ', 'km', "\xc2\xa0" ), '', $s );
		if ( ! preg_match( '/^\d+$/', $s ) ) { return null; }
		return (int) $s;
	}

	/** "Y" | "Yes" | "N" | "No" | "" => bool|null */
	public static function tri( $raw ) {
		if ( null === $raw ) { return null; }
		$s = strtolower( trim( (string) $raw ) );
		if ( '' === $s || '?' === $s || '-' === $s || 'unknown' === $s ) { return null; }
		if ( in_array( $s, array( 'y', 'yes', 'true', '1' ), true ) ) { return true; }
		if ( in_array( $s, array( 'n', 'no', 'false', '0' ), true ) ) { return false; }
		return null;
	}

	/** Year as int, or null when absent/implausible. */
	public static function year( $raw ) {
		if ( null === $raw ) { return null; }
		$s = trim( (string) $raw );
		if ( ! preg_match( '/^(\d{4})$/', $s, $m ) ) { return null; }
		$y = (int) $m[1];
		return ( $y >= 1900 && $y <= 2100 ) ? $y : null;
	}

	/** Trim to null — empty string, "-" and "?" are absences, not values. */
	public static function s( $raw ) {
		if ( null === $raw ) { return null; }
		$v = trim( (string) $raw );
		if ( '' === $v || '-' === $v || '?' === $v ) { return null; }
		return $v;
	}

	/** "Front + Front Window" => array('Front', 'Front Window') */
	public static function split_damage( $raw ) {
		$v = self::s( $raw );
		if ( null === $v ) { return array( null, null ); }
		$parts = preg_split( '/\s*\+\s*/', $v, 2 );
		$p     = isset( $parts[0] ) ? trim( $parts[0] ) : '';
		$q     = isset( $parts[1] ) ? trim( $parts[1] ) : '';
		if ( '(none)' === strtolower( $p ) ) { $p = ''; }
		if ( '(none)' === strtolower( $q ) ) { $q = ''; }
		return array( ( '' === $p ? null : $p ), ( '' === $q ? null : $q ) );
	}

	/** Apply the screening rules to a partly-filled record. */
	public static function apply_rules( array $r, $calendar_year ) {
		$damage = trim( (string) $r['primary_damage'] . ' ' . (string) $r['secondary_damage'] );
		$damage = ( '' === trim( $damage ) ) ? null : $damage;

		$r['damage_published']  = ( null !== $damage );
		$r['flood_pvoc_reject'] = IC_Salvage_Rules::flood_reject( $damage );

		$k                   = IC_Salvage_Rules::kenya_eligible( $r['year'], $damage, $calendar_year );
		$r['kebs_eligible']  = $k['eligible'];
		$r['kebs_reasons']   = $k['reasons'];

		if ( null === $r['state'] ) {
			$r['state'] = IC_Salvage_Rules::state_from_location( $r['location'] );
		}
		$r['vic_statutory_epa'] = IC_Salvage_Rules::vic_statutory_epa( $r['state'], $r['wovr'] );

		$conflict = IC_Salvage_Rules::drivetrain_conflict( $r['model'], $r['variant'] );
		if ( null !== $conflict ) {
			$r['data_warnings'][] = $conflict;
		}

		// The three destination books. Stored so they can be filtered in SQL;
		// book_cy records which calendar year the bands were measured against,
		// because every band moves on 1 January and a stored 1 is only true for
		// the year it was computed in.
		$b = IC_Salvage_Books::assess( IC_Salvage_Books::from_row( $r ), $calendar_year );
		$r['book_kenya']  = $b['kenya_ok'];
		$r['book_uganda'] = $b['uganda_ok'];
		$r['book_rental'] = $b['au_rental_ok'];
		$r['book_flags']  = $b['flags'];
		$r['book_cy']     = (int) $calendar_year;

		return $r;
	}

	/**
	 * IAA raw scrape row.
	 * Columns: Search, Year, Model/variant, Stock #, Damage, WOVR, Odometer,
	 *          Keys, Drives, Starts, Location, Kenya eligible, Listing
	 */
	public static function from_iaa_scrape( array $row, $ctx ) {
		$r = self::blank();
		$r['source'] = 'IAA';
		$r['model']  = self::s( self::col( $row, 0 ) );
		$r['year']   = self::year( self::col( $row, 1 ) );
		$r['variant'] = self::s( self::col( $row, 2 ) );
		$r['stock']  = self::s( self::col( $row, 3 ) );

		list( $p, $q ) = self::split_damage( self::col( $row, 4 ) );
		$r['primary_damage']   = $p;
		$r['secondary_damage'] = $q;

		$r['wovr']         = self::s( self::col( $row, 5 ) );
		$r['odometer_km']  = self::km( self::col( $row, 6 ) );
		$r['keys_present'] = self::tri( self::col( $row, 7 ) );
		$r['drives']       = self::tri( self::col( $row, 8 ) );
		$r['starts']       = self::tri( self::col( $row, 9 ) );
		$r['location']     = self::s( self::col( $row, 10 ) );
		$r['detail_url']   = self::s( self::col( $row, 12 ) );
		$r['make']         = self::make_for_model( $r['model'] );

		// IAA publishes no sale date in this feed. Left null on purpose: a blank
		// "sells tomorrow" must read as unknown, not as "no".
		$r['sale_time_note'] = 'IAA does not publish a sale date in this feed — sale timing is unknown for this lot.';

		return self::apply_rules( $r, $ctx['calendar_year'] );
	}

	/**
	 * Pickles salvage row.
	 * Columns: Model, Year, Variant, Stock #, WOVR, Odometer, Location, Sale starts
	 */
	public static function from_pickles( array $row, $ctx ) {
		$r = self::blank();
		$r['source']      = 'Pickles';
		$r['model']       = self::s( self::col( $row, 0 ) );
		$r['year']        = self::year( self::col( $row, 1 ) );
		$r['variant']     = self::s( self::col( $row, 2 ) );
		$r['stock']       = self::s( self::col( $row, 3 ) );
		$r['wovr']        = self::s( self::col( $row, 4 ) );
		$r['odometer_km'] = self::km( self::col( $row, 5 ) );
		$r['location']    = self::s( self::col( $row, 6 ) );
		$r['make']        = self::make_for_model( $r['model'] );

		$r = self::attach_sale_time( $r, self::col( $row, 7 ), $ctx );

		// Pickles publishes no damage description in this feed. Damage stays null,
		// which makes Kenya eligibility "unknown" rather than "eligible" — water
		// damage cannot be ruled out on a lot whose damage was never published.
		return self::apply_rules( $r, $ctx['calendar_year'] );
	}

	/**
	 * Manheim salvage row.
	 * Columns: Model, Year, Description, Stock #, WOVR, Odometer, Colour, Location, Sale starts
	 */
	public static function from_manheim( array $row, $ctx ) {
		$r = self::blank();
		$r['source']      = 'Manheim';
		$r['model']       = self::s( self::col( $row, 0 ) );
		$r['year']        = self::year( self::col( $row, 1 ) );
		$r['variant']     = self::s( self::col( $row, 2 ) );
		$r['stock']       = self::s( self::col( $row, 3 ) );
		$r['wovr']        = self::s( self::col( $row, 4 ) );
		$r['odometer_km'] = self::km( self::col( $row, 5 ) );
		$r['colour']      = self::s( self::col( $row, 6 ) );
		$r['location']    = self::s( self::col( $row, 7 ) );
		$r['make']        = self::make_for_model( $r['model'] );

		$r = self::attach_sale_time( $r, self::col( $row, 8 ), $ctx );

		return self::apply_rules( $r, $ctx['calendar_year'] );
	}

	/**
	 * IAA deep-valuation row — ENRICHMENT ONLY.
	 *
	 * Returns a sparse patch keyed by stock number. It never creates a lot and
	 * never overwrites a scrape field with a blank.
	 *
	 * Columns: Photo/listing, Year, Vehicle, Stock #, Primary Damage, Secondary
	 * Damage, WOVR, Odometer, Start Code, Keys, Air Bags, Colour, Compliance,
	 * Parts subtotal, Paint & labour, Est. repair, Est. AU value, Est. Kenya
	 * resale, Verdict, Assessment, Lane/Lot, Location, High pre-bid
	 */
	public static function from_iaa_valuation( array $row, $ctx ) {
		$patch = array(
			'source' => 'IAA',
			'stock'  => self::s( self::col( $row, 3 ) ),
		);
		$map = array(
			8  => 'start_code',
			9  => 'keys_present',
			10 => 'airbags',
			11 => 'colour',
			12 => 'compliance_date',
			15 => 'est_repair',
			16 => 'est_au_value',
			17 => 'est_kenya_resale',
			18 => 'verdict',
			19 => 'assessment',
			20 => 'lot_ref',
			22 => 'high_pre_bid',
		);
		foreach ( $map as $idx => $field ) {
			$v = self::s( self::col( $row, $idx ) );
			if ( null === $v ) { continue; }
			$patch[ $field ] = ( 'keys_present' === $field ) ? self::tri( $v ) : $v;
		}
		return $patch;
	}

	/** Parse a published sale-start string onto a record. */
	private static function attach_sale_time( array $r, $raw, $ctx ) {
		$parsed = IC_Salvage_SaleDate::parse( $raw, $ctx['reference_date'], $ctx['timezone'] );
		$r['sale_datetime_raw'] = $parsed['raw'];
		$r['sale_datetime']     = $parsed['datetime'];
		if ( null !== $parsed['unknown_reason'] ) {
			$r['sale_time_note'] = $parsed['unknown_reason'];
		}
		if ( false === $parsed['weekday_ok'] ) {
			$r['data_warnings'][] = $parsed['unknown_reason'];
		}
		return $r;
	}

	/** Safe positional column read. */
	private static function col( array $row, $i ) {
		return array_key_exists( $i, $row ) ? $row[ $i ] : null;
	}

	/** Default import context. */
	public static function context( $reference_date, $calendar_year = null, $timezone = IC_Salvage_SaleDate::DEFAULT_TZ ) {
		if ( null === $calendar_year ) {
			$calendar_year = (int) substr( $reference_date, 0, 4 );
		}
		return array(
			'reference_date' => $reference_date,
			'calendar_year'  => (int) $calendar_year,
			'timezone'       => $timezone,
		);
	}
}
