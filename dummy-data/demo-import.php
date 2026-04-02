<?php
/**
 * Imani Cars — Demo Import
 * Registers the admin page and provides ic_render_demo_import_page().
 *
 * RULES (see SKILL.md fatal errors):
 * - ic_render_demo_import_page() defined ONLY here — never in functions.php
 * - ZERO wp_remote_get() calls — no HTTP downloads
 * - Images use local file_get_contents() only, or are skipped
 * - Registered under add_theme_page() NOT add_management_page()
 */

defined( 'ABSPATH' ) || exit;

/* =========================================================
   REGISTER ADMIN PAGE
   ========================================================= */
function ic_demo_import_menu() {
    add_theme_page(
        __( 'Imani Cars Demo Import', 'imanicars' ),
        __( 'Demo Import', 'imanicars' ),
        'manage_options',
        'ic-demo-import',
        'ic_render_demo_import_page'
    );
}
add_action( 'admin_menu', 'ic_demo_import_menu' );

/* =========================================================
   RENDER PAGE
   ========================================================= */
function ic_render_demo_import_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $result_msg = '';
    $result_type = '';

    if ( isset( $_POST['ic_run_import'] ) && check_admin_referer( 'ic_demo_import', 'ic_demo_nonce' ) ) {
        $result = ic_run_demo_import();
        if ( is_wp_error( $result ) ) {
            $result_msg  = $result->get_error_message();
            $result_type = 'error';
        } else {
            /* translators: %d: number of vehicles created */
            $result_msg  = sprintf( __( 'Demo import complete! %d vehicle posts created.', 'imanicars' ), (int) $result );
            $result_type = 'success';
        }
    }
    ?>
    <div class="wrap">
      <h1><?php esc_html_e( 'Imani Cars Demo Import', 'imanicars' ); ?></h1>
      <p><?php esc_html_e( 'Click the button below to create 14 sample vehicle listings across 4 cities. This helps you see how the theme looks with real content.', 'imanicars' ); ?></p>
      <p><strong><?php esc_html_e( 'Note: This will create new posts. Run only once on a fresh install.', 'imanicars' ); ?></strong></p>

      <?php if ( $result_msg ) : ?>
      <div class="notice notice-<?php echo esc_attr( $result_type === 'success' ? 'success' : 'error' ); ?> is-dismissible">
        <p><?php echo esc_html( $result_msg ); ?></p>
      </div>
      <?php endif; ?>

      <form method="post">
        <?php wp_nonce_field( 'ic_demo_import', 'ic_demo_nonce' ); ?>
        <p>
          <input type="submit" name="ic_run_import" class="button button-primary button-large"
                 value="<?php esc_attr_e( 'Install Demo Data', 'imanicars' ); ?>">
        </p>
      </form>

      <hr>
      <h2><?php esc_html_e( 'What gets imported:', 'imanicars' ); ?></h2>
      <ul style="list-style:disc;margin-left:20px">
        <li><?php esc_html_e( '14 vehicle listings (6 Brisbane, 3 Melbourne, 3 Perth, 2 Darwin)', 'imanicars' ); ?></li>
        <li><?php esc_html_e( 'All vehicle meta (price, year, make, model, specs, dealer)', 'imanicars' ); ?></li>
        <li><?php esc_html_e( 'Taxonomy terms: Makes, Body Types, Cities', 'imanicars' ); ?></li>
        <li><?php esc_html_e( 'No images downloaded — Unsplash CDN used for placeholders', 'imanicars' ); ?></li>
      </ul>
    </div>
    <?php
}

/* =========================================================
   RUN IMPORT
   NOTE: ZERO wp_remote_get() calls — images served from Unsplash CDN
   ========================================================= */
