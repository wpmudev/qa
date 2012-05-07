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
		
		add_action( 'wp_login', array( &$this, 'wp_login' ) );
		add_action( 'user_register', array( &$this, 'user_register' ), 1000);
		add_action( 'login_message', array( &$this, 'login_message' ) );
		
		add_filter( 'registration_redirect', array( &$this, 'registration_redirect' ), 10, 1);
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

		return qa_get_url( 'single', $question_id );
	}

	function handle_answer_editing() {
		global $wpdb;

		if ( !wp_verify_nonce( $_POST['_wpnonce'], 'qa_answer' ) )
			wp_die( __( 'Are you sure you want to do that?', QA_TEXTDOMAIN ) );

		$question_id = (int) $_POST['question_id'];
		$answer_id = (int) $_POST['answer_id'];
		
		$title = trim(strip_tags($_POST['answer']));
		
		if (strlen($title) >= 255) {
			$title = substr($title, 0, 252)."...";
		}

		$answer = array(
			'post_title' => $title,
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
			global $wpdb, $qa_email_notification_content, $qa_email_notification_subject, $current_site;
			
			if (!isset($current_site)) {
				$_url = get_bloginfo('url');
				$_domain_parts = split('/', $_url);
				$current_site = (object) array('domain' => $_domain_parts[2]);
			}
			
			// Check for flooding
			$most_recent = $wpdb->get_var( $wpdb->prepare( "
				SELECT MAX(post_date)
				FROM $wpdb->posts
				WHERE post_status = 'publish'
				AND post_type IN ('question', 'answer')
				AND post_author = %d
			", get_current_user_id() ) );

			$diff = current_time( 'timestamp' ) - strtotime( $most_recent );
			if ( !current_user_can('manage_options') && $diff < QA_FLOOD_SECONDS )
				wp_die( __( 'You are posting too fast. Slow down.', QA_TEXTDOMAIN ) );

			// Create new post
			$post = array_merge( $post, $defaults );
			$post['post_status'] = is_user_logged_in() ? 'publish' : 'draft';
			$post_id = wp_insert_post( $post, true );
			
			// Notification
			if (isset($defaults['post_type']) && $defaults['post_type'] == 'question') {
				$notification_subscriptions = $wpdb->get_results( "SELECT user_id
					FROM {$wpdb->usermeta}
					WHERE meta_key = 'qa_notification'
					AND meta_value = 1");
				
				$message_content = get_option('qa_email_notification_content', $qa_email_notification_content);
				$message_content = str_replace( "SITE_NAME", get_option( 'blogname' ), $message_content );
				$message_content = str_replace( "SITE_URL", 'http://' . $current_site->domain . '', $message_content );
				
				$message_content = str_replace( "QUESTION_TITLE", $post['post_title'], $message_content );
				$message_content = str_replace( "QUESTION_DESCRIPTION", strip_tags($post['post_content']), $message_content );
				$message_content = str_replace( "QUESTION_LINK", get_permalink($post_id), $message_content );
				
				$message_content = str_replace( "\'", "'", $message_content );
				
				$subject_content = get_option('qa_email_notification_subject', $qa_email_notification_subject);
				$subject_content = str_replace( "SITE_NAME", get_option( 'blogname' ), $subject_content );
				
				$admin_email = get_site_option('admin_email');
				if ($admin_email == ''){
					$admin_email = 'admin@' . $current_site->domain;
				}
				
				$from_email = $admin_email;
				$message_headers = "MIME-Version: 1.0\n" . "From: " . get_option( 'blogname' ) .  " <{$from_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
				
				foreach ($notification_subscriptions as $uid) {
					$user_data = get_userdata($uid->user_id);
					$email = $user_data->user_email;
					
					$tmp_to_email =  $user_data->user_email;
					
					$message_content_send = str_replace( "TO_USER", $user_data->display_name, $message_content );
					
					wp_mail($tmp_to_email, $subject_content, $message_content_send, $message_headers);
				}
			}
			
			
			wp_set_post_terms( $post_id, $_POST['question_tags'], 'question_tag' );
			wp_set_post_terms( $post_id, array( (int) $_POST['question_cat'] ), 'question_category' );
			
			// Anon posting
			if ( !is_user_logged_in() ) {
				$key = md5( current_time('timestamp') . $post_id );
				add_post_meta( $post_id, '_claim', $key );
				setcookie( '_qa_claim', $key, 0, '/' );	// TODO: handle multiple claims

				$url = site_url( 'wp-login.php', 'login' );
				if ( get_option( 'users_can_register' ) )
					$url .= '?action=register';

				wp_redirect( $url );
				die;
			}
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) )
				die( "Cheatin' uh?" );

			// Update post
			$post['ID'] = $post_id;
			$post_id = wp_update_post( $post, true );
			
			wp_set_post_terms( $post_id, $_POST['question_tags'], 'question_tag' );
			wp_set_post_terms( $post_id, array( (int) $_POST['question_cat'] ), 'question_category' );
		}

		if ( is_wp_error( $post_id ) ) {
			wp_die( $post_id->get_error_message() );
		}

		return $post_id;
	}
	
	// Redirect user to his claimed post, if there is one
	function wp_login( $login ) {
		$post_id = $this->_get_post_to_claim();
		
		if ( !$post_id )
			return;
		
		$user = get_userdatabylogin( $login );
		
		wp_update_post( array(
			'ID' => $post_id,
			'post_author' => $user->ID,
			'post_status' => 'publish'
		) );
		
		delete_post_meta( $post_id, '_claim' );
		setcookie( '_qa_claim', false, time() - 3600, '/' );
		
		wp_safe_redirect( qa_get_url( 'single', $post_id ) );
		// die;
	}

	// Automatically log in newly registered user, if there's a claimed post
	function user_register( $user_id ) {
		$post_id = $this->_get_post_to_claim();
		
		if ( !$post_id )
			return;
		
		wp_set_auth_cookie( $user_id, true, is_ssl() );
		
		$user = get_userdata( $user_id );
		
		do_action( 'wp_login', $user->user_login );
	}
	
	
	function registration_redirect( $redirect_to ) {
		$post_id = $this->_get_post_to_claim();
		
		if ( !$post_id )
			return;
		
		wp_set_auth_cookie( $user_id, true, is_ssl() );
		
		$user = get_userdata( $user_id );
		
		return qa_get_url( 'single', $post_id );
	}

	// Customized login message
	function login_message( $msg ) {
		$post_id = $this->_get_post_to_claim();

		if ( !$post_id )
			return $msg;

		if ( 'register' != $_GET['action'] )
			return $msg;

		$text = sprintf( __( 'To finish posting your %s, please register below. If you already have an account, please <a href="%s">login</a> instead.', QA_TEXTDOMAIN ), get_post_type( $post_id ), wp_login_url() );

		return '<p class="message register">' . $text . '</p>';
	}

	function _get_post_to_claim() {
		if ( !isset( $_COOKIE['_qa_claim'] ) )
			return false;

		$posts = get_posts( array(
			'post_type' => array( 'question', 'answer' ),
			'post_status' => 'draft',
			'meta_key' => '_claim',
			'meta_value' => $_COOKIE['_qa_claim']
		) );

		if ( empty( $posts ) )
			return false;

		return reset( $posts )->ID;	
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
		
		global $user_ID;
		
		$current_user = get_userdata( $user_ID );
		$this->update_answer_count( $post->ID );

		if ( 'answer' == $post->post_type && 'publish' == $new_status ) {
			$this->_touch_post( $post->post_parent );
			// Post to activity stream
			if (function_exists('bp_activity_add')) {
				$question = get_post($post->post_parent);
				$activity_id = bp_activity_add( array(
					'id' => get_post_meta($post->ID, '_bp_activity_id', true),
					'user_id' => $user_ID,
					'action' => sprintf(__('%s answered question "<a href="%s">%s</a>"', 'qa'), $current_user->display_name, get_permalink($post->ID), $question->post_title),
					'primary_link' => get_permalink($post->ID),
					'component' => 'qa',
					'type' => 'activity_update',
					'item_id' => $post->ID,
					'secondary_item_id' => $question->ID
				));
				if ($activity_id) {
					update_post_meta($post->ID, '_bp_activity_id', $activity_id);
				}
			}
		}
		
		if ( 'question' == $post->post_type && 'publish' == $new_status ) {
			// Post to activity stream
			if (function_exists('bp_activity_add')) {
				$activity_id = bp_activity_add( array(
					'id' => get_post_meta($post->ID, '_bp_activity_id', true),
					'user_id' => $user_ID,
					'action' => sprintf(__('%s asked "<a href="%s">%s</a>"', 'qa'), $current_user->display_name, get_permalink($post->ID), $post->post_title),
					'primary_link' => get_permalink($post->ID),
					'component' => 'qa',
					'type' => 'activity_update',
					'item_id' => $post->ID,
				));
				if ($activity_id) {
					update_post_meta($post->ID, '_bp_activity_id', $activity_id);
				}
			}
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

