<?php
/**
 * Imani Cars — functions.php
 * Theme setup, enqueue, CPTs, image sizes, menus, sidebars.
 * PHP 7.4 compatible — no str_ends_with / str_starts_with / match{}.
 */

defined( 'ABSPATH' ) || exit;

define( 'IC_THEME_VERSION', '1.0.0' );
define( 'IC_THEME_DIR',     get_template_directory() );
define( 'IC_THEME_URI',     get_template_directory_uri() );

/* =========================================================
   THEME SUPPORT
   ========================================================= */
function ic_theme_setup() {
    load_theme_textdomain( 'imanicars', IC_THEME_DIR . '/languages' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'responsive-embeds' );
    add_image_size( 'ic-card',   400, 267, true );
    add_image_size( 'ic-hero',  1920, 600, true );
    add_image_size( 'ic-thumb',  200, 133, true );
    add_image_size( 'ic-single', 800, 534, true );
    register_nav_menus( [
        'primary'  => __( 'Primary Menu', 'imanicars' ),
        'footer-1' => __( 'Footer: Buy a Car', 'imanicars' ),
        'footer-2' => __( 'Footer: Sell / Dealers', 'imanicars' ),
        'footer-3' => __( 'Footer: Company', 'imanicars' ),
    ] );
}
add_action( 'after_setup_theme', 'ic_theme_setup' );

/* =========================================================
   ENQUEUE SCRIPTS & STYLES
   ========================================================= */
function ic_enqueue_assets() {
    wp_enqueue_style( 'ic-fonts', 'https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap', [], null );
    wp_enqueue_style( 'ic-style', IC_THEME_URI . '/style.css', [ 'ic-fonts' ], IC_THEME_VERSION );
    wp_enqueue_style( 'ic-main',  IC_THEME_URI . '/assets/css/main.css', [ 'ic-style' ], IC_THEME_VERSION );
    wp_enqueue_script( 'ic-main', IC_THEME_URI . '/assets/js/main.js', [], IC_THEME_VERSION, true );
    wp_localize_script( 'ic-main', 'IC', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'ic_nonce' ),
        'homeUrl' => home_url( '/' ),
    ] );
}
add_action( 'wp_enqueue_scripts', 'ic_enqueue_assets' );

/* =========================================================
   CUSTOM POST TYPE — VEHICLE
   ========================================================= */
function ic_register_vehicle_cpt() {
    register_post_type( 'vehicle', [
        'labels' => [
            'name'               => __( 'Vehicles', 'imanicars' ),
            'singular_name'      => __( 'Vehicle', 'imanicars' ),
            'add_new'            => __( 'Add New Vehicle', 'imanicars' ),
            'add_new_item'       => __( 'Add New Vehicle', 'imanicars' ),
            'edit_item'          => __( 'Edit Vehicle', 'imanicars' ),
            'new_item'           => __( 'New Vehicle', 'imanicars' ),
            'view_item'          => __( 'View Vehicle', 'imanicars' ),
            'search_items'       => __( 'Search Vehicles', 'imanicars' ),
            'not_found'          => __( 'No vehicles found', 'imanicars' ),
            'not_found_in_trash' => __( 'No vehicles found in Trash', 'imanicars' ),
            'menu_name'          => __( 'Vehicles', 'imanicars' ),
        ],
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => [ 'slug' => 'cars', 'with_front' => false ],
        'capability_type'    => 'post',
        'has_archive'        => 'cars',
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-car',
        'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'show_in_rest'       => true,
    ] );
}
add_action( 'init', 'ic_register_vehicle_cpt' );

/* =========================================================
   TAXONOMIES
   ========================================================= */
function ic_register_taxonomies() {
    register_taxonomy( 'vehicle_make', 'vehicle', [
        'labels'       => [ 'name' => 'Makes', 'singular_name' => 'Make' ],
        'public'       => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite'      => [ 'slug' => 'make' ],
    ] );
    register_taxonomy( 'vehicle_body', 'vehicle', [
        'labels'       => [ 'name' => 'Body Types', 'singular_name' => 'Body Type' ],
        'public'       => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite'      => [ 'slug' => 'body-type' ],
    ] );
    register_taxonomy( 'vehicle_city', 'vehicle', [
        'labels'       => [ 'name' => 'Cities', 'singular_name' => 'City' ],
        'public'       => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite'      => [ 'slug' => 'city' ],
    ] );
    register_taxonomy( 'vehicle_condition', 'vehicle', [
        'labels'       => [ 'name' => 'Conditions', 'singular_name' => 'Condition' ],
        'public'       => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite'      => [ 'slug' => 'condition' ],
    ] );
}
add_action( 'init', 'ic_register_taxonomies' );

