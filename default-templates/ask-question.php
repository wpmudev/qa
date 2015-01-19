<?php 
wp_enqueue_style( 'qa-section', QA_PLUGIN_URL . QA_DEFAULT_TEMPLATE_DIR . '/css/general.css', array(), QA_VERSION );
?>

<div id="qa-page-wrapper">
    <div id="qa-content-wrapper">
    <?php do_action( 'qa_before_content', 'ask-question' ); ?>
    
    <div id="ask-question">
    <?php the_question_form(); ?>
    </div>
    
    <?php do_action( 'qa_after_content', 'ask-question' ); ?>
    </div>
</div><!--#qa-page-wrapper-->

<?php 
//global $qa_general_settings;

//if ( isset( $qa_general_settings["page_layout"] ) && $qa_general_settings["page_layout"] !='content' )	
//	get_sidebar( 'question' ); 
?>

<?php //get_footer( 'question' ); ?>

