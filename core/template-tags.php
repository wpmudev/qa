<?php

/**
 * The following functions are meant to be used directly in template files.
 * v1.4.2.1
 */
/* = General Template Tags
  -------------------------------------------------------------- */

function get_the_qa_menu() {
	global $user_ID, $wp;
	$menu = array();

	if ( ($user_ID == 0 && qa_visitor_can( 'read_questions' )) || current_user_can( 'read_questions' ) ) {
		$menu[]	 = array(
			'title'		 => __( 'Questions', QA_TEXTDOMAIN ),
			'type'		 => 'archive',
			'current'	 => !is_qa_page( 'unanswered' ) && !(isset( $wp->query_vars[ 'qa_ask' ] ) ? true : false) && !is_qa_page( 'edit' )
		);
		$menu[]	 = array(
			'title'		 => __( 'Unanswered', QA_TEXTDOMAIN ),
			'type'		 => 'unanswered',
			'current'	 => is_qa_page( 'unanswered' )
		);
	}

	if ( ($user_ID == 0 && qa_visitor_can( 'publish_questions' )) || current_user_can( 'publish_questions' ) ) {
		$menu[] = array(
			'title'		 => __( 'Ask a Question', QA_TEXTDOMAIN ),
			'type'		 => 'ask',
			'current'	 => isset( $wp->query_vars[ 'qa_ask' ] ) ? true : false
		);
	}
	$menu = apply_filters( 'qa_modify_menu_items', $menu );

	$out = apply_filters( 'qa_before_menu', '' );

	$out .= "<div id='qa-menu'>";

	$out .= "<ul>";
	$out = apply_filters( 'qa_first_menu_item', $out );

	foreach ( $menu as $item ) {
		extract( $item );

		$url = qa_get_url( $type );

		$id = $current ? 'qa-current-url' : '';

		$out .= _qa_html( 'li', array( 'id' => $id ), _qa_html( 'a', array( 'href' => $url ), $title
		)
		);
	}
	$out = apply_filters( 'qa_last_menu_item', $out );
	if ( apply_filters( 'qa_show_menu_search_form', false ) ) {
		$out .= "<li class='qa-search'>";
		$out .= get_the_qa_search_form();
		$out .= "</li>";
	}
	$out .= "</ul>";

	$out = apply_filters( 'qa_after_menu', $out );

	$out .= "</div>";

	return $out;
}

function the_qa_menu() {
	echo get_the_qa_menu();
}

function get_the_qa_error_notice() {
	if ( !isset( $_GET[ 'qa_error' ] ) )
		return;
	$out = '';
	$out .= '<div id="qa-error-notice">';
	$out .= __( 'An error has occured while processing your submission.', QA_TEXTDOMAIN );
	$out .= '</div>';
}

function the_qa_error_notice() {
	echo get_the_qa_error_notice();
}

function get_the_qa_search_form() {

	$out = '';
	$out .= '<form method="get" action="' . qa_get_url( 'archive' ) . '">';
	$out .= '<button>' . __( 'Search', QA_TEXTDOMAIN ) . '</button>';
	$out .= '<input type="text" name="s" value="' . get_search_query() . '" />';
	$out .= '</form>';

	return apply_filters( 'the_qa_search_form', $out );
}

function the_qa_search_form() {
	echo get_the_qa_search_form();
}

