<?php
/**
 * Salvage board — module bootstrap.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'IC_SALVAGE_DIR', __DIR__ );

require_once IC_SALVAGE_DIR . '/fees.php';
require_once IC_SALVAGE_DIR . '/estimate.php';
require_once IC_SALVAGE_DIR . '/rules.php';
require_once IC_SALVAGE_DIR . '/saledate.php';
require_once IC_SALVAGE_DIR . '/normalise.php';
require_once IC_SALVAGE_DIR . '/books.php';
require_once IC_SALVAGE_DIR . '/schema.php';
require_once IC_SALVAGE_DIR . '/repo.php';
require_once IC_SALVAGE_DIR . '/view.php';
require_once IC_SALVAGE_DIR . '/access.php';
require_once IC_SALVAGE_DIR . '/ajax.php';
require_once IC_SALVAGE_DIR . '/import.php';

IC_Salvage_Access::init();
IC_Salvage_Ajax::init();
IC_Salvage_Import::init();

add_action( 'after_switch_theme', array( 'IC_Salvage_Schema', 'install' ) );
add_action( 'admin_init', array( 'IC_Salvage_Schema', 'maybe_upgrade' ) );

/** Board assets load only on the board. */
function ic_salvage_enqueue() {
	if ( ! IC_Salvage_Access::is_board_page() ) { return; }
	wp_enqueue_style( 'ic-salvage', IC_THEME_URI . '/assets/css/salvage.css', array( 'ic-main' ), IC_THEME_VERSION );
	wp_enqueue_script( 'ic-salvage', IC_THEME_URI . '/assets/js/salvage.js', array(), IC_THEME_VERSION, true );
	wp_localize_script( 'ic-salvage', 'ICSalvage', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( IC_Salvage_Ajax::NONCE ),
		'strings' => array(
			'saving'    => __( 'Saving…', 'imanicars' ),
			'saved'     => __( 'Recorded', 'imanicars' ),
			'failed'    => __( 'Not saved', 'imanicars' ),
			'notFound'  => __( 'not found', 'imanicars' ),
			'copied'    => __( 'Table copied to the clipboard', 'imanicars' ),
			'copyFail'  => __( 'Could not copy — select the table and copy manually', 'imanicars' ),
			'sending'   => __( 'Sending…', 'imanicars' ),
			'sent'      => __( 'Sent', 'imanicars' ),
			'sendFail'  => __( 'Not sent', 'imanicars' ),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'ic_salvage_enqueue', 20 );
