<?php
/**
 * Front Page — imanicars.com
 * Hero + search tabs + stats bar + featured listings + category tiles + dealer CTA + testimonials
 */
get_header();
?>

<!-- =========================================================
     HERO SECTION
     ========================================================= -->
<section class="ic-hero" aria-label="<?php esc_attr_e( 'Search for cars', 'imanicars' ); ?>">
  <div class="ic-hero__bg">
    <img src="<?php echo esc_url( ic_unsplash( '1492144534655-ae79c964c9d7', 1920, 600 ) ); ?>"
         alt="Australian cars for sale" width="1920" height="600" loading="eager" class="ic-hero__img">
  </div>
  <div class="ic-hero__overlay" aria-hidden="true"></div>
  <div class="container ic-hero__content">
    <h1 class="ic-hero__title">
      <?php esc_html_e( 'Find Cars for Sale Across Australia.', 'imanicars' ); ?><br>
      <span class="ic-hero__accent"><?php esc_html_e( 'Australia-Wide. Free to Search.', 'imanicars' ); ?></span>
    </h1>
    <p class="ic-hero__sub"><?php esc_html_e( 'Browse thousands of used &amp; new cars from dealers and private sellers across Brisbane, Melbourne, Perth and Darwin.', 'imanicars' ); ?></p>

    <!-- SEARCH CARD -->
    <div class="ic-search-card">
      <div class="ic-search-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Search type', 'imanicars' ); ?>">
        <button class="ic-search-tab ic-search-tab--active" role="tab" aria-selected="true" data-tab="all"><?php esc_html_e( 'All Cars', 'imanicars' ); ?></button>
        <button class="ic-search-tab" role="tab" aria-selected="false" data-tab="used"><?php esc_html_e( 'Used', 'imanicars' ); ?></button>
        <button class="ic-search-tab" role="tab" aria-selected="false" data-tab="new"><?php esc_html_e( 'New', 'imanicars' ); ?></button>
        <button class="ic-search-tab" role="tab" aria-selected="false" data-tab="dealer"><?php esc_html_e( 'Dealer', 'imanicars' ); ?></button>
        <button class="ic-search-tab" role="tab" aria-selected="false" data-tab="private"><?php esc_html_e( 'Private', 'imanicars' ); ?></button>
        <button class="ic-search-tab" role="tab" aria-selected="false" data-tab="finance"><?php esc_html_e( 'Finance', 'imanicars' ); ?></button>
      </div>
      <form class="ic-search-form" id="ic-hero-search" role="search" action="<?php echo esc_url( home_url( '/cars/' ) ); ?>" method="get">
        <input type="hidden" name="condition" id="ic-search-condition" value="">
        <input type="hidden" name="seller"    id="ic-search-seller"    value="">
        <div class="ic-search-form__row">
          <div class="ic-search-form__group">
            <label for="ic-make" class="ic-search-form__label"><?php esc_html_e( 'Make', 'imanicars' ); ?></label>
            <select name="make" id="ic-make" class="ic-search-form__select">
              <option value=""><?php esc_html_e( 'Any Make', 'imanicars' ); ?></option>
              <?php
              $makes = [ 'Toyota', 'Mazda', 'Ford', 'Holden', 'Honda', 'Nissan', 'Mitsubishi', 'Hyundai', 'Kia', 'Subaru', 'BMW', 'Mercedes-Benz', 'Audi', 'Volkswagen', 'Jeep', 'Land Rover', 'Tesla', 'Isuzu', 'LDV', 'MG', 'Suzuki', 'Renault', 'Skoda', 'Volvo', 'Peugeot', 'BYD', 'GWM', 'Haval' ];
              foreach ( $makes as $make ) {
                  echo '<option value="' . esc_attr( $make ) . '">' . esc_html( $make ) . '</option>';
              }
              ?>
            </select>
          </div>
          <div class="ic-search-form__group">
            <label for="ic-model" class="ic-search-form__label"><?php esc_html_e( 'Model', 'imanicars' ); ?></label>
            <select name="model" id="ic-model" class="ic-search-form__select">
              <option value=""><?php esc_html_e( 'Any Model', 'imanicars' ); ?></option>
            </select>
          </div>
          <div class="ic-search-form__group">
            <label for="ic-location" class="ic-search-form__label"><?php esc_html_e( 'Location', 'imanicars' ); ?></label>
            <input type="text" name="location" id="ic-location" class="ic-search-form__input"
                   placeholder="<?php esc_attr_e( 'Suburb or postcode', 'imanicars' ); ?>">
          </div>
          <div class="ic-search-form__group ic-search-form__group--submit">
            <button type="submit" class="btn btn-primary btn-lg ic-search-form__submit">
              <?php esc_html_e( 'Search Cars', 'imanicars' ); ?>
            </button>
          </div>
        </div>
        <div class="ic-search-form__advanced-toggle">
          <button type="button" class="ic-search-form__more-btn" id="ic-toggle-advanced" aria-expanded="false">
            <?php esc_html_e( '+ More filters (price, year, body type)', 'imanicars' ); ?>
          </button>
        </div>
        <div class="ic-search-form__advanced" id="ic-advanced-filters" hidden>
          <div class="ic-search-form__row">
            <div class="ic-search-form__group">
              <label for="ic-price-min" class="ic-search-form__label"><?php esc_html_e( 'Min Price', 'imanicars' ); ?></label>
              <select name="price_min" id="ic-price-min" class="ic-search-form__select">
                <option value=""><?php esc_html_e( 'No min', 'imanicars' ); ?></option>
                <?php foreach ( [ 5000, 10000, 15000, 20000, 25000, 30000, 40000, 50000, 75000 ] as $p ) echo '<option value="' . esc_attr( $p ) . '">$' . esc_html( number_format( $p ) ) . '</option>'; ?>
              </select>
            </div>
            <div class="ic-search-form__group">
              <label for="ic-price-max" class="ic-search-form__label"><?php esc_html_e( 'Max Price', 'imanicars' ); ?></label>
              <select name="price_max" id="ic-price-max" class="ic-search-form__select">
                <option value=""><?php esc_html_e( 'No max', 'imanicars' ); ?></option>
                <?php foreach ( [ 15000, 20000, 25000, 30000, 40000, 50000, 75000, 100000, 150000 ] as $p ) echo '<option value="' . esc_attr( $p ) . '">$' . esc_html( number_format( $p ) ) . '</option>'; ?>
              </select>
            </div>
            <div class="ic-search-form__group">
              <label for="ic-year-from" class="ic-search-form__label"><?php esc_html_e( 'Year From', 'imanicars' ); ?></label>
              <select name="year_min" id="ic-year-from" class="ic-search-form__select">
                <option value=""><?php esc_html_e( 'Any', 'imanicars' ); ?></option>
                <?php for ( $y = (int) gmdate( 'Y' ); $y >= 1995; $y-- ) echo '<option value="' . esc_attr( $y ) . '">' . esc_html( $y ) . '</option>'; ?>
              </select>
            </div>
            <div class="ic-search-form__group">
              <label for="ic-body-type" class="ic-search-form__label"><?php esc_html_e( 'Body Type', 'imanicars' ); ?></label>
              <select name="body_type" id="ic-body-type" class="ic-search-form__select">
                <option value=""><?php esc_html_e( 'Any', 'imanicars' ); ?></option>
                <?php foreach ( [ 'SUV', 'Sedan', 'Hatch', 'Ute', 'Wagon', 'Coupe', 'Convertible', 'Van', 'People Mover' ] as $b ) echo '<option value="' . esc_attr( $b ) . '">' . esc_html( $b ) . '</option>'; ?>
              </select>
            </div>
          </div>
        </div>
      </form>
    </div><!-- /.ic-search-card -->
  </div>