function get_the_qa_pagination( $query = null ) {

	if ( is_null( $query ) )
		$query = $GLOBALS[ 'wp_query' ];

	if ( $query->max_num_pages <= 1 )
		return;

	$out = '';

	$current_page	 = max( 1, $query->get( 'paged' ) );
	$total_pages	 = $query->max_num_pages;

	$padding		 = 2;
	$range_start	 = max( 1, $current_page - $padding );
	$range_finish	 = min( $total_pages, $current_page + $padding );

	$out .= '<div class="qa-pagination">';

	if ( $current_page > 1 )
		$out .= get_qa_single_page_link( $query, $current_page - 1, __( 'prev', QA_TEXTDOMAIN ), 'prev' );

	if ( $range_start > 1 )
		$out .= get_qa_single_page_link( $query, 1 );

	if ( $range_start > $padding )
		$out .= '<span class="dots">...</span>';

	foreach ( range( $range_start, $range_finish ) as $num ) {
		if ( $num == $current_page )
			$out .= _qa_html( 'span', array( 'class' => 'current' ), number_format_i18n( $num ) );
		else
			$out .= get_qa_single_page_link( $query, $num );
	}

	if ( $range_finish + $padding <= $total_pages )
		$out .= '<span class="dots">...</span>';

	if ( $range_finish < $total_pages )
		$out .= get_qa_single_page_link( $query, $total_pages );

	if ( $current_page < $total_pages )
		$out .= get_qa_single_page_link( $query, $current_page + 1, __( 'next', QA_TEXTDOMAIN ), 'next' );

	$out .= '</div>';

	return $out;
}

function the_qa_pagination( $query = null ) {
	echo get_the_qa_pagination( $query );
}

function get_qa_single_page_link( $query, $num, $title = '', $class = '' ) {
	if ( !$title )
		$title = number_format_i18n( $num );

	$args = array( 'href' => get_pagenum_link( $num ) );

	if ( $class )
		$args[ 'class' ] = $class;

	return apply_filters( 'qa_single_page_link', _qa_html( 'a', $args, $title ) );
}

function _qa_single_page_link( $query, $num, $title = '', $class = '' ) {
	echo get_qa_single_page_link( $query, $num, $title	 = '', $class	 = '' );
}

function get_the_qa_time( $id ) {
	$post = get_post( $id );

	$time = get_post_time( 'G', true, $post );

	$time_diff = time() - $time;

	if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 )
		$h_time	 = sprintf( __( '%s ago', QA_TEXTDOMAIN ), human_time_diff( $time ) );
	else
		$h_time	 = mysql2date( get_option( 'date_format' ), $post->post_date );

	$h_time = apply_filters( 'qa_time', $h_time, $time );
	return '<span class="qa-timediff">' . $h_time . '</span>';
}

function the_qa_time( $id ) {
	echo get_the_qa_time( $id );
}

function get_the_qa_author_box( $id ) {
	$user_id = get_post_field( 'post_author', $id );
	$out	 = '';

	$out .= '<div class="qa-user-box">';
	$out .= get_avatar( $user_id, 32 );
	$out .= '<div class="qa-user-details">';
	$out .= get_the_qa_user_link( $user_id );
	$out .= get_the_qa_user_rep( $user_id );
	$out .= '</div>
	</div>';

	return $out;
}

function the_qa_author_box( $id ) {
	echo get_the_qa_author_box( $id );
}