/* =========================================================
   SIDEBARS
   ========================================================= */
function ic_register_sidebars() {
    $cfg = [
        'before_widget' => '<div id="%1$s" class="ic-sidebar-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="ic-sidebar-widget__title">',
        'after_title'   => '</h4>',
    ];
    register_sidebar( array_merge( $cfg, [
        'name'        => __( 'Car Listings Sidebar', 'imanicars' ),
        'id'          => 'ic-sidebar-listings',
        'description' => __( 'Shown on car listing archive pages', 'imanicars' ),
    ] ) );
    register_sidebar( array_merge( $cfg, [
        'name'        => __( 'Single Car Sidebar', 'imanicars' ),
        'id'          => 'ic-sidebar-single',
        'description' => __( 'Shown on single car detail pages', 'imanicars' ),
    ] ) );
}
add_action( 'widgets_init', 'ic_register_sidebars' );

/* =========================================================
   AJAX — ENQUIRY FORM
   ========================================================= */
function ic_handle_enquiry() {
    check_ajax_referer( 'ic_nonce', 'nonce' );
    $name    = sanitize_text_field( wp_unslash( isset( $_POST['name'] )    ? $_POST['name']    : '' ) );
    $email   = sanitize_email(      wp_unslash( isset( $_POST['email'] )   ? $_POST['email']   : '' ) );
    $phone   = sanitize_text_field( wp_unslash( isset( $_POST['phone'] )   ? $_POST['phone']   : '' ) );
    $message = sanitize_textarea_field( wp_unslash( isset( $_POST['message'] ) ? $_POST['message'] : '' ) );
    $car_id  = absint( isset( $_POST['car_id'] ) ? $_POST['car_id'] : 0 );
    if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
        wp_send_json_error( [ 'message' => 'Please fill in all required fields.' ] );
    }
    $car_title = $car_id ? get_the_title( $car_id ) : 'General Enquiry';
    $to        = get_option( 'admin_email' );
    $subject   = 'Enquiry for ' . $car_title . ' from ' . $name;
    $body      = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n\nMessage:\n{$message}";
    $sent = wp_mail( $to, $subject, $body );
    if ( $sent ) {
        wp_send_json_success( [ 'message' => "Your enquiry has been sent! We'll be in touch soon." ] );
    } else {
        wp_send_json_error( [ 'message' => 'Could not send your enquiry. Please call us directly.' ] );
    }
}
add_action( 'wp_ajax_ic_enquiry',        'ic_handle_enquiry' );
add_action( 'wp_ajax_nopriv_ic_enquiry', 'ic_handle_enquiry' );

/* =========================================================
   AJAX — DEALER SIGNUP
   ========================================================= */
function ic_handle_dealer_signup() {
    check_ajax_referer( 'ic_nonce', 'nonce' );
    $dealership = sanitize_text_field( wp_unslash( isset( $_POST['dealership'] )  ? $_POST['dealership']  : '' ) );
    $contact    = sanitize_text_field( wp_unslash( isset( $_POST['contact'] )     ? $_POST['contact']     : '' ) );
    $phone      = sanitize_text_field( wp_unslash( isset( $_POST['phone'] )       ? $_POST['phone']       : '' ) );
    $email      = sanitize_email(      wp_unslash( isset( $_POST['email'] )       ? $_POST['email']       : '' ) );
    $city       = sanitize_text_field( wp_unslash( isset( $_POST['city'] )        ? $_POST['city']        : '' ) );
    $cars_count = sanitize_text_field( wp_unslash( isset( $_POST['cars_count'] )  ? $_POST['cars_count']  : '' ) );
    if ( empty( $dealership ) || empty( $email ) || empty( $phone ) ) {
        wp_send_json_error( [ 'message' => 'Please fill in all required fields.' ] );
    }
    $to      = get_option( 'admin_email' );
    $subject = 'New Dealer Signup: ' . $dealership;
    $body    = "Dealership: {$dealership}\nContact: {$contact}\nPhone: {$phone}\nEmail: {$email}\nCity: {$city}\nCars in Yard: {$cars_count}";
    wp_mail( $to, $subject, $body );
    wp_send_json_success( [ 'message' => "Welcome to Imani Cars! We'll set up your free listing within 24 hours." ] );
}
add_action( 'wp_ajax_ic_dealer_signup',        'ic_handle_dealer_signup' );
add_action( 'wp_ajax_nopriv_ic_dealer_signup', 'ic_handle_dealer_signup' );

