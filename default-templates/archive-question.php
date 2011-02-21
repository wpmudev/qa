<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
<div class="question">
	<h2><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>
	<div class="question-content"><?php the_content(); ?></div>
	<?php the_question_tags(); ?>
</div>
<?php endwhile; ?>

<?php get_footer(); ?>