function get_the_qa_action_links( $id ) {
	$links = array();

	$links[ 'single' ] = __( 'link', QA_TEXTDOMAIN );

	if ( current_user_can( 'edit_post', $id ) )
		$links[ 'edit' ] = __( 'edit', QA_TEXTDOMAIN );

	if ( current_user_can( 'delete_post', $id ) )
		$links[ 'delete' ] = __( 'delete', QA_TEXTDOMAIN );

	if ( is_user_logged_in() ) {
		if ( current_user_can( 'flag_questions', $id ) )
			$links[ 'flag' ] = __( 'report', QA_TEXTDOMAIN );
	}
	else if ( qa_visitor_can( 'flag_questions' ) )
		$links[ 'flag' ] = __( 'report', QA_TEXTDOMAIN );

	$show_form = false;
	foreach ( $links as $type => $title ) {
		if ( 'flag' == $type ) {
			$flag_link		 = '<a name="qa_report" href="javascript:void(0)" onClick="javascript:document.getElementById(\'qa_flag_form_' . $id . '\').style.display=\'block\';" >' . $title . '</a>';
			if ( isset( $_GET[ 'flag_received' ] ) )
				$links[ 'flag' ] = '<span style="color:green">' . __( 'Your report has been received.', QA_TEXTDOMAIN ) . '</span>';
			else if ( isset( $_GET[ 'no_reason' ] ) ) {
				$links[ 'flag' ] = $flag_link . " " . '<span style="color:red">' . __( 'Please select a reason for reporting.', QA_TEXTDOMAIN ) . '</span>';
				$show_form		 = true;
			} else if ( isset( $_GET[ 'flag_error' ] ) ) {
				$links[ 'flag' ] = $flag_link . " " . '<span style="color:red">' . __( 'Captcha error. Please try again.', QA_TEXTDOMAIN ) . '</span>';
				$show_form		 = true;
			} else {
				$links[ 'flag' ] = $flag_link;
				$show_form		 = true;
			}
		} else
			$links[ $type ] = _qa_html( 'a', array( 'href' => qa_get_url( $type, $id ) ), $title );
	}

	$out = '';

	$out .= '<div class="qa-action-links">';
	$out .= implode( ' | ', $links );
	if ( $show_form )
		$out .= the_qa_flag_form( $id );
	$out .= '</div>';

	return $out;
}

function the_qa_action_links( $id ) {
	echo get_the_qa_action_links( $id );
}

// Since V 1.3.1
function the_qa_flag_form( $id ) {
	global $qa_general_settings;

	$f = '';
	$f .= '<div id="qa_flag_form_' . $id . '" style="display:none" class="qa_flag_form" >';
	$f .= '<form method="post" action="' . admin_url( "admin-ajax.php" ) . '" >';
	$f .= '<input type="hidden" name="action" value="qa_flag" />';
	$f .= '<input type="hidden" name="ID" value="' . $id . '" />';

	if ( isset( $qa_general_settings[ "report_reasons" ] ) && '' != trim( $qa_general_settings[ "report_reasons" ] ) ) {
		$reasons = explode( ",", $qa_general_settings[ "report_reasons" ] );
		if ( is_array( $reasons ) ) {
			$f .= '<div class="qa_report_reason">';
			$f .= __( 'Select a reason for reporting:', QA_TEXTDOMAIN );
			$f .= '<br />';
			foreach ( $reasons as $reason ) {
				$f .= '<input type="radio" name="report_reason" value="' . stripslashes( trim( $reason ) ) . '" /> ' . stripslashes( trim( $reason ) );
				$f .= '<br />';
			}
			$f .= '</div>';
		}
	}

	if ( isset( $qa_general_settings[ "captcha" ] ) && $qa_general_settings[ "captcha" ] && qa_is_captcha_usable() ) {

		$f .= sprintf( '<br/><label class="qa_captcha"><img src="%s" style="vertical-align:top" /> <input type="text" name="random" placeholder="%s"/></label> ', QA_PLUGIN_URL . 'default-templates/captcha-image.php', __( 'Enter letters in image', QA_TEXTDOMAIN ) );


//		$f .= '<div class="qa_captcha">
//		<label class="description" >' . __('Type the letters you see in the image below:',QA_TEXTDOMAIN ). '</label>
//		<div class="qa_captcha_inner">
//		<img class="captcha_image" id="captcha_'.$id.'" src="' . plugins_url( "/qa/securimage/securimage_show.php" ). '" alt="CAPTCHA Image" />
//		</div>
//		<div>
//		<input type="text" id="captcha_code_'.$id.'" name="captcha_code" size="10" maxlength="6" />
//		<a href="javascript:void(0)" onclick="document.getElementsByClassName(\'captcha_image\').src=\'' . plugins_url( "/qa/securimage/images/blank.png" ). '\';document.getElementById(\'captcha_'.$id.'\').src = \''. plugins_url( "/qa/securimage/securimage_show.php") . '?\' + Math.random(); document.getElementById(\'captcha_code_'.$id.'\').value=\'\'; return false;">'. __('[ Different Image ]',QA_TEXTDOMAIN ). '</a>
//		</div>
//		</div>';
	}
	$f .= '<input type="submit" value="' . __( 'Send Report', QA_TEXTDOMAIN ) . '" />';
	$f .= '</form>';
	//$f .= '<br />';
	$f .= '<input type="submit" value="' . __( 'Cancel', QA_TEXTDOMAIN ) . '" onClick="javascript:document.getElementById(\'qa_flag_form_' . $id . '\').style.display=\'none\';" />';
	$f .= '</div>';

	return $f;
}