/* =========================================================
   HELPER — FORMAT PRICE
   ========================================================= */
function ic_format_price( $price ) {
    if ( empty( $price ) ) return 'POA';
    return '$' . number_format( (float) $price, 0, '.', ',' );
}

/* =========================================================
   HELPER — UNSPLASH IMAGE URL
   ========================================================= */
function ic_unsplash( $seed, $w, $h ) {
    return 'https://images.unsplash.com/photo-' . esc_attr( $seed ) . '?auto=format&fit=crop&w=' . intval( $w ) . '&h=' . intval( $h ) . '&q=80';
}

/* =========================================================
   HELPER — VEHICLE CARD IMAGE
   ========================================================= */
function ic_get_car_image( $post_id, $size = 'ic-card' ) {
    if ( has_post_thumbnail( $post_id ) ) {
        return wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), $size );
    }
    $seeds = [
        '1552519152-9214d16d56ab',
        '1503376780353-7e6692767b70',
        '1590362891991-f776e747a588',
        '1555215695-3004980ad54e',
        '1544636331-e26879cd4d9b',
        '1617788602929-5c43d8e73dac',
        '1542362567-b07e54358753',
        '1558618666-fcd25c85cd64',
        '1605559424843-9073199d0f75',
        '1549317661-cf369843ed2c',
    ];
    $seed = $seeds[ $post_id % count( $seeds ) ];
    return ic_unsplash( $seed, 400, 267 );
}

/* =========================================================
   DOCUMENT TITLE — SEO-optimised per page
   ========================================================= */
function ic_document_title( $title ) {
    if ( is_front_page() ) {
        return 'Used Cars for Sale Australia | Free Dealer Listings | Imani Cars';
    }
    if ( is_page( 'list-your-car' ) ) {
        return 'List Your Cars Free | Dealer Listings | Imani Cars';
    }
    if ( is_page( 'pricing' ) ) {
        return 'Dealer Listing Plans | Free to $699/mo | Imani Cars';
    }
    if ( is_page( 'dealers' ) ) {
        return 'Car Dealers Brisbane Melbourne Perth Darwin | Imani Cars';
    }
    if ( is_page( 'finance' ) ) {
        return 'Car Finance Australia | Low Rate Car Loans | Imani Cars';
    }
    if ( is_page( 'sell-your-car' ) ) {
        return 'Sell My Car | Instant Offer Across Australia | Imani Cars';
    }
    if ( is_post_type_archive( 'vehicle' ) ) {
        $city = sanitize_text_field( isset( $_GET['city'] ) ? $_GET['city'] : '' );
        $make = sanitize_text_field( isset( $_GET['make'] ) ? $_GET['make'] : '' );
        if ( $city && $make ) {
            return 'Used ' . ucfirst( $make ) . ' for Sale ' . ucfirst( $city ) . ' | Imani Cars';
        }
        if ( $city ) {
            return 'Used Cars ' . ucfirst( $city ) . ' | Browse Listings | Imani Cars';
        }
        if ( $make ) {
            return 'Used ' . ucfirst( $make ) . ' for Sale Australia | Imani Cars';
        }
        return 'Used Cars for Sale Australia | Browse Listings | Imani Cars';
    }
    if ( is_singular( 'vehicle' ) ) {
        $pid   = get_the_ID();
        $year  = get_post_meta( $pid, '_ic_year', true );
        $make  = get_post_meta( $pid, '_ic_make', true );
        $model = get_post_meta( $pid, '_ic_model', true );
        $city  = get_post_meta( $pid, '_ic_city', true );
        if ( $year && $make && $model ) {
            $t = $year . ' ' . $make . ' ' . $model;
            if ( $city ) $t .= ' for Sale ' . ucfirst( $city );
            return $t . ' | Imani Cars';
        }
        return get_the_title() . ' for Sale | Imani Cars';
    }
    return $title . ' | Imani Cars';
}
add_filter( 'pre_get_document_title', 'ic_document_title' );

/* =========================================================
   SEO HEAD — meta description, OG, Twitter Card, canonical,
               JSON-LD schema. Runs on wp_head priority 5.
   ========================================================= */
