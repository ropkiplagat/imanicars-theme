<?php
/**
 * Template Name: Create Listing
 * 3-step listing flow: Photos → Details → Publish
 */
get_header();
?>

<section class="page-hero" style="background:var(--brand-dark);" aria-label="<?php esc_attr_e( 'Create a listing', 'imanicars' ); ?>">
  <div class="container">
    <h1 style="color:var(--white);"><?php esc_html_e( 'List Your Car', 'imanicars' ); ?></h1>
    <p style="color:var(--grey-3);"><?php esc_html_e( 'Free for up to 10 listings. Complete all 3 steps to publish.', 'imanicars' ); ?></p>
  </div>
</section>

<section class="section" aria-label="<?php esc_attr_e( 'Listing creation form', 'imanicars' ); ?>">
  <div class="container" style="max-width:800px;">

    <!-- Step progress -->
    <div class="listing-steps" role="list" aria-label="<?php esc_attr_e( 'Listing steps', 'imanicars' ); ?>">
      <div class="listing-step active" role="listitem" id="step-1-indicator">
        <div class="step-num">1</div>
        <div class="step-info"><strong><?php esc_html_e( 'Photos', 'imanicars' ); ?></strong><span><?php esc_html_e( 'Upload images', 'imanicars' ); ?></span></div>
      </div>
      <div class="listing-step" role="listitem" id="step-2-indicator">
        <div class="step-num">2</div>
        <div class="step-info"><strong><?php esc_html_e( 'Details', 'imanicars' ); ?></strong><span><?php esc_html_e( 'Vehicle info', 'imanicars' ); ?></span></div>
      </div>
      <div class="listing-step" role="listitem" id="step-3-indicator">
        <div class="step-num">3</div>
        <div class="step-info"><strong><?php esc_html_e( 'Publish', 'imanicars' ); ?></strong><span><?php esc_html_e( 'Review & go live', 'imanicars' ); ?></span></div>
      </div>
    </div>

    <!-- STEP 1: Photos -->
    <div id="step-1" class="listing-step-content" aria-label="<?php esc_attr_e( 'Step 1: Upload photos', 'imanicars' ); ?>">
      <div class="account-card">
        <h2 style="font-size:1.1rem;margin-bottom:.25rem;"><?php esc_html_e( 'Upload Photos', 'imanicars' ); ?></h2>
        <p style="color:var(--grey-5);font-size:.875rem;margin-bottom:1.25rem;"><?php esc_html_e( 'Add up to 10 photos. First photo is the main listing image. JPG, PNG — max 5MB each.', 'imanicars' ); ?></p>

        <div class="photo-upload-zone" id="photo-zone" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Click or drag to upload photos', 'imanicars' ); ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 16l4.586-4.586a2 2 0 0 1 2.828 0L16 16m-2-2l1.586-1.586a2 2 0 0 1 2.828 0L20 14m-6-6h.01M6 20h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/></svg>
          <strong><?php esc_html_e( 'Click to upload or drag and drop', 'imanicars' ); ?></strong>
          <p><?php esc_html_e( 'Up to 10 photos · JPG or PNG · Max 5MB each', 'imanicars' ); ?></p>
          <input type="file" id="photo-input" name="photos[]" multiple accept="image/jpeg,image/png" style="display:none;" aria-label="<?php esc_attr_e( 'Upload photos', 'imanicars' ); ?>">
        </div>
        <div class="photo-previews" id="photo-previews" aria-label="<?php esc_attr_e( 'Photo previews', 'imanicars' ); ?>"></div>
        <div style="display:flex;justify-content:flex-end;margin-top:1.5rem;">
          <button type="button" class="btn btn-primary" id="step-1-next"><?php esc_html_e( 'Next: Vehicle Details →', 'imanicars' ); ?></button>
        </div>
      </div>
    </div>

    <!-- STEP 2: Details -->
    <div id="step-2" class="listing-step-content" style="display:none;" aria-label="<?php esc_attr_e( 'Step 2: Vehicle details', 'imanicars' ); ?>">
      <form id="listing-details-form" novalidate aria-label="<?php esc_attr_e( 'Vehicle details form', 'imanicars' ); ?>">
        <?php wp_nonce_field( 'ic_nonce', 'ic_listing_nonce' ); ?>
        <div class="account-card">
          <h2 style="font-size:1.1rem;margin-bottom:1.25rem;"><?php esc_html_e( 'Vehicle Details', 'imanicars' ); ?></h2>
          <div class="form-grid">
            <div class="form-field">
              <label for="ld-year"><?php esc_html_e( 'Year *', 'imanicars' ); ?></label>
              <select id="ld-year" name="year" required aria-required="true">
                <option value=""><?php esc_html_e( 'Select year', 'imanicars' ); ?></option>
                <?php for ( $y = 2025; $y >= 1990; $y-- ) echo '<option value="' . $y . '">' . $y . '</option>'; ?>
              </select>
            </div>
            <div class="form-field">
              <label for="ld-make"><?php esc_html_e( 'Make *', 'imanicars' ); ?></label>
              <select id="ld-make" name="make" required aria-required="true">
                <option value=""><?php esc_html_e( 'Select make', 'imanicars' ); ?></option>
                <?php foreach ( [ 'Toyota','Ford','Mazda','Holden','Hyundai','Kia','Mitsubishi','Nissan','Volkswagen','BMW','Mercedes-Benz','BYD','Chery','GWM','Isuzu','Subaru','Honda','Lexus','Other' ] as $m ) echo '<option>' . esc_html( $m ) . '</option>'; ?>
              </select>
            </div>
            <div class="form-field">
              <label for="ld-model"><?php esc_html_e( 'Model *', 'imanicars' ); ?></label>
              <input type="text" id="ld-model" name="model" placeholder="e.g. Camry, Ranger, CX-5" required aria-required="true">
            </div>
            <div class="form-field">
              <label for="ld-variant"><?php esc_html_e( 'Variant / Grade', 'imanicars' ); ?></label>
              <input type="text" id="ld-variant" name="variant" placeholder="e.g. Ascent Sport, SR5, Touring">
            </div>
            <div class="form-field">
              <label for="ld-km"><?php esc_html_e( 'Odometer (km) *', 'imanicars' ); ?></label>
              <input type="number" id="ld-km" name="km" placeholder="e.g. 45000" min="0" required aria-required="true">
            </div>
            <div class="form-field">
              <label for="ld-price"><?php esc_html_e( 'Asking Price ($) *', 'imanicars' ); ?></label>
              <input type="number" id="ld-price" name="price" placeholder="e.g. 32990" min="0" required aria-required="true">
            </div>
            <div class="form-field">
              <label for="ld-fuel"><?php esc_html_e( 'Fuel Type *', 'imanicars' ); ?></label>
              <select id="ld-fuel" name="fuel" required aria-required="true">
                <option value=""><?php esc_html_e( 'Select fuel type', 'imanicars' ); ?></option>
                <?php foreach ( [ 'Petrol','Diesel','Hybrid','PHEV','Electric','LPG' ] as $f ) echo '<option>' . esc_html( $f ) . '</option>'; ?>
              </select>
            </div>
            <div class="form-field">
              <label for="ld-trans"><?php esc_html_e( 'Transmission *', 'imanicars' ); ?></label>
              <select id="ld-trans" name="trans" required aria-required="true">
                <option value=""><?php esc_html_e( 'Select transmission', 'imanicars' ); ?></option>
                <option><?php esc_html_e( 'Automatic', 'imanicars' ); ?></option>
                <option><?php esc_html_e( 'Manual', 'imanicars' ); ?></option>
                <option><?php esc_html_e( 'CVT', 'imanicars' ); ?></option>
                <option><?php esc_html_e( 'Semi-automatic', 'imanicars' ); ?></option>
              </select>
            </div>
            <div class="form-field">
              <label for="ld-body"><?php esc_html_e( 'Body Type *', 'imanicars' ); ?></label>
              <select id="ld-body" name="body_type" required aria-required="true">
                <option value=""><?php esc_html_e( 'Select body type', 'imanicars' ); ?></option>
                <?php foreach ( [ 'SUV','Ute','Sedan','Hatchback','Wagon','4WD / Off-road','Coupe','Convertible','Van / People Mover' ] as $b ) echo '<option>' . esc_html( $b ) . '</option>'; ?>
              </select>
            </div>
            <div class="form-field">
              <label for="ld-colour"><?php esc_html_e( 'Colour', 'imanicars' ); ?></label>
              <input type="text" id="ld-colour" name="colour" placeholder="e.g. Midnight Black, Pearl White">
            </div>
            <div class="form-field">
              <label for="ld-city"><?php esc_html_e( 'City *', 'imanicars' ); ?></label>
              <select id="ld-city" name="city" required aria-required="true">
                <option value=""><?php esc_html_e( 'Select city', 'imanicars' ); ?></option>
                <option value="brisbane"><?php esc_html_e( 'Brisbane, QLD', 'imanicars' ); ?></option>
                <option value="melbourne"><?php esc_html_e( 'Melbourne, VIC', 'imanicars' ); ?></option>
                <option value="perth"><?php esc_html_e( 'Perth, WA', 'imanicars' ); ?></option>
                <option value="darwin"><?php esc_html_e( 'Darwin, NT', 'imanicars' ); ?></option>
              </select>
            </div>
            <div class="form-field">
              <label for="ld-vin"><?php esc_html_e( 'VIN (optional)', 'imanicars' ); ?></label>
              <input type="text" id="ld-vin" name="vin" placeholder="17-character VIN" maxlength="17" autocomplete="off">
            </div>
            <div class="form-field full">
              <label for="ld-desc"><?php esc_html_e( 'Description', 'imanicars' ); ?></label>
              <textarea id="ld-desc" name="description" rows="5" placeholder="<?php esc_attr_e( 'Describe the vehicle condition, service history, features, reason for selling…', 'imanicars' ); ?>"></textarea>
            </div>
          </div>
          <div style="display:flex;gap:.75rem;justify-content:space-between;margin-top:1.5rem;">
            <button type="button" class="btn btn-outline" id="step-2-back">← <?php esc_html_e( 'Back', 'imanicars' ); ?></button>
            <button type="button" class="btn btn-primary" id="step-2-next"><?php esc_html_e( 'Next: Review & Publish →', 'imanicars' ); ?></button>
          </div>
        </div>
      </form>
    </div>

    <!-- STEP 3: Review & Publish -->
    <div id="step-3" class="listing-step-content" style="display:none;" aria-label="<?php esc_attr_e( 'Step 3: Review and publish', 'imanicars' ); ?>">
      <div class="account-card">
        <h2 style="font-size:1.1rem;margin-bottom:.25rem;"><?php esc_html_e( 'Review & Publish', 'imanicars' ); ?></h2>
        <p style="color:var(--grey-5);font-size:.875rem;margin-bottom:1.5rem;"><?php esc_html_e( 'Your listing will go live within 24 hours after our quick review.', 'imanicars' ); ?></p>

        <div id="listing-preview" style="border:1px solid var(--grey-2);border-radius:var(--radius);padding:1.25rem;margin-bottom:1.5rem;background:var(--grey-1);">
          <p style="color:var(--grey-5);text-align:center;"><?php esc_html_e( 'Listing preview will appear here.', 'imanicars' ); ?></p>
        </div>

        <div style="background:var(--badge-great-bg);border:1px solid #c3e6cb;border-radius:var(--radius);padding:1rem;margin-bottom:1.5rem;">
          <strong style="color:var(--badge-great-fg);">✅ <?php esc_html_e( '14-Day Freshness Rule', 'imanicars' ); ?></strong>
          <p style="color:var(--badge-great-fg);font-size:.875rem;margin:.25rem 0 0;"><?php esc_html_e( 'Your listing will be live for 14 days. Relist for free anytime to keep it fresh for buyers.', 'imanicars' ); ?></p>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:space-between;">
          <button type="button" class="btn btn-outline" id="step-3-back">← <?php esc_html_e( 'Back', 'imanicars' ); ?></button>
          <button type="button" class="btn btn-primary btn-lg" id="publish-btn">🚀 <?php esc_html_e( 'Publish Listing', 'imanicars' ); ?></button>
        </div>
      </div>
    </div>

  </div>