// Find prerequistes to use Captcha
// Since V1.3.1
// http://www.phpcaptcha.org/faq/
function qa_is_captcha_usable() {
	if ( !function_exists( 'imageftbbox' ) || !function_exists( 'imagecreate' ) || !function_exists( 'imagecreatetruecolor' ) || !function_exists( 'imagettftext' ) || version_compare( PHP_VERSION, '5.2.0' ) < 0 )
		return false;

	return true;
}

function get_the_qa_user_link( $user_id ) {
	$author_name = get_the_author_meta( 'display_name', $user_id );
	$author_url	 = qa_get_url( 'user', $user_id );

	return apply_filters( 'qa_user_link', "<a class='qa-user-link' href='$author_url'>$author_name</a>" );
}

function the_qa_user_link( $user_id ) {
	echo get_the_qa_user_link( $user_id );
}

function get_the_qa_user_rep( $user_id ) {

	return '<div class="qa-user-rep">' . number_format_i18n( qa_get_user_rep( $user_id ) ) . '</div>';
}

function the_qa_user_rep( $user_id ) {
	echo get_the_qa_user_rep( $user_id );
}

/* = Question Template Tags
  -------------------------------------------------------------- */

function the_question_link( $question_id = 0 ) {
	global $post;
	if ( !$question_id )
		$question_id = $post->ID;
	if ( !$question_id )
		$question_id = get_the_ID();

	echo get_question_link( $question_id );
}

function get_question_link( $question_id = 0 ) {
	global $post;
	if ( !$question_id )
		$question_id = $post->ID;
	if ( !$question_id )
		$question_id = get_the_ID();

	if ( !isset( $post ) ) {
		$post = get_post( $question_id );
	}

	return apply_filters( 'qa_get_question_link', _qa_html( 'a', array( 'class' => 'question-link', 'href' => qa_get_url( 'single', $question_id ) ), $post->post_title ) );
}

function get_the_question_score( $question_id = 0, $label = true, $count_class = 'mini-count' ) {
	global $post;
	if ( !$question_id )
		$question_id = $post->ID;
	if ( !$question_id )
		$question_id = get_the_ID();

	list( $up, $down ) = qa_get_votes( $question_id );

	$score	 = $up - $down;
	$score	 = apply_filters( 'qa_question_score', $score );

	$out = '';
	$out .= "<div class='question-score'>";

	$out .= "<div class='" . $count_class . "'>" . __( 'Votes:', QA_TEXTDOMAIN ) . ' ' . number_format_i18n( $score ) . "</div>";

	$out .= "</div>";

	return $out;
}

function the_question_score( $question_id = 0, $label = true, $count_class = 'mini-count' ) {
	echo get_the_question_score( $question_id, $label, $count_class );
}

function get_the_question_voting( $question_id = 0 ) {
	global $_qa_core;

	if ( !$question_id )
		$question_id = get_the_ID();

	list( $up, $down, $current ) = qa_get_votes( $question_id );

	$buttons = array(
		'up'	 => __( 'This question is useful and clear (click again to undo)', QA_TEXTDOMAIN ),
		'down'	 => __( 'This question is unclear or not useful (click again to undo)', QA_TEXTDOMAIN )
	);

	foreach ( $buttons as $type => $text ) {
		$buttons[ $type ] = $GLOBALS[ '_qa_votes' ]->get_link( $question_id, $type, $current, $text );
	}

	$out = '';
	$out .= '<div class="qa-voting-box">';
	$out .= $buttons[ 'up' ];
	$out .= '<span title="' . __( 'Score', QA_TEXTDOMAIN ) . '">' . number_format_i18n( $up - $down ) . '</span>';
	$out .= $buttons[ 'down' ];
	$out .= '</div>';

	return $out;
}

