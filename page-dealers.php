<?php
/**
 * Template Name: Dealer Directory
 * All dealers with city filter tabs — Brisbane, Melbourne, Perth, Darwin
 */
get_header();

$dealers = [
    'brisbane'  => [
        [ 'name' => 'City Cars Brisbane',    'address' => '23 Brunswick St, Fortitude Valley QLD 4006', 'rating' => 4.8, 'reviews' => 127, 'stock' => 45, 'badges' => [ 'Featured', 'Used Cars' ],       'seed' => '1570295999-10bde93aefc1' ],
        [ 'name' => 'South Side Auto',       'address' => '88 Ipswich Rd, Moorooka QLD 4105',           'rating' => 4.6, 'reviews' => 89,  'stock' => 23, 'badges' => [ 'Used Cars' ],                   'seed' => '1507003211-2a6f260ac0e9' ],
        [ 'name' => 'QLD Car Centre',        'address' => '14 Moss St, Springwood QLD 4127',            'rating' => 4.7, 'reviews' => 203, 'stock' => 67, 'badges' => [ 'Featured', 'New Cars', 'Used Cars' ], 'seed' => '1494790108-d7b21cd3cd26' ],
        [ 'name' => 'Logan Road Motors',     'address' => '412 Logan Rd, Stones Corner QLD 4120',       'rating' => 4.5, 'reviews' => 56,  'stock' => 31, 'badges' => [ 'Used Cars' ],                   'seed' => '1518977168-de71b-3ad12c4' ],
        [ 'name' => 'Northside Car Sales',   'address' => '7 Stafford Rd, Stafford QLD 4053',           'rating' => 4.4, 'reviews' => 42,  'stock' => 18, 'badges' => [ 'Used Cars' ],                   'seed' => '1531427007-9d52b6f17b9c' ],
        [ 'name' => 'Gold Coast Auto Group', 'address' => '190 Ferry Rd, Southport QLD 4215',           'rating' => 4.9, 'reviews' => 318, 'stock' => 82, 'badges' => [ 'Featured', 'New Cars' ],         'seed' => '1559056736-98e50dfc57dc' ],
    ],
    'melbourne' => [
        [ 'name' => 'Metro Motors Melbourne','address' => '34 Princes Hwy, Dandenong VIC 3175',          'rating' => 4.7, 'reviews' => 156, 'stock' => 38, 'badges' => [ 'Featured', 'Used Cars' ],       'seed' => '1526726702-f454726f5bfb' ],
        [ 'name' => 'Vic Auto Traders',      'address' => '211 Barkly St, Footscray VIC 3011',           'rating' => 4.6, 'reviews' => 78,  'stock' => 52, 'badges' => [ 'Used Cars' ],                   'seed' => '1533473407-0c8d6249ef72' ],
        [ 'name' => 'South Eastern Cars',    'address' => '56 Beach St, Frankston VIC 3199',             'rating' => 4.8, 'reviews' => 241, 'stock' => 29, 'badges' => [ 'Featured', 'New Cars' ],         'seed' => '1583121798-6d8ff1cf1226' ],
        [ 'name' => 'Ringwood Motors',       'address' => '88 Canterbury Rd, Ringwood VIC 3134',         'rating' => 4.5, 'reviews' => 63,  'stock' => 44, 'badges' => [ 'Used Cars' ],                   'seed' => '1533469934-d0cdca51b95e' ],
        [ 'name' => 'Essendon Auto',         'address' => '44 Keilor Rd, Essendon VIC 3040',             'rating' => 4.6, 'reviews' => 91,  'stock' => 37, 'badges' => [ 'Used Cars' ],                   'seed' => '1615415027-0cd849c12c4e' ],
        [ 'name' => 'Melbourne City Cars',   'address' => '9 Bourke St, Melbourne VIC 3000',             'rating' => 4.9, 'reviews' => 427, 'stock' => 71, 'badges' => [ 'Featured', 'New Cars', 'Used Cars' ], 'seed' => '1617531398-9f3c91f2c86d' ],
    ],
    'perth'     => [
        [ 'name' => 'Perth City Cars',       'address' => '111 Scarborough Beach Rd, Osborne Park WA 6017', 'rating' => 4.7, 'reviews' => 189, 'stock' => 41, 'badges' => [ 'Featured', 'Used Cars' ], 'seed' => '1469285949483-d7b2a82b8b9e' ],
        [ 'name' => 'West Coast Autos',      'address' => '24 Great Eastern Hwy, Midland WA 6056',          'rating' => 4.5, 'reviews' => 54,  'stock' => 18, 'badges' => [ 'Used Cars' ],              'seed' => '1580274438-98e0b05fefb5' ],
        [ 'name' => 'Swan Valley Motors',    'address' => '7 Lloyd St, Midvale WA 6056',                    'rating' => 4.6, 'reviews' => 112, 'stock' => 33, 'badges' => [ 'Featured', 'New Cars' ],   'seed' => '1549399511-fa4c4d8ce477' ],
        [ 'name' => 'Fremantle Auto',        'address' => '45 South Tce, Fremantle WA 6160',                'rating' => 4.8, 'reviews' => 76,  'stock' => 27, 'badges' => [ 'Used Cars' ],              'seed' => '1601049861-adf3849d0e53' ],
        [ 'name' => 'Canning Vale Cars',     'address' => '22 Nicholson Rd, Canning Vale WA 6155',          'rating' => 4.4, 'reviews' => 39,  'stock' => 15, 'badges' => [ 'Used Cars' ],              'seed' => '1633956100-a84cc7791db1' ],
    ],
    'darwin'    => [
        [ 'name' => 'Top End Auto Sales',    'address' => '18 Winnellie Rd, Winnellie NT 0820',   'rating' => 4.9, 'reviews' => 67,  'stock' => 14, 'badges' => [ 'Featured', 'Used Cars' ], 'seed' => '1494905060-a01de8f7b4c1' ],
        [ 'name' => 'NT Car Centre',         'address' => '33 Trade Pl, Berrimah NT 0828',        'rating' => 4.7, 'reviews' => 88,  'stock' => 22, 'badges' => [ 'Used Cars' ],              'seed' => '1542362567-b07e54358753' ],
        [ 'name' => 'Darwin Auto Group',     'address' => '5 Cavenagh St, Darwin City NT 0800',   'rating' => 4.6, 'reviews' => 44,  'stock' => 19, 'badges' => [ 'Used Cars' ],              'seed' => '1558618666-fcd25c85cd64' ],
        [ 'name' => 'Palmerston Car Sales',  'address' => '12 Temple Tce, Palmerston NT 0830',    'rating' => 4.5, 'reviews' => 31,  'stock' => 11, 'badges' => [ 'Used Cars' ],              'seed' => '1605559424843-9073199d0f75' ],
    ],
];
?>