function ic_seo_head() {
    $site_url  = 'https://imanicars.com';
    $og_image  = $site_url . '/assets/images/og-imanicars.jpg';
    $logo_url  = $site_url . '/assets/images/logo.png';

    /* ---- Determine per-page values ---- */
    $meta_desc = '';
    $canonical = '';
    $schema    = [];

    /* === FRONT PAGE === */
    if ( is_front_page() ) {
        $meta_desc = 'Buy and sell cars across Brisbane, Melbourne, Perth and Darwin. Search 8,000+ listings from 300+ dealers. Free dealer listings. Imani Cars Australia.';
        $canonical = $site_url . '/';
        $schema[]  = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => [ 'Organization', 'AutoDealer' ],
                    'name'        => 'Imani Cars',
                    'url'         => $site_url,
                    'logo'        => $logo_url,
                    'description' => 'Australian automotive marketplace across Brisbane, Melbourne, Perth and Darwin.',
                    'areaServed'  => [ 'Brisbane', 'Melbourne', 'Perth', 'Darwin' ],
                    'telephone'   => '+61-1800-462-647',
                    'priceRange'  => 'Free to list',
                    'aggregateRating' => [
                        '@type'       => 'AggregateRating',
                        'ratingValue' => '4.9',
                        'reviewCount' => '312',
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    'url'   => $site_url,
                    'name'  => 'Imani Cars',
                    'potentialAction' => [
                        '@type'       => 'SearchAction',
                        'target'      => $site_url . '/cars/?s={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    }

    /* === LIST YOUR CAR === */
    elseif ( is_page( 'list-your-car' ) ) {
        $meta_desc = 'List up to 10 cars free on Imani Cars. Reach buyers across Brisbane, Melbourne, Perth and Darwin. No credit card. No contract. Start listing today.';
        $canonical = $site_url . '/list-your-car/';
        $schema[]  = [
            '@context' => 'https://schema.org',
            '@type'    => 'Product',
            'name'     => 'Imani Cars Dealer Listing',
            'description' => 'List your cars free on Australia\'s growing automotive marketplace.',
            'offers'   => [
                [ '@type' => 'Offer', 'name' => 'Free Plan',  'price' => '0',   'priceCurrency' => 'AUD', 'description' => 'Up to 10 listings free' ],
                [ '@type' => 'Offer', 'name' => 'Growth',     'price' => '149', 'priceCurrency' => 'AUD', 'description' => 'Up to 50 listings per month' ],
                [ '@type' => 'Offer', 'name' => 'Pro',        'price' => '349', 'priceCurrency' => 'AUD', 'description' => 'Unlimited listings' ],
                [ '@type' => 'Offer', 'name' => 'Premium',    'price' => '699', 'priceCurrency' => 'AUD', 'description' => 'Unlimited + homepage placement' ],
            ],
        ];
        $schema[]  = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',           'item' => $site_url . '/' ],
                [ '@type' => 'ListItem', 'position' => 2, 'name' => 'List Your Car',  'item' => $site_url . '/list-your-car/' ],
            ],
        ];
    }

    /* === PRICING === */
    elseif ( is_page( 'pricing' ) ) {
        $meta_desc = 'Choose your Imani Cars dealer plan. Start free with 10 listings. Upgrade to Growth $149, Pro $349 or Premium $699/mo for unlimited listings and premium placement.';
        $canonical = $site_url . '/pricing/';
        $schema[]  = [
            '@context' => 'https://schema.org',
            '@type'    => 'Product',
            'name'     => 'Imani Cars Dealer Listing Plans',
            'description' => 'Dealer listing plans for Australia\'s fastest-growing car marketplace.',
            'offers'   => [
                [ '@type' => 'Offer', 'name' => 'Free Plan',  'price' => '0',   'priceCurrency' => 'AUD', 'description' => 'Up to 10 listings free — no credit card' ],
                [ '@type' => 'Offer', 'name' => 'Growth',     'price' => '149', 'priceCurrency' => 'AUD', 'description' => 'Up to 50 listings, priority search placement' ],
                [ '@type' => 'Offer', 'name' => 'Pro',        'price' => '349', 'priceCurrency' => 'AUD', 'description' => 'Unlimited listings, dealer profile page' ],
                [ '@type' => 'Offer', 'name' => 'Premium',    'price' => '699', 'priceCurrency' => 'AUD', 'description' => 'Unlimited listings + homepage featured placement' ],
            ],
        ];
        $schema[]  = [
            '@context' => 'https://schema.org',
            '@type'    => 'FAQPage',
            'mainEntity' => [
                [
                    '@type'          => 'Question',
                    'name'           => 'Is the free plan really free forever?',
                    'acceptedAnswer' => [ '@type' => 'Answer', 'text' => 'Yes. The Free plan is free forever with up to 10 active listings. No credit card required, no contract.' ],
                ],
                [
                    '@type'          => 'Question',
                    'name'           => 'Can I cancel anytime?',
                    'acceptedAnswer' => [ '@type' => 'Answer', 'text' => 'Yes. All paid plans are month-to-month with no lock-in contract. Cancel anytime with one click.' ],
                ],
                [
                    '@type'          => 'Question',
                    'name'           => 'How is Imani Cars different from carsales.com.au?',
                    'acceptedAnswer' => [ '@type' => 'Answer', 'text' => 'Imani Cars offers a free listing tier and lower-cost paid plans than carsales. Ideal for small and independent dealers across Brisbane, Melbourne, Perth and Darwin.' ],
                ],
            ],
        ];
        $schema[]  = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',    'item' => $site_url . '/' ],
                [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Pricing', 'item' => $site_url . '/pricing/' ],
            ],
        ];
    }

    /* === DEALERS === */
    elseif ( is_page( 'dealers' ) ) {
        $meta_desc = 'Browse 300+ trusted car dealers across Brisbane, Melbourne, Perth and Darwin. Find your local used car dealer at Imani Cars Australia.';
        $canonical = $site_url . '/dealers/';
        $schema[]  = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',    'item' => $site_url . '/' ],
                [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Dealers', 'item' => $site_url . '/dealers/' ],
            ],
        ];
        $schema[]  = [
            '@context'    => 'https://schema.org',
            '@type'       => 'ItemList',
            'name'        => 'Car Dealers Australia',
            'description' => 'Trusted car dealers across Brisbane, Melbourne, Perth and Darwin.',
            'itemListElement' => [
                [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Car Dealers Brisbane',  'url' => $site_url . '/dealers/brisbane/' ],
                [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Car Dealers Melbourne', 'url' => $site_url . '/dealers/melbourne/' ],
                [ '@type' => 'ListItem', 'position' => 3, 'name' => 'Car Dealers Perth',     'url' => $site_url . '/dealers/perth/' ],
                [ '@type' => 'ListItem', 'position' => 4, 'name' => 'Car Dealers Darwin',    'url' => $site_url . '/dealers/darwin/' ],
            ],
        ];
    }

    /* === FINANCE === */
    elseif ( is_page( 'finance' ) ) {
        $meta_desc = 'Get car finance across Australia from 6.9% p.a. Pre-approved in minutes. 20+ lenders. Apply online. Available in Brisbane, Melbourne, Perth and Darwin.';
        $canonical = $site_url . '/finance/';
        $schema[]  = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',    'item' => $site_url . '/' ],
                [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Finance', 'item' => $site_url . '/finance/' ],
            ],
        ];
    }

    /* === SELL YOUR CAR === */
    elseif ( is_page( 'sell-your-car' ) ) {
        $meta_desc = 'Sell your car fast across Brisbane, Melbourne, Perth and Darwin. Get an instant cash offer. Free valuation, no obligation. Imani Cars Australia.';
        $canonical = $site_url . '/sell-your-car/';
        $schema[]  = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',          'item' => $site_url . '/' ],
                [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Sell Your Car', 'item' => $site_url . '/sell-your-car/' ],
            ],
        ];
    }

    /* === VEHICLE ARCHIVE (cars for sale) === */
    elseif ( is_post_type_archive( 'vehicle' ) ) {
        $city = sanitize_text_field( isset( $_GET['city'] ) ? $_GET['city'] : '' );
        $make = sanitize_text_field( isset( $_GET['make'] ) ? $_GET['make'] : '' );
        $canonical = $site_url . '/cars/';
        if ( $city ) {
            $city_cap = ucfirst( $city );
            if ( $make ) {
                $meta_desc = 'Browse used ' . esc_html( $make ) . ' for sale in ' . $city_cap . '. Hundreds of listings from trusted dealers. Easy finance. Imani Cars.';
            } else {
                $meta_desc = 'Browse used cars for sale in ' . $city_cap . '. New and used cars from trusted dealers. Great prices, easy finance. Imani Cars ' . $city_cap . '.';
            }
            $canonical = add_query_arg( 'city', $city, $site_url . '/cars/' );
        } elseif ( $make ) {
            $meta_desc = 'Browse used ' . esc_html( $make ) . ' for sale across Australia. New and used ' . esc_html( $make ) . ' from trusted dealers on Imani Cars.';
            $canonical = add_query_arg( 'make', $make, $site_url . '/cars/' );
        } else {
            $meta_desc = 'Browse 8,000+ used and new cars for sale in Brisbane, Melbourne, Perth and Darwin. Trusted dealers, great prices, easy car finance. Imani Cars.';
        }
        $schema[]  = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',         'item' => $site_url . '/' ],
                [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Cars for Sale', 'item' => $site_url . '/cars/' ],
            ],
        ];
        /* ItemList schema — city hubs */
        $schema[] = [
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'name'            => 'Used Cars for Sale Australia',
            'description'     => 'Search used and new cars for sale across Brisbane, Melbourne, Perth and Darwin.',
            'itemListElement' => [
                [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Used Cars Brisbane',  'url' => $site_url . '/cars/?city=brisbane' ],
                [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Used Cars Melbourne', 'url' => $site_url . '/cars/?city=melbourne' ],
                [ '@type' => 'ListItem', 'position' => 3, 'name' => 'Used Cars Perth',     'url' => $site_url . '/cars/?city=perth' ],
                [ '@type' => 'ListItem', 'position' => 4, 'name' => 'Used Cars Darwin',    'url' => $site_url . '/cars/?city=darwin' ],
            ],
        ];
    }

    /* === SINGLE VEHICLE === */
    elseif ( is_singular( 'vehicle' ) ) {
        $pid         = get_the_ID();
        $year        = get_post_meta( $pid, '_ic_year', true );
        $v_make      = get_post_meta( $pid, '_ic_make', true );
        $v_model     = get_post_meta( $pid, '_ic_model', true );
        $km          = get_post_meta( $pid, '_ic_odometer', true );
        $trans       = get_post_meta( $pid, '_ic_transmission', true );
        $fuel        = get_post_meta( $pid, '_ic_fuel_type', true );
        $price       = get_post_meta( $pid, '_ic_price', true );
        $city        = get_post_meta( $pid, '_ic_city', true );
        $dealer_name = get_post_meta( $pid, '_ic_dealer_name', true );
        $condition   = get_post_meta( $pid, '_ic_condition', true );
        $city_cap    = $city ? ucfirst( $city ) : 'Australia';
        $title_str   = trim( $year . ' ' . $v_make . ' ' . $v_model );
        if ( empty( $title_str ) ) $title_str = get_the_title();
        $cond_label = ( $condition === 'new' ) ? 'new' : 'used';
        $meta_desc  = 'Buy this ' . $cond_label . ' ' . esc_html( $title_str );
        if ( $km )  $meta_desc .= ', ' . number_format( (int) $km ) . ' km';
        if ( $trans ) $meta_desc .= ', ' . esc_html( $trans );
        $meta_desc .= ' for sale in ' . $city_cap . '.';
        if ( $price ) $meta_desc .= ' Price: $' . number_format( (float) $price, 0 ) . '.';
        $meta_desc  .= ' Imani Cars Australia.';
        $slug        = get_post_field( 'post_name', $pid );
        $canonical   = $site_url . '/cars/' . $slug . '/';

        /* Vehicle JSON-LD */
        $vehicle_schema = [
            '@context'              => 'https://schema.org',
            '@type'                 => 'Vehicle',
            'name'                  => $title_str,
            'brand'                 => [ '@type' => 'Brand', 'name' => $v_make ],
            'model'                 => $v_model,
            'vehicleModelDate'      => $year,
            'vehicleCondition'      => ( $condition === 'new' ) ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition',
            'fuelType'              => $fuel,
            'vehicleTransmission'   => $trans,
            'offers'                => [
                '@type'        => 'Offer',
                'price'        => $price ? (string) $price : '0',
                'priceCurrency'=> 'AUD',
                'availability' => 'https://schema.org/InStock',
                'seller'       => [
                    '@type' => 'AutoDealer',
                    'name'  => $dealer_name ? $dealer_name : 'Imani Cars Dealer',
                    'url'   => $site_url,
                ],
            ],
        ];
        if ( $km ) {
            $vehicle_schema['mileageFromOdometer'] = [
                '@type'    => 'QuantitativeValue',
                'value'    => (int) $km,
                'unitCode' => 'KMT',
            ];
        }
        /* freshness signal */
        $vehicle_schema['dateModified'] = get_the_modified_date( 'c', $pid );
        $schema[] = $vehicle_schema;

        /* Breadcrumb schema */
        $schema[] = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',          'item' => $site_url . '/' ],
                [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Cars for Sale', 'item' => $site_url . '/cars/' ],
                [ '@type' => 'ListItem', 'position' => 3, 'name' => $title_str,      'item' => $canonical ],
            ],
        ];
    }

    /* === FALLBACK (standard pages) === */
    else {
        $meta_desc = get_the_title() ? 'Find used and new cars for sale in Brisbane, Melbourne, Perth and Darwin. Trusted dealers, great prices, easy finance. Imani Cars Australia.' : '';
        $canonical = get_permalink() ? get_permalink() : $site_url . '/';
    }

    /* ---- Build the OG title from the current document title ---- */
    $og_title = wp_get_document_title();
    if ( empty( $og_title ) ) $og_title = 'Imani Cars — Australian Car Marketplace';

    /* ---- Output meta tags ---- */
    ?>
