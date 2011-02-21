<?php

/*
 * Test suite; just drop this file in your mu-plugins folder
 */

function test_rewrites() {
	$question_id = reset( get_posts( array(
		'post_type' => 'question',
		'fields' => 'ids'
	) ) );

	$tag_id = (int) reset( get_terms( 'question_tag', array( 'fields' => 'ids' ) ) );

	$category_id = (int) reset( get_terms( 'question_category', array( 'fields' => 'ids' ) ) );

	$archives = array(
		get_post_type_archive_link('question'),
		get_term_link(get_term($category_id, 'question_category')),
		get_term_link(get_term($tag_id, 'question_tag')),	
	);

	$urls = array(
		get_ask_question_link()
		,get_permalink( $question_id )
		,get_edit_question_link( $question_id )
	);

	$urls = array_merge( $urls,
		$archives

#		,array_map(function($url) {
#			return trailingslashit( $url ) . $GLOBALS['wp_rewrite']->pagination_base . '/2';			
#		}, $archives)

#		,array_map(function($url) {
#			return trailingslashit( $url ) . 'feed';			
#		}, $archives)
	);

	foreach ( $urls as $url ) {
		$class = ( '200' == wp_remote_retrieve_response_code( wp_remote_get( $url ) ) ) ? 'updated' : 'error';
		echo "<div class='$class'><p><a href='$url'>$url</a></p></div>";
	}
}
add_action('admin_notices', 'test_rewrites');

function test_templates() {
	foreach ( array( 'ask', 'edit', 'single', 'archive', 'tag', 'category' ) as $type ) {
		if ( is_question_page( $type ) )
			echo( "<pre>$type: true\n</pre>" );
	}
}
add_action( 'template_redirect', 'test_templates' );

