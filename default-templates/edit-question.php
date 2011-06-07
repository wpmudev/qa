<?php get_header( 'question' ); ?>

<div id="qa-page-wrapper">

<?php the_qa_menu(); ?>

<div id="edit-question">
<?php the_question_form(); ?>
</div>

</div><!--#qa-page-wrapper-->

<?php get_sidebar( 'question' ); ?>

<?php get_footer( 'question' ); ?>