<meta name="description" content="<?php echo esc_attr( $meta_desc ); ?>">
<meta name="robots" content="index, follow">
<?php if ( $canonical ) : ?>
<link rel="canonical" href="<?php echo esc_url( $canonical ); ?>">
<?php endif; ?>

<!-- Open Graph -->
<meta property="og:title" content="<?php echo esc_attr( $og_title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $meta_desc ); ?>">
<meta property="og:url" content="<?php echo esc_url( $canonical ? $canonical : get_permalink() ); ?>">
<meta property="og:type" content="<?php echo is_singular( 'vehicle' ) ? 'product' : 'website'; ?>">
<meta property="og:image" content="<?php echo esc_url( is_singular( 'vehicle' ) ? ic_get_car_image( get_the_ID(), 'ic-single' ) : $og_image ); ?>">
<meta property="og:site_name" content="Imani Cars">
<meta property="og:locale" content="en_AU">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr( $og_title ); ?>">
<meta name="twitter:description" content="<?php echo esc_attr( $meta_desc ); ?>">
<meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>">
<meta name="twitter:site" content="@imanicars">

<!-- Geo targeting -->
<meta name="geo.region" content="AU">
<meta name="geo.placename" content="Brisbane, Melbourne, Perth, Darwin">
<meta name="geo.position" content="-27.4698;153.0251">
<meta name="ICBM" content="-27.4698, 153.0251">

