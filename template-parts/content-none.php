<?php
/**
 * Template Part: No Content / No Results
 * Used when no posts are found.
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="ic-no-results">
  <div class="ic-no-results__inner">
    <span class="ic-no-results__icon" aria-hidden="true">&#128269;</span>
    <h2 class="ic-no-results__title">
      <?php
      if ( is_search() ) {
          esc_html_e( 'No results found for your search.', 'imanicars' );
      } else {
          esc_html_e( 'No listings found.', 'imanicars' );
      }
      ?>
    </h2>
    <p class="ic-no-results__desc">
      <?php
      if ( is_search() ) {
          esc_html_e( 'Try different keywords, or browse all cars below.', 'imanicars' );
      } else {
          esc_html_e( 'Try adjusting your filters, or browse all available listings.', 'imanicars' );
      }
      ?>
    </p>
    <div class="ic-no-results__actions">
      <a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Browse All Cars', 'imanicars' ); ?></a>
      <?php if ( is_search() ) : ?>
      <form class="ic-no-results__search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <input type="search" name="s" class="ic-form-input" placeholder="<?php esc_attr_e( 'Try a different search...', 'imanicars' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'imanicars' ); ?>">
        <button type="submit" class="btn btn-outline"><?php esc_html_e( 'Search', 'imanicars' ); ?></button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
