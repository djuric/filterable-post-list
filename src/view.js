/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';

store( 'create-block', {
	actions: {
		*next( e ) {
			e.preventDefault();
			const ctx = getContext();
			const url = new URL( window.location );

			const currentPage = +url.searchParams.get( ctx.pageQueryVar );
			let nextPage = 2;

			if ( ! isNaN( currentPage ) && currentPage !== 0 ) {
				nextPage = currentPage + 1;
			}

			url.searchParams.set( ctx.pageQueryVar, nextPage );

			const { actions } = yield import(
				'@wordpress/interactivity-router'
			);
			yield actions.navigate(
				`${ window.location.pathname }${ url.search }`
			);
		},
		*previous( e ) {
			e.preventDefault();
			const ctx = getContext();
			const url = new URL( window.location );

			const currentPage = +url.searchParams.get( ctx.pageQueryVar );
			let previousPage = 1;

			if ( ! isNaN( currentPage ) && currentPage !== 0 ) {
				previousPage = currentPage - 1;
			}

			url.searchParams.set( ctx.pageQueryVar, previousPage );

			const { actions } = yield import(
				'@wordpress/interactivity-router'
			);
			yield actions.navigate(
				`${ window.location.pathname }${ url.search }`
			);
		},
		*updateCategory( e ) {
			const ctx = getContext();
			const categoryId = e.target.value;
			const url = new URL( window.location );

			// Update URL with new category ID and reset pagination.
			url.searchParams.set( ctx.categoryQueryVar, categoryId );
			url.searchParams.set( ctx.pageQueryVar, 1 );

			// Update local context.
			ctx.selectedCategory = +categoryId;

			const { actions } = yield import(
				'@wordpress/interactivity-router'
			);
			yield actions.navigate(
				`${ window.location.pathname }${ url.search }`
			);
		},
		*updateTags( e ) {
			e.preventDefault();
			const ctx = getContext();
			const tagId = e.target.value;
			const url = new URL( window.location );

			const existingTags = url.searchParams.get( ctx.tagsQueryVar );

			// Create an array out of existing tag IDs from URL.
			const tagIds = existingTags
				? existingTags.split( ',' ).map( ( id ) => id.trim() )
				: [];

			// Toggle tag ID in array.
			if ( ! tagIds.includes( tagId ) ) {
				tagIds.push( tagId );
			} else {
				tagIds.splice( tagIds.indexOf( tagId ), 1 );
			}

			// Update URL with new tag IDs and reset pagination.
			url.searchParams.set( ctx.tagsQueryVar, tagIds.join( ',' ) );
			url.searchParams.set( ctx.pageQueryVar, 1 );

			// Update local context.
			ctx.selectedTags = tagIds;

			const { actions } = yield import(
				'@wordpress/interactivity-router'
			);
			yield actions.navigate(
				`${ window.location.pathname }${ url.search }`
			);
		},
	},
} );