<!-- JSON-LD Schema -->
<?php foreach ( $schema as $s ) : ?>
<script type="application/ld+json"><?php echo wp_json_encode( $s, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ); ?></script>
<?php endforeach; ?>
    <?php
}
add_action( 'wp_head', 'ic_seo_head', 5 );

/* =========================================================
   EXCERPT LENGTH
   ========================================================= */
function ic_excerpt_length() { return 25; }
add_filter( 'excerpt_length', 'ic_excerpt_length', 999 );

/* =========================================================
   REMOVE WP EMOJI
   ========================================================= */
remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles',     'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles',  'print_emoji_styles' );

/* =========================================================
   DEMO IMPORT — loaded from dummy-data/
   NOTE: ic_render_demo_import_page() is defined ONLY in demo-import.php
   ========================================================= */
require_once IC_THEME_DIR . '/dummy-data/demo-import.php';

/* =========================================================
   VEHICLE PHOTO GALLERY
   Real uploads, stored as an ordered list of attachment IDs in
   _ic_gallery. Featured image always leads. Falls back to the
   Unsplash placeholders only when a vehicle has no photos at all.
   ========================================================= */

/**
 * Ordered gallery for a vehicle.
 *
 * @return array<int, array{full:string, thumb:string, alt:string}> Empty when
 *         the vehicle has no real photos — callers decide the fallback.
 */