function the_question_voting( $question_id = 0 ) {
	echo get_the_question_voting( $question_id );
}

function get_the_question_subscription() {
	return $GLOBALS[ '_qa_subscriptions' ]->get_link(
	get_queried_object_id(), __( 'Click here to be notified of followup answers via e-mail', QA_TEXTDOMAIN ), __( 'Stop notifying me of followup answers via e-mail', QA_TEXTDOMAIN )
	);
}

function the_question_subscription() {
	echo get_the_question_subscription();
}

function get_the_answer_voting( $answer_id ) {
	list( $up, $down, $current ) = qa_get_votes( $answer_id );

	$buttons = array(
		'up'	 => __( 'This answer is useful (click again to undo)', QA_TEXTDOMAIN ),
		'down'	 => __( 'This answer is not useful (click again to undo)', QA_TEXTDOMAIN )
	);

	foreach ( $buttons as $type => $text ) {
		$buttons[ $type ] = $GLOBALS[ '_qa_votes' ]->get_link( $answer_id, $type, $current, $text );
	}

	$out = '';
	$out .= '<div class="qa-voting-box">';
	$out .=$buttons[ 'up' ];
	$out .= '<span title="' . __( 'Score', QA_TEXTDOMAIN ) . '">' . number_format_i18n( $up - $down ) . '</span>';
	$out .= $buttons[ 'down' ];

	$out .= get_the_answer_accepted( $answer_id );
	$out .= '</div>';

	return $out;
}

function the_answer_voting( $answer_id ) {
	echo get_the_answer_voting( $answer_id );
}

function get_the_answer_accepted( $answer_id ) {
	$question_id = get_post_field( 'post_parent', $answer_id );

	$user_can_accept = get_post_field( 'post_author', $question_id ) == get_current_user_id();

	$is_accepted = get_post_meta( $question_id, '_accepted_answer', true ) == $answer_id;

	$out = '';

	if ( $user_can_accept ) {
		$data = array(
			'action'	 => 'qa_accept',
			'answer_id'	 => $answer_id,
			'accept'	 => ( $is_accepted ? 'off' : 'on' )
		);

		$out .= '<form method="post" action="">';
		$out .= wp_nonce_field( 'qa_accept', "_wpnonce", true, false );

		foreach ( $data as $key => $value ) {
			$out .= _qa_html( 'input', array( 'type' => 'hidden', 'name' => $key, 'value' => $value ) );
		}

		$out .= _qa_html( 'input', array(
			'type'	 => 'submit',
			'title'	 => __( 'Accept answer (click again to undo)', QA_TEXTDOMAIN ),
			'class'	 => 'vote-accepted-' . ( $is_accepted ? 'on' : 'off' )
		) );
		$out .= '</form>';
	} elseif ( $is_accepted ) {
		$out .= _qa_html( 'span', array(
			'title'	 => __( 'Accepted answer', QA_TEXTDOMAIN ),
			'class'	 => 'vote-accepted-on'
		), __( 'accepted', QA_TEXTDOMAIN ) );
	}
	return $out;
}

function the_answer_accepted( $answer_id ) {
	echo get_the_answer_accepted( $answer_id );
}

