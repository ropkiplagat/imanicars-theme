<?php
/**
 * Template Name: Used Cars
 */
get_header();
// Redirect to cars archive
wp_redirect( home_url( '/cars/' ) );
exit;