function ic_get_car_gallery( $post_id ) {
    $ids = array_filter( array_map(
        'absint',
        explode( ',', (string) get_post_meta( $post_id, '_ic_gallery', true ) )
    ) );

    // The featured image is the main listing photo, so it leads.
    if ( has_post_thumbnail( $post_id ) ) {
        array_unshift( $ids, (int) get_post_thumbnail_id( $post_id ) );
    }

    $out = [];
    foreach ( array_unique( $ids ) as $id ) {
        $full = wp_get_attachment_image_url( $id, 'ic-single' );
        if ( ! $full ) {
            continue; // Attachment deleted from the Media Library.
        }
        $thumb = wp_get_attachment_image_url( $id, 'ic-thumb' );
        $out[] = [
            'full'  => $full,
            'thumb' => $thumb ? $thumb : $full,
            'alt'   => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
        ];
    }

    return $out;
}

/**
 * Placeholder gallery — the pre-existing stock shots, kept as the
 * no-photos fallback so a new listing never renders an empty frame.
 */
function ic_get_placeholder_gallery() {
    $seeds = [
        '1552519152-9214d16d56ab',
        '1503376780353-7e6692767b70',
        '1590362891991-f776e747a588',
        '1555215695-3004980ad54e',
    ];

    $out = [];
    foreach ( $seeds as $seed ) {
        $out[] = [
            'full'  => ic_unsplash( $seed, 800, 534 ),
            'thumb' => ic_unsplash( $seed, 200, 133 ),
            'alt'   => '',
        ];
    }

    return $out;
}

