<?php
/**
 * Imani Cars — Demo Data Installer
 * Adds a page at WP Admin → Tools → Imani Cars Demo
 * Downloads real car images (free via picsum/Unsplash) into the media library.
 *
 * REMOVE this file after installing demo data on a production site.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   ADMIN MENU + PAGE
   ============================================================ */
add_action( 'admin_menu', 'ic_demo_menu' );
function ic_demo_menu() {
    add_management_page(
        'Imani Cars Demo Data',
        'Imani Cars Demo',
        'manage_options',
        'imanicars-demo',
        'ic_demo_page_html'
    );
}

function ic_demo_page_html() {
    $count = wp_count_posts( 'vehicle' );
    $existing = (int) ( $count->publish ?? 0 );
    ?>
    <div class="wrap">
        <h1>🚗 Imani Cars — Demo Data Installer</h1>
        <p>Installs 12 demo vehicle listings with real car images downloaded into your media library. Images are sourced from <strong>picsum.photos</strong> (powered by Unsplash — free to use for demos).</p>

        <?php if ( $existing > 0 ) : ?>
        <div class="notice notice-warning"><p><strong>Note:</strong> <?php echo $existing; ?> vehicle listing(s) already exist. The installer will skip duplicates.</p></div>
        <?php endif; ?>

        <table class="widefat" style="max-width:600px;margin:20px 0;">
            <thead><tr><th>What gets installed</th><th>Count</th></tr></thead>
            <tbody>
                <tr><td>Vehicle listings (with images)</td><td>12</td></tr>
                <tr><td>Cities covered</td><td>Brisbane, Melbourne, Perth, Darwin</td></tr>
                <tr><td>Car types</td><td>SUV, Ute, Sedan, Hatchback, Wagon</td></tr>
                <tr><td>Images downloaded to media library</td><td>12 (800×534px each)</td></tr>
                <tr><td>Support pages</td><td>Finance, Sell Your Car, About, Contact + more</td></tr>
            </tbody>
        </table>

        <div id="ic-progress" style="display:none;background:#fff;border:1px solid #ccc;border-radius:4px;padding:15px;margin:15px 0;max-width:700px;max-height:400px;overflow-y:auto;font-family:monospace;font-size:12px;line-height:1.6;"></div>

        <button id="ic-install-btn" class="button button-primary button-hero" onclick="icRunInstall(this)">
            ▶ Install Demo Data
        </button>
        &nbsp;
        <span id="ic-done-msg" style="display:none;color:green;font-weight:bold;font-size:16px;">✅ Installation complete! <a href="<?php echo admin_url( 'edit.php?post_type=vehicle' ); ?>">View vehicles →</a></span>
    </div>

    <script>
    function icRunInstall(btn) {
        btn.disabled = true;
        btn.textContent = '⏳ Installing…';
        var box = document.getElementById('ic-progress');
        box.style.display = 'block';
        box.innerHTML = 'Starting…\n';

        fetch(ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=ic_run_demo_install&nonce=<?php echo wp_create_nonce( 'ic_demo_install' ); ?>'
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
            if ( data.success ) {
                box.innerHTML = data.data.log.join('\n');
                btn.textContent = '✅ Done';
                document.getElementById('ic-done-msg').style.display = 'inline';
            } else {
                box.innerHTML = '❌ Error: ' + JSON.stringify(data.data);
                btn.disabled = false;
                btn.textContent = '▶ Retry';
            }
        })
        .catch(function(err){
            box.innerHTML = '❌ Network error: ' + err;
            btn.disabled = false;
            btn.textContent = '▶ Retry';
        });
    }
    </script>
    <?php
}

/* ============================================================
   AJAX HANDLER
   ============================================================ */
