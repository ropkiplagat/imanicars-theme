<?php
/**
 * Salvage board — data access.
 *
 * Two rules hold this layer together:
 *
 * 1. An import NEVER deletes a lot and NEVER writes a status. A lot that has
 *    sold is marked sold by an observation, and the next scan must not resurrect
 *    it as Live. Status is derived from the observation history, so there is no
 *    denormalised copy to drift out of step with it.
 *
 * 2. Each feed declares the columns it owns. The IAA scrape must not blank the
 *    valuation columns that arrive from a different sheet, and the valuation
 *    sheet must not blank the scrape's columns. Anything outside a feed's owned
 *    list is left exactly as it was.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class IC_Salvage_Repo {

	/** Statuses a lot can be observed in. */
	public static function statuses() {
		return array( 'Live', 'Sold', 'Passed In', 'Withdrawn', 'Gone' );
	}

	/** Columns owned by a scrape feed (the per-lot facts each auction house publishes). */
	public static function scrape_fields() {
		return array(
			'lot_ref', 'sale_event', 'sale_datetime', 'sale_datetime_raw', 'sale_time_note',
			'make', 'model', 'variant', 'year', 'wovr', 'odometer_km',
			'primary_damage', 'secondary_damage', 'damage_published',
			'keys_present', 'drives', 'starts', 'colour',
			'location', 'state', 'kebs_eligible', 'kebs_reasons',
			'flood_pvoc_reject', 'vic_statutory_epa', 'detail_url', 'data_warnings',
		);
	}

	/** Columns owned by the deep-valuation sheet. */
	public static function valuation_fields() {
		return array(
			'lot_ref', 'airbags', 'start_code', 'colour', 'compliance_date', 'keys_present',
			'est_repair', 'est_au_value', 'est_kenya_resale', 'verdict', 'assessment',
			'high_pre_bid', 'photo_url',
		);
	}

	/**
	 * Insert or update one lot, keyed on (source, stock).
	 *
	 * @param array  $record    Canonical record from IC_Salvage_Normalise.
	 * @param array  $owned     Column names this feed is authoritative for.
	 * @param string $scan_date Y-m-d of the scan this row came from.
	 * @return array{id:int, action:string}
	 */
	public static function upsert_lot( array $record, array $owned, $scan_date ) {
		global $wpdb;
		$table = IC_Salvage_Schema::lots_table();
		$now   = current_time( 'mysql' );

		$source = isset( $record['source'] ) ? (string) $record['source'] : '';
		$stock  = isset( $record['stock'] ) ? (string) $record['stock'] : '';
		if ( '' === $source || '' === $stock ) {
			return array( 'id' => 0, 'action' => 'skipped_no_key' );
		}

		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT id, first_seen FROM {$table} WHERE source = %s AND stock = %s",
			$source, $stock
		) );

		$data = array();
		foreach ( $owned as $field ) {
			if ( ! array_key_exists( $field, $record ) ) { continue; }
			$data[ $field ] = self::encode( $field, $record[ $field ] );
		}

		$data['source']           = $source;
		$data['stock']            = $stock;
		$data['source_scan_date'] = $scan_date;
		$data['last_seen']        = $now;
		$data['updated_at']       = $now;

		if ( $existing ) {
			$wpdb->update( $table, $data, array( 'id' => (int) $existing->id ) );
			return array( 'id' => (int) $existing->id, 'action' => 'updated' );
		}

		$data['first_seen'] = $now;
		$data['created_at'] = $now;
		$wpdb->insert( $table, $data );
		return array( 'id' => (int) $wpdb->insert_id, 'action' => 'inserted' );
	}

	/**
	 * Apply a sparse enrichment patch. Only keys present in the patch are written,
	 * so an empty valuation cell never blanks a value the scrape supplied.
	 */
	public static function apply_patch( array $patch, array $owned ) {
		global $wpdb;
		$table = IC_Salvage_Schema::lots_table();

		$source = isset( $patch['source'] ) ? (string) $patch['source'] : '';
		$stock  = isset( $patch['stock'] ) ? (string) $patch['stock'] : '';
		if ( '' === $source || '' === $stock ) {
			return array( 'id' => 0, 'action' => 'skipped_no_key' );
		}

		$id = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$table} WHERE source = %s AND stock = %s",
			$source, $stock
		) );
		if ( ! $id ) {
			return array( 'id' => 0, 'action' => 'no_matching_lot' );
		}

		$data = array();
		foreach ( $owned as $field ) {
			if ( ! array_key_exists( $field, $patch ) ) { continue; }
			if ( null === $patch[ $field ] ) { continue; }
			$data[ $field ] = self::encode( $field, $patch[ $field ] );
		}
		if ( ! $data ) {
			return array( 'id' => $id, 'action' => 'nothing_to_patch' );
		}

		$data['updated_at'] = current_time( 'mysql' );
		$wpdb->update( $table, $data, array( 'id' => $id ) );
		return array( 'id' => $id, 'action' => 'patched' );
	}

	/** Booleans to 0/1/NULL, arrays to JSON, everything else through. */
	private static function encode( $field, $value ) {
		if ( is_array( $value ) ) {
			return $value ? wp_json_encode( array_values( $value ) ) : null;
		}
		if ( is_bool( $value ) ) {
			return $value ? 1 : 0;
		}
		return $value;
	}

	/* ---------------------------------------------------------------
	 * Reading
	 * ------------------------------------------------------------- */

	/**
	 * Fetch lots with their derived current status.
	 *
	 * @param array $f Filters.
	 * @return object[]
	 */
	public static function query( array $f = array() ) {
		global $wpdb;
		$lots = IC_Salvage_Schema::lots_table();
		$obs  = IC_Salvage_Schema::obs_table();

		list( $where, $params ) = self::build_where( $f );

		$sql = "SELECT l.*,
			(SELECT o.status FROM {$obs} o WHERE o.lot_id = l.id ORDER BY o.observed_on DESC, o.id DESC LIMIT 1) AS current_status,
			(SELECT o.hammer_cents FROM {$obs} o WHERE o.lot_id = l.id AND o.hammer_cents IS NOT NULL ORDER BY o.observed_on DESC, o.id DESC LIMIT 1) AS latest_hammer_cents,
			(SELECT o.observed_on FROM {$obs} o WHERE o.lot_id = l.id ORDER BY o.observed_on DESC, o.id DESC LIMIT 1) AS last_observed_on,
			(SELECT COUNT(*) FROM {$obs} o WHERE o.lot_id = l.id) AS observation_count
			FROM {$lots} l
			WHERE {$where}
			ORDER BY (l.sale_datetime IS NULL), l.sale_datetime ASC, l.source ASC, l.stock ASC";

		if ( $params ) {
			$sql = $wpdb->prepare( $sql, $params );
		}
		$rows = $wpdb->get_results( $sql );
		return $rows ? $rows : array();
	}

	/** Build the WHERE clause. Every value goes through prepare(). */
	private static function build_where( array $f ) {
		$where  = array( '1=1' );
		$params = array();

		foreach ( array( 'source' => 'l.source', 'model' => 'l.model', 'wovr' => 'l.wovr', 'state' => 'l.state' ) as $key => $col ) {
			if ( empty( $f[ $key ] ) ) { continue; }
			$vals = array_values( array_filter( (array) $f[ $key ], 'strlen' ) );
			if ( ! $vals ) { continue; }
			$where[] = $col . ' IN (' . implode( ',', array_fill( 0, count( $vals ), '%s' ) ) . ')';
			$params  = array_merge( $params, $vals );
		}

		if ( ! empty( $f['year_min'] ) ) { $where[] = 'l.year >= %d'; $params[] = (int) $f['year_min']; }
		if ( ! empty( $f['year_max'] ) ) { $where[] = 'l.year <= %d'; $params[] = (int) $f['year_max']; }

		if ( ! empty( $f['sale_from'] ) ) { $where[] = 'l.sale_datetime >= %s'; $params[] = $f['sale_from'] . ' 00:00:00'; }
		if ( ! empty( $f['sale_to'] ) )   { $where[] = 'l.sale_datetime <= %s'; $params[] = $f['sale_to'] . ' 23:59:59'; }

		// Kenya eligibility is a CHECKBOX, default off. When on it admits only
		// lots proven eligible — an "unknown" is not an "eligible".
		if ( ! empty( $f['kenya_only'] ) ) {
			$where[] = 'l.kebs_eligible = 1';
		}

		if ( ! empty( $f['exclude_flood'] ) ) {
			$where[] = '( l.flood_pvoc_reject IS NULL OR l.flood_pvoc_reject = 0 )';
		}

		if ( ! empty( $f['search'] ) ) {
			global $wpdb;
			$like    = '%' . $wpdb->esc_like( (string) $f['search'] ) . '%';
			$where[] = '( l.stock LIKE %s OR l.model LIKE %s OR l.variant LIKE %s OR l.location LIKE %s )';
			array_push( $params, $like, $like, $like, $like );
		}

		return array( implode( ' AND ', $where ), $params );
	}

	/** Distinct values for the filter controls. */
	public static function facets() {
		global $wpdb;
		$t = IC_Salvage_Schema::lots_table();
		return array(
			'source' => $wpdb->get_col( "SELECT DISTINCT source FROM {$t} WHERE source <> '' ORDER BY source" ),
			'model'  => $wpdb->get_col( "SELECT DISTINCT model FROM {$t} WHERE model IS NOT NULL AND model <> '' ORDER BY model" ),
			'wovr'   => $wpdb->get_col( "SELECT DISTINCT wovr FROM {$t} WHERE wovr IS NOT NULL AND wovr <> '' ORDER BY wovr" ),
			'state'  => $wpdb->get_col( "SELECT DISTINCT state FROM {$t} WHERE state IS NOT NULL AND state <> '' ORDER BY state" ),
			'years'  => $wpdb->get_row( "SELECT MIN(year) AS min_year, MAX(year) AS max_year FROM {$t} WHERE year IS NOT NULL" ),
		);
	}

	/* ---------------------------------------------------------------
	 * Observations — append only
	 * ------------------------------------------------------------- */

	public static function add_observation( $lot_id, array $in, $user_id ) {
		global $wpdb;
		$lots = IC_Salvage_Schema::lots_table();
		$obs  = IC_Salvage_Schema::obs_table();

		$lot_id = (int) $lot_id;
		$lot    = $wpdb->get_row( $wpdb->prepare( "SELECT id, source FROM {$lots} WHERE id = %d", $lot_id ) );
		if ( ! $lot ) {
			return new WP_Error( 'ic_salvage_no_lot', __( 'That lot no longer exists.', 'imanicars' ) );
		}

		$status = isset( $in['status'] ) ? (string) $in['status'] : '';
		if ( ! in_array( $status, self::statuses(), true ) ) {
			return new WP_Error( 'ic_salvage_bad_status', __( 'Pick a status from the list.', 'imanicars' ) );
		}

		$hammer = isset( $in['hammer'] ) ? IC_Salvage_Money::parse( $in['hammer'] ) : null;
		if ( isset( $in['hammer'] ) && '' !== trim( (string) $in['hammer'] ) && null === $hammer ) {
			return new WP_Error( 'ic_salvage_bad_hammer', __( 'Hammer price is not a number. Nothing was saved.', 'imanicars' ) );
		}
		if ( null !== $hammer && $hammer < 0 ) {
			return new WP_Error( 'ic_salvage_bad_hammer', __( 'Hammer price cannot be negative.', 'imanicars' ) );
		}

		$quote = IC_Salvage_Fees::quote( $lot->source, $hammer );

		$row = array(
			'lot_id'                   => $lot_id,
			'observed_on'              => self::today(),
			'status'                   => $status,
			'hammer_cents'             => $hammer,
			'buyer_fee_cents'          => $quote['buyer_fee'],
			'landed_before_duty_cents' => $quote['landed_before_duty'],
			'fee_known'                => $quote['known'] ? 1 : 0,
			'fee_partial'              => $quote['partial'] ? 1 : 0,
			'actual_repair_cents'      => isset( $in['actual_repair'] ) ? IC_Salvage_Money::parse( $in['actual_repair'] ) : null,
			'resold_for_cents'         => isset( $in['resold_for'] ) ? IC_Salvage_Money::parse( $in['resold_for'] ) : null,
			'notes'                    => isset( $in['notes'] ) ? wp_kses_post( (string) $in['notes'] ) : null,
			'created_by'               => (int) $user_id,
			'created_at'               => current_time( 'mysql' ),
		);

		$ok = $wpdb->insert( $obs, $row );
		if ( false === $ok ) {
			return new WP_Error( 'ic_salvage_db', __( 'The observation could not be saved.', 'imanicars' ) );
		}
		$row['id']    = (int) $wpdb->insert_id;
		$row['quote'] = $quote;
		return $row;
	}

	/** Observation history for one lot, newest first. */
	public static function observations( $lot_id ) {
		global $wpdb;
		$obs = IC_Salvage_Schema::obs_table();
		$r   = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$obs} WHERE lot_id = %d ORDER BY observed_on DESC, id DESC",
			(int) $lot_id
		) );
		return $r ? $r : array();
	}

	/**
	 * Summary strip: lots tracked, observations recorded, and hammer
	 * average/range by model.
	 *
	 * Averages are computed only over observations that actually carry a hammer
	 * price. A lot with no price recorded is counted as untracked, never as zero.
	 */
	public static function summary( array $f = array() ) {
		global $wpdb;
		$lots = IC_Salvage_Schema::lots_table();
		$obs  = IC_Salvage_Schema::obs_table();

		list( $where, $params ) = self::build_where( $f );

		$sql = "SELECT l.model,
				COUNT(DISTINCT l.id) AS lots,
				COUNT(o.id) AS observations,
				SUM(CASE WHEN o.hammer_cents IS NOT NULL THEN 1 ELSE 0 END) AS priced,
				MIN(o.hammer_cents) AS min_hammer,
				MAX(o.hammer_cents) AS max_hammer,
				AVG(o.hammer_cents) AS avg_hammer
			FROM {$lots} l
			LEFT JOIN {$obs} o ON o.lot_id = l.id
			WHERE {$where}
			GROUP BY l.model
			ORDER BY l.model ASC";

		if ( $params ) { $sql = $wpdb->prepare( $sql, $params ); }
		$rows = $wpdb->get_results( $sql );
		return $rows ? $rows : array();
	}

	/** Totals across the whole board, unfiltered. */
	public static function totals() {
		global $wpdb;
		$lots = IC_Salvage_Schema::lots_table();
		$obs  = IC_Salvage_Schema::obs_table();
		return array(
			'lots'         => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$lots}" ),
			'observations' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$obs}" ),
			'priced'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$obs} WHERE hammer_cents IS NOT NULL" ),
			'scan_date'    => $wpdb->get_var( "SELECT MAX(source_scan_date) FROM {$lots}" ),
			'sources'      => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT source) FROM {$lots}" ),
		);
	}

	public static function today() {
		return current_time( 'Y-m-d' );
	}
}