function ic_add_gallery_meta_box() {
    add_meta_box(
        'ic_gallery',
        __( 'Vehicle Photos', 'imanicars' ),
        'ic_render_gallery_meta_box',
        'vehicle',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'ic_add_gallery_meta_box' );

function ic_render_gallery_meta_box( $post ) {
    wp_nonce_field( 'ic_save_gallery', 'ic_gallery_nonce' );
    $value = (string) get_post_meta( $post->ID, '_ic_gallery', true );
    $ids   = array_filter( array_map( 'absint', explode( ',', $value ) ) );
    ?>
    <div class="ic-gallery-field" id="ic-gallery-field">
        <p class="description">
            <?php esc_html_e( 'Photos shown in the gallery strip on the car page. The Featured Image is the main photo and always appears first — do not add it again here. Drag to reorder.', 'imanicars' ); ?>
        </p>

        <ul class="ic-gallery-list" id="ic-gallery-list">
            <?php foreach ( $ids as $id ) :
                $thumb = wp_get_attachment_image_url( $id, 'ic-thumb' );
                if ( ! $thumb ) {
                    continue;
                }
                ?>
                <li class="ic-gallery-item" data-id="<?php echo esc_attr( $id ); ?>">
                    <img src="<?php echo esc_url( $thumb ); ?>" alt="" width="100" height="67">
                    <button type="button" class="button-link ic-gallery-remove"
                            aria-label="<?php esc_attr_e( 'Remove this photo', 'imanicars' ); ?>">&times;</button>
                </li>
            <?php endforeach; ?>
        </ul>

        <p class="ic-gallery-empty" id="ic-gallery-empty"<?php echo $ids ? ' hidden' : ''; ?>>
            <?php esc_html_e( 'No photos yet — this car will show stock placeholder images until you add some.', 'imanicars' ); ?>
        </p>

        <button type="button" class="button" id="ic-gallery-add">
            <?php esc_html_e( 'Add photos from Media Library', 'imanicars' ); ?>
        </button>

        <input type="hidden" name="ic_gallery" id="ic-gallery-input" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
    </div>
    <?php
}

function ic_save_gallery_meta( $post_id ) {
    if ( ! isset( $_POST['ic_gallery_nonce'] )
        || ! wp_verify_nonce( sanitize_key( $_POST['ic_gallery_nonce'] ), 'ic_save_gallery' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $raw = isset( $_POST['ic_gallery'] ) ? sanitize_text_field( wp_unslash( $_POST['ic_gallery'] ) ) : '';
    $ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );

    // Only keep IDs that are real image attachments.
    $ids = array_filter( $ids, function ( $id ) {
        return wp_attachment_is_image( $id );
    } );

    if ( $ids ) {
        update_post_meta( $post_id, '_ic_gallery', implode( ',', array_unique( $ids ) ) );
    } else {
        delete_post_meta( $post_id, '_ic_gallery' );
    }
}
add_action( 'save_post_vehicle', 'ic_save_gallery_meta' );

function ic_gallery_admin_assets( $hook ) {
    if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
        return;
    }
    if ( get_current_screen() && 'vehicle' !== get_current_screen()->post_type ) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script(
        'ic-admin-gallery',
        IC_THEME_URI . '/assets/js/admin-gallery.js',
        [ 'jquery', 'jquery-ui-sortable' ],
        IC_THEME_VERSION,
        true
    );
    wp_enqueue_style(
        'ic-admin-gallery',
        IC_THEME_URI . '/assets/css/admin-gallery.css',
        [],
        IC_THEME_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'ic_gallery_admin_assets' );

/* =========================================================
   VEHICLE FIELDS OVER REST
   The _ic_* keys are underscore-prefixed, so WordPress treats them as
   protected and the REST API refuses to read or write them. Registering
   them makes bulk stock entry possible at all — the public listing form
   saves nothing, so the admin screen was the only way in.
   ========================================================= */
function ic_register_vehicle_meta() {
    $fields = [
        '_ic_price', '_ic_price_label', '_ic_year', '_ic_make', '_ic_model',
        '_ic_badge', '_ic_odometer', '_ic_transmission', '_ic_fuel_type',
        '_ic_body_type', '_ic_engine', '_ic_colour', '_ic_doors', '_ic_seats',
        '_ic_condition', '_ic_seller_type', '_ic_dealer_name', '_ic_city',
        '_ic_suburb', '_ic_state', '_ic_phone', '_ic_email', '_ic_finance_mo',
        '_ic_badge_type', '_ic_gallery',
    ];

    foreach ( $fields as $key ) {
        register_post_meta( 'vehicle', $key, [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
                return current_user_can( 'edit_post', $post_id );
            },
        ] );
    }
}
add_action( 'init', 'ic_register_vehicle_meta' );