function get_the_question_status( $question_id = 0, $label = true, $count_class = 'mini-count' ) {
	global $post;
	if ( !$question_id )
		$question_id = $post->ID;

	$count = get_answer_count( $question_id );

	if ( get_post_meta( $question_id, '_accepted_answer', true ) )
		$status	 = 'answered-accepted';
	elseif ( $count > 0 )
		$status	 = 'answered';
	else
		$status	 = 'unanswered';

	$status = apply_filters( 'qa_question_status', $status );

	$out = '';

	$out .= "<div class='question-status $status'>";
	$out .= "<div class='" . $count_class . "'>" . __( 'Answers:', QA_TEXTDOMAIN ) . ' ' . number_format_i18n( $count ) . "</div>";

	$out .= "</div>";

	return $out;
}

function the_question_status( $question_id = 0, $label = true, $count_class = 'mini-count' ) {
	echo get_the_question_status( $question_id, $label, $count_class );
}

function get_the_question_tags( $before = '', $sep = ', ', $after = '' ) {
	return get_the_term_list( 0, 'question_tag', $before, $sep, $after );
}

function the_question_tags( $before = '', $sep = ', ', $after = '' ) {
	echo get_the_question_tags( $before, $sep, $after );
}

function get_the_question_category( $before = '', $sep = ', ', $after = '' ) {
	return get_the_term_list( 0, 'question_category', $before, $sep, $after );
}

function the_question_category( $before = '', $sep = ', ', $after = '' ) {
	echo get_the_question_category( $before, $sep, $after );
}

function get_the_question_form() {
	global $wp_query, $wp_version, $qa_general_settings, $post;

	if ( is_qa_page( 'edit' ) ) {
		$question = $wp_query->posts[ 0 ];

		if ( !current_user_can( 'edit_question', $question->ID ) )
			return;

		$question->tags = wp_get_object_terms( $question->ID, 'question_tag', array( 'fields' => 'names' ) );

		$args = apply_filters( 'qa_category_args', array( 'fields' => 'ids' ) );

		$cats			 = wp_get_object_terms( $question->ID, 'question_category', $args );
		$question->cat	 = empty( $cats ) ? false : reset( $cats );
	} else {
		$post		 = null; //Necessary after 3.5 to prevent media upload from failing for users less than admin
		$question	 = (object) array(
			'ID'			 => '',
			'post_content'	 => '',
			'post_title'	 => '',
			'tags'			 => array(),
			'cat'			 => false
		);
	}

	$out = '';


	$out .= '<form id="question-form" method="post" action="' . qa_get_url( 'archive' ) . '">';
	$out .= wp_nonce_field( 'qa_edit', "_wpnonce", true, false );

	$out .= '<input type="hidden" name="qa_action" value="edit_question" />';
	$out .= '<input type="hidden" name="question_id" value="' . esc_attr( $question->ID ) . '" />';

	$out .= '<div id="question-form-table">';
	$out .= '<div id="question-title-td">';
	$out .= '<input type="text" id="question-title" name="question_title" placeholder="' . esc_attr( __( 'Question Title', QA_TEXTDOMAIN ) ) . '" value="' . esc_attr( $question->post_title ) . '" />';
	$out .= '</div></div>';

	$use_editor	 = true;
	if ( isset( $qa_general_settings[ "disable_editor" ] ) && $qa_general_settings[ "disable_editor" ] )
		$use_editor	 = false;

	if ( version_compare( $wp_version, "3.3" ) >= 0 && $use_editor ) {
		$wp_editor_settings = apply_filters( 'qa_question_editor_settings', array(), $question->ID );
		ob_start();
		wp_editor( $question->post_content, 'question_content', $wp_editor_settings );
		$out .= ob_get_contents();
		ob_end_clean();
	} else
		$out .= '<textarea name="question_content" class="wp32">' . esc_textarea( $question->post_content ) . '</textarea>';

	$out .= '
	<div id="question-category">';
	$out .= wp_dropdown_categories( array(
		'orderby'			 => 'name',
		'order'				 => 'ASC',
		'taxonomy'			 => 'question_category',
		'selected'			 => $question->cat,
		'hide_empty'		 => false,
		'hierarchical'		 => true,
		'name'				 => 'question_cat',
		'class'				 => '',
		'show_option_none'	 => __( 'Select category...', QA_TEXTDOMAIN ),
		'echo'				 => 0
	) );
	$out .= '</div>
	<div id="question-tags">
	<input type="text" id="question-tags" name="question_tags" placeholder="' . esc_attr( __( 'Tags:', QA_TEXTDOMAIN ) ) . '" value="' . implode( ', ', $question->tags ) . '" />
	</div>';

	if ( $qa_general_settings[ 'captcha' ] ) {
		$out .= sprintf( '<label><img src="%s" style="vertical-align:top" /> <input type="text" name="random" placeholder="%s"/></label> ', QA_PLUGIN_URL . 'default-templates/captcha-image.php', __( 'Enter letters in image', QA_TEXTDOMAIN ) );
	}
	$out .= get_the_qa_submit_button();
	$out .= '</form>';

	return apply_filters( 'the_question_form', $out );
}

