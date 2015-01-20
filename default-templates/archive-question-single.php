<?php get_header( 'question' ); ?>
<div id="question-list">
	<div class="question-stats">
		<?php do_action( 'qa_before_question_stats' ); ?>
		<?php the_question_score( 0, false, '' ); ?>
		<?php the_question_status( 0, false, '' ); ?>
		<?php do_action( 'qa_after_question_stats' ); ?>
	</div>

	<div class="question-summary">
		<?php do_action( 'qa_before_question_summary' ); ?>
		<?php the_question_tags( '<div class="question-tags">'.__('Tags: ', QA_TEXTDOMAIN), ' ', '</div>' ); ?>
		<?php the_question_category( '<div class="question-categories">'.__('Categories: ', QA_TEXTDOMAIN), ' ', '</div>' ); ?>
		<div class="question-started">
			<?php the_qa_time( get_the_ID() ); ?>
			<?php the_qa_user_link( $post->post_author ); ?>
		</div>
		<?php do_action( 'qa_after_question_summary' ); ?>
	</div>
</div>