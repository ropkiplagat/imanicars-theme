<?php
/**
 * Template Name: Seller Dashboard
 * Views, enquiries, price rank, days on market per listing
 */
get_header();

// Demo listings data with stats
$listings = [
  [ 'seed'=>'ic-dash-01','year'=>2022,'make'=>'Toyota','model'=>'Camry Ascent Sport','price'=>32990,'views_today'=>24,'views_week'=>187,'views_total'=>247,'enquiries'=>5,'saved_by'=>12,'days_on_market'=>3,'days_left'=>11,'price_rank'=>-12,'status'=>'active' ],
  [ 'seed'=>'ic-dash-02','year'=>2021,'make'=>'Mazda','model'=>'CX-5 Touring','price'=>35500,'views_today'=>11,'views_week'=>98,'views_total'=>189,'enquiries'=>3,'saved_by'=>7,'days_on_market'=>7,'days_left'=>7,'price_rank'=>-5,'status'=>'active' ],
  [ 'seed'=>'ic-dash-03','year'=>2020,'make'=>'Ford','model'=>'Ranger XLT 4x4','price'=>48750,'views_today'=>41,'views_week'=>312,'views_total'=>412,'enquiries'=>8,'saved_by'=>23,'days_on_market'=>1,'days_left'=>13,'price_rank'=>-18,'status'=>'active' ],
];
?>

<!-- ==================== DASHBOARD HERO ==================== -->
<section class="dashboard-hero" aria-label="<?php esc_attr_e( 'Seller dashboard', 'imanicars' ); ?>">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <div>
        <h1><?php esc_html_e( 'Seller Dashboard', 'imanicars' ); ?></h1>
        <p><?php esc_html_e( 'Your listings, stats, and buyer activity in one place.', 'imanicars' ); ?></p>
      </div>
      <a href="<?php echo esc_url( home_url( '/create-listing/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( '+ Add New Listing', 'imanicars' ); ?></a>
    </div>
  </div>
</section>

