<?php get_header( 'question' ); ?>

<div id="qa-page-wrapper">

<?php the_qa_menu(); ?>

<?php the_post(); ?>

<div id="single-question">
	<h1><?php the_title(); ?></h1>
	<div id="single-question-container">
		<?php the_question_voting(); ?>
		<div id="question-body">
			<div id="question-content"><?php the_content(); ?></div>
			<?php the_question_tags( __( 'Tags:', QA_TEXTDOMAIN ) . ' <span class="question-tags">', ' ', '</span>' ); ?>
			<span id="qa-lastaction"><?php _e( 'asked', QA_TEXTDOMAIN ); ?> <?php the_qa_time( get_the_ID() ); ?></span>

			<?php the_qa_action_links( get_the_ID() ); ?>

			<?php the_qa_author_box( get_the_ID() ); ?>
		</div>
	</div>
</div>

<?php if ( is_question_answered() ) { ?>
<div id="answer-list">
	<h2><?php the_answer_count(); ?></h2>
	<?php the_answer_list(); ?>
</div>
<?php } ?>

<div id="edit-answer">
	<h2><?php _e( 'Your Answer', QA_TEXTDOMAIN ); ?></h2>
	<?php the_answer_form(); ?>
</div>

</div><!--#qa-page-wrapper-->

<?php get_sidebar( 'question' ); ?>

<?php get_footer( 'question' ); ?>

