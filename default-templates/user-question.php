<?php
//get_header( 'question' ); 
global $wp_query, $wp, $_qa_core;
$wp_query->max_num_pages = 1;

$author                         = get_user_by( 'login', $wp->query_vars['author_name'] );
$wp_query->queried_object_id    = (int) $author->ID;
$wp_query->queried_object       = get_userdata( $author->ID );
$wp_query->is_post_type_archive = false;
?>
<?php
wp_enqueue_style( 'qa-section', QA_PLUGIN_URL . QA_DEFAULT_TEMPLATE_DIR . '/css/general.css', array(), QA_VERSION );
wp_enqueue_script( 'qa-init', QA_PLUGIN_URL . QA_DEFAULT_TEMPLATE_DIR . '/js/init.js', array( 'jquery' ), QA_VERSION );
wp_localize_script( 'qa-init', 'QA_L10N', array(
	'ajaxurl'       => admin_url( 'admin-ajax.php' ),
	'msg_login'     => __( 'Please login or register to vote.', QA_TEXTDOMAIN ),
	'msg_own'       => __( 'You can\'t vote on your own post.', QA_TEXTDOMAIN ),
	'content_width' => $_qa_core->_get_content_width()
) );
?>

<div id="qa-page-wrapper">
	<div id="qa-content-wrapper">
		<?php do_action( 'qa_before_content', 'edit-question' ); ?>

		<?php the_qa_menu(); ?>

		<div id="qa-user-box">

			<?php
			global $bp; //BuddyPress active
			$userdata = ( isset( $bp ) ) ? $userdata = $bp->displayed_user->userdata : get_queried_object();
			//var_dump($userdata);
			?>

			<?php echo get_avatar( $userdata->ID, 128 ); ?>
			<?php the_qa_user_rep( $userdata->ID ); ?>
		</div>

		<table id="qa-user-details">
			<tr>
				<th><?php _e( 'Name', QA_TEXTDOMAIN ); ?></th>
				<td><strong><?php echo $userdata->display_name; ?></strong></td>
			</tr>
			<tr>
				<th><?php _e( 'Member for', QA_TEXTDOMAIN ); ?></th>
				<td><?php echo human_time_diff( strtotime( $userdata->user_registered ) ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Website', QA_TEXTDOMAIN ); ?></th>
				<td><?php echo make_clickable( $userdata->user_url ); ?></td>
			</tr>
		</table>

		<?php
		$question_query = new WP_Query( array(
			'author'                 => $userdata->ID,
			'post_type'              => 'question',
			'posts_per_page'         => 20,
			'update_post_term_cache' => false
		) );

		$answer_query = new WP_Query( array(
			'author'                 => $userdata->ID,
			'post_type'              => 'answer',
			'posts_per_page'         => 20,
			'update_post_term_cache' => false
		) );

		$fav_query = new WP_Query( array(
			'post_type'      => 'question',
			'meta_key'       => '_fav',
			'meta_value'     => $userdata->ID,
			'posts_per_page' => 20,
		) );
		?>

		<div id="qa-user-tabs-wrapper">
			<ul id="qa-user-tabs">
				<li><a href="#qa-user-questions">
						<span
							id="user-questions-total"><?php echo number_format_i18n( $question_query->found_posts ); ?></span>
						<?php echo _n( 'Question', 'Questions', $question_query->found_posts, QA_TEXTDOMAIN ); ?>
					</a></li>

				<li><a href="#qa-user-answers">
						<span
							id="user-answers-total"><?php echo number_format_i18n( $answer_query->found_posts ); ?></span>
						<?php echo _n( 'Answer', 'Answers', $answer_query->found_posts, QA_TEXTDOMAIN ); ?>
					</a></li>
			</ul>

			<div id="qa-user-questions">
				<div id="question-list">
					<?php while ( $question_query->have_posts() ) : $question_query->the_post(); ?>
						<?php do_action( 'qa_before_question_loop' ); ?>
						<div class="question">
							<?php do_action( 'qa_before_question' ); ?>
							<div class="question-stats">
								<?php do_action( 'qa_before_question_stats' ); ?>
								<?php the_question_score(); ?>
								<?php the_question_status(); ?>
								<?php do_action( 'qa_after_question_stats' ); ?>
							</div>
							<div class="question-summary">
								<?php do_action( 'qa_before_question_summary' ); ?>
								<h3><?php the_question_link(); ?></h3>
								<?php the_question_tags(); ?>
								<div class="question-started">
									<?php the_qa_time( get_the_ID() ); ?>
								</div>
								<?php do_action( 'qa_after_question_summary' ); ?>
							</div>
							<?php do_action( 'qa_after_question' ); ?>
						</div>
						<?php do_action( 'qa_after_question_loop' ); ?>
					<?php endwhile; ?>
				</div><!--#question-list-->
			</div><!--#qa-user-questions-->

			<div id="qa-user-answers">
				<ul>
					<?php
					while ( $answer_query->have_posts() ) : $answer_query->the_post();
						list( $up, $down ) = qa_get_votes( get_the_ID() );

						echo '<li>';
						echo "<div class='answer-score'>";
						echo number_format_i18n( $up - $down );
						echo "</div> ";
						the_answer_link( get_the_ID() );
						echo '</li>';
					endwhile;
					?>
				</ul>
			</div><!--#qa-user-answers-->

		</div><!--#qa-user-tabs-wrapper-->

		<?php do_action( 'qa_after_content', 'edit-question' ); ?>
	</div>
</div><!--#qa-page-wrapper-->

<?php //get_footer( 'question' ); ?>