</section>

<script>
(function() {
  'use strict';

  // Step navigation
  function showStep(n) {
    [1,2,3].forEach(function(i) {
      var el = document.getElementById('step-' + i);
      var ind = document.getElementById('step-' + i + '-indicator');
      if (el) el.style.display = i === n ? 'block' : 'none';
      if (ind) {
        ind.classList.toggle('active', i === n);
        ind.classList.toggle('done', i < n);
      }
    });
  }

  var s1n = document.getElementById('step-1-next');
  var s2b = document.getElementById('step-2-back');
  var s2n = document.getElementById('step-2-next');
  var s3b = document.getElementById('step-3-back');
  var pub = document.getElementById('publish-btn');

  if (s1n) s1n.addEventListener('click', function() { showStep(2); });
  if (s2b) s2b.addEventListener('click', function() { showStep(1); });
  if (s2n) s2n.addEventListener('click', function() { showStep(3); });
  if (s3b) s3b.addEventListener('click', function() { showStep(2); });
  if (pub) pub.addEventListener('click', function() {
    this.textContent = 'Submitting…';
    this.disabled = true;
    setTimeout(function() {
      window.location.href = (typeof icConfig !== 'undefined' ? '' : '') + '/seller-dashboard/?published=1';
    }, 1200);
  });

  // Photo upload zone
  var zone  = document.getElementById('photo-zone');
  var input = document.getElementById('photo-input');
  var prev  = document.getElementById('photo-previews');

  if (zone && input) {
    zone.addEventListener('click', function() { input.click(); });
    zone.addEventListener('keydown', function(e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
    input.addEventListener('change', function() {
      if (!prev) return;
      prev.innerHTML = '';
      Array.from(this.files).slice(0,10).forEach(function(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
          var div = document.createElement('div');
          div.className = 'photo-preview';
          div.innerHTML = '<img src="' + e.target.result + '" alt="Preview"><button class="remove-btn" type="button" aria-label="Remove photo">✕</button>';
          prev.appendChild(div);
          div.querySelector('.remove-btn').addEventListener('click', function() { div.remove(); });
        };
        reader.readAsDataURL(file);
      });
    });
  }
})();
</script>

<?php get_footer(); ?>