function ic_run_demo_import() {
    $vehicles = [
        [
            'title'       => '2021 Toyota RAV4 GX AWD',
            'city'        => 'Brisbane',
            'make'        => 'Toyota',
            'body'        => 'SUV',
            'condition'   => 'Used',
            'meta'        => [
                '_ic_price'        => 42990,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2021,
                '_ic_make'         => 'Toyota',
                '_ic_model'        => 'RAV4',
                '_ic_badge'        => 'GX AWD',
                '_ic_odometer'     => 38000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Petrol',
                '_ic_body_type'    => 'SUV',
                '_ic_engine'       => '2.5L 4cyl',
                '_ic_colour'       => 'White',
                '_ic_doors'        => 5,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'City Cars Brisbane',
                '_ic_city'         => 'Brisbane',
                '_ic_suburb'       => 'Fortitude Valley',
                '_ic_state'        => 'QLD',
                '_ic_badge_type'   => 'used',
                '_ic_finance_mo'   => 699,
            ],
        ],
        [
            'title'     => '2020 Mazda CX-5 Touring AWD',
            'city'      => 'Brisbane',
            'make'      => 'Mazda',
            'body'      => 'SUV',
            'condition' => 'Used',
            'meta'      => [
                '_ic_price'        => 37500,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2020,
                '_ic_make'         => 'Mazda',
                '_ic_model'        => 'CX-5',
                '_ic_badge'        => 'Touring AWD',
                '_ic_odometer'     => 52000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Diesel',
                '_ic_body_type'    => 'SUV',
                '_ic_engine'       => '2.2L 4cyl Turbo Diesel',
                '_ic_colour'       => 'Red',
                '_ic_doors'        => 5,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'South Side Auto',
                '_ic_city'         => 'Brisbane',
                '_ic_suburb'       => 'Moorooka',
                '_ic_state'        => 'QLD',
                '_ic_badge_type'   => 'used',
                '_ic_finance_mo'   => 595,
            ],
        ],
        [
            'title'     => '2022 Ford Ranger XLT 4x4',
            'city'      => 'Brisbane',
            'make'      => 'Ford',
            'body'      => 'Ute',
            'condition' => 'Used',
            'meta'      => [
                '_ic_price'        => 61900,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2022,
                '_ic_make'         => 'Ford',
                '_ic_model'        => 'Ranger',
                '_ic_badge'        => 'XLT 4x4',
                '_ic_odometer'     => 18000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Diesel',
                '_ic_body_type'    => 'Ute',
                '_ic_engine'       => '2.0L Bi-Turbo Diesel',
                '_ic_colour'       => 'Grey',
                '_ic_doors'        => 4,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'QLD Car Centre',
                '_ic_city'         => 'Brisbane',
                '_ic_suburb'       => 'Springwood',
                '_ic_state'        => 'QLD',
                '_ic_badge_type'   => 'featured',
                '_ic_is_featured'  => '1',
                '_ic_finance_mo'   => 999,
            ],
        ],
        [
            'title'     => '2023 Hyundai Tucson Elite AWD',
            'city'      => 'Brisbane',
            'make'      => 'Hyundai',
            'body'      => 'SUV',
            'condition' => 'New',
            'meta'      => [
                '_ic_price'        => 47900,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2023,
                '_ic_make'         => 'Hyundai',
                '_ic_model'        => 'Tucson',
                '_ic_badge'        => 'Elite AWD',
                '_ic_odometer'     => 9500,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Petrol',
                '_ic_body_type'    => 'SUV',
                '_ic_engine'       => '1.6L Turbo Petrol',
                '_ic_colour'       => 'Blue',
                '_ic_doors'        => 5,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'New',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'South Side Auto',
                '_ic_city'         => 'Brisbane',
                '_ic_suburb'       => 'Moorooka',
                '_ic_state'        => 'QLD',
                '_ic_badge_type'   => 'new',
                '_ic_finance_mo'   => 779,
            ],
        ],
        [
            'title'     => '2021 Subaru Outback AWD Premium',
            'city'      => 'Brisbane',
            'make'      => 'Subaru',
            'body'      => 'Wagon',
            'condition' => 'Used',
            'meta'      => [
                '_ic_price'        => 39990,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2021,
                '_ic_make'         => 'Subaru',
                '_ic_model'        => 'Outback',
                '_ic_badge'        => 'AWD Premium',
                '_ic_odometer'     => 41000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Petrol',
                '_ic_body_type'    => 'Wagon',
                '_ic_engine'       => '2.5L 4cyl',
                '_ic_colour'       => 'Silver',
                '_ic_doors'        => 5,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'QLD Car Centre',
                '_ic_city'         => 'Brisbane',
                '_ic_suburb'       => 'Springwood',
                '_ic_state'        => 'QLD',
                '_ic_badge_type'   => 'used',
                '_ic_finance_mo'   => 649,
            ],
        ],
        [
            'title'     => '2019 Honda CR-V VTi-L 4WD',
            'city'      => 'Brisbane',
            'make'      => 'Honda',
            'body'      => 'SUV',
            'condition' => 'Used',
            'meta'      => [
                '_ic_price'        => 31500,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2019,
                '_ic_make'         => 'Honda',
                '_ic_model'        => 'CR-V',
                '_ic_badge'        => 'VTi-L 4WD',
                '_ic_odometer'     => 74000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Petrol',
                '_ic_body_type'    => 'SUV',
                '_ic_engine'       => '1.5L Turbo',
                '_ic_colour'       => 'Black',
                '_ic_doors'        => 5,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'City Cars Brisbane',
                '_ic_city'         => 'Brisbane',
                '_ic_suburb'       => 'Fortitude Valley',
                '_ic_state'        => 'QLD',
                '_ic_badge_type'   => 'used',
            ],
        ],
        [
            'title'     => '2022 Kia Sportage GT-Line AWD',
            'city'      => 'Melbourne',
            'make'      => 'Kia',
            'body'      => 'SUV',
            'condition' => 'Used',
            'meta'      => [
                '_ic_price'        => 49500,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2022,
                '_ic_make'         => 'Kia',
                '_ic_model'        => 'Sportage',
                '_ic_badge'        => 'GT-Line AWD',
                '_ic_odometer'     => 22000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Petrol',
                '_ic_body_type'    => 'SUV',
                '_ic_engine'       => '1.6L Turbo',
                '_ic_colour'       => 'White',
                '_ic_doors'        => 5,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'Metro Motors Melbourne',
                '_ic_city'         => 'Melbourne',
                '_ic_suburb'       => 'Dandenong',
                '_ic_state'        => 'VIC',
                '_ic_badge_type'   => 'featured',
                '_ic_is_featured'  => '1',
                '_ic_finance_mo'   => 799,
            ],
        ],
        [
            'title'     => '2023 Tesla Model 3 Long Range AWD',
            'city'      => 'Melbourne',
            'make'      => 'Tesla',
            'body'      => 'Sedan',
            'condition' => 'New',
            'meta'      => [
                '_ic_price'        => 76990,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2023,
                '_ic_make'         => 'Tesla',
                '_ic_model'        => 'Model 3',
                '_ic_badge'        => 'Long Range AWD',
                '_ic_odometer'     => 4200,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Electric',
                '_ic_body_type'    => 'Sedan',
                '_ic_engine'       => 'Dual Motor Electric',
                '_ic_colour'       => 'White',
                '_ic_doors'        => 4,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'New',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'Vic Auto Traders',
                '_ic_city'         => 'Melbourne',
                '_ic_suburb'       => 'Footscray',
                '_ic_state'        => 'VIC',
                '_ic_badge_type'   => 'new',
                '_ic_finance_mo'   => 1249,
            ],
        ],
        [
            'title'     => '2021 Toyota HiLux SR5 4x4',
            'city'      => 'Melbourne',
            'make'      => 'Toyota',
            'body'      => 'Ute',
            'condition' => 'Used',
            'meta'      => [
                '_ic_price'        => 52800,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2021,
                '_ic_make'         => 'Toyota',
                '_ic_model'        => 'HiLux',
                '_ic_badge'        => 'SR5 4x4',
                '_ic_odometer'     => 61000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Diesel',
                '_ic_body_type'    => 'Ute',
                '_ic_engine'       => '2.8L Turbo Diesel',
                '_ic_colour'       => 'Black',
                '_ic_doors'        => 4,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'Metro Motors Melbourne',
                '_ic_city'         => 'Melbourne',
                '_ic_suburb'       => 'Dandenong',
                '_ic_state'        => 'VIC',
                '_ic_badge_type'   => 'used',
                '_ic_finance_mo'   => 859,
            ],
        ],
        [
            'title'     => '2021 Isuzu D-Max LS-U 4x4',
            'city'      => 'Perth',
            'make'      => 'Isuzu',
            'body'      => 'Ute',
            'condition' => 'Used',
            'meta'      => [
                '_ic_price'        => 54900,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2021,
                '_ic_make'         => 'Isuzu',
                '_ic_model'        => 'D-Max',
                '_ic_badge'        => 'LS-U 4x4',
                '_ic_odometer'     => 42000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Diesel',
                '_ic_body_type'    => 'Ute',
                '_ic_engine'       => '3.0L Turbo Diesel',
                '_ic_colour'       => 'Silver',
                '_ic_doors'        => 4,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'Perth City Cars',
                '_ic_city'         => 'Perth',
                '_ic_suburb'       => 'Osborne Park',
                '_ic_state'        => 'WA',
                '_ic_badge_type'   => 'used',
                '_ic_finance_mo'   => 899,
            ],
        ],
        [
            'title'     => '2022 Land Rover Defender 110 SE D200',
            'city'      => 'Perth',
            'make'      => 'Land Rover',
            'body'      => 'SUV',
            'condition' => 'Used',
            'meta'      => [
                '_ic_price'        => 115000,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2022,
                '_ic_make'         => 'Land Rover',
                '_ic_model'        => 'Defender',
                '_ic_badge'        => '110 SE D200',
                '_ic_odometer'     => 15000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Diesel',
                '_ic_body_type'    => 'SUV',
                '_ic_engine'       => '2.0L Turbo Diesel',
                '_ic_colour'       => 'Green',
                '_ic_doors'        => 5,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'Swan Valley Motors',
                '_ic_city'         => 'Perth',
                '_ic_suburb'       => 'Midvale',
                '_ic_state'        => 'WA',
                '_ic_badge_type'   => 'featured',
                '_ic_is_featured'  => '1',
                '_ic_finance_mo'   => 1849,
            ],
        ],
        [
            'title'     => '2023 GWM Haval H6 Lux Hybrid',
            'city'      => 'Perth',
            'make'      => 'GWM',
            'body'      => 'SUV',
            'condition' => 'New',
            'meta'      => [
                '_ic_price'        => 44990,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2023,
                '_ic_make'         => 'GWM',
                '_ic_model'        => 'Haval H6',
                '_ic_badge'        => 'Lux Hybrid',
                '_ic_odometer'     => 7800,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Hybrid',
                '_ic_body_type'    => 'SUV',
                '_ic_engine'       => '1.5L Turbo Hybrid',
                '_ic_colour'       => 'White',
                '_ic_doors'        => 5,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'New',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'West Coast Autos',
                '_ic_city'         => 'Perth',
                '_ic_suburb'       => 'Midland',
                '_ic_state'        => 'WA',
                '_ic_badge_type'   => 'new',
                '_ic_finance_mo'   => 729,
            ],
        ],
        [
            'title'     => '2020 Toyota LandCruiser 200 GXL 4WD',
            'city'      => 'Darwin',
            'make'      => 'Toyota',
            'body'      => 'SUV',
            'condition' => 'Used',
            'meta'      => [
                '_ic_price'        => 89900,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2020,
                '_ic_make'         => 'Toyota',
                '_ic_model'        => 'LandCruiser',
                '_ic_badge'        => '200 GXL 4WD',
                '_ic_odometer'     => 68000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Diesel',
                '_ic_body_type'    => 'SUV',
                '_ic_engine'       => '4.5L V8 Turbo Diesel',
                '_ic_colour'       => 'White',
                '_ic_doors'        => 5,
                '_ic_seats'        => 8,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'Top End Auto Sales',
                '_ic_city'         => 'Darwin',
                '_ic_suburb'       => 'Winnellie',
                '_ic_state'        => 'NT',
                '_ic_badge_type'   => 'featured',
                '_ic_is_featured'  => '1',
                '_ic_finance_mo'   => 1449,
            ],
        ],
        [
            'title'     => '2021 Ford Ranger Wildtrak 4x4',
            'city'      => 'Darwin',
            'make'      => 'Ford',
            'body'      => 'Ute',
            'condition' => 'Used',
            'meta'      => [
                '_ic_price'        => 55800,
                '_ic_price_label'  => 'Drive Away',
                '_ic_year'         => 2021,
                '_ic_make'         => 'Ford',
                '_ic_model'        => 'Ranger',
                '_ic_badge'        => 'Wildtrak 4x4',
                '_ic_odometer'     => 39000,
                '_ic_transmission' => 'Auto',
                '_ic_fuel_type'    => 'Diesel',
                '_ic_body_type'    => 'Ute',
                '_ic_engine'       => '2.0L Bi-Turbo Diesel',
                '_ic_colour'       => 'Orange',
                '_ic_doors'        => 4,
                '_ic_seats'        => 5,
                '_ic_condition'    => 'Used',
                '_ic_seller_type'  => 'Dealer',
                '_ic_dealer_name'  => 'NT Car Centre',
                '_ic_city'         => 'Darwin',
                '_ic_suburb'       => 'Berrimah',
                '_ic_state'        => 'NT',
                '_ic_badge_type'   => 'used',
                '_ic_finance_mo'   => 909,
            ],
        ],
    ];

    $count = 0;

    foreach ( $vehicles as $vehicle ) {
        $post_data = [
            'post_title'   => sanitize_text_field( $vehicle['title'] ),
            'post_content' => 'Well-maintained vehicle. Full service history available. Contact us for more details and to arrange an inspection.',
            'post_status'  => 'publish',
            'post_type'    => 'vehicle',
        ];

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            continue;
        }

        // Save meta fields
        foreach ( $vehicle['meta'] as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }

        // Assign taxonomy terms
        if ( ! empty( $vehicle['city'] ) ) {
            wp_set_post_terms( $post_id, [ $vehicle['city'] ], 'vehicle_city', false );
        }
        if ( ! empty( $vehicle['make'] ) ) {
            wp_set_post_terms( $post_id, [ $vehicle['make'] ], 'vehicle_make', false );
        }
        if ( ! empty( $vehicle['body'] ) ) {
            wp_set_post_terms( $post_id, [ $vehicle['body'] ], 'vehicle_body', false );
        }
        if ( ! empty( $vehicle['condition'] ) ) {
            wp_set_post_terms( $post_id, [ $vehicle['condition'] ], 'vehicle_condition', false );
        }

        $count++;
    }

    return $count;
}
