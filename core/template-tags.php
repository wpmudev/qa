<?php

/* URL functions */

function get_ask_question_link() {
	return trailingslashit( get_post_type_archive_link( 'question' ) ) . user_trailingslashit( 'ask' );
}

function get_edit_question_link( $question_id ) {
	return trailingslashit( get_permalink( $question_id ) ) . user_trailingslashit( 'edit' );
}


/* Question Template Tags */

function is_question_page( $type = 'single' ) {
	switch ( $type ) {
		case 'ask':
			return get_query_var( 'ask_question' );
			break;
		case 'edit':
			return get_query_var( 'edit_question' );
			break;
		case 'single':
			return is_singular( 'question' );
			break;
		case 'archive':
			return is_post_type_archive( 'question' );
			break;
		case 'tag':
			return is_tax( 'question_tag' );
			break;
		case 'category':
			return is_tax( 'question_category' );
			break;
	}
}

function the_question_tags( $before = '<div class="question-tags">', $sep = ', ', $after = '</div>' ) {
	the_terms( 0, 'question_tag', $before, $sep, $after );
}

function the_edit_question_link( $question_id = 0 ) {
	// TODO: check cap

	if ( empty( $question_id ) )
		$question_id = get_the_ID();
?>
	<a href="<?php echo get_edit_question_link( $question_id ); ?>"><?php _e( 'Edit', 'qa_textdomain' ); ?></a>
<?php
}

function the_question_form() {
	// TODO: check cap

	if ( is_question_page( 'edit' ) ) {
		$question = $GLOBALS['post'];
		$question->tags = wp_get_object_terms( $question->ID, 'question_tag', array( 'fields' => 'names' ) );
	} else {
		$question = (object) array(
			'ID' => '',
			'post_content' => '',
			'post_title' => '',
			'tags' => array()
		);
	}

?>
<form method="post" action="<?php echo get_post_type_archive_link('question'); ?>">
	<?php wp_nonce_field( 'qa_edit' ); ?>

	<input type="hidden" name="question_id" value="<?php echo esc_attr( $question->ID ); ?>" />

	<label for="question_title">
	<?php _e('Question Title:', 'qa_textdomain'); ?>
	<p>
		<input type="text" name="question_title" value="<?php echo esc_attr( $question->post_title ); ?>" />
	</p>
	</label>

	<label for="question_content">
	<?php _e('Question Content:', 'qa_textdomain'); ?>
	<p>
		<textarea name="question_content"><?php echo esc_textarea( $question->post_content ); ?></textarea>
	</p>
	</label>

	<label for="question_content">
	<?php _e('Question Tags:', 'qa_textdomain'); ?>
	<p>
		<input type="text" name="question_tags" value="<?php echo implode( ', ', $question->tags ); ?>" />
	</p>
	</label>

	<input type="submit" value="<?php _e( 'Submit', 'qa_textdomain' ); ?>" />
</form>
<?php
}


/* Question-Answer Template Tags */

function is_question_answered( $question_id = 0, $type = 'any' ) {
	if ( !$question_id )
		$question_id = get_the_ID();

	if ( 'accepted' == $type ) {
		return get_post_meta( $question_id, 'accepted_answer', true );
	} else {
		return get_answer_count( $question_id ) > 0;
	}
}

function the_answer_count( $question_id = 0 ) {
	$count = get_answer_count( $question_id );

	printf( _n( '1 Answer', '%d Answers', $count, 'qa_textdomain' ), number_format_i18n( $count ) );
}

function get_answer_count( $question_id = 0 ) {
	if ( !$question_id )
		$question_id = get_the_ID();

	$question = get_post( $question_id );

	return $question->comment_count;
}


/* Answer Template Tags */

function the_answer_list() {
	// TODO: check cap

	$answers = get_comments( array( 'post_id' => get_the_ID() ) );

	if ( empty( $answers ) )
		return;

	echo "<ul>";

	foreach ( $answers as $answer ) {
?>
	<li id="comment-<?php echo $answer->comment_ID; ?>">
		<?php comment_author( $answer->comment_ID ); ?>:
		<?php echo $answer->comment_content; ?>
	</li>
<?php
	}

	echo "</ul>";
}

function the_answer_form() {
	// TODO: check cap

?>
<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
	<input type="hidden" name="comment_post_ID" value="<?php echo get_the_ID(); ?>" />

	<p><textarea name="comment"></textarea></p>

	<input type="submit" value="<?php _e( 'Submit', 'qa_textdomain' ); ?>" />
</form>
<?php
}

