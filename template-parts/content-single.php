<?php
/**
 * Template Part: Single Post Content
 * Used for blog/news post detail views.
 */
defined( 'ABSPATH' ) || exit;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'ic-single-post' ); ?>>
  <header class="ic-single-post__header">
    <div class="ic-single-post__meta">
      <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" class="ic-single-post__date">
        <?php echo esc_html( get_the_date() ); ?>
      </time>
      <?php if ( has_category() ) : ?>
      <span class="ic-single-post__cats"><?php the_category( ', ' ); ?></span>
      <?php endif; ?>
      <span class="ic-single-post__author">
        <?php
        /* translators: %s: author display name */
        printf( esc_html__( 'By %s', 'imanicars' ), '<span class="ic-single-post__author-name">' . esc_html( get_the_author() ) . '</span>' );
        ?>
      </span>
    </div>
    <h1 class="ic-single-post__title"><?php the_title(); ?></h1>
    <?php if ( has_post_thumbnail() ) : ?>
    <div class="ic-single-post__hero-img">
      <?php the_post_thumbnail( 'large', [ 'loading' => 'eager' ] ); ?>
    </div>
    <?php endif; ?>
  </header>
  <div class="ic-single-post__body ic-rich-text">
    <?php the_content(); ?>
    <?php
    wp_link_pages( [
        'before' => '<nav class="page-links">',
        'after'  => '</nav>',
    ] );
    ?>
  </div>
  <footer class="ic-single-post__footer">
    <?php if ( has_tag() ) : ?>
    <div class="ic-single-post__tags">
      <?php the_tags( '<span class="ic-single-post__tags-label">' . esc_html__( 'Tags:', 'imanicars' ) . ' </span>' ); ?>
    </div>
    <?php endif; ?>
  </footer>
</article>
