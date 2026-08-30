/**
 * Vehicle Photos meta box — Media Library picker + drag reorder.
 * Stores an ordered, comma-separated list of attachment IDs.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		var $field = $( '#ic-gallery-field' );
		if ( ! $field.length ) {
			return;
		}

		var $list  = $( '#ic-gallery-list' );
		var $input = $( '#ic-gallery-input' );
		var $empty = $( '#ic-gallery-empty' );
		var frame;

		function sync() {
			var ids = $list.children( '.ic-gallery-item' ).map( function () {
				return $( this ).data( 'id' );
			} ).get();

			$input.val( ids.join( ',' ) );
			$empty.prop( 'hidden', ids.length > 0 );
		}

		function addItem( attachment ) {
			if ( $list.find( '[data-id="' + attachment.id + '"]' ).length ) {
				return; // Already in the gallery.
			}

			var sizes = attachment.sizes || {};
			var src   = ( sizes[ 'ic-thumb' ] || sizes.thumbnail || sizes.full || attachment ).url;

			$( '<li/>', { 'class': 'ic-gallery-item', 'data-id': attachment.id } )
				.append( $( '<img/>', { src: src, alt: '', width: 100, height: 67 } ) )
				.append( $( '<button/>', {
					type: 'button',
					'class': 'button-link ic-gallery-remove',
					'aria-label': 'Remove this photo',
					html: '&times;'
				} ) )
				.appendTo( $list );
		}

		$( '#ic-gallery-add' ).on( 'click', function () {
			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title: 'Select vehicle photos',
				library: { type: 'image' },
				button: { text: 'Add to gallery' },
				multiple: 'add'
			} );

			frame.on( 'select', function () {
				frame.state().get( 'selection' ).each( function ( a ) {
					addItem( a.toJSON() );
				} );
				sync();
			} );

			frame.open();
		} );

		$list.on( 'click', '.ic-gallery-remove', function () {
			$( this ).closest( '.ic-gallery-item' ).remove();
			sync();
		} );

		if ( $.fn.sortable ) {
			$list.sortable( { items: '> .ic-gallery-item', update: sync } );
		}
	} );
}( jQuery ) );
