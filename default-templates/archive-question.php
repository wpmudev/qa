<?php get_header( 'question' ); ?>

<div id="qa-page-wrapper">

<?php the_qa_error_notice(); ?>
<?php the_qa_menu(); ?>

<?php if ( !have_posts() ) : ?>

<p><?php $question_ptype = get_post_type_object( 'question' ); echo $question_ptype->labels->not_found; ?></p>

<?php else: ?>

<div id="question-list">
<?php while ( have_posts() ) : the_post(); ?>
	<div class="question">
		<div class="question-stats">
			<?php the_question_score(); ?>
			<?php the_question_status(); ?>
		</div>
		<div class="question-summary">
			<h3><?php the_question_link(); ?></h3>
			<?php the_question_tags( '<div class="question-tags">', ' ', '</div>' ); ?>
			<div class="question-started">
				<?php the_qa_time( get_the_ID() ); ?>
				<?php the_qa_user_link( $post->post_author ); ?>
			</div>
		</div>
	</div>
<?php endwhile; ?>
</div><!--#question-list-->

<?php the_qa_pagination(); ?>

<?php endif; ?>

</div><!--#qa-page-wrapper-->

<?php get_sidebar( 'question' ); ?>

<?php get_footer( 'question' ); ?>