</section>

<!-- =========================================================
     STATS BAR
     ========================================================= -->
<section class="ic-stats-bar" aria-label="<?php esc_attr_e( 'Platform stats', 'imanicars' ); ?>">
  <div class="container">
    <div class="ic-stats-bar__grid">
      <div class="ic-stats-bar__item">
        <span class="ic-stats-bar__number">4</span>
        <span class="ic-stats-bar__label"><?php esc_html_e( 'Cities Covered', 'imanicars' ); ?></span>
      </div>
      <div class="ic-stats-bar__item">
        <span class="ic-stats-bar__number">300+</span>
        <span class="ic-stats-bar__label"><?php esc_html_e( 'Verified Dealers', 'imanicars' ); ?></span>
      </div>
      <div class="ic-stats-bar__item">
        <span class="ic-stats-bar__number">8,000+</span>
        <span class="ic-stats-bar__label"><?php esc_html_e( 'Active Listings', 'imanicars' ); ?></span>
      </div>
      <div class="ic-stats-bar__item">
        <span class="ic-stats-bar__number ic-stats-bar__number--free">FREE</span>
        <span class="ic-stats-bar__label"><?php esc_html_e( 'to List Your Car', 'imanicars' ); ?></span>
      </div>
    </div>
  </div>
</section>

