<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php
global $wp_roles, $qa_email_notification_subject, $qa_email_notification_content;
$options = $this->get_options('general_settings');
?>

<div class="wrap">
	<?php screen_icon('options-general'); ?>

	<h2><?php _e( 'Q&A Settings', QA_TEXTDOMAIN ); ?></h2>

	<form action="" method="post" class="qa-general">

		<table class="form-table">
			<tr>
				<th>
					<label for="qa_email_notification_subject"><?php _e( 'Notification E-mail Subject', QA_TEXTDOMAIN ) ?></label>
				</th>
				<td>
					<input id="qa_email_notification_subject" name="qa_email_notification_subject" value="<?php echo get_option('qa_email_notification_subject', $qa_email_notification_subject); ?>" />
					<br/>
					<?php _e('Variables:', 'messaging'); ?> SITE_NAME</td>
				</td>
			</tr>
			
			<tr>
				<th>
					<label for="qa_email_notification_content"><?php _e( 'Notification E-mail Content', QA_TEXTDOMAIN ) ?></label>
				</th>
				<td>
					<textarea id="qa_email_notification_content" name="qa_email_notification_content" rows="12" cols="40"><?php echo get_option('qa_email_notification_content', $qa_email_notification_content); ?></textarea>
					<br/>
					<?php _e('Variables:', 'messaging'); ?> TO_USER, SITE_NAME, SITE_URL, QUESTION_TITLE, QUESTION_DESCRIPTION, QUESTION_LINK</td>
				</td>
			</tr>
			
			<tr>
				<th>
					<label for="roles"><?php _e( 'Assign Capabilities', QA_TEXTDOMAIN ) ?></label>
					<img id="ajax-loader" src="<?php echo QA_PLUGIN_URL . 'ui-admin/images/ajax-loader.gif'; ?>" />
				</th>
				<td>
					<select id="roles" name="roles">
						<?php foreach ( $wp_roles->role_names as $role => $name ): ?>
							<option value="<?php echo $role; ?>"><?php echo $name; ?></option>
						<?php endforeach; ?>
					</select>
					<span class="description"><?php _e('Select a role to which you want to assign WP Q&A capabilities.', QA_TEXTDOMAIN); ?></span>

					<br /><br />

					<div id="capabilities">
						<?php foreach ( $GLOBALS['_qa_core_admin']->capability_map as $capability => $description ): ?>
							<input type="checkbox" name="capabilities[<?php echo $capability; ?>]" value="1" />
							<span class="description"><?php echo $description; ?></span>
							<br />
						<?php endforeach; ?>
					</div>
				</td>
			</tr>
		</table>

		<p class="submit">
			<?php wp_nonce_field('qa-verify'); ?>
			<input type="hidden" name="action" value="qa-save" />
			<input type="hidden" name="key" value="general_settings" />
			<input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Changes', QA_TEXTDOMAIN ); ?>">
		</p>

	</form>

</div>