<!-- PAGE HEADER -->
<section class="ic-page-hero section" aria-label="<?php esc_attr_e( 'Dealer directory header', 'imanicars' ); ?>">
  <div class="container">
    <h1 class="ic-page-hero__title"><?php esc_html_e( 'Find a Car Dealer Near You', 'imanicars' ); ?></h1>
    <p class="ic-page-hero__sub"><?php esc_html_e( 'Browse verified dealers across Brisbane, Melbourne, Perth and Darwin. View stock, read reviews and contact dealers directly.', 'imanicars' ); ?></p>

    <!-- DEALER SEARCH -->
    <form class="ic-dealers-search" role="search" action="<?php echo esc_url( home_url( '/dealers/' ) ); ?>" method="get">
      <input type="text" name="location" class="ic-dealers-search__input" placeholder="<?php esc_attr_e( 'Suburb or postcode', 'imanicars' ); ?>" aria-label="<?php esc_attr_e( 'Search by location', 'imanicars' ); ?>">
      <select name="make" class="ic-dealers-search__select" aria-label="<?php esc_attr_e( 'Filter by make', 'imanicars' ); ?>">
        <option value=""><?php esc_html_e( 'All Makes', 'imanicars' ); ?></option>
        <?php foreach ( [ 'Toyota', 'Ford', 'Mazda', 'Hyundai', 'Kia', 'Nissan', 'Mitsubishi', 'BMW', 'Mercedes-Benz', 'Audi' ] as $m ) echo '<option value="' . esc_attr( $m ) . '">' . esc_html( $m ) . '</option>'; ?>
      </select>
      <button type="submit" class="btn btn-primary ic-dealers-search__btn"><?php esc_html_e( 'Search Dealers', 'imanicars' ); ?></button>
    </form>
  </div>
</section>