<!-- =========================================================
     FEATURED LISTINGS — CITY TABS
     ========================================================= -->
<section class="ic-listings-section section" aria-label="<?php esc_attr_e( 'Featured listings by city', 'imanicars' ); ?>">
  <div class="container">
    <div class="ic-section-header">
      <h2 class="ic-section-title"><?php esc_html_e( 'Featured Cars Near You', 'imanicars' ); ?></h2>
      <a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>" class="ic-section-link"><?php esc_html_e( 'View all listings', 'imanicars' ); ?> &rarr;</a>
      <a href="<?php echo esc_url( home_url( '/used-cars/' ) ); ?>" class="ic-section-link" style="margin-left:1rem;"><?php esc_html_e( 'Used Cars', 'imanicars' ); ?> &rarr;</a>
    </div>
    <div class="ic-city-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Filter by city', 'imanicars' ); ?>">
      <button class="ic-city-tab ic-city-tab--active" role="tab" aria-selected="true"  data-city="brisbane"><?php esc_html_e( 'Brisbane', 'imanicars' ); ?></button>
      <button class="ic-city-tab" role="tab" aria-selected="false" data-city="melbourne"><?php esc_html_e( 'Melbourne', 'imanicars' ); ?></button>
      <button class="ic-city-tab" role="tab" aria-selected="false" data-city="perth"><?php esc_html_e( 'Perth', 'imanicars' ); ?></button>
      <button class="ic-city-tab" role="tab" aria-selected="false" data-city="darwin"><?php esc_html_e( 'Darwin', 'imanicars' ); ?></button>
    </div>

    <?php
    $city_cars = [
        'brisbane'  => [
            [ 'year' => 2021, 'make' => 'Toyota',   'model' => 'RAV4',        'badge_v' => 'GX AWD',       'price' => 42990, 'km' => 38000, 'trans' => 'Auto',   'fuel' => 'Petrol',   'body' => 'SUV',   'suburb' => 'Fortitude Valley', 'dealer' => 'City Cars Brisbane',    'badge_type' => 'used',     'seed' => '1552519152-9214d16d56ab' ],
            [ 'year' => 2020, 'make' => 'Mazda',    'model' => 'CX-5',        'badge_v' => 'Touring AWD',  'price' => 37500, 'km' => 52000, 'trans' => 'Auto',   'fuel' => 'Diesel',   'body' => 'SUV',   'suburb' => 'Moorooka',         'dealer' => 'South Side Auto',       'badge_type' => 'used',     'seed' => '1503376780353-7e6692767b70' ],
            [ 'year' => 2022, 'make' => 'Ford',     'model' => 'Ranger',      'badge_v' => 'XLT 4x4',     'price' => 61900, 'km' => 18000, 'trans' => 'Auto',   'fuel' => 'Diesel',   'body' => 'Ute',   'suburb' => 'Springwood',       'dealer' => 'QLD Car Centre',        'badge_type' => 'featured', 'seed' => '1590362891991-f776e747a588' ],
            [ 'year' => 2019, 'make' => 'Honda',    'model' => 'CR-V',        'badge_v' => 'VTi-L 4WD',   'price' => 31500, 'km' => 74000, 'trans' => 'Auto',   'fuel' => 'Petrol',   'body' => 'SUV',   'suburb' => 'Fortitude Valley', 'dealer' => 'City Cars Brisbane',    'badge_type' => 'used',     'seed' => '1555215695-3004980ad54e' ],
            [ 'year' => 2023, 'make' => 'Hyundai',  'model' => 'Tucson',      'badge_v' => 'Elite AWD',    'price' => 47900, 'km' => 9500,  'trans' => 'Auto',   'fuel' => 'Petrol',   'body' => 'SUV',   'suburb' => 'Moorooka',         'dealer' => 'South Side Auto',       'badge_type' => 'new',      'seed' => '1544636331-e26879cd4d9b' ],
            [ 'year' => 2021, 'make' => 'Subaru',   'model' => 'Outback',     'badge_v' => 'AWD Premium',  'price' => 39990, 'km' => 41000, 'trans' => 'Auto',   'fuel' => 'Petrol',   'body' => 'Wagon', 'suburb' => 'Springwood',       'dealer' => 'QLD Car Centre',        'badge_type' => 'used',     'seed' => '1617788602929-5c43d8e73dac' ],
        ],
        'melbourne' => [
            [ 'year' => 2022, 'make' => 'Kia',           'model' => 'Sportage',  'badge_v' => 'GT-Line AWD',   'price' => 49500, 'km' => 22000, 'trans' => 'Auto',   'fuel' => 'Petrol',   'body' => 'SUV',   'suburb' => 'Dandenong',    'dealer' => 'Metro Motors Melbourne', 'badge_type' => 'featured', 'seed' => '1542362567-b07e54358753' ],
            [ 'year' => 2020, 'make' => 'Volkswagen',    'model' => 'Tiguan',    'badge_v' => 'Allspace 7-seat','price' => 44200, 'km' => 48000, 'trans' => 'Auto',   'fuel' => 'Petrol',   'body' => 'SUV',   'suburb' => 'Footscray',    'dealer' => 'Vic Auto Traders',       'badge_type' => 'used',     'seed' => '1558618666-fcd25c85cd64' ],
            [ 'year' => 2021, 'make' => 'Mercedes-Benz', 'model' => 'C200',      'badge_v' => 'Luxury Sedan',  'price' => 72000, 'km' => 28000, 'trans' => 'Auto',   'fuel' => 'Petrol',   'body' => 'Sedan', 'suburb' => 'Frankston',    'dealer' => 'South Eastern Cars',     'badge_type' => 'featured', 'seed' => '1605559424843-9073199d0f75' ],
            [ 'year' => 2019, 'make' => 'Toyota',        'model' => 'HiLux',     'badge_v' => 'SR5 4x4',       'price' => 52800, 'km' => 61000, 'trans' => 'Auto',   'fuel' => 'Diesel',   'body' => 'Ute',   'suburb' => 'Dandenong',    'dealer' => 'Metro Motors Melbourne', 'badge_type' => 'used',     'seed' => '1549317661-cf369843ed2c' ],
            [ 'year' => 2023, 'make' => 'Tesla',         'model' => 'Model 3',   'badge_v' => 'Long Range AWD','price' => 76990, 'km' => 4200,  'trans' => 'Auto',   'fuel' => 'Electric', 'body' => 'Sedan', 'suburb' => 'Footscray',    'dealer' => 'Vic Auto Traders',       'badge_type' => 'new',      'seed' => '1526726702-f454726f5bfb' ],
            [ 'year' => 2022, 'make' => 'BMW',            'model' => 'X3',        'badge_v' => 'xDrive20i',     'price' => 68500, 'km' => 31000, 'trans' => 'Auto',   'fuel' => 'Petrol',   'body' => 'SUV',   'suburb' => 'Frankston',    'dealer' => 'South Eastern Cars',     'badge_type' => 'used',     'seed' => '1617531398-9f3c91f2c86d' ],
        ],
        'perth'     => [
            [ 'year' => 2021, 'make' => 'Isuzu',      'model' => 'D-Max',        'badge_v' => 'LS-U 4x4',     'price' => 54900,  'km' => 42000, 'trans' => 'Auto',   'fuel' => 'Diesel',  'body' => 'Ute',   'suburb' => 'Osborne Park', 'dealer' => 'Perth City Cars',    'badge_type' => 'used',     'seed' => '1469285949483-d7b2a82b8b9e' ],
            [ 'year' => 2020, 'make' => 'Mitsubishi', 'model' => 'Pajero Sport', 'badge_v' => 'Exceed 4WD',   'price' => 46800,  'km' => 55000, 'trans' => 'Auto',   'fuel' => 'Diesel',  'body' => 'SUV',   'suburb' => 'Midland',      'dealer' => 'West Coast Autos',   'badge_type' => 'used',     'seed' => '1580274438-98e0b05fefb5' ],
            [ 'year' => 2022, 'make' => 'Land Rover', 'model' => 'Defender',     'badge_v' => '110 SE D200',  'price' => 115000, 'km' => 15000, 'trans' => 'Auto',   'fuel' => 'Diesel',  'body' => 'SUV',   'suburb' => 'Midvale',      'dealer' => 'Swan Valley Motors', 'badge_type' => 'featured', 'seed' => '1549399511-fa4c4d8ce477' ],
            [ 'year' => 2019, 'make' => 'Nissan',     'model' => 'Navara',       'badge_v' => 'ST-X 4x4',     'price' => 38500,  'km' => 78000, 'trans' => 'Manual', 'fuel' => 'Diesel',  'body' => 'Ute',   'suburb' => 'Osborne Park', 'dealer' => 'Perth City Cars',    'badge_type' => 'used',     'seed' => '1601049861-adf3849d0e53' ],
            [ 'year' => 2023, 'make' => 'GWM',        'model' => 'Haval H6',     'badge_v' => 'Lux Hybrid',   'price' => 44990,  'km' => 7800,  'trans' => 'Auto',   'fuel' => 'Hybrid',  'body' => 'SUV',   'suburb' => 'Midland',      'dealer' => 'West Coast Autos',   'badge_type' => 'new',      'seed' => '1633956100-a84cc7791db1' ],
            [ 'year' => 2021, 'make' => 'Jeep',       'model' => 'Wrangler',     'badge_v' => 'Rubicon 4xe',  'price' => 82000,  'km' => 24000, 'trans' => 'Auto',   'fuel' => 'Hybrid',  'body' => 'SUV',   'suburb' => 'Midvale',      'dealer' => 'Swan Valley Motors', 'badge_type' => 'used',     'seed' => '1599007229-c10a0d14d1d3' ],
        ],
        'darwin'    => [
            [ 'year' => 2020, 'make' => 'Toyota',    'model' => 'LandCruiser', 'badge_v' => '200 GXL 4WD', 'price' => 89900, 'km' => 68000, 'trans' => 'Auto',   'fuel' => 'Diesel', 'body' => 'SUV', 'suburb' => 'Winnellie', 'dealer' => 'Top End Auto Sales', 'badge_type' => 'featured', 'seed' => '1494905060-a01de8f7b4c1' ],
            [ 'year' => 2021, 'make' => 'Ford',      'model' => 'Ranger',      'badge_v' => 'Wildtrak 4x4','price' => 55800, 'km' => 39000, 'trans' => 'Auto',   'fuel' => 'Diesel', 'body' => 'Ute', 'suburb' => 'Berrimah',  'dealer' => 'NT Car Centre',      'badge_type' => 'used',     'seed' => '1533473407-0c8d6249ef72' ],
            [ 'year' => 2019, 'make' => 'Mitsubishi','model' => 'Triton',      'badge_v' => 'GLS 4WD',     'price' => 36200, 'km' => 82000, 'trans' => 'Auto',   'fuel' => 'Diesel', 'body' => 'Ute', 'suburb' => 'Winnellie', 'dealer' => 'Top End Auto Sales', 'badge_type' => 'used',     'seed' => '1583121798-6d8ff1cf1226' ],
            [ 'year' => 2022, 'make' => 'Mazda',     'model' => 'BT-50',       'badge_v' => 'SP 4x4',      'price' => 50900, 'km' => 21000, 'trans' => 'Auto',   'fuel' => 'Diesel', 'body' => 'Ute', 'suburb' => 'Berrimah',  'dealer' => 'NT Car Centre',      'badge_type' => 'new',      'seed' => '1533469934-d0cdca51b95e' ],
            [ 'year' => 2020, 'make' => 'Nissan',    'model' => 'Patrol',      'badge_v' => 'TI-L 4WD',    'price' => 74000, 'km' => 47000, 'trans' => 'Auto',   'fuel' => 'Petrol', 'body' => 'SUV', 'suburb' => 'Winnellie', 'dealer' => 'Top End Auto Sales', 'badge_type' => 'used',     'seed' => '1615415027-0cd849c12c4e' ],
            [ 'year' => 2021, 'make' => 'Toyota',    'model' => 'HiLux',       'badge_v' => 'SR 4x4',      'price' => 48500, 'km' => 33000, 'trans' => 'Manual', 'fuel' => 'Diesel', 'body' => 'Ute', 'suburb' => 'Berrimah',  'dealer' => 'NT Car Centre',      'badge_type' => 'used',     'seed' => '1559056736-98e50dfc57dc' ],
        ],
    ];
    $badge_labels = [ 'featured' => 'FEATURED', 'new' => 'NEW', 'certified' => 'CERTIFIED', 'demo' => 'DEMO', 'used' => 'USED' ];
    $city_names   = [ 'brisbane' => 'Brisbane', 'melbourne' => 'Melbourne', 'perth' => 'Perth', 'darwin' => 'Darwin' ];

    foreach ( $city_cars as $city => $cars ) :
        $is_active = ( $city === 'brisbane' );
    ?>
    <div class="ic-city-panel <?php echo $is_active ? 'ic-city-panel--active' : ''; ?>"
         id="ic-city-<?php echo esc_attr( $city ); ?>"
         role="tabpanel" aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>">
      <div class="ic-car-grid">
        <?php foreach ( $cars as $car ) :
            $bl = isset( $badge_labels[ $car['badge_type'] ] ) ? $badge_labels[ $car['badge_type'] ] : 'USED';
        ?>
        <article class="ic-car-card">
          <div class="ic-car-card__img-wrap">
            <a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>">
              <img src="<?php echo esc_url( ic_unsplash( $car['seed'], 400, 267 ) ); ?>"
                   alt="<?php echo esc_attr( $car['year'] . ' ' . $car['make'] . ' ' . $car['model'] ); ?>"
                   width="400" height="267" loading="lazy" class="ic-car-card__img">
            </a>
            <span class="ic-car-card__badge ic-car-card__badge--<?php echo esc_attr( $car['badge_type'] ); ?>"><?php echo esc_html( $bl ); ?></span>
            <button class="ic-car-card__fav" aria-label="<?php esc_attr_e( 'Save to favourites', 'imanicars' ); ?>">
              <span aria-hidden="true">&#9825;</span>
            </button>
          </div>
          <div class="ic-car-card__body">
            <div class="ic-car-card__price-row">
              <span class="ic-car-card__price"><?php echo esc_html( ic_format_price( $car['price'] ) ); ?></span>
              <span class="ic-car-card__price-label"><?php esc_html_e( 'Drive Away', 'imanicars' ); ?></span>
            </div>
            <h3 class="ic-car-card__title">
              <a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>">
                <?php echo esc_html( $car['year'] . ' ' . $car['make'] . ' ' . $car['model'] . ' ' . $car['badge_v'] ); ?>
              </a>
            </h3>
            <ul class="ic-car-card__specs">
              <li class="ic-spec-pill"><?php echo esc_html( number_format( $car['km'] ) ); ?> km</li>
              <li class="ic-spec-pill"><?php echo esc_html( $car['trans'] ); ?></li>
              <li class="ic-spec-pill"><?php echo esc_html( $car['fuel'] ); ?></li>
              <li class="ic-spec-pill"><?php echo esc_html( $car['body'] ); ?></li>
            </ul>
            <div class="ic-car-card__seller">
              <span class="ic-car-card__location"><?php echo esc_html( $car['suburb'] . ', ' . strtoupper( substr( $city, 0, 3 ) ) ); ?></span>
              <span class="ic-car-card__dealer"><?php echo esc_html( $car['dealer'] ); ?></span>
            </div>
          </div>
          <div class="ic-car-card__actions">
            <a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>" class="btn btn-primary btn-sm ic-car-card__enquire"><?php esc_html_e( 'Enquire', 'imanicars' ); ?></a>
            <button class="btn btn-outline btn-sm ic-car-card__save"><?php esc_html_e( 'Save', 'imanicars' ); ?></button>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <div class="ic-listings-section__footer-cta">
        <a href="<?php echo esc_url( home_url( '/' . $city . '/' ) ); ?>" class="btn btn-outline">
          <?php
          $cname = isset( $city_names[ $city ] ) ? $city_names[ $city ] : ucfirst( $city );
          /* translators: %s: city name */
          printf( esc_html__( 'View all %s listings', 'imanicars' ), esc_html( $cname ) );
          ?>
          &rarr;
        </a>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</section>

