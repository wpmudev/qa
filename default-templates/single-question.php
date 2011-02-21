<?php get_header(); ?>

<?php the_post(); ?>

<div id="single-question">
	<h1><?php the_title(); ?></h1>
	<div class="question-content"><?php the_content(); ?></div>
	<?php the_question_tags(); ?>
	<?php the_edit_question_link(); ?>
</div>

<?php if ( is_question_answered() ) { ?>
<div id="answer-list">
	<h2><?php the_answer_count(); ?></h2>
	<?php the_answer_list(); ?>
</div>
<?php } ?>

<div id="answer-form">
	<h2><?php _e( 'Your Answer', 'qa_textdomain' ); ?></h2>
	<?php the_answer_form(); ?>
</div>

<?php get_footer(); ?>
