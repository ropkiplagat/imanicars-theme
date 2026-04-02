<?php
/**
 * Template Name: Account
 * Seller account — verification, ratings, profile settings
 */
get_header();
?>

<!-- ==================== ACCOUNT HERO ==================== -->
<section class="account-hero" aria-label="<?php esc_attr_e( 'Account settings', 'imanicars' ); ?>">
  <div class="container">
    <h1><?php esc_html_e( 'My Account', 'imanicars' ); ?></h1>
    <p><?php esc_html_e( 'Manage your profile, verification, and account settings.', 'imanicars' ); ?></p>
  </div>
</section>

<!-- ==================== ACCOUNT LAYOUT ==================== -->
<section class="section" aria-label="<?php esc_attr_e( 'Account management', 'imanicars' ); ?>">
  <div class="container">
    <div class="account-grid">

      <!-- Sidebar -->
      <aside aria-label="<?php esc_attr_e( 'Account navigation', 'imanicars' ); ?>">
        <div class="account-card" style="text-align:center;">
          <div class="avatar-ring">
            <img src="https://picsum.photos/seed/ic-acct-avatar/80/80" alt="<?php esc_attr_e( 'Your profile photo', 'imanicars' ); ?>" width="80" height="80" loading="lazy">
          </div>
          <div class="verified-badge">✅ <?php esc_html_e( 'Verified', 'imanicars' ); ?></div>
          <strong style="display:block;">City Cars Brisbane</strong>
          <span style="font-size:.8rem;color:var(--grey-5);">mark@citycarsbrisbane.com.au</span>
          <div style="display:flex;justify-content:center;gap:.25rem;margin-top:.75rem;">
            <span style="color:var(--brand-gold);">★★★★★</span>
            <span style="font-size:.8rem;color:var(--grey-5);">(24 <?php esc_html_e( 'reviews', 'imanicars' ); ?>)</span>
          </div>
        </div>

        <nav class="dash-nav" aria-label="<?php esc_attr_e( 'Account sections', 'imanicars' ); ?>">
          <a href="#profile" class="active">👤 <?php esc_html_e( 'Profile', 'imanicars' ); ?></a>
          <a href="#verification">✅ <?php esc_html_e( 'Verification', 'imanicars' ); ?></a>
          <a href="#ratings">⭐ <?php esc_html_e( 'Ratings', 'imanicars' ); ?></a>
          <a href="#billing">💳 <?php esc_html_e( 'Billing', 'imanicars' ); ?></a>
          <a href="#notifications">🔔 <?php esc_html_e( 'Notifications', 'imanicars' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/seller-dashboard/' ) ); ?>">📊 <?php esc_html_e( 'Dashboard', 'imanicars' ); ?></a>
          <a href="#" style="color:var(--brand-red);">🚪 <?php esc_html_e( 'Sign Out', 'imanicars' ); ?></a>
        </nav>
      </aside>

      <!-- Main content -->
      <div>

        <!-- Profile -->
        <section id="profile" class="account-card" aria-label="<?php esc_attr_e( 'Profile settings', 'imanicars' ); ?>">
          <h3><?php esc_html_e( 'Profile Settings', 'imanicars' ); ?></h3>
          <form novalidate aria-label="<?php esc_attr_e( 'Profile form', 'imanicars' ); ?>">
            <?php wp_nonce_field( 'ic_nonce', 'ic_profile_nonce' ); ?>
            <div class="form-grid">
              <div class="form-field">
                <label for="p-dealership"><?php esc_html_e( 'Dealership Name', 'imanicars' ); ?></label>
                <input type="text" id="p-dealership" name="dealership" value="City Cars Brisbane" autocomplete="organization">
              </div>
              <div class="form-field">
                <label for="p-contact"><?php esc_html_e( 'Contact Name', 'imanicars' ); ?></label>
                <input type="text" id="p-contact" name="contact" value="Mark Thompson" autocomplete="name">
              </div>
              <div class="form-field">
                <label for="p-phone"><?php esc_html_e( 'Phone', 'imanicars' ); ?></label>
                <input type="tel" id="p-phone" name="phone" value="07 3xxx xxxx" autocomplete="tel">
              </div>
              <div class="form-field">
                <label for="p-email"><?php esc_html_e( 'Email', 'imanicars' ); ?></label>
                <input type="email" id="p-email" name="email" value="mark@citycarsbrisbane.com.au" autocomplete="email">
              </div>
              <div class="form-field">
                <label for="p-city"><?php esc_html_e( 'City', 'imanicars' ); ?></label>
                <select id="p-city" name="city">
                  <option value="brisbane" selected><?php esc_html_e( 'Brisbane, QLD', 'imanicars' ); ?></option>
                  <option value="melbourne"><?php esc_html_e( 'Melbourne, VIC', 'imanicars' ); ?></option>
                  <option value="perth"><?php esc_html_e( 'Perth, WA', 'imanicars' ); ?></option>
                  <option value="darwin"><?php esc_html_e( 'Darwin, NT', 'imanicars' ); ?></option>
                </select>
              </div>
              <div class="form-field">
                <label for="p-suburb"><?php esc_html_e( 'Suburb', 'imanicars' ); ?></label>
                <input type="text" id="p-suburb" name="suburb" value="Fortitude Valley">
              </div>
              <div class="form-field full">
                <label for="p-bio"><?php esc_html_e( 'About Your Dealership', 'imanicars' ); ?></label>
                <textarea id="p-bio" name="bio" rows="3" placeholder="<?php esc_attr_e( 'Tell buyers about your dealership…', 'imanicars' ); ?>">City Cars Brisbane has been serving Queenslanders for over 10 years. We specialise in quality used vehicles with full service histories. Come visit our yard in Fortitude Valley.</textarea>
              </div>
            </div>
            <div style="margin-top:1.25rem;">
              <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Save Profile', 'imanicars' ); ?></button>
            </div>
          </form>
        </section>

        <!-- Verification -->
        <section id="verification" class="account-card" aria-label="<?php esc_attr_e( 'Verification status', 'imanicars' ); ?>">
          <h3><?php esc_html_e( 'Verification', 'imanicars' ); ?></h3>
          <div style="display:grid;gap:.875rem;">
            <?php
            $checks = [
              [ 'label' => 'Email verified',           'done' => true ],
              [ 'label' => 'Phone number verified',     'done' => true ],
              [ 'label' => 'ABN verified',              'done' => true ],
              [ 'label' => 'Business address confirmed','done' => false ],
              [ 'label' => 'ID document uploaded',      'done' => false ],
            ];
            foreach ( $checks as $c ) :
            ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem;background:var(--grey-1);border-radius:var(--radius);border:1px solid var(--grey-2);">
              <span style="display:flex;align-items:center;gap:.625rem;font-size:.9rem;font-weight:600;">
                <span style="color:<?php echo $c['done'] ? 'var(--color-new-badge)' : 'var(--grey-4)'; ?>;font-size:1.1rem;"><?php echo $c['done'] ? '✅' : '○'; ?></span>
                <?php echo esc_html( $c['label'] ); ?>
              </span>
              <?php if ( ! $c['done'] ) : ?>
                <button type="button" class="btn btn-sm btn-outline"><?php esc_html_e( 'Verify →', 'imanicars' ); ?></button>
              <?php else : ?>
                <span style="color:var(--color-new-badge);font-size:.8rem;font-weight:700;"><?php esc_html_e( 'Verified', 'imanicars' ); ?></span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <p style="font-size:.8rem;color:var(--grey-5);margin-top:1rem;"><?php esc_html_e( 'Fully verified accounts get a "Verified Dealer" badge and appear higher in search results.', 'imanicars' ); ?></p>
        </section>

        <!-- Ratings -->
        <section id="ratings" class="account-card" aria-label="<?php esc_attr_e( 'Buyer ratings', 'imanicars' ); ?>">
          <h3><?php esc_html_e( 'Buyer Ratings', 'imanicars' ); ?></h3>
          <div style="display:flex;align-items:center;gap:1.5rem;margin-bottom:1.25rem;flex-wrap:wrap;">
            <div style="text-align:center;">
              <div style="font-size:3rem;font-weight:700;color:var(--brand-red);line-height:1;">4.9</div>
              <div style="color:var(--brand-gold);font-size:1.1rem;">★★★★★</div>
              <div style="font-size:.8rem;color:var(--grey-5);">24 <?php esc_html_e( 'reviews', 'imanicars' ); ?></div>
            </div>
            <div style="flex:1;">
              <?php
              $bars = [ 5 => 20, 4 => 3, 3 => 1, 2 => 0, 1 => 0 ];
              foreach ( $bars as $stars => $count ) :
              $pct = $count > 0 ? round( $count / 24 * 100 ) : 0;
              ?>
              <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.35rem;font-size:.8rem;">
                <span style="width:12px;text-align:right;"><?php echo esc_html( $stars ); ?></span>
                <span style="color:var(--brand-gold);font-size:.7rem;">★</span>
                <div style="flex:1;height:8px;background:var(--grey-2);border-radius:999px;">
                  <div style="width:<?php echo esc_attr( $pct ); ?>%;height:100%;background:var(--brand-gold);border-radius:999px;"></div>
                </div>
                <span style="color:var(--grey-5);width:24px;"><?php echo esc_html( $count ); ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Sample reviews -->
          <?php
          $reviews = [
            [ 'name'=>'Sarah K.', 'stars'=>5, 'seed'=>'ic-reviewer-sarah', 'time'=>'3 days ago', 'text'=>'Smooth transaction. Car exactly as described. Mark was responsive and helpful.' ],
            [ 'name'=>'Dave O.',  'stars'=>5, 'seed'=>'ic-reviewer-dave',  'time'=>'1 week ago', 'text'=>'Great experience. Fast response, no pressure, honest about the car condition. Would recommend.' ],
          ];
          foreach ( $reviews as $r ) :
          ?>
          <div style="border-top:1px solid var(--grey-2);padding-top:1rem;margin-top:1rem;">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
              <img src="https://picsum.photos/seed/<?php echo esc_attr( $r['seed'] ); ?>/36/36" alt="<?php echo esc_attr( $r['name'] ); ?>" width="36" height="36" loading="lazy" style="border-radius:50%;">
              <div>
                <strong style="font-size:.875rem;"><?php echo esc_html( $r['name'] ); ?></strong>
                <span style="color:var(--grey-5);font-size:.775rem;margin-left:.5rem;"><?php echo esc_html( $r['time'] ); ?></span>
              </div>
              <div style="color:var(--brand-gold);margin-left:auto;font-size:.875rem;"><?php echo str_repeat( '★', $r['stars'] ); ?></div>
            </div>
            <p style="font-size:.875rem;color:var(--grey-5);margin:0;"><?php echo esc_html( $r['text'] ); ?></p>
          </div>
          <?php endforeach; ?>
        </section>

        <!-- Billing -->
        <section id="billing" class="account-card" aria-label="<?php esc_attr_e( 'Billing and plan', 'imanicars' ); ?>">
          <h3><?php esc_html_e( 'Billing & Plan', 'imanicars' ); ?></h3>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem;background:var(--grey-1);border-radius:var(--radius);border:1px solid var(--grey-2);margin-bottom:1rem;flex-wrap:wrap;gap:1rem;">
            <div>
              <strong><?php esc_html_e( 'Current Plan: Starter (Free)', 'imanicars' ); ?></strong>
              <p style="color:var(--grey-5);font-size:.875rem;margin:.2rem 0 0;"><?php esc_html_e( 'Up to 10 listings. No billing required.', 'imanicars' ); ?></p>
            </div>
            <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Upgrade Plan →', 'imanicars' ); ?></a>
          </div>
          <p style="font-size:.875rem;color:var(--grey-5);"><?php esc_html_e( 'No payment method required on the free plan. Upgrade to Growth, Pro, or Premium for more listings and features.', 'imanicars' ); ?></p>
        </section>

      </div><!-- main -->
    </div><!-- .account-grid -->
  </div>
</section>

<?php get_footer(); ?>