<!-- =========================================================
     BROWSE BY BODY TYPE
     ========================================================= -->
<section class="ic-body-types section section--grey" aria-label="<?php esc_attr_e( 'Browse by body type', 'imanicars' ); ?>">
  <div class="container">
    <h2 class="ic-section-title text-center"><?php esc_html_e( 'Browse by Body Type', 'imanicars' ); ?></h2>
    <div class="ic-type-grid">
      <?php
      $types_data = [
          [ 'slug' => 'SUV',         'label' => 'SUV',         'seed' => '1494905060-a01de8f7b4c1' ],
          [ 'slug' => 'Sedan',       'label' => 'Sedan',       'seed' => '1526726702-f454726f5bfb' ],
          [ 'slug' => 'Hatch',       'label' => 'Hatch',       'seed' => '1617531398-9f3c91f2c86d' ],
          [ 'slug' => 'Ute',         'label' => 'Ute',         'seed' => '1469285949483-d7b2a82b8b9e' ],
          [ 'slug' => 'Wagon',       'label' => 'Wagon',       'seed' => '1580274438-98e0b05fefb5' ],
          [ 'slug' => 'Coupe',       'label' => 'Coupe',       'seed' => '1549399511-fa4c4d8ce477' ],
          [ 'slug' => 'Convertible', 'label' => 'Convertible', 'seed' => '1601049861-adf3849d0e53' ],
          [ 'slug' => 'People Mover','label' => 'People Mover','seed' => '1633956100-a84cc7791db1' ],
          [ 'slug' => 'Van',         'label' => 'Van',         'seed' => '1599007229-c10a0d14d1d3' ],
          [ 'slug' => 'Caravan',     'label' => 'Caravan',     'seed' => '' ],
      ];
      foreach ( $types_data as $type ) :
      ?>
      <a href="<?php echo esc_url( home_url( '/cars/?body_type=' . urlencode( $type['slug'] ) ) ); ?>" class="ic-type-tile">
        <div class="ic-type-tile__img-wrap">
          <img src="<?php echo esc_url( ic_body_type_image( $type['slug'], 280, 187 ) ); ?>"
               alt="<?php echo esc_attr( $type['label'] . ' cars for sale' ); ?>"
               width="280" height="187" loading="lazy" class="ic-type-tile__img">
        </div>
        <span class="ic-type-tile__label"><?php echo esc_html( $type['label'] ); ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     DEALER CTA BANNER
     ========================================================= -->
