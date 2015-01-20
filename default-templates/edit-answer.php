<?php
//get_header( 'question' );  
global $wp, $wp_query;

$wp_query->set( 'post_type', array( 'question', 'answer' ) );
$wp_query->set( 'post__in', array( (int) $wp->query_vars[ 'qa_edit' ] ) );
$post = get_post( (int) $wp->query_vars[ 'qa_edit' ] );
?>
<?php
wp_enqueue_style( 'qa-section', QA_PLUGIN_URL . QA_DEFAULT_TEMPLATE_DIR . '/css/general.css', array(), QA_VERSION );
?>

<div id="qa-page-wrapper">
	<div id="qa-content-wrapper">
		<?php do_action( 'qa_before_content', 'edit-answer' ); ?>

		<?php the_qa_menu(); ?>

		<?php wp_reset_postdata(); ?>

		<div id="answer-form">
			<h2><?php 
			printf( __( 'Answer for %s', QA_TEXTDOMAIN ), get_question_link( $post->post_parent ) ); ?></h2>
			<?php the_answer_form(); ?>
		</div>

		<?php do_action( 'qa_after_content', 'edit-answer' ); ?>
	</div>
</div><!--#qa-page-wrapper-->

<?php
//global $qa_general_settings;
//if ( isset( $qa_general_settings["page_layout"] ) && $qa_general_settings["page_layout"] !='content' )
//	get_sidebar( 'question' ); 
?>

<?php //get_footer( 'question' );  ?>