function the_question_form() {
	echo get_the_question_form();
}

/* = Answer Template Tags
  -------------------------------------------------------------- */

function get_the_answer_link( $answer_id ) {
	$question_id = get_post_field( 'post_parent', $answer_id );

	return _qa_html( 'a', array( 'class' => 'answer-link', 'href' => qa_get_url( 'single', $answer_id ) ), get_the_title( $question_id ) );
}

function the_answer_link( $answer_id ) {
	echo get_the_answer_link( $answer_id );
}

function get_the_answer_count( $question_id = 0 ) {
	$count = get_answer_count( $question_id ? $question_id : get_the_ID()  );

	return sprintf( _n( '1 Answer', '%d Answers', $count, QA_TEXTDOMAIN ), number_format_i18n( $count ) );
}

function the_answer_count( $question_id = 0 ) {
	echo get_the_answer_count( $question_id );
}

function get_the_answer_list() {
	global $user_ID, $post;
	$question_id = $post->ID;

	if ( post_password_required( $post ) )
		return;

	if ( ($user_ID == 0 && !qa_visitor_can( 'read_answers', $question_id )) && !current_user_can( 'read_answers', $question_id ) )
		return;

	$accepted_answer = get_post_meta( $question_id, '_accepted_answer', true );

	$answers = new WP_Query( array(
		'post_type'		 => 'answer',
		'post_parent'	 => $question_id,
		'post__not_in'	 => array( $accepted_answer ),
		'orderby'		 => 'qa_score',
		'posts_per_page' => QA_ANSWERS_PER_PAGE,
		'paged'			 => get_query_var( 'paged' )
	) );

	if ( $accepted_answer && !get_query_var( 'paged' ) )
		array_unshift( $answers->posts, get_post( $accepted_answer ) );

	$out = '';

	$out .= get_the_qa_pagination( $answers );

	foreach ( $answers->posts as $answer ) {
		setup_postdata( $answer );

		$out .= '<div id="answer-' . $answer->ID . '" class="answer">';
		$out .= get_the_answer_voting( $answer->ID );
		$out .= '<div class="answer-body">';

		do_action( 'qa_before_answer_content', $answer->ID );

		$out .= '<div class="answer-content">';
		$out .= apply_filters( 'the_content', $answer->post_content );
		$out .= '</div>';

		do_action( 'qa_before_answer_meta', $answer->ID );

		$out .= '<div class="answer-meta">';
		$out .= get_the_qa_action_links( $answer->ID );
		$out .= get_the_qa_author_box( $answer->ID );
		$out .= '</div>';

		do_action( 'qa_after_answer_meta', $answer->ID );

		$out .= '</div>
		</div>';
	}

	get_the_qa_pagination( $answers );

	wp_reset_postdata();

	return $out;
}

function the_answer_list() {
	echo get_the_answer_list();
}