<section class="ic-dealer-cta" aria-label="<?php esc_attr_e( 'List your dealership', 'imanicars' ); ?>">
  <div class="ic-dealer-cta__bg" aria-hidden="true">
    <img src="<?php echo esc_url( ic_unsplash( '1534073828943-129b9a0b32bb', 1280, 427 ) ); ?>"
         alt="" width="1280" height="427" loading="lazy" class="ic-dealer-cta__bg-img">
  </div>
  <div class="ic-dealer-cta__overlay" aria-hidden="true"></div>
  <div class="container ic-dealer-cta__content">
    <div class="ic-dealer-cta__text">
      <h2 class="ic-dealer-cta__title"><?php esc_html_e( 'Are You a Car Dealer?', 'imanicars' ); ?></h2>
      <p class="ic-dealer-cta__sub"><?php esc_html_e( 'List your inventory for FREE. Reach thousands of serious buyers across 4 cities. No credit card. No contract.', 'imanicars' ); ?></p>
      <div class="ic-dealer-cta__perks">
        <span class="ic-dealer-cta__perk">&#10003; <?php esc_html_e( 'Free forever for up to 10 listings', 'imanicars' ); ?></span>
        <span class="ic-dealer-cta__perk">&#10003; <?php esc_html_e( 'Get buyer leads direct to your email', 'imanicars' ); ?></span>
        <span class="ic-dealer-cta__perk">&#10003; <?php esc_html_e( 'Cancel anytime — no lock-in', 'imanicars' ); ?></span>
      </div>
    </div>
    <div class="ic-dealer-cta__action">
      <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="btn btn-gold btn-lg">
        <?php esc_html_e( 'List My Cars Free', 'imanicars' ); ?> &rarr;
      </a>
      <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="ic-dealer-cta__pricing-link">
        <?php esc_html_e( 'View Pricing Plans', 'imanicars' ); ?>
      </a>
    </div>
  </div>
