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
		add_action( 'wp_ajax_ic_salvage_email',            array( __CLASS__, 'email_board' ) );
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
		header( 'Content-Disposition: attachment; filename="' . self::csv_filename() . '"' );

		echo self::build_csv( $rows ); // phpcs:ignore WordPress.Security.EscapeOutput -- CSV body, not HTML.
		exit;
	}

	public static function csv_filename() {
		return 'imani-salvage-' . wp_date( 'Y-m-d' ) . '.csv';
	}

	/**
	 * Render the filtered rows as a CSV string.
	 *
	 * ONE builder, used by the download and by the emailed attachment. Two
	 * builders would drift, and the drift would land in the file Rop forwards to
	 * a buyer rather than in the one he looks at.
	 *
	 * @param object[] $rows
	 * @return string
	 */
	private static function build_csv( array $rows ) {
		$out = fopen( 'php://temp', 'r+' );
		// BOM so Excel reads UTF-8 rather than mangling it.
		fwrite( $out, "\xEF\xBB\xBF" );

		fputcsv( $out, array(
			'Source', 'Stock', 'Lot', 'Sale date (AEST)', 'Sale time known', 'Make', 'Model', 'Variant', 'Year',
			'WOVR', 'Odometer km', 'Primary damage', 'Secondary damage', 'Keys', 'Drives', 'Starts',
			'Colour', 'Location', 'State', 'Kenya eligible (KEBS)', 'Flood PVoC reject', 'VIC statutory EPA licence',
			'Book: Imani Car Rentals', 'Book: Uganda', 'Book: Kenya', 'Book flags',
			'Verdict', 'Est. repair', 'Est. AU value', 'Est. Kenya resale', 'High pre-bid',
			'Current status', 'Latest hammer', 'Estimate (pre-bid)', 'Actual vs estimate',
			'Buyer fee', 'Landed before duty', 'Observations', 'Detail URL',
		) );

		// Books are recomputed against today's calendar year, exactly as the board
		// does. Reading the stored columns would export last year's bands.
		$cy = IC_Salvage_Books::calendar_year();

		foreach ( $rows as $r ) {
			$quote = IC_Salvage_Fees::quote( $r->source, $r->latest_hammer_cents );
			$books = IC_Salvage_Books::assess( IC_Salvage_Books::from_row( $r ), $cy );
			$cmp   = IC_Salvage_Estimate::compare( $r->high_pre_bid, $r->latest_hammer_cents );
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
				self::csv_tri( $books['au_rental_ok'] ),
				self::csv_tri( $books['uganda_ok'] ),
				self::csv_tri( $books['kenya_ok'] ),
				implode( ' | ', (array) $books['flags'] ),
				self::csv_val( $r->verdict ),
				self::csv_val( $r->est_repair ),
				self::csv_val( $r->est_au_value ),
				self::csv_val( $r->est_kenya_resale ),
				self::csv_val( $r->high_pre_bid ),
				$r->current_status ? $r->current_status : 'Live (no observation yet)',
				null === $r->latest_hammer_cents ? 'no price recorded' : IC_Salvage_Money::format( $r->latest_hammer_cents ),
				self::csv_val( IC_Salvage_Estimate::format_estimate( $cmp ) ),
				self::csv_val( IC_Salvage_Estimate::format_variance( $cmp ) ),
				self::csv_fee( $quote['buyer_fee'], $r->latest_hammer_cents ),
				self::csv_fee( $quote['landed_before_duty'], $r->latest_hammer_cents ),
				(int) $r->observation_count,
				self::csv_val( $r->detail_url ),
			) );
		}

		// A trailing note, so the file cannot be read as implying duty is included.
		fputcsv( $out, array() );
		fputcsv( $out, array( 'NOTE', 'Landed before duty excludes Kenyan duty. Duty is assessed on KRA CRSP, not on the price paid — run the KRA calculator per car.' ) );
		fputcsv( $out, array( 'NOTE', '"not found" in a fee column means the auction house publishes no retrievable fee schedule (Manheim). "no price recorded yet" means no hammer price has been observed for that lot — it says nothing about the house\'s fees.' ) );
		fputcsv( $out, array( 'NOTE', sprintf( 'Book columns are computed against %1$d: Imani Car Rentals %2$s (WOVR N/A only), Uganda %3$s, Kenya %4$s. The bands are disjoint — no lot is in two. "unknown" means a field the rule needs was never published, and is not the same as "no".', $cy, IC_Salvage_Books::band_label( 'rental', $cy ), IC_Salvage_Books::band_label( 'uganda', $cy ), IC_Salvage_Books::band_label( 'kenya', $cy ) ) ) );
		fputcsv( $out, array( 'NOTE', '"Actual vs estimate" compares the observed hammer against the High pre-bid ceiling. A range is measured from the end the price missed, never from a midpoint.' ) );

		rewind( $out );
		$csv = stream_get_contents( $out );
		fclose( $out );
		return $csv;
	}

	/* ---------------------------------------------------------------
	 * Email the current view
	 * ------------------------------------------------------------- */

	/**
	 * Send the FILTERED rows, as the same CSV the Export button produces.
	 *
	 * This replaced a mailto: link that opened the mail client with a 20-row
	 * text summary and no attachment. It was labelled "Email" and it did not
	 * email anything — Rop reported it as "email doesn't send the filtered
	 * results", and he was right.
	 *
	 * If WordPress cannot send mail, this says so with the reason. It never
	 * reports success on a send that did not happen: the whole board exists so
	 * that an absent fact is visible as absent.
	 */
	public static function email_board() {
		self::require_access();

		$to = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
		if ( '' === $to || ! is_email( $to ) ) {
			wp_send_json_error( array( 'message' => __( 'That is not an email address. Nothing was sent.', 'imanicars' ) ), 400 );
		}

		$filters = IC_Salvage_View::filters_from_request_array(
			isset( $_POST['filters'] ) ? wp_unslash( $_POST['filters'] ) : ''
		);
		$rows = IC_Salvage_Repo::query( $filters );

		if ( ! $rows ) {
			wp_send_json_error( array( 'message' => __( 'The current filters match no lots. Nothing was sent — an empty board is not worth an email.', 'imanicars' ) ), 400 );
		}

		$csv  = self::build_csv( $rows );
		$file = trailingslashit( get_temp_dir() ) . self::csv_filename();
		if ( false === file_put_contents( $file, $csv ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
			wp_send_json_error( array( 'message' => __( 'The attachment could not be written on the server. Nothing was sent.', 'imanicars' ) ), 500 );
		}

		$cy      = IC_Salvage_Books::calendar_year();
		$subject = sprintf(
			/* translators: 1: lot count, 2: date */
			__( 'Imani salvage board — %1$d lots, %2$s', 'imanicars' ),
			count( $rows ),
			wp_date( 'j M Y' )
		);

		$body = implode( "\n", array(
			sprintf( __( '%d lots in the current view. The full table is attached as a CSV.', 'imanicars' ), count( $rows ) ),
			'',
			self::describe_filters( $filters, $cy ),
			'',
			__( 'Reading the attachment:', 'imanicars' ),
			__( '  · All costs are BEFORE Kenyan duty. Duty is assessed on KRA CRSP, not on what you paid.', 'imanicars' ),
			__( '  · "not found" in a fee column = the house publishes no retrievable fee schedule (Manheim).', 'imanicars' ),
			__( '  · "no price recorded yet" = we have not observed a hammer price. It is not a fee problem.', 'imanicars' ),
			__( '  · "unknown" in a book column = a field the rule needs was never published. It is not a "no".', 'imanicars' ),
			'',
			sprintf(
				__( 'Books, against %1$d: Imani Car Rentals %2$s (WOVR N/A only) · Uganda %3$s · Kenya %4$s. Disjoint — no lot is in two.', 'imanicars' ),
				$cy,
				IC_Salvage_Books::band_label( 'rental', $cy ),
				IC_Salvage_Books::band_label( 'uganda', $cy ),
				IC_Salvage_Books::band_label( 'kenya', $cy )
			),
			'',
			__( 'Private and proprietary. Not for circulation to anyone bidding at the same auctions.', 'imanicars' ),
		) );

		// Capture the reason a send failed instead of reporting a bare false.
		$fail_reason = null;
		$capture     = static function ( $wp_error ) use ( &$fail_reason ) {
			$fail_reason = $wp_error->get_error_message();
		};
		add_action( 'wp_mail_failed', $capture );
		$sent = wp_mail( $to, $subject, $body, array(), array( $file ) );
		remove_action( 'wp_mail_failed', $capture );

		@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

		if ( ! $sent ) {
			wp_send_json_error( array(
				'message' => $fail_reason
					? sprintf( __( 'Not sent: %s', 'imanicars' ), $fail_reason )
					: __( 'WordPress could not send the mail and gave no reason. This site has no SMTP configured, so PHP mail() is being used and the host is probably refusing it. Use Export to Excel and attach it yourself until SMTP is set up.', 'imanicars' ),
			), 500 );
		}

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: 1: lot count, 2: recipient */
				__( 'Sent %1$d lots to %2$s. Handed to the mail server — delivery is not confirmed here.', 'imanicars' ),
				count( $rows ),
				$to
			),
		) );
	}

	/** A one-line, human description of what the attached rows were filtered to. */
	private static function describe_filters( array $f, $cy ) {
		$bits = array();
		foreach ( array( 'source' => 'House', 'model' => 'Model', 'wovr' => 'WOVR', 'state' => 'State' ) as $k => $label ) {
			if ( ! empty( $f[ $k ] ) ) { $bits[] = $label . ': ' . implode( ', ', (array) $f[ $k ] ); }
		}
		if ( ! empty( $f['book'] ) ) {
			$names = array();
			foreach ( (array) $f['book'] as $b ) {
				$names[] = IC_Salvage_Books::label( $b ) . ' ' . IC_Salvage_Books::band_label( $b, $cy );
			}
			$bits[] = 'Book: ' . implode( ' or ', $names );
		}
		if ( ! empty( $f['year_min'] ) || ! empty( $f['year_max'] ) ) {
			$bits[] = 'Year: ' . ( $f['year_min'] ? $f['year_min'] : '…' ) . '–' . ( $f['year_max'] ? $f['year_max'] : '…' );
		}
		if ( ! empty( $f['sale_from'] ) || ! empty( $f['sale_to'] ) ) {
			$bits[] = 'Sale: ' . ( $f['sale_from'] ? $f['sale_from'] : '…' ) . ' to ' . ( $f['sale_to'] ? $f['sale_to'] : '…' );
		}
		if ( ! empty( $f['kenya_only'] ) )    { $bits[] = 'KEBS age window only'; }
		if ( ! empty( $f['exclude_flood'] ) ) { $bits[] = 'flood rejects hidden'; }
		if ( '' !== (string) $f['search'] )   { $bits[] = 'Search: ' . $f['search']; }

		return $bits
			? __( 'Filters applied: ', 'imanicars' ) . implode( ' · ', $bits )
			: __( 'No filters applied — this is the whole board.', 'imanicars' );
	}

	private static function csv_val( $v ) {
		return ( null === $v || '' === $v ) ? '' : (string) $v;
	}

	/**
	 * A fee cell, distinguishing the two reasons it can be absent.
	 *
	 * "not found" means the auction house publishes no retrievable fee schedule —
	 * Manheim. "no price recorded yet" means WE have not observed a hammer price,
	 * which says nothing at all about the house's fees. Printing "not found" for
	 * both made every un-observed Pickles lot read like a Manheim one, and Pickles
	 * fees are published and known.
	 *
	 * @param int|null $cents  The computed figure, or null.
	 * @param int|null $hammer The hammer price the figure was computed from.
	 */
	private static function csv_fee( $cents, $hammer ) {
		if ( null !== $cents ) { return IC_Salvage_Money::format( $cents ); }
		if ( null === $hammer ) { return 'no price recorded yet'; }
		return 'not found';
	}

	/** Tri-state to text. "unknown" is a distinct value, never blank or "no". */
	private static function csv_tri( $v ) {
		if ( null === $v || '' === $v ) { return 'unknown'; }
		return ( (int) $v === 1 ) ? 'yes' : 'no';
	}
}
