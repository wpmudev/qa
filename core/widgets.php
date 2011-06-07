<?php

class QA_Widget_Helper extends WP_Widget {
	var $default_instance = array();

	function parse_instance( $instance ) {
		return wp_parse_args( $instance, $this->default_instance );
	}

	function widget( $args, $instance ) {
		$instance = $this->parse_instance( $instance );

		extract($args);
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		$this->content( $instance );

		echo $after_widget;
	}
	
	function title_field( $title ) {
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', QA_TEXTDOMAIN ); ?></label>
			<?php
			echo _qa_html( 'input', array(
				'class' => 'widefat',
				'type' => 'text',
				'id' => $this->get_field_id('title'),
				'name' => $this->get_field_name('title'),
				'value' => $title
			) );
			?>
		</p>
<?php	
	}
}

class QA_Widget_Questions extends QA_Widget_Helper {

	var $default_instance = array(
		'title' => '',
		'which' => 'recent',
		'number' => 5
	);

	function QA_Widget_Questions() {
		$widget_ops = array( 'description' => __( 'List of questions', QA_TEXTDOMAIN ) );
		$this->WP_Widget( 'questions', __( 'Questions', QA_TEXTDOMAIN ), $widget_ops );
	}

	function content( $instance ) {
		global $post;

		extract( $instance );

		switch ( $which ) {
			case 'recent':
				$args = array();
				break;
			case 'popular':
				$args = array( 'meta_key' => '_answer_count', 'orderby' => 'meta_value_num' );
				break;
			case 'unanswered':
				$args = array( 'qa_unanswered' => true );
				break;
		}

		$args = array_merge( $args, array(
			'post_type' => 'question',
			'posts_per_page' => 5,
			'suppress_filters' => false
		) );

		echo '<ul>';
		foreach ( get_posts( $args ) as $post ) {
			setup_postdata( $post );

			echo _qa_html( 'li', _qa_html( 'a', array( 'href' => get_permalink() ), get_the_title() ) );
		}
		echo '</ul>';

		wp_reset_postdata();
	}

	function form( $instance ) {
		$instance = $this->parse_instance( $instance );
		$this->title_field( $instance['title'] );
?>

		<p>
			<label for="<?php echo $this->get_field_id('which'); ?>"><?php _e( 'Which:', QA_TEXTDOMAIN ); ?></label>
			<select id="<?php echo $this->get_field_id('which'); ?>" name="<?php echo $this->get_field_name('which'); ?>">
				<?php
				$options = array(
					'recent' => __( 'Recent', QA_TEXTDOMAIN ),
					'popular' => __( 'Popular', QA_TEXTDOMAIN ),
					'unanswered' => __( 'Unanswered', QA_TEXTDOMAIN ),
				);

				foreach ( $options as $value => $title ) {
					$attr = compact( 'value' );
					if ( $instance['which'] == $value )
						$attr['selected'] = 'selected';
					echo _qa_html( 'option', $attr, $title );
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e( 'Number of questions to show:', QA_TEXTDOMAIN ); ?></label>
			<?php
			echo _qa_html( 'input', array(
				'type' => 'text',
				'size' => 2,
				'id' => $this->get_field_id('number'),
				'name' => $this->get_field_name('number'),
				'value' => $instance['number']
			) );
			?>
		</p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = $this->parse_instance( $new_instance );
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['which'] = $new_instance['which'];
		$instance['number'] = (int) $new_instance['number'];
		return $instance;
	}
}


class QA_Widget_Tags extends QA_Widget_Helper {

	var $default_instance = array(
		'title' => '',
	);

	function QA_Widget_Tags() {
		$widget_ops = array( 'description' => __( 'The most popular question tags in cloud format', QA_TEXTDOMAIN ) );
		$this->WP_Widget( 'question_tags', __( 'Question Tags', QA_TEXTDOMAIN ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract($args);

		if ( !empty($instance['title']) ) {
			$title = $instance['title'];
		} else {
			$tax = get_taxonomy( 'question_tag' );
			$title = $tax->labels->name;
		}
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		echo '<div class="question-tagcloud">';
		wp_tag_cloud( array(
			'taxonomy' => 'question_tag',
			'topic_count_text_callback' => array( $this, 'count_text_callback' ),
		) );
		echo "</div>\n";
		echo $after_widget;
	}
	
	function count_text_callback( $count ) {
		return sprintf( _n('%s question', '%s questions', $count), number_format_i18n( $count ) );	
	}

	function form( $instance ) {
		$instance = $this->parse_instance( $instance );
		$this->title_field( $instance['title'] );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}
}

function qa_widgets_init() {
	if ( !is_blog_installed() )
		return;

	register_widget( 'QA_Widget_Questions' );

	register_widget( 'QA_Widget_Tags' );
}

add_action( 'widgets_init', 'qa_widgets_init' );

