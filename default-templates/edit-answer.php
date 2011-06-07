<?php get_header( 'question' ); ?>

<div id="qa-page-wrapper">

<?php the_qa_menu(); ?>

<?php the_post(); ?>

<div id="answer-form">
	<h2><?php _e( 'Answer for ', QA_TEXTDOMAIN ); the_question_link( $post->post_parent ); ?></h2>
	<?php the_answer_form(); ?>
</div>

</div><!--#qa-page-wrapper-->

<?php get_sidebar( 'question' ); ?>

<?php get_footer( 'question' ); ?>