<!-- CITY TABS + DEALER PANELS -->
<section class="ic-dealers-section section" aria-label="<?php esc_attr_e( 'Dealer listings', 'imanicars' ); ?>">
  <div class="container">
    <div class="ic-city-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Filter dealers by city', 'imanicars' ); ?>">
      <button class="ic-city-tab ic-city-tab--active" role="tab" aria-selected="true"  data-city="brisbane"><?php esc_html_e( 'Brisbane', 'imanicars' ); ?></button>
      <button class="ic-city-tab" role="tab" aria-selected="false" data-city="melbourne"><?php esc_html_e( 'Melbourne', 'imanicars' ); ?></button>
      <button class="ic-city-tab" role="tab" aria-selected="false" data-city="perth"><?php esc_html_e( 'Perth', 'imanicars' ); ?></button>
      <button class="ic-city-tab" role="tab" aria-selected="false" data-city="darwin"><?php esc_html_e( 'Darwin', 'imanicars' ); ?></button>
    </div>

    <?php foreach ( $dealers as $city => $dealer_list ) :
        $is_active = ( $city === 'brisbane' );
    ?>
    <div class="ic-city-panel <?php echo $is_active ? 'ic-city-panel--active' : ''; ?>"
         id="ic-city-<?php echo esc_attr( $city ); ?>"
         role="tabpanel" aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>">
      <div class="ic-dealers-grid">
        <?php foreach ( $dealer_list as $dealer ) :
            $stars = round( $dealer['rating'] * 2 ) / 2;
        ?>
        <article class="ic-dealer-card">
          <div class="ic-dealer-card__logo">
            <img src="<?php echo esc_url( ic_unsplash( $dealer['seed'], 250, 250 ) ); ?>"
                 alt="<?php echo esc_attr( $dealer['name'] ); ?>" width="250" height="250" loading="lazy" class="ic-dealer-card__logo-img">
          </div>
          <div class="ic-dealer-card__body">
            <div class="ic-dealer-card__top">
              <h3 class="ic-dealer-card__name"><?php echo esc_html( $dealer['name'] ); ?></h3>
              <div class="ic-dealer-card__badges">
                <?php foreach ( $dealer['badges'] as $badge ) : ?>
                <span class="ic-dealer-card__badge ic-dealer-card__badge--<?php echo esc_attr( strtolower( str_replace( ' ', '-', $badge ) ) ); ?>"><?php echo esc_html( $badge ); ?></span>
                <?php endforeach; ?>
              </div>
            </div>
            <p class="ic-dealer-card__address"><?php echo esc_html( $dealer['address'] ); ?></p>
            <div class="ic-dealer-card__rating">
              <span class="ic-dealer-card__stars" aria-label="<?php echo esc_attr( $dealer['rating'] . ' out of 5 stars' ); ?>">
                <?php
                for ( $s = 1; $s <= 5; $s++ ) {
                    if ( $s <= (int) $stars ) {
                        echo '<span class="ic-star ic-star--full" aria-hidden="true">&#9733;</span>';
                    } elseif ( $dealer['rating'] >= $s - 0.5 ) {
                        echo '<span class="ic-star ic-star--half" aria-hidden="true">&#9733;</span>';
                    } else {
                        echo '<span class="ic-star ic-star--empty" aria-hidden="true">&#9734;</span>';
                    }
                }
                ?>
              </span>
              <span class="ic-dealer-card__rating-num"><?php echo esc_html( number_format( $dealer['rating'], 1 ) ); ?></span>
              <span class="ic-dealer-card__review-count">
                <?php
                /* translators: %d: number of reviews */
                printf( esc_html__( '(%d reviews)', 'imanicars' ), (int) $dealer['reviews'] );
                ?>
              </span>
            </div>
            <p class="ic-dealer-card__stock">
              <?php
              /* translators: %d: number of cars */
              printf( esc_html__( '%d cars listed', 'imanicars' ), (int) $dealer['stock'] );
              ?>
            </p>
          </div>
          <div class="ic-dealer-card__actions">
            <a href="<?php echo esc_url( home_url( '/cars/?dealer=' . urlencode( $dealer['name'] ) ) ); ?>" class="btn btn-primary btn-sm"><?php esc_html_e( 'View Stock', 'imanicars' ); ?></a>
            <a href="<?php echo esc_url( home_url( '/contact/?dealer=' . urlencode( $dealer['name'] ) ) ); ?>" class="btn btn-outline btn-sm"><?php esc_html_e( 'Contact Dealer', 'imanicars' ); ?></a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</section>

<!-- DEALER CTA -->
<section class="ic-dealers-join section section--grey" aria-label="<?php esc_attr_e( 'Join as a dealer', 'imanicars' ); ?>">
  <div class="container text-center">
    <h2><?php esc_html_e( 'Are You a Car Dealer?', 'imanicars' ); ?></h2>
    <p><?php esc_html_e( "Join 50+ dealers already listing on Imani Cars. Free to start, no contract required.", 'imanicars' ); ?></p>
    <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="btn btn-primary btn-lg"><?php esc_html_e( 'List Your Cars Free', 'imanicars' ); ?> &rarr;</a>
  </div>
</section>

<?php get_footer(); ?>