</section>

<!-- =========================================================
     WHY IMANI CARS
     ========================================================= -->
<section class="ic-why section" aria-label="<?php esc_attr_e( 'Why choose Imani Cars', 'imanicars' ); ?>">
  <div class="container">
    <h2 class="ic-section-title text-center"><?php esc_html_e( 'Why Choose Imani Cars?', 'imanicars' ); ?></h2>
    <p class="ic-section-sub text-center"><?php esc_html_e( "Australia's growing marketplace for buyers and dealers who want more value.", 'imanicars' ); ?></p>
    <div class="ic-why__grid">
      <div class="ic-why__item">
        <div class="ic-why__icon why-icon" aria-hidden="true">&#128272;</div>
        <h3 class="ic-why__title"><?php esc_html_e( 'Free Listings', 'imanicars' ); ?></h3>
        <p class="ic-why__desc"><?php esc_html_e( 'Dealers list up to 10 cars free. No subscription, no credit card. Start getting leads today.', 'imanicars' ); ?></p>
      </div>
      <div class="ic-why__item">
        <div class="ic-why__icon why-icon" aria-hidden="true">&#128205;</div>
        <h3 class="ic-why__title"><?php esc_html_e( '4 Cities', 'imanicars' ); ?></h3>
        <p class="ic-why__desc"><?php esc_html_e( 'Active in Brisbane, Melbourne, Perth and Darwin — Australia\'s fastest-growing car markets.', 'imanicars' ); ?></p>
      </div>
      <div class="ic-why__item">
        <div class="ic-why__icon why-icon" aria-hidden="true">&#128176;</div>
        <h3 class="ic-why__title"><?php esc_html_e( 'Fair Pricing', 'imanicars' ); ?></h3>
        <p class="ic-why__desc"><?php esc_html_e( 'Our plans cost a fraction of carsales.com.au. Save $500+/month and reach the same buyers.', 'imanicars' ); ?></p>
      </div>
      <div class="ic-why__item">
        <div class="ic-why__icon why-icon" aria-hidden="true">&#128241;</div>
        <h3 class="ic-why__title"><?php esc_html_e( 'Mobile-First', 'imanicars' ); ?></h3>
        <p class="ic-why__desc"><?php esc_html_e( '80% of car buyers search on mobile. Our platform is built for fast, easy browsing.', 'imanicars' ); ?></p>
      </div>
    </div>
  </div>
