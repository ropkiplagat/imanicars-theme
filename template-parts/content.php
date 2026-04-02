<?php
/**
 * Template Part: Generic Post/Page Card
 * Used in index.php and search results for non-vehicle posts.
 */
defined( 'ABSPATH' ) || exit;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'ic-post-card' ); ?>>
  <?php if ( has_post_thumbnail() ) : ?>
  <div class="ic-post-card__thumb">
    <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
      <?php the_post_thumbnail( 'medium', [ 'loading' => 'lazy' ] ); ?>
    </a>
  </div>
  <?php endif; ?>
  <div class="ic-post-card__body">
    <div class="ic-post-card__meta">
      <span class="ic-post-card__date">
        <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
      </span>
      <?php if ( has_category() ) : ?>
      <span class="ic-post-card__cats"><?php the_category( ', ' ); ?></span>
      <?php endif; ?>
    </div>
    <h2 class="ic-post-card__title">
      <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h2>
    <div class="ic-post-card__excerpt">
      <?php the_excerpt(); ?>
    </div>
    <a href="<?php the_permalink(); ?>" class="ic-post-card__read-more">
      <?php esc_html_e( 'Read more', 'imanicars' ); ?> &rarr;
    </a>
  </div>
</article>
