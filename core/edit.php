<?php

/**
 * Takes care of everything that has to do with content creation and editing.
 */
class QA_Edit {

	function QA_Edit() {
		add_action( 'init', array( &$this, 'handle_forms' ), 11 );
		add_filter( 'wp_unique_post_slug_is_bad_flat_slug', array( &$this, 'handle_slugs' ), 10, 4 );

		add_action( 'delete_post', array( &$this, 'update_answer_count' ) );
		add_action( 'trash_post', array( &$this, 'update_answer_count' ) );
		add_action( 'transition_post_status', array( &$this, 'transition_post_status' ), 10, 3 );
	}

	function handle_forms() {
		if ( !isset( $_REQUEST['_wpnonce'] ) )
			return;

		// Handle deletions
		if ( isset( $_REQUEST['qa_delete'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'qa_delete' ) ) {
			$post = get_post( $_REQUEST['qa_delete'] );

			if ( $post && current_user_can( 'delete_post', $post->ID ) ) {
				if ( 'answer' == $post->post_type ) {
					wp_delete_post( $post->ID );
					$url = qa_get_url( 'single', $post->post_parent );
				} elseif ( 'question' == $post->post_type ) {
					wp_delete_post( $post->ID );
					$url = add_query_arg( 'qa_msg', 'deleted', qa_get_url( 'archive' ) );
				}
			}
		} elseif ( isset( $_POST['qa_action'] ) ) {
			switch ( $_POST['qa_action'] ) {
				case 'edit_question':
					$url = $this->handle_question_editing();
					break;

				case 'edit_answer':
					$url = $this->handle_answer_editing();
					break;
			}
		} else {
			return;
		}

		if ( !$url ) {
			$url = add_query_arg( 'qa_error', 1, qa_get_url( 'archive' ) );
		}

		wp_redirect( $url );
		die;
	}

	function handle_question_editing() {
		global $wpdb;

		if ( !wp_verify_nonce( $_POST['_wpnonce'], 'qa_edit' ) )
			wp_die( __( 'Are you sure you want to do that?', QA_TEXTDOMAIN ) );

		$question_id = (int) $_POST['question_id'];

		if ( !$question_id && !current_user_can( 'publish_questions' ) )
			wp_die( __( 'You are not allowed to post questions', QA_TEXTDOMAIN ) );

		$question = array(
			'post_title' => trim( $_POST['question_title'] ),
			'post_content' => trim( $_POST['question_content'] ),
		);

		if ( empty( $question['post_title'] ) || empty( $question['post_content'] ) )
			wp_die( __( 'Questions must have both a title and a body.', QA_TEXTDOMAIN ) );

		// Check for duplicates
		if ( !$question_id ) {
			$dup_id = $wpdb->get_var( $wpdb->prepare( "
				SELECT ID
				FROM $wpdb->posts
				WHERE post_type = 'question'
				AND post_status = 'publish'
				AND (post_title = %s OR post_content = %s)
				LIMIT 1
			", $question['post_title'], $question['post_content'] ) );

			if ( $dup_id ) {
				wp_die( sprintf( __( 'It seems that question was <a href="%s">already asked</a>.', QA_TEXTDOMAIN ), qa_get_url( 'single', $dup_id ) ) );
			}
		}

		$question_id = $this->_insert_post( $question_id, $question, array(
			'post_type' => 'question',
			'comment_status' => 'open',
		) );

		wp_set_post_terms( $question_id, $_POST['question_tags'], 'question_tag' );

		return qa_get_url( 'single', $question_id );
	}

	function handle_answer_editing() {
		global $wpdb;

		if ( !wp_verify_nonce( $_POST['_wpnonce'], 'qa_answer' ) )
			wp_die( __( 'Are you sure you want to do that?', QA_TEXTDOMAIN ) );

		$question_id = (int) $_POST['question_id'];
		$answer_id = (int) $_POST['answer_id'];

		if ( !$answer_id && !current_user_can( 'publish_answers' ) )
			wp_die( __( 'You are not allowed to post answers', QA_TEXTDOMAIN ) );

		$answer = array(
			'post_parent' => absint( $question_id ),
			'post_content' => trim( $_POST['answer'] ),
			'post_type' => 'answer',
			'post_status' => 'publish',
		);

		if ( empty( $answer['post_parent'] ) )
			wp_die( __( 'Answer must be associated to a question.', QA_TEXTDOMAIN ) );
		
		if ( empty( $answer['post_content'] ) )
			wp_die( __( 'You have to actually write something.', QA_TEXTDOMAIN ) );

		// Check for duplicates
		$dup_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_type = 'answer'
			AND post_status = 'publish'
			AND post_parent = %d
			AND post_content = %s
			LIMIT 1
		", $answer['post_parent'], $answer['post_content'] ) );

		if ( $dup_id ) {
			wp_die( sprintf( __( 'It seems that answer was <a href="%s">already given</a>.', QA_TEXTDOMAIN ), qa_get_url( 'single', $dup_id ) ) );
		}

		$answer_id = $this->_insert_post( $answer_id, $answer, array(
			'post_type' => 'answer',
			'comment_status' => 'open',
		) );

		return qa_get_url( 'single', $answer_id );
	}

	function _insert_post( $post_id, $post, $defaults ) {
		if ( !$post_id ) {
			global $wpdb;

			// Check for flooding
			$most_recent = $wpdb->get_var( $wpdb->prepare( "
				SELECT MAX(post_date)
				FROM $wpdb->posts
				WHERE post_status = 'publish'
				AND post_type IN ('question', 'answer')
				AND post_author = %d
			", get_current_user_id() ) );

			$diff = current_time( 'timestamp' ) - strtotime( $most_recent );
			if ( $diff < QA_FLOOD_SECONDS )
				wp_die( __( 'You are posting too fast. Slow down.', QA_TEXTDOMAIN ) );

			// Create new post
			$post = array_merge( $post, $defaults );
			$post['post_status'] = is_user_logged_in() ? 'publish' : 'draft';
			$post_id = wp_insert_post( $post, true );
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) )
				die( "Cheatin' uh?" );

			// Update post
			$post['ID'] = $post_id;
			$post_id = wp_update_post( $post, true );
		}

		if ( is_wp_error( $post_id ) ) {
			wp_die( $post_id->get_error_message() );
		}

		return $post_id;
	}

	// Reserve some slugs that are used for other purposes.
	function handle_slugs( $r, $slug, $post_type ) {
		global $wp_rewrite;
	
		if ( 'question' == $post_type )
			return in_array( $slug, array( 'ask', 'unanswered', 'edit', 'tags', $wp_rewrite->pagination_base ) );

		return $r;
	}

	function transition_post_status( $new_status, $old_status, $post ) {
		if ( $new_status == $old_status )
			return;

		$this->update_answer_count( $post->ID );

		if ( 'answer' == $post->post_type && 'publish' == $new_status ) {
			$this->_touch_post( $post->post_parent );
		}
	}

	// Update the modified time of a post
	function _touch_post( $post_id ) {
		global $wpdb;

		$r = $wpdb->update( $wpdb->posts,
			array(
				'post_modified' => current_time( 'mysql' ),
				'post_modified_gmt' => current_time( 'mysql', true ),
			),
			array( 'ID' => $post_id )
		);
	}

	function update_answer_count( $post_id ) {
		global $_qa_core;

		$post = get_post( $post_id );

		if ( 'answer' != $post->post_type )
			return;

		$question_id = $post->post_parent;

		$count = $_qa_core->get_count( array(
			'post_type' => 'answer',
			'post_parent' => $question_id,
		) );

		// When deleting/trashing, don't count it
		if ( 'transition_post_status' != current_filter() ) {
			$count -= 1;
		}

		update_post_meta( $question_id, '_answer_count', $count );
	}
}

$_qa_edit = new QA_Edit();