</section>

<!-- =========================================================
     TESTIMONIALS
     ========================================================= -->
<section class="ic-testimonials section section--grey" aria-label="<?php esc_attr_e( 'Customer testimonials', 'imanicars' ); ?>">
  <div class="container">
    <h2 class="ic-section-title text-center"><?php esc_html_e( 'What Our Dealers &amp; Buyers Say', 'imanicars' ); ?></h2>
    <div class="ic-testimonials__grid">
      <?php
      $testimonials = [
          [ 'name' => 'Brad Thompson',  'role' => 'Dealer — City Cars Brisbane',      'rating' => 5, 'text' => 'Switched from carsales and saved $600/month. Leads are just as good — sometimes better because buyers are actually local.', 'seed' => '1570295999-10bde93aefc1' ],
          [ 'name' => 'Sasha Williams', 'role' => 'Private Buyer — Melbourne',         'rating' => 5, 'text' => "Found my Mazda CX-5 in 2 days. The search was simple and the dealer responded within the hour. Couldn't be easier.",       'seed' => '1494790108-d7b21cd3cd26' ],
          [ 'name' => 'Kevin Nguyen',   'role' => 'Dealer — West Coast Autos Perth',   'rating' => 5, 'text' => 'The free tier was a no-brainer to start. We had our first enquiry within 6 hours of listing. Now on the Pro plan.',         'seed' => '1507003211-2a6f260ac0e9' ],
      ];
      foreach ( $testimonials as $t ) :
      ?>
      <article class="ic-review-card">
        <div class="ic-review-card__stars" aria-label="<?php echo esc_attr( $t['rating'] ); ?> out of 5 stars">
          <?php for ( $s = 0; $s < $t['rating']; $s++ ) { echo '<span class="ic-review-card__star" aria-hidden="true">&#9733;</span>'; } ?>
        </div>
        <blockquote class="ic-review-card__text">"<?php echo esc_html( $t['text'] ); ?>"</blockquote>
        <div class="ic-review-card__author">
          <img src="<?php echo esc_url( ic_unsplash( $t['seed'], 80, 80 ) ); ?>"
               alt="<?php echo esc_attr( $t['name'] ); ?>" width="80" height="80" loading="lazy" class="ic-review-card__avatar">
          <div>
            <strong class="ic-review-card__name"><?php echo esc_html( $t['name'] ); ?></strong>
            <span class="ic-review-card__role"><?php echo esc_html( $t['role'] ); ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     SELL YOUR CAR CTA
     ========================================================= -->