function get_the_answer_form() {
	global $wp, $wp_query, $user_ID, $wp_version, $qa_general_settings, $post;

	//if(!isset($post)){
	$post = get_post( (int) $wp->query_vars[ 'qa_edit' ] );
	//}

	if ( post_password_required( $post ) )
		return;

	$out = '';

	if ( isset( $wp->query_vars[ 'qa_edit' ] ) ) {
		$answer = $post;

		if ( ($user_ID == 0 && !qa_visitor_can( 'edit_published_answers', $answer->ID )) && !current_user_can( 'edit_published_answers', $answer->ID ) )
			return;
	} else {
		//if ( ($user_ID == 0 && !qa_visitor_can( 'publish_answers' )) && !current_user_can( 'publish_answers' ) ) {
		if ( !current_user_can( 'publish_answers' ) ) {
			$out .= '<p>' . __( 'You are not allowed to add answers!', QA_TEXTDOMAIN ) . '</p>';
			return;
		}
		$answer = (object) array(
			'ID'			 => '',
			'post_parent'	 => $post->ID,
			'post_content'	 => ''
		);

		$post = null; //Necessary after 3.5 to prevent media upload from failing for users less than admin
	}


	$out .= '<form id="answer-form" method="post" action="' . qa_get_url( 'archive' ) . '">';
	$out .= wp_nonce_field( 'qa_answer', "_wpnonce", true, false );

	$out .= '<input type="hidden" name="qa_action" value="edit_answer" />
	<input type="hidden" name="question_id" value="' . esc_attr( $answer->post_parent ) . '" />
	<input type="hidden" name="answer_id" value="' . esc_attr( $answer->ID ) . '" />';

	$use_editor	 = true;
	if ( isset( $qa_general_settings[ "disable_editor" ] ) && $qa_general_settings[ "disable_editor" ] )
		$use_editor	 = false;

	if ( version_compare( $wp_version, "3.3" ) >= 0 && $use_editor ) {
		$wp_editor_settings = apply_filters( 'qa_answer_editor_settings', array(), $answer->ID );
		$out .= '<p>';

		ob_start();
		wp_editor( $answer->post_content, 'answer', $wp_editor_settings );
		$out .= ob_get_contents();
		ob_end_clean();

		$out .= '</p>';
	} else
		$out .= '<p><textarea name="answer" class="wp32">' . esc_textarea( $answer->post_content ) . '</textarea></p>';

	if ( $qa_general_settings[ 'captcha' ] ) {
		$out .= sprintf( '<label><img src="%s" style="vertical-align:top" /> <input type="text" name="random" placeholder="%s"/></label> ', QA_PLUGIN_URL . 'default-templates/captcha-image.php', __( 'Enter letters in image', QA_TEXTDOMAIN ) );
	}

	$out .= get_the_qa_submit_button();
	$out .= '</form>';

	return apply_filters( 'the_answer_form', $out );
}

function the_answer_form() {
	echo get_the_answer_form();
}

function get_the_qa_submit_button() {
	global $qa_general_settings;
	if ( is_user_logged_in() || ( is_array( $qa_general_settings ) && isset( $qa_general_settings[ "method" ] ) && 'assign' == $qa_general_settings[ "method" ]
	/* && qa_visitor_can( 'immediately_publish_questions' ) */ ) ) {
		$button = __( 'Submit', QA_TEXTDOMAIN );
	} elseif ( get_option( 'users_can_register' ) ) {
		$button = __( 'Register/Login and Submit', QA_TEXTDOMAIN );
	} else {
		$button = __( 'Login and Submit', QA_TEXTDOMAIN );
	}

	return apply_filters( 'the_qa_submit_button', '<input class="qa-edit-submit" type="submit" value="' . $button . '" />' );
}

function the_qa_submit_button() {
	echo get_the_qa_submit_button();
}

function qa_visitor_can( $capability, $post_id = null ) {
	$role = get_role( 'visitor' );

	if ( $role && is_object( $role ) && $role->has_cap( $capability, $post_id ) ) {
		return true;
	}
	return false;
}
