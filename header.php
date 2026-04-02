<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="ic-skip-link" href="#ic-main"><?php esc_html_e( 'Skip to main content', 'imanicars' ); ?></a>

<!-- =========================================================
     HEADER — 3 rows: utility bar + main header + category nav
     ========================================================= -->
<header class="ic-site-header" id="ic-site-header" role="banner">

  <!-- ROW 1 — UTILITY BAR (32px) -->
  <div class="ic-topbar">
    <div class="ic-topbar__inner container">
      <div class="ic-topbar__left">
        <span class="ic-topbar__item">Australia's Fastest Growing Car Marketplace</span>
        <span class="ic-topbar__sep">|</span>
        <span class="ic-topbar__item">4 Cities: Brisbane | Melbourne | Perth | Darwin</span>
      </div>
      <div class="ic-topbar__right">
        <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="ic-topbar__link">List Free</a>
        <span class="ic-topbar__sep">|</span>
        <a href="tel:1800426426" class="ic-topbar__link">1800 IMANI</a>
        <span class="ic-topbar__sep">|</span>
        <a href="<?php echo esc_url( wp_login_url() ); ?>" class="ic-topbar__link">Sign In</a>
      </div>
    </div>
  </div><!-- /.ic-topbar -->

  <!-- ROW 2 — MAIN HEADER (60px) -->
  <div class="ic-header">
    <div class="ic-header__inner container">

      <!-- LOGO -->
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ic-logo" aria-label="<?php esc_attr_e( 'Imani Cars — Home', 'imanicars' ); ?>">
        <img src="<?php echo esc_url( IC_THEME_URI . '/assets/images/logo.png' ); ?>"
             alt="Imani Cars" width="160" height="48" loading="eager">
      </a>

      <!-- SEARCH BAR (header inline) -->
      <form class="ic-header-search" role="search" action="<?php echo esc_url( home_url( '/cars/' ) ); ?>" method="get">
        <select name="make" class="ic-header-search__select" aria-label="<?php esc_attr_e( 'Make', 'imanicars' ); ?>">
          <option value=""><?php esc_html_e( 'All Makes', 'imanicars' ); ?></option>
          <?php
          $makes = [ 'Toyota', 'Mazda', 'Ford', 'Holden', 'Honda', 'Nissan', 'Mitsubishi', 'Hyundai', 'Kia', 'Subaru', 'BMW', 'Mercedes-Benz', 'Audi', 'Volkswagen', 'Jeep', 'Land Rover', 'Tesla', 'Isuzu', 'LDV', 'MG' ];
          foreach ( $makes as $make ) {
              echo '<option value="' . esc_attr( $make ) . '">' . esc_html( $make ) . '</option>';
          }
          ?>
        </select>
        <select name="body_type" class="ic-header-search__select" aria-label="<?php esc_attr_e( 'Body type', 'imanicars' ); ?>">
          <option value=""><?php esc_html_e( 'All Types', 'imanicars' ); ?></option>
          <?php
          $types = [ 'SUV', 'Sedan', 'Hatch', 'Ute', 'Wagon', 'Coupe', 'Convertible', 'Van', 'People Mover' ];
          foreach ( $types as $t ) {
              echo '<option value="' . esc_attr( $t ) . '">' . esc_html( $t ) . '</option>';
          }
          ?>
        </select>
        <input type="text" name="location" class="ic-header-search__location" placeholder="<?php esc_attr_e( 'Suburb or postcode', 'imanicars' ); ?>" aria-label="<?php esc_attr_e( 'Location', 'imanicars' ); ?>">
        <button type="submit" class="ic-header-search__btn" aria-label="<?php esc_attr_e( 'Search cars', 'imanicars' ); ?>">
          <span class="ic-icon-search" aria-hidden="true">&#128269;</span>
          <span><?php esc_html_e( 'Search', 'imanicars' ); ?></span>
        </button>
      </form>

      <!-- NAV ACTIONS -->
      <div class="ic-header__actions">
        <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="btn btn-primary btn-sm ic-header__sell-btn">
          <?php esc_html_e( 'List Your Car Free', 'imanicars' ); ?>
        </a>
        <button class="ic-hamburger" id="ic-hamburger" aria-label="<?php esc_attr_e( 'Open menu', 'imanicars' ); ?>" aria-expanded="false" aria-controls="ic-nav">
          <span class="ic-hamburger__bar"></span>
          <span class="ic-hamburger__bar"></span>
          <span class="ic-hamburger__bar"></span>
        </button>
      </div>

    </div>
  </div><!-- /.ic-header -->

  <!-- ROW 3 — CATEGORY NAV (44px) -->
  <nav class="ic-category-nav" id="ic-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'imanicars' ); ?>">
    <div class="ic-category-nav__inner container">
      <ul class="ic-nav__list" role="list">
        <li class="ic-nav__item ic-nav__item--dropdown">
          <button class="ic-nav__link ic-nav__link--dropdown" aria-expanded="false" aria-haspopup="true">
            <?php esc_html_e( 'Buy', 'imanicars' ); ?>
            <span class="ic-nav__caret" aria-hidden="true">&#9660;</span>
          </button>
          <div class="ic-nav__dropdown">
            <div class="ic-nav__dropdown-inner">
              <a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'All Cars for Sale', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/cars/?condition=used' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Used Cars', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/cars/?condition=new' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'New Cars', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/cars/?seller=dealer' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Dealer Cars', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/cars/?seller=private' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Private Seller Cars', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/cars/?fuel_type=Electric' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Electric Cars', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/finance/' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Car Finance', 'imanicars' ); ?></a>
            </div>
          </div>
        </li>
        <li class="ic-nav__item ic-nav__item--dropdown">
          <button class="ic-nav__link ic-nav__link--dropdown" aria-expanded="false" aria-haspopup="true">
            <?php esc_html_e( 'Sell', 'imanicars' ); ?>
            <span class="ic-nav__caret" aria-hidden="true">&#9660;</span>
          </button>
          <div class="ic-nav__dropdown">
            <div class="ic-nav__dropdown-inner">
              <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'List Your Car (Free)', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Dealer Pricing', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/sell-your-car/' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Private Seller Guide', 'imanicars' ); ?></a>
            </div>
          </div>
        </li>
        <li class="ic-nav__item">
          <a href="<?php echo esc_url( home_url( '/dealers/' ) ); ?>" class="ic-nav__link <?php echo is_page( 'dealers' ) ? 'ic-nav__link--active' : ''; ?>">
            <?php esc_html_e( 'Dealers', 'imanicars' ); ?>
          </a>
        </li>
        <li class="ic-nav__item ic-nav__item--dropdown">
          <button class="ic-nav__link ic-nav__link--dropdown" aria-expanded="false" aria-haspopup="true">
            <?php esc_html_e( 'Browse by City', 'imanicars' ); ?>
            <span class="ic-nav__caret" aria-hidden="true">&#9660;</span>
          </button>
          <div class="ic-nav__dropdown">
            <div class="ic-nav__dropdown-inner">
              <a href="<?php echo esc_url( home_url( '/brisbane/' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Brisbane', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/melbourne/' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Melbourne', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/perth/' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Perth', 'imanicars' ); ?></a>
              <a href="<?php echo esc_url( home_url( '/darwin/' ) ); ?>" class="ic-nav__dropdown-link"><?php esc_html_e( 'Darwin', 'imanicars' ); ?></a>
            </div>
          </div>
        </li>
        <li class="ic-nav__item">
          <a href="<?php echo esc_url( home_url( '/finance/' ) ); ?>" class="ic-nav__link <?php echo is_page( 'finance' ) ? 'ic-nav__link--active' : ''; ?>">
            <?php esc_html_e( 'Finance', 'imanicars' ); ?>
          </a>
        </li>
        <li class="ic-nav__item">
          <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="ic-nav__link <?php echo is_page( 'pricing' ) ? 'ic-nav__link--active' : ''; ?>">
            <?php esc_html_e( 'Pricing', 'imanicars' ); ?>
          </a>
        </li>
      </ul>
    </div>
  </nav><!-- /.ic-category-nav -->

</header><!-- /.ic-site-header -->

<!-- HEADER SPACER — accounts for sticky header height (32+60+44=136px) -->
<div class="ic-header-spacer" aria-hidden="true"></div>

<main id="ic-main" class="ic-main" role="main">
