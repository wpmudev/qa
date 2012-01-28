<?php get_header( 'question' ); ?>

<div id="qa-page-wrapper">
    
<?php do_action( 'qa_before_content', 'ask-question' ); ?>

<?php the_qa_menu(); ?>

<div id="ask-question">
<?php the_question_form(); ?>
</div>

<?php do_action( 'qa_after_content', 'ask-question' ); ?>

</div><!--#qa-page-wrapper-->

<?php get_sidebar( 'question' ); ?>

<?php get_footer( 'question' ); ?>

