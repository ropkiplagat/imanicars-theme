<?php
/**
 * Salvage board — database schema.
 *
 * Two custom tables rather than a custom post type: this is a dataset, not
 * content. Postmeta would turn every filter into a dozen joins, and the board
 * filters on eight columns at once.
 *
 * Design rules that the rest of the system depends on:
 *
 * 1. UNIQUE(source, stock) is the upsert key. A stock number is only unique
 *    within an auction house.
 * 2. Every tri-state column is NULLable. NULL means "the source did not publish
 *    this", which is different from 0/no. Collapsing the two would let an
 *    unknown render as a confident "No".
 * 3. Observations are append-only and are NEVER deleted by an import. The price
 *    history is the point of the system; a lot that has sold is marked sold, not
 *    removed.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class IC_Salvage_Schema {

	const DB_VERSION = '1.0.0';
	const OPT_DB_VERSION = 'ic_salvage_db_version';

	public static function lots_table() {
		global $wpdb;
		return $wpdb->prefix . 'ic_salvage_lots';
	}

	public static function obs_table() {
		global $wpdb;
		return $wpdb->prefix . 'ic_salvage_observations';
	}

	/** Create or upgrade tables. Safe to call repeatedly. */
	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$lots    = self::lots_table();
		$obs     = self::obs_table();

		$sql_lots = "CREATE TABLE {$lots} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			source VARCHAR(20) NOT NULL,
			stock VARCHAR(64) NOT NULL,
			lot_ref VARCHAR(120) NULL,
			sale_event VARCHAR(190) NULL,
			sale_datetime DATETIME NULL,
			sale_datetime_raw VARCHAR(64) NULL,
			sale_time_note TEXT NULL,
			make VARCHAR(60) NULL,
			model VARCHAR(120) NULL,
			variant VARCHAR(190) NULL,
			year SMALLINT UNSIGNED NULL,
			wovr VARCHAR(64) NULL,
			odometer_km INT UNSIGNED NULL,
			primary_damage VARCHAR(190) NULL,
			secondary_damage VARCHAR(190) NULL,
			damage_published TINYINT(1) NULL,
			keys_present TINYINT(1) NULL,
			drives TINYINT(1) NULL,
			starts TINYINT(1) NULL,
			airbags VARCHAR(60) NULL,
			start_code VARCHAR(60) NULL,
			colour VARCHAR(60) NULL,
			compliance_date VARCHAR(20) NULL,
			location VARCHAR(190) NULL,
			state VARCHAR(4) NULL,
			kebs_eligible TINYINT(1) NULL,
			kebs_reasons TEXT NULL,
			flood_pvoc_reject TINYINT(1) NULL,
			vic_statutory_epa TINYINT(1) NULL,
			detail_url TEXT NULL,
			photo_url TEXT NULL,
			est_repair VARCHAR(190) NULL,
			est_au_value VARCHAR(190) NULL,
			est_kenya_resale VARCHAR(190) NULL,
			verdict VARCHAR(40) NULL,
			assessment TEXT NULL,
			high_pre_bid VARCHAR(60) NULL,
			data_warnings TEXT NULL,
			source_scan_date DATE NULL,
			first_seen DATETIME NOT NULL,
			last_seen DATETIME NOT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY source_stock (source, stock),
			KEY idx_sale (sale_datetime),
			KEY idx_model (model),
			KEY idx_year (year),
			KEY idx_state (state),
			KEY idx_kebs (kebs_eligible),
			KEY idx_source (source)
		) {$charset};";

		$sql_obs = "CREATE TABLE {$obs} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			lot_id BIGINT UNSIGNED NOT NULL,
			observed_on DATE NOT NULL,
			status VARCHAR(20) NOT NULL,
			hammer_cents BIGINT NULL,
			buyer_fee_cents BIGINT NULL,
			landed_before_duty_cents BIGINT NULL,
			fee_known TINYINT(1) NOT NULL DEFAULT 0,
			fee_partial TINYINT(1) NOT NULL DEFAULT 0,
			actual_repair_cents BIGINT NULL,
			resold_for_cents BIGINT NULL,
			notes TEXT NULL,
			created_by BIGINT UNSIGNED NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY idx_lot (lot_id),
			KEY idx_observed (observed_on),
			KEY idx_lot_observed (lot_id, observed_on)
		) {$charset};";

		dbDelta( $sql_lots );
		dbDelta( $sql_obs );

		update_option( self::OPT_DB_VERSION, self::DB_VERSION );
	}

	/** Run the installer when the stored version is behind. */
	public static function maybe_upgrade() {
		if ( get_option( self::OPT_DB_VERSION ) !== self::DB_VERSION ) {
			self::install();
		}
	}

	/** Do the tables actually exist? Used by the board to show a real error, not an empty grid. */
	public static function tables_exist() {
		global $wpdb;
		foreach ( array( self::lots_table(), self::obs_table() ) as $t ) {
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) );
			if ( $found !== $t ) { return false; }
		}
		return true;
	}
}
