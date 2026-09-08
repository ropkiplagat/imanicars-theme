<?php
/**
 * Salvage board — write endpoints.
 *
 * Fees are computed on the SERVER and the browser renders the strings it is
 * given. The page never multiplies a percentage in JavaScript: a float in the
 * browser and integer cents on the server would eventually disagree, and the
 * number Rop bids to would be the one that happened to be on screen.
 *
 * Every endpoint checks the capability AND the nonce. The capability alone is
 * not enough (CSRF), and the nonce alone is not enough (a logged-in user without
 * the role).
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class IC_Salvage_Ajax {

	const NONCE = 'ic_salvage';

	public static function init() {
		add_action( 'wp_ajax_ic_salvage_save_observation', array( __CLASS__, 'save_observation' ) );
		add_action( 'wp_ajax_ic_salvage_quote',            array( __CLASS__, 'quote' ) );
		add_action( 'init',                                array( __CLASS__, 'maybe_export' ) );
	}

	private static function require_access() {
		if ( ! is_user_logged_in() || ! current_user_can( IC_Salvage_Access::CAP ) ) {
			wp_send_json_error( array( 'message' => __( 'Not permitted.', 'imanicars' ) ), 403 );
		}
		if ( ! check_ajax_referer( self::NONCE, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Your session expired. Reload the page and try again.', 'imanicars' ) ), 403 );
		}
	}

	/** Record a status / hammer price against a lot. */
	public static function save_observation() {
		self::require_access();

		$lot_id = isset( $_POST['lot_id'] ) ? (int) $_POST['lot_id'] : 0;
		$in     = array(
			'status'        => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '',
			'hammer'        => isset( $_POST['hammer'] ) ? sanitize_text_field( wp_unslash( $_POST['hammer'] ) ) : '',
			'actual_repair' => isset( $_POST['actual_repair'] ) ? sanitize_text_field( wp_unslash( $_POST['actual_repair'] ) ) : '',
			'resold_for'    => isset( $_POST['resold_for'] ) ? sanitize_text_field( wp_unslash( $_POST['resold_for'] ) ) : '',
			'notes'         => isset( $_POST['notes'] ) ? wp_unslash( $_POST['notes'] ) : '',
		);

		$res = IC_Salvage_Repo::add_observation( $lot_id, $in, get_current_user_id() );
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( array( 'message' => $res->get_error_message() ), 400 );
		}

		$quote = $res['quote'];
		wp_send_json_success( array(
			'message'            => __( 'Recorded.', 'imanicars' ),
			'observed_on'        => $res['observed_on'],
			'status'             => $res['status'],
			'hammer'             => null === $res['hammer_cents'] ? null : IC_Salvage_Money::format( $res['hammer_cents'] ),
			'buyer_fee'          => null === $quote['buyer_fee'] ? null : IC_Salvage_Money::format( $quote['buyer_fee'] ),
			'landed_before_duty' => null === $quote['landed_before_duty'] ? null : IC_Salvage_Money::format( $quote['landed_before_duty'] ),
			'fee_known'          => (bool) $quote['known'],
			'fee_partial'        => (bool) $quote['partial'],
			'unavailable_reason' => $quote['unavailable_reason'],
			'caveats'            => $quote['caveats'],
			'components'         => array_map(
				function ( $c ) {
					return array( 'label' => $c['label'], 'amount' => IC_Salvage_Money::format( $c['amount'] ) );
				},
				$quote['components']
			),
		) );
	}

	/** Live fee preview as a hammer price is typed. Computes nothing in the browser. */
	public static function quote() {
		self::require_access();

		$source = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '';
		$raw    = isset( $_POST['hammer'] ) ? sanitize_text_field( wp_unslash( $_POST['hammer'] ) ) : '';
		$cents  = IC_Salvage_Money::parse( $raw );

		if ( '' !== trim( $raw ) && null === $cents ) {
			wp_send_json_success( array(
				'ok'      => false,
				'message' => __( 'Not a number.', 'imanicars' ),
			) );
		}

		$q = IC_Salvage_Fees::quote( $source, $cents );
		wp_send_json_success( array(
			'ok'                 => true,
			'buyer_fee'          => null === $q['buyer_fee'] ? null : IC_Salvage_Money::format( $q['buyer_fee'] ),
			'landed_before_duty' => null === $q['landed_before_duty'] ? null : IC_Salvage_Money::format( $q['landed_before_duty'] ),
			'fee_known'          => (bool) $q['known'],
			'fee_partial'        => (bool) $q['partial'],
			'unavailable_reason' => $q['unavailable_reason'],
			'caveats'            => $q['caveats'],
		) );
	}

	/* ---------------------------------------------------------------
	 * CSV export — opens in Excel, no library, no upload
	 * ------------------------------------------------------------- */

	public static function maybe_export() {
		if ( empty( $_GET['ic_salvage_export'] ) ) { return; }
		if ( ! is_user_logged_in() || ! current_user_can( IC_Salvage_Access::CAP ) ) {
			wp_die( esc_html__( 'Not permitted.', 'imanicars' ), '', array( 'response' => 403 ) );
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), self::NONCE ) ) {
			wp_die( esc_html__( 'That export link has expired. Reload the board and try again.', 'imanicars' ), '', array( 'response' => 403 ) );
		}

		$filters = IC_Salvage_View::filters_from_request();
		$rows    = IC_Salvage_Repo::query( $filters );

		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow', true );
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="imani-salvage-' . gmdate( 'Y-m-d' ) . '.csv"' );

		$out = fopen( 'php://output', 'w' );
		// BOM so Excel reads UTF-8 rather than mangling it.
		fwrite( $out, "\xEF\xBB\xBF" );

		fputcsv( $out, array(
			'Source', 'Stock', 'Lot', 'Sale date (AEST)', 'Sale time known', 'Make', 'Model', 'Variant', 'Year',
			'WOVR', 'Odometer km', 'Primary damage', 'Secondary damage', 'Keys', 'Drives', 'Starts',
			'Colour', 'Location', 'State', 'Kenya eligible (KEBS)', 'Flood PVoC reject', 'VIC statutory EPA licence',
			'Verdict', 'Est. repair', 'Est. AU value', 'Est. Kenya resale', 'High pre-bid',
			'Current status', 'Latest hammer', 'Buyer fee', 'Landed before duty', 'Observations', 'Detail URL',
		) );

		foreach ( $rows as $r ) {
			$quote = IC_Salvage_Fees::quote( $r->source, $r->latest_hammer_cents );
			fputcsv( $out, array(
				$r->source,
				$r->stock,
				self::csv_val( $r->lot_ref ),
				$r->sale_datetime ? $r->sale_datetime : 'not published',
				$r->sale_datetime ? 'yes' : 'no',
				self::csv_val( $r->make ),
				self::csv_val( $r->model ),
				self::csv_val( $r->variant ),
				null === $r->year ? 'unknown' : $r->year,
				self::csv_val( $r->wovr ),
				null === $r->odometer_km ? 'unknown' : $r->odometer_km,
				self::csv_val( $r->primary_damage ),
				self::csv_val( $r->secondary_damage ),
				self::csv_tri( $r->keys_present ),
				self::csv_tri( $r->drives ),
				self::csv_tri( $r->starts ),
				self::csv_val( $r->colour ),
				self::csv_val( $r->location ),
				self::csv_val( $r->state ),
				self::csv_tri( $r->kebs_eligible ),
				self::csv_tri( $r->flood_pvoc_reject ),
				self::csv_tri( $r->vic_statutory_epa ),
				self::csv_val( $r->verdict ),
				self::csv_val( $r->est_repair ),
				self::csv_val( $r->est_au_value ),
				self::csv_val( $r->est_kenya_resale ),
				self::csv_val( $r->high_pre_bid ),
				$r->current_status ? $r->current_status : 'Live (no observation yet)',
				null === $r->latest_hammer_cents ? '' : IC_Salvage_Money::format( $r->latest_hammer_cents ),
				null === $quote['buyer_fee'] ? 'not found' : IC_Salvage_Money::format( $quote['buyer_fee'] ),
				null === $quote['landed_before_duty'] ? 'not found' : IC_Salvage_Money::format( $quote['landed_before_duty'] ),
				(int) $r->observation_count,
				self::csv_val( $r->detail_url ),
			) );
		}

		// A trailing note, so the file cannot be read as implying duty is included.
		fputcsv( $out, array() );
		fputcsv( $out, array( 'NOTE', 'Landed before duty excludes Kenyan duty. Duty is assessed on KRA CRSP, not on the price paid — run the KRA calculator per car. Manheim buyer fees are not published and show as "not found".' ) );

		fclose( $out );
		exit;
	}

	private static function csv_val( $v ) {
		return ( null === $v || '' === $v ) ? '' : (string) $v;
	}

	/** Tri-state to text. "unknown" is a distinct value, never blank or "no". */
	private static function csv_tri( $v ) {
		if ( null === $v || '' === $v ) { return 'unknown'; }
		return ( (int) $v === 1 ) ? 'yes' : 'no';
	}
}
