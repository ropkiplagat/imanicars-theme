<?php
/**
 * Salvage board — view helpers.
 *
 * The rendering rule that matters most here: a tri-state value has THREE
 * renderings. True and false get a definite label; NULL gets an em dash plus a
 * title explaining that the source never published the field.
 *
 * Rendering NULL the same as false is the specific bug this board cannot afford.
 * "Sells tomorrow: No" on an IAA lot whose sale time was never published would
 * read as a reason not to check it — and IAA publishes no sale times at all.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class IC_Salvage_View {

	/** Read and sanitise filters from the query string. */
	public static function filters_from_request() {
		$multi = function ( $key ) {
			if ( empty( $_GET[ $key ] ) ) { return array(); }
			$vals = (array) wp_unslash( $_GET[ $key ] );
			return array_values( array_filter( array_map( 'sanitize_text_field', $vals ), 'strlen' ) );
		};

		$f = array(
			'source'        => $multi( 'source' ),
			'model'         => $multi( 'model' ),
			'wovr'          => $multi( 'wovr' ),
			'state'         => $multi( 'state' ),
			'year_min'      => isset( $_GET['year_min'] ) && '' !== $_GET['year_min'] ? (int) $_GET['year_min'] : null,
			'year_max'      => isset( $_GET['year_max'] ) && '' !== $_GET['year_max'] ? (int) $_GET['year_max'] : null,
			'sale_from'     => isset( $_GET['sale_from'] ) ? self::date_or_null( $_GET['sale_from'] ) : null,
			'sale_to'       => isset( $_GET['sale_to'] ) ? self::date_or_null( $_GET['sale_to'] ) : null,
			// Kenya eligibility is a checkbox and it DEFAULTS OFF.
			'kenya_only'    => ! empty( $_GET['kenya_only'] ),
			'exclude_flood' => ! empty( $_GET['exclude_flood'] ),
			'search'        => isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '',
		);
		return $f;
	}

	private static function date_or_null( $v ) {
		$v = sanitize_text_field( wp_unslash( $v ) );
		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v ) ? $v : null;
	}

	/** Is any filter active? Used to choose the right empty state. */
	public static function has_active_filters( array $f ) {
		foreach ( array( 'source', 'model', 'wovr', 'state' ) as $k ) {
			if ( ! empty( $f[ $k ] ) ) { return true; }
		}
		foreach ( array( 'year_min', 'year_max', 'sale_from', 'sale_to' ) as $k ) {
			if ( ! empty( $f[ $k ] ) ) { return true; }
		}
		return ! empty( $f['kenya_only'] ) || ! empty( $f['exclude_flood'] ) || '' !== $f['search'];
	}

	/**
	 * Render a tri-state flag.
	 *
	 * @param bool|int|null $v
	 * @param string        $yes    Label when true.
	 * @param string        $no     Label when false.
	 * @param string        $tone   'good' | 'bad' | 'warn' | 'neutral' — applied to the TRUE state.
	 * @param string        $unknown_note Tooltip explaining why it is unknown.
	 */
	public static function flag( $v, $yes, $no, $tone = 'neutral', $unknown_note = '' ) {
		if ( null === $v || '' === $v ) {
			$note = $unknown_note ? $unknown_note : __( 'The source did not publish this field.', 'imanicars' );
			return '<span class="sb-flag sb-flag--unknown" title="' . esc_attr( $note ) . '" aria-label="' . esc_attr( $note ) . '">&mdash;</span>';
		}
		if ( (int) $v === 1 ) {
			return '<span class="sb-flag sb-flag--' . esc_attr( $tone ) . '">' . esc_html( $yes ) . '</span>';
		}
		return '<span class="sb-flag sb-flag--off">' . esc_html( $no ) . '</span>';
	}

	/** A value that may be absent. Absence is an em dash with an explanation, never a blank cell. */
	public static function val( $v, $unknown_note = '' ) {
		if ( null === $v || '' === $v ) {
			$note = $unknown_note ? $unknown_note : __( 'Not published by the source.', 'imanicars' );
			return '<span class="sb-unknown" title="' . esc_attr( $note ) . '">&mdash;</span>';
		}
		return esc_html( $v );
	}

	/** Integer with thousands separators, or the unknown marker. */
	public static function num( $v, $suffix = '', $unknown_note = '' ) {
		if ( null === $v || '' === $v ) {
			return self::val( null, $unknown_note );
		}
		return esc_html( number_format( (int) $v ) . $suffix );
	}

	/** Decode a JSON text column back to an array. */
	public static function json_list( $v ) {
		if ( empty( $v ) ) { return array(); }
		$a = json_decode( (string) $v, true );
		return is_array( $a ) ? $a : array();
	}

	/** Current status, with the "never observed" case named rather than assumed. */
	public static function status_label( $row ) {
		if ( ! empty( $row->current_status ) ) { return $row->current_status; }
		return __( 'Not yet observed', 'imanicars' );
	}

	public static function status_tone( $row ) {
		$s = $row->current_status;
		if ( ! $s ) { return 'unobserved'; }
		$map = array(
			'Live'      => 'live',
			'Sold'      => 'sold',
			'Passed In' => 'passed',
			'Withdrawn' => 'withdrawn',
			'Gone'      => 'gone',
		);
		return isset( $map[ $s ] ) ? $map[ $s ] : 'unobserved';
	}
}