<section class="ic-sell-cta section" aria-label="<?php esc_attr_e( 'Sell your car', 'imanicars' ); ?>">
  <div class="container">
    <div class="ic-sell-cta__grid">
      <div class="ic-sell-cta__img-col">
        <img src="<?php echo esc_url( ic_unsplash( '1586880244406-556ebe35f282', 560, 400 ) ); ?>"
             alt="Sell your car with Imani Cars" width="560" height="400" loading="lazy" class="ic-sell-cta__img">
      </div>
      <div class="ic-sell-cta__text-col">
        <h2 class="ic-sell-cta__title"><?php esc_html_e( 'Ready to Sell Your Car?', 'imanicars' ); ?></h2>
        <p><?php esc_html_e( 'List your car on Imani Cars and reach thousands of active buyers across 4 cities. Private sellers from $29. Dealers list free.', 'imanicars' ); ?></p>
        <ul class="ic-sell-cta__list">
          <li>&#10003; <?php esc_html_e( 'Live in under 24 hours', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'Buyer enquiries direct to your inbox', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'Dashboard with views, clicks and enquiry stats', 'imanicars' ); ?></li>
        </ul>
        <div class="ic-sell-cta__btns">
          <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="btn btn-primary btn-lg"><?php esc_html_e( 'List Free as a Dealer', 'imanicars' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/sell-your-car/' ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'Sell as Private Seller', 'imanicars' ); ?></a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