<!-- ==================== DASHBOARD LAYOUT ==================== -->
<section class="section" aria-label="<?php esc_attr_e( 'Dashboard content', 'imanicars' ); ?>">
  <div class="container">
    <div class="dashboard-grid">

      <!-- Sidebar nav -->
      <aside class="dashboard-sidebar" aria-label="<?php esc_attr_e( 'Dashboard navigation', 'imanicars' ); ?>">
        <div class="account-card" style="text-align:center;margin-bottom:1rem;">
          <div class="avatar-ring">
            <img src="https://picsum.photos/seed/ic-dash-avatar/80/80" alt="<?php esc_attr_e( 'Your avatar', 'imanicars' ); ?>" width="80" height="80" loading="lazy">
          </div>
          <div class="verified-badge">✅ <?php esc_html_e( 'Verified Dealer', 'imanicars' ); ?></div>
          <strong style="display:block;font-size:1rem;">City Cars Brisbane</strong>
          <span style="font-size:.8rem;color:var(--grey-5);">Fortitude Valley, QLD</span>
          <div style="margin-top:.875rem;padding-top:.875rem;border-top:1px solid var(--grey-2);">
            <span style="display:inline-block;background:var(--grey-1);border-radius:999px;padding:.25rem .75rem;font-size:.75rem;font-weight:700;color:var(--tier-free);"><?php esc_html_e( 'Starter Plan', 'imanicars' ); ?></span>
            <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" style="display:block;margin-top:.5rem;font-size:.8rem;color:var(--brand-red);font-weight:600;"><?php esc_html_e( 'Upgrade for more →', 'imanicars' ); ?></a>
          </div>
        </div>

        <nav class="dash-nav" aria-label="<?php esc_attr_e( 'Dashboard sections', 'imanicars' ); ?>">
          <a href="#overview" class="active">📊 <?php esc_html_e( 'Overview', 'imanicars' ); ?></a>
          <a href="#listings">🚗 <?php esc_html_e( 'My Listings', 'imanicars' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/create-listing/' ) ); ?>">➕ <?php esc_html_e( 'Add Listing', 'imanicars' ); ?></a>
          <a href="#enquiries">💬 <?php esc_html_e( 'Enquiries', 'imanicars' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">⬆️ <?php esc_html_e( 'Upgrade Plan', 'imanicars' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/account/' ) ); ?>">👤 <?php esc_html_e( 'Account Settings', 'imanicars' ); ?></a>
        </nav>
      </aside>

      <!-- Main content -->
      <div>

        <!-- OVERVIEW STATS -->
        <section id="overview" aria-label="<?php esc_attr_e( 'Overview statistics', 'imanicars' ); ?>">
          <h2 style="font-size:1.15rem;margin-bottom:1rem;"><?php esc_html_e( 'Overview — Last 7 Days', 'imanicars' ); ?></h2>
          <div class="stats-cards-grid">
            <div class="stat-card"><div class="stat-card__value">597</div><div class="stat-card__label"><?php esc_html_e( 'Total Views', 'imanicars' ); ?></div><div class="stat-card__trend">↑ 12% vs last week</div></div>
            <div class="stat-card"><div class="stat-card__value">76</div><div class="stat-card__label"><?php esc_html_e( 'Views Today', 'imanicars' ); ?></div><div class="stat-card__trend">↑ 8 vs yesterday</div></div>
            <div class="stat-card"><div class="stat-card__value">16</div><div class="stat-card__label"><?php esc_html_e( 'Enquiries', 'imanicars' ); ?></div><div class="stat-card__trend">↑ 3 vs last week</div></div>
            <div class="stat-card"><div class="stat-card__value">42</div><div class="stat-card__label"><?php esc_html_e( 'Saved by Buyers', 'imanicars' ); ?></div><div class="stat-card__trend">↑ 5 new this week</div></div>
            <div class="stat-card"><div class="stat-card__value">3</div><div class="stat-card__label"><?php esc_html_e( 'Active Listings', 'imanicars' ); ?></div><div class="stat-card__trend"><?php esc_html_e( '7 listings remaining', 'imanicars' ); ?></div></div>
          </div>
        </section>

        <!-- MY LISTINGS -->
        <section id="listings" style="margin-top:2.5rem;" aria-label="<?php esc_attr_e( 'My listings', 'imanicars' ); ?>">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem;">
            <h2 style="font-size:1.15rem;margin:0;"><?php esc_html_e( 'My Listings', 'imanicars' ); ?></h2>
            <a href="<?php echo esc_url( home_url( '/create-listing/' ) ); ?>" class="btn btn-primary btn-sm"><?php esc_html_e( '+ Add Listing', 'imanicars' ); ?></a>
          </div>

          <?php foreach ( $listings as $l ) : ?>
          <div style="border:1px solid var(--grey-2);border-radius:var(--radius);padding:1.25rem;margin-bottom:1rem;background:var(--white);display:grid;grid-template-columns:100px 1fr;gap:1rem;" aria-label="<?php echo esc_attr( $l['year'] . ' ' . $l['make'] . ' ' . $l['model'] ); ?>">
            <img src="https://picsum.photos/seed/<?php echo esc_attr( $l['seed'] ); ?>/100/67" alt="<?php echo esc_attr( $l['year'] . ' ' . $l['make'] . ' ' . $l['model'] ); ?>" width="100" height="67" loading="lazy" style="border-radius:var(--radius-sm);object-fit:cover;width:100%;height:auto;">
            <div>
              <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                <div>
                  <strong style="display:block;font-size:.95rem;"><?php echo esc_html( $l['year'] . ' ' . $l['make'] . ' ' . $l['model'] ); ?></strong>
                  <span style="font-size:1.1rem;font-weight:700;color:var(--brand-red);">$<?php echo esc_html( number_format( $l['price'], 0 ) ); ?></span>
                </div>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                  <span style="background:<?php echo $l['status'] === 'active' ? 'var(--badge-great-bg)' : 'var(--grey-2)'; ?>;color:<?php echo $l['status'] === 'active' ? 'var(--badge-great-fg)' : 'var(--grey-5)'; ?>;padding:.2rem .6rem;border-radius:999px;font-size:.75rem;font-weight:700;">
                    <?php echo esc_html( ucfirst( $l['status'] ) ); ?>
                  </span>
                  <span style="background:var(--badge-fair-bg);color:var(--badge-fair-fg);padding:.2rem .6rem;border-radius:999px;font-size:.75rem;font-weight:700;">
                    🕐 <?php echo esc_html( $l['days_left'] ); ?> <?php esc_html_e( 'days left', 'imanicars' ); ?>
                  </span>
                </div>
              </div>

              <!-- Stats row -->
              <div style="display:flex;flex-wrap:wrap;gap:.875rem;margin-top:.75rem;font-size:.8rem;color:var(--grey-5);">
                <span>👁 <strong><?php echo esc_html( $l['views_today'] ); ?></strong> <?php esc_html_e( 'today', 'imanicars' ); ?></span>
                <span>📅 <strong><?php echo esc_html( $l['views_week'] ); ?></strong> <?php esc_html_e( 'this week', 'imanicars' ); ?></span>
                <span>👁 <strong><?php echo esc_html( $l['views_total'] ); ?></strong> <?php esc_html_e( 'total', 'imanicars' ); ?></span>
                <span>💬 <strong><?php echo esc_html( $l['enquiries'] ); ?></strong> <?php esc_html_e( 'enquiries', 'imanicars' ); ?></span>
                <span>❤️ <strong><?php echo esc_html( $l['saved_by'] ); ?></strong> <?php esc_html_e( 'saved', 'imanicars' ); ?></span>
                <span>📅 <?php printf( esc_html__( 'Day %d of 14', 'imanicars' ), $l['days_on_market'] ); ?></span>
              </div>

              <!-- Price rank -->
              <div class="price-rank-bar" style="margin-top:.875rem;padding:1rem;background:var(--grey-1);">
                <p style="font-size:.8rem;font-weight:600;margin-bottom:.5rem;">
                  📊 <?php printf( esc_html__( 'Your price is %d%% below average for similar %s %s in Brisbane', 'imanicars' ), abs( $l['price_rank'] ), esc_html( $l['make'] ), esc_html( $l['model'] ) ); ?>
                  <span style="color:var(--color-new-badge);font-weight:700;"><?php esc_html_e( ' — competitive!', 'imanicars' ); ?></span>
                </p>
                <div class="rank-track" aria-label="<?php esc_attr_e( 'Price rank bar', 'imanicars' ); ?>">
                  <div class="rank-fill" style="width:<?php echo esc_attr( 50 + $l['price_rank'] ); ?>%;"></div>
                </div>
                <div class="rank-label">
                  <span><?php esc_html_e( 'Below market', 'imanicars' ); ?></span>
                  <span><?php esc_html_e( 'At market', 'imanicars' ); ?></span>
                  <span><?php esc_html_e( 'Above market', 'imanicars' ); ?></span>
                </div>
              </div>

              <!-- Actions -->
              <div style="display:flex;gap:.5rem;margin-top:.875rem;flex-wrap:wrap;">
                <a href="#" class="btn btn-sm btn-outline"><?php esc_html_e( 'Edit', 'imanicars' ); ?></a>
                <a href="#" class="btn btn-sm btn-dark"><?php esc_html_e( 'Relist', 'imanicars' ); ?></a>
                <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="btn btn-sm btn-gold"><?php esc_html_e( 'Boost Listing →', 'imanicars' ); ?></a>
                <a href="#" class="btn btn-sm" style="color:var(--grey-5);border:1px solid var(--grey-3);"><?php esc_html_e( 'Mark Sold', 'imanicars' ); ?></a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>

          <!-- Upgrade prompt (free tier) -->
          <div style="background:linear-gradient(135deg,var(--brand-dark),var(--brand-charcoal));border-radius:var(--radius);padding:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-top:.5rem;">
            <div>
              <strong style="color:var(--white);display:block;"><?php esc_html_e( 'Free plan: 7 listing slots remaining', 'imanicars' ); ?></strong>
              <span style="color:var(--grey-3);font-size:.875rem;"><?php esc_html_e( 'Upgrade to Growth for 50 listings + dealer logo + priority search.', 'imanicars' ); ?></span>
            </div>
            <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="btn btn-gold"><?php esc_html_e( 'Upgrade Now →', 'imanicars' ); ?></a>
          </div>
        </section>

        <!-- ENQUIRIES SECTION -->
        <section id="enquiries" style="margin-top:2.5rem;" aria-label="<?php esc_attr_e( 'Recent enquiries', 'imanicars' ); ?>">
          <h2 style="font-size:1.15rem;margin-bottom:1rem;"><?php esc_html_e( 'Recent Enquiries', 'imanicars' ); ?></h2>
          <?php
          $enquiries = [
            [ 'name'=>'Sarah K.',  'time'=>'2 hours ago',  'listing'=>'2022 Toyota Camry',      'phone'=>'0412 xxx xxx', 'msg'=>"Hi, I'm interested in the Camry. Is it still available?" ],
            [ 'name'=>'Dave O.',   'time'=>'5 hours ago',  'listing'=>'2020 Ford Ranger',       'phone'=>'0423 xxx xxx', 'msg'=>"Can I arrange an inspection this weekend?" ],
            [ 'name'=>'Mark T.',   'time'=>'1 day ago',    'listing'=>'2021 Mazda CX-5',        'phone'=>'0401 xxx xxx', 'msg'=>"What's the drive-away price? Any finance available?" ],
          ];
          foreach ( $enquiries as $enq ) :
          ?>
          <div style="border:1px solid var(--grey-2);border-radius:var(--radius);padding:1rem 1.25rem;margin-bottom:.75rem;background:var(--white);" aria-label="<?php printf( esc_attr__( 'Enquiry from %s', 'imanicars' ), $enq['name'] ); ?>">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.5rem;margin-bottom:.5rem;">
              <div>
                <strong><?php echo esc_html( $enq['name'] ); ?></strong>
                <span style="color:var(--grey-5);font-size:.8rem;margin-left:.5rem;"><?php echo esc_html( $enq['time'] ); ?></span>
              </div>
              <span style="font-size:.8rem;color:var(--grey-5);"><?php esc_html_e( 'Re:', 'imanicars' ); ?> <em><?php echo esc_html( $enq['listing'] ); ?></em></span>
            </div>
            <p style="color:var(--grey-5);font-size:.875rem;margin:0 0 .75rem;">"<?php echo esc_html( $enq['msg'] ); ?>"</p>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
              <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $enq['phone'] ) ); ?>" class="btn btn-sm btn-primary">📞 <?php esc_html_e( 'Call', 'imanicars' ); ?></a>
              <button type="button" class="btn btn-sm btn-outline"><?php esc_html_e( 'Reply via Email', 'imanicars' ); ?></button>
            </div>
          </div>
          <?php endforeach; ?>
        </section>

      </div><!-- main content -->
    </div><!-- .dashboard-grid -->
  </div>
</section>

<?php get_footer(); ?>