add_action( 'wp_ajax_ic_run_demo_install', 'ic_ajax_demo_install' );
function ic_ajax_demo_install() {
    check_ajax_referer( 'ic_demo_install', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

    // Required for wp_upload_bits(), wp_insert_attachment(), wp_generate_attachment_metadata()
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $log     = [];
    $created = 0;

    /* ----------------------------------------------------------
       1. VEHICLE DATA
       All meta keys match what content.php and single.php read.
       ---------------------------------------------------------- */
    $vehicles = [
        [
            'title'     => '2022 Toyota RAV4 GXL Hybrid AWD',
            'year'      => '2022',
            'make'      => 'Toyota',
            'model'     => 'RAV4',
            'variant'   => 'GXL Hybrid AWD',
            'price'     => '42990',
            'km'        => '28500',
            'fuel'      => 'Hybrid',
            'trans'     => 'CVT Auto',
            'body'      => 'SUV',
            'colour'    => 'Glacier White',
            'seats'     => '5',
            'reg'       => 'QLD until Dec 2025',
            'vin'       => 'JTMWRREV7ND100001',
            'city'      => 'brisbane',
            'condition' => 'used',
            'views'     => '312',
            'enquiries' => '7',
            'days_ago'  => '3',
            'img_url'   => 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=800&h=534&fit=crop',
            'content'   => 'Immaculate 2022 Toyota RAV4 GXL Hybrid AWD. Full service history, one owner from new. Features Toyota Safety Sense, Apple CarPlay, Android Auto, dual-zone climate, heated front seats, panoramic moonroof. Books available.',
        ],
        [
            'title'     => '2022 Ford Ranger XLT 4x4 Double Cab',
            'year'      => '2022',
            'make'      => 'Ford',
            'model'     => 'Ranger',
            'variant'   => 'XLT 4x4 Double Cab',
            'price'     => '56990',
            'km'        => '32000',
            'fuel'      => 'Diesel',
            'trans'     => 'Auto 10-Speed',
            'body'      => 'Ute',
            'colour'    => 'Meteor Grey',
            'seats'     => '5',
            'reg'       => 'QLD until Mar 2026',
            'vin'       => 'MNALXXMJ2ME200002',
            'city'      => 'brisbane',
            'condition' => 'used',
            'views'     => '487',
            'enquiries' => '11',
            'days_ago'  => '1',
            'img_url'   => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=534&fit=crop',
            'content'   => 'Low km 2022 Ford Ranger XLT. Tow bar, reverse camera, Android Auto/Apple CarPlay, Ford Co-Pilot360. Excellent throughout — ready to work or play.',
        ],
        [
            'title'     => '2021 Mazda CX-5 Akera Turbo AWD',
            'year'      => '2021',
            'make'      => 'Mazda',
            'model'     => 'CX-5',
            'variant'   => 'Akera Turbo AWD',
            'price'     => '37990',
            'km'        => '41000',
            'fuel'      => 'Petrol',
            'trans'     => 'Auto 6-Speed',
            'body'      => 'SUV',
            'colour'    => 'Soul Red Crystal',
            'seats'     => '5',
            'reg'       => 'VIC until Aug 2025',
            'vin'       => 'JM7KF2W7XM0300003',
            'city'      => 'melbourne',
            'condition' => 'used',
            'views'     => '205',
            'enquiries' => '4',
            'days_ago'  => '5',
            'img_url'   => 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=800&h=534&fit=crop',
            'content'   => 'Top-of-range Mazda CX-5 Akera Turbo AWD in stunning Soul Red. Premium Bose sound, leather interior, head-up display, i-Activsense safety. One owner, full service history.',
        ],
        [
            'title'     => '2023 Hyundai Tucson Elite Hybrid AWD',
            'year'      => '2023',
            'make'      => 'Hyundai',
            'model'     => 'Tucson',
            'variant'   => 'Elite Hybrid AWD',
            'price'     => '47990',
            'km'        => '14200',
            'fuel'      => 'Hybrid',
            'trans'     => 'Auto 6-Speed',
            'body'      => 'SUV',
            'colour'    => 'Magnetic Force',
            'seats'     => '5',
            'reg'       => 'WA until Nov 2025',
            'vin'       => 'TMAJ381AXPJ400004',
            'city'      => 'perth',
            'condition' => 'used',
            'views'     => '178',
            'enquiries' => '3',
            'days_ago'  => '7',
            'img_url'   => 'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?w=800&h=534&fit=crop',
            'content'   => 'Near-new 2023 Hyundai Tucson Elite Hybrid. Balance of factory warranty. Panoramic sunroof, 10.25" touchscreen, wireless charging, BLIS, 360° camera. Stunning condition.',
        ],
        [
            'title'     => '2020 BMW 3 Series 330i M Sport',
            'year'      => '2020',
            'make'      => 'BMW',
            'model'     => '3 Series',
            'variant'   => '330i M Sport',
            'price'     => '54990',
            'km'        => '55000',
            'fuel'      => 'Petrol',
            'trans'     => 'Auto 8-Speed',
            'body'      => 'Sedan',
            'colour'    => 'Mineral White',
            'seats'     => '5',
            'reg'       => 'VIC until Jun 2025',
            'vin'       => 'WBA5R3C06LFJ50005',
            'city'      => 'melbourne',
            'condition' => 'used',
            'views'     => '341',
            'enquiries' => '8',
            'days_ago'  => '2',
            'img_url'   => 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800&h=534&fit=crop',
            'content'   => 'Stunning BMW 330i M Sport with full dealer service history. Live Cockpit Professional, Harman Kardon sound system, heated M Sport seats, parking assist. No expense spared.',
        ],
        [
            'title'     => '2023 Kia Sportage GT-Line PHEV AWD',
            'year'      => '2023',
            'make'      => 'Kia',
            'model'     => 'Sportage',
            'variant'   => 'GT-Line PHEV AWD',
            'price'     => '52990',
            'km'        => '8200',
            'fuel'      => 'Hybrid',
            'trans'     => 'Auto 6-Speed',
            'body'      => 'SUV',
            'colour'    => 'Snow White Pearl',
            'seats'     => '5',
            'reg'       => 'QLD until Feb 2026',
            'vin'       => 'KNAJX814XPK600006',
            'city'      => 'brisbane',
            'condition' => 'demo',
            'views'     => '156',
            'enquiries' => '2',
            'days_ago'  => '9',
            'img_url'   => 'https://images.unsplash.com/photo-1609521263047-f8f205293f24?w=800&h=534&fit=crop',
            'content'   => 'Low km dealer demo Kia Sportage GT-Line PHEV. Balance of 7-year factory warranty. 360° camera, Meridian sound, ventilated front seats, heads-up display. Exceptional value.',
        ],
        [
            'title'     => '2022 Nissan Navara Pro-4X King Cab',
            'year'      => '2022',
            'make'      => 'Nissan',
            'model'     => 'Navara',
            'variant'   => 'Pro-4X King Cab',
            'price'     => '48990',
            'km'        => '28000',
            'fuel'      => 'Diesel',
            'trans'     => 'Manual 6-Speed',
            'body'      => 'Ute',
            'colour'    => 'Gun Metallic',
            'seats'     => '4',
            'reg'       => 'NT until Sep 2025',
            'vin'       => 'MNTCUD22XNK700007',
            'city'      => 'darwin',
            'condition' => 'used',
            'views'     => '223',
            'enquiries' => '5',
            'days_ago'  => '4',
            'img_url'   => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?w=800&h=534&fit=crop',
            'content'   => 'Adventure-ready Nissan Navara Pro-4X with Bilstein off-road suspension, locking rear diff, and all-terrain tyres. Leather interior, active safety, tow bar. Darwin ready.',
        ],
        [
            'title'     => '2019 Volkswagen Golf GTI 5-Door',
            'year'      => '2019',
            'make'      => 'Volkswagen',
            'model'     => 'Golf',
            'variant'   => 'GTI 5-Door',
            'price'     => '28990',
            'km'        => '68000',
            'fuel'      => 'Petrol',
            'trans'     => 'DSG 7-Speed',
            'body'      => 'Hatchback',
            'colour'    => 'Deep Black Pearl',
            'seats'     => '5',
            'reg'       => 'VIC until Jul 2025',
            'vin'       => 'WVWZZZAUZKY800008',
            'city'      => 'melbourne',
            'condition' => 'used',
            'views'     => '289',
            'enquiries' => '6',
            'days_ago'  => '6',
            'img_url'   => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&h=534&fit=crop',
            'content'   => 'Classic VW Golf GTI in stunning condition. Full dealer service history, Dynaudio Soundsystem, sport bucket seats, adaptive cruise control, park assist.',
        ],
        [
            'title'     => '2023 Toyota HiLux SR5 4x4 Double Cab',
            'year'      => '2023',
            'make'      => 'Toyota',
            'model'     => 'HiLux',
            'variant'   => 'SR5 4x4 Double Cab',
            'price'     => '61990',
            'km'        => '22000',
            'fuel'      => 'Diesel',
            'trans'     => 'Auto 6-Speed',
            'body'      => 'Ute',
            'colour'    => 'Graphite',
            'seats'     => '5',
            'reg'       => 'QLD until Oct 2025',
            'vin'       => 'MR0FX3GD5P0900009',
            'city'      => 'brisbane',
            'condition' => 'used',
            'views'     => '412',
            'enquiries' => '9',
            'days_ago'  => '2',
            'img_url'   => 'https://images.unsplash.com/photo-1612544448445-b8232cff3b6c?w=800&h=534&fit=crop',
            'content'   => 'Premium Toyota HiLux SR5 with genuine low km. Leather interior, JBL Premium Sound, Smart Tow, Toyota Safety Sense. Australia\'s #1 ute — find out why.',
        ],
        [
            'title'     => '2021 Mercedes-Benz C200 Sedan',
            'year'      => '2021',
            'make'      => 'Mercedes-Benz',
            'model'     => 'C-Class',
            'variant'   => 'C200 Sedan',
            'price'     => '58490',
            'km'        => '38000',
            'fuel'      => 'Petrol',
            'trans'     => 'Auto 9-Speed',
            'body'      => 'Sedan',
            'colour'    => 'Iridium Silver',
            'seats'     => '5',
            'reg'       => 'WA until Apr 2026',
            'vin'       => 'WDD2050011F100010',
            'city'      => 'perth',
            'condition' => 'used',
            'views'     => '267',
            'enquiries' => '5',
            'days_ago'  => '8',
            'img_url'   => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800&h=534&fit=crop',
            'content'   => 'Elegant Mercedes-Benz C200 with MBUX infotainment, Burmester 3D sound, ambient lighting, and driver assistance package. Full Mercedes service history.',
        ],
        [
            'title'     => '2022 Subaru Forester 2.5i-S AWD',
            'year'      => '2022',
            'make'      => 'Subaru',
            'model'     => 'Forester',
            'variant'   => '2.5i-S AWD',
            'price'     => '39990',
            'km'        => '31000',
            'fuel'      => 'Petrol',
            'trans'     => 'CVT Auto',
            'body'      => 'SUV',
            'colour'    => 'Crystal White',
            'seats'     => '5',
            'reg'       => 'NT until Jan 2026',
            'vin'       => 'JF1SKAJC4NH100011',
            'city'      => 'darwin',
            'condition' => 'used',
            'views'     => '143',
            'enquiries' => '3',
            'days_ago'  => '11',
            'img_url'   => 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=800&h=534&fit=crop',
            'content'   => 'Practical 2022 Subaru Forester 2.5i-S with Symmetrical AWD. Harman Kardon audio, panoramic sunroof, power tailgate, EyeSight safety. Perfect Darwin all-rounder.',
        ],
        [
            'title'     => '2021 Mazda 3 G25 Astina Hatchback',
            'year'      => '2021',
            'make'      => 'Mazda',
            'model'     => 'Mazda 3',
            'variant'   => 'G25 Astina',
            'price'     => '29490',
            'km'        => '44500',
            'fuel'      => 'Petrol',
            'trans'     => 'Auto 6-Speed',
            'body'      => 'Hatchback',
            'colour'    => 'Polymetal Grey',
            'seats'     => '5',
            'reg'       => 'VIC until Nov 2025',
            'vin'       => 'JMZBPBL3XM0100012',
            'city'      => 'melbourne',
            'condition' => 'used',
            'views'     => '198',
            'enquiries' => '4',
            'days_ago'  => '5',
            'img_url'   => 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?w=800&h=534&fit=crop',
            'content'   => 'Sharp 2021 Mazda 3 G25 Astina hatch. Bose 12-speaker sound, leather interior, head-up display, rear cross-traffic alert. The sharpest-looking hatch in its class.',
        ],
    ];

    /* ----------------------------------------------------------
       2. TAXONOMIES
       ---------------------------------------------------------- */
    $makes = ['Toyota','Ford','Mazda','Holden','Hyundai','Kia','Nissan','BMW',
              'Mercedes-Benz','Volkswagen','Subaru','BYD','Chery','MG'];
    $bodies = ['SUV','Sedan','Hatchback','Ute','Wagon','Coupe','Van','Convertible'];
    $cities_tax = ['Brisbane','Melbourne','Perth','Darwin'];

    foreach ( $makes as $m ) {
        if ( ! term_exists( $m, 'vehicle_make' ) ) {
            wp_insert_term( $m, 'vehicle_make' );
        }
    }
    foreach ( $bodies as $b ) {
        if ( ! term_exists( $b, 'vehicle_type' ) ) {
            wp_insert_term( $b, 'vehicle_type' );
        }
    }
    foreach ( $cities_tax as $c ) {
        if ( ! term_exists( $c, 'vehicle_city' ) ) {
            wp_insert_term( $c, 'vehicle_city' );
        }
    }
    $log[] = '✓ Taxonomies registered';

    /* ----------------------------------------------------------
       3. SUPPORT PAGES
       ---------------------------------------------------------- */
    $pages = [
        'finance'        => 'Car Finance',
        'sell-your-car'  => 'Sell Your Car',
        'about'          => 'About Imani Cars',
        'contact'        => 'Contact Us',
        'privacy-policy' => 'Privacy Policy',
        'terms'          => 'Terms of Use',
        'brisbane'       => 'Cars for Sale Brisbane',
        'melbourne'      => 'Cars for Sale Melbourne',
        'perth'          => 'Cars for Sale Perth',
        'darwin'         => 'Cars for Sale Darwin NT',
        'list-your-car'  => 'List Your Cars Free',
        'pricing'        => 'Dealer Pricing Plans',
    ];
    foreach ( $pages as $slug => $title ) {
        if ( ! get_page_by_path( $slug ) ) {
            wp_insert_post( [
                'post_title'  => $title,
                'post_name'   => $slug,
                'post_status' => 'publish',
                'post_type'   => 'page',
                'meta_input'  => [ '_wp_page_template' => 'default' ],
            ] );
            $log[] = "✓ Page created: $title";
        }
    }

    /* ----------------------------------------------------------
       4. VEHICLES + IMAGES
       ---------------------------------------------------------- */
    foreach ( $vehicles as $v ) {
        // Skip if already exists
        $existing = get_posts( [
            'post_type'   => 'vehicle',
            'name'        => sanitize_title( $v['title'] ),
            'post_status' => 'publish',
            'numberposts' => 1,
        ] );
        if ( $existing ) {
            $log[] = "— Skipped (exists): {$v['title']}";
            continue;
        }

        // Create the post (use a recent date so 14-day rule shows days remaining)
        $days_ago = (int) $v['days_ago'];
        $post_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_ago} days" ) );

        $post_id = wp_insert_post( [
            'post_title'    => $v['title'],
            'post_content'  => $v['content'],
            'post_status'   => 'publish',
            'post_type'     => 'vehicle',
            'post_date'     => $post_date,
            'post_date_gmt' => get_gmt_from_date( $post_date ),
            'meta_input'    => [
                '_ic_year'      => $v['year'],
                '_ic_make'      => $v['make'],
                '_ic_model'     => $v['model'],
                '_ic_variant'   => $v['variant'],
                '_ic_price'     => $v['price'],
                '_ic_km'        => $v['km'],
                '_ic_fuel'      => $v['fuel'],
                '_ic_trans'     => $v['trans'],
                '_ic_body'      => $v['body'],
                '_ic_colour'    => $v['colour'],
                '_ic_seats'     => $v['seats'],
                '_ic_reg'       => $v['reg'],
                '_ic_vin'       => $v['vin'],
                '_ic_city'      => $v['city'],
                '_ic_condition' => $v['condition'],
                '_ic_views'     => $v['views'],
                '_ic_enquiries' => $v['enquiries'],
            ],
        ] );

        if ( is_wp_error( $post_id ) ) {
            $log[] = "✗ Error creating {$v['title']}: " . $post_id->get_error_message();
            continue;
        }

        // Assign taxonomies
        $term_make = get_term_by( 'name', $v['make'], 'vehicle_make' );
        if ( $term_make ) wp_set_post_terms( $post_id, [ $term_make->term_id ], 'vehicle_make' );

        $term_body = get_term_by( 'name', $v['body'], 'vehicle_type' );
        if ( $term_body ) wp_set_post_terms( $post_id, [ $term_body->term_id ], 'vehicle_type' );

        $city_label = ucfirst( $v['city'] );
        $term_city = get_term_by( 'name', $city_label, 'vehicle_city' );
        if ( $term_city ) wp_set_post_terms( $post_id, [ $term_city->term_id ], 'vehicle_city' );

        // Download image via wp_remote_get() — bypasses SiteGround's external HTTP block
        // that prevents media_sideload_image() from fetching Unsplash URLs directly.
        $img_url  = $v['img_url'];
        $response = wp_remote_get( $img_url, [
            'timeout'    => 30,
            'user-agent' => 'Mozilla/5.0 (compatible; WordPress)',
            'sslverify'  => false,
        ] );

        if ( is_wp_error( $response ) ) {
            $log[] = "⚠ Image fetch failed for {$v['title']}: " . $response->get_error_message();
            $log[] = "✓ Vehicle created (no image): {$v['title']} — \${$v['price']} — {$v['city']}";
        } else {
            $image_data = wp_remote_retrieve_body( $response );
            $filename   = sanitize_file_name( $v['title'] ) . '.jpg';
            $upload     = wp_upload_bits( $filename, null, $image_data );

            if ( $upload['error'] ) {
                $log[] = "⚠ Image upload failed for {$v['title']}: " . $upload['error'];
                $log[] = "✓ Vehicle created (no image): {$v['title']} — \${$v['price']} — {$v['city']}";
            } else {
                $attachment_id = wp_insert_attachment( [
                    'post_mime_type' => 'image/jpeg',
                    'post_title'     => sanitize_text_field( $v['title'] ),
                    'post_status'    => 'inherit',
                    'post_parent'    => $post_id,
                ], $upload['file'], $post_id );

                wp_update_attachment_metadata(
                    $attachment_id,
                    wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
                );
                set_post_thumbnail( $post_id, $attachment_id );
                $log[] = "✓ Vehicle + image: {$v['title']} — \${$v['price']} — {$v['city']}";
            }
        }

        $created++;
    }

    $log[] = '';
    $log[] = "=== Done! Vehicles created: $created ===";
    $log[] = 'Visit WP Admin → Vehicles to review listings.';
    $log[] = 'Tip: Remove dummy-data/install-demo.php before going live.';

    wp_send_json_success( [ 'log' => $log ] );
}
