<?php if ( !defined( 'ABSPATH' ) ) die( 'No direct access allowed!' ); ?>
<?php
if ( !current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}
?>

<?php
global $wp_roles, $qa_email_notification_subject, $qa_email_notification_content;
$options		 = $this->get_options( 'general_settings' );
$wp_nonce_verify = wp_nonce_field( 'qa-verify', '_wpnonce', true, false );

/**
 * Returns an array of layout options
 *
 * @since 1.4
 */
function qa_layouts() {
	return array(
		'content-sidebar'	 => array(
			'value'		 => 'content-sidebar',
			'label'		 => __( 'Content on left', QA_TEXTDOMAIN ),
			'thumbnail'	 => QA_PLUGIN_URL . 'ui-admin/images/content-sidebar.png',
		),
		'sidebar-content'	 => array(
			'value'		 => 'sidebar-content',
			'label'		 => __( 'Content on right', QA_TEXTDOMAIN ),
			'thumbnail'	 => QA_PLUGIN_URL . 'ui-admin/images/sidebar-content.png',
		),
		'content'			 => array(
			'value'		 => 'content',
			'label'		 => __( 'One-column, no sidebar', QA_TEXTDOMAIN ),
			'thumbnail'	 => QA_PLUGIN_URL . 'ui-admin/images/content.png',
		),
	);
}

/**
 * Renders the Layout setting field.
 *
 * @since 1.4
 */
function qa_settings_field_layout() {
	global $qa_general_settings;
	foreach ( qa_layouts() as $layout ) {
		?>
		<div class="layout image-radio-option theme-layout">
			<label class="description">
				<input type="radio" name="qa_page_layout" value="<?php echo esc_attr( $layout[ 'value' ] ); ?>" <?php checked( $qa_general_settings[ 'page_layout' ], $layout[ 'value' ], true ); ?> />
				<span>
					<img src="<?php echo esc_url( $layout[ 'thumbnail' ] ); ?>" width="136" height="122" alt="" />
		<?php echo $layout[ 'label' ]; ?>
				</span>
			</label>
		</div>
		<?php
	}
}
?>

<div class="wrap">
<?php screen_icon( 'options-general' ); ?>

	<h2><?php _e( 'Q&A Settings', QA_TEXTDOMAIN ); ?></h2>

	<br />
	<span class="description"><?php _e( 'This page uses ajax. Settings are saved without a page refresh.', QA_TEXTDOMAIN ) ?></span>

	<div id="poststuff" class="metabox-holder">

		<form action="" method="post" class="qa-general">
<?php if ( 0 == 1 ) { ?>
				<div class="postbox <?php echo $this->postbox_classes( 'qa_display' ) ?>" id="qa_display">
					<h3 class='hndle'><span><?php _e( 'Theme Adaptation Settings', QA_TEXTDOMAIN ) ?></span></h3>
					<div class="inside">
						<table class="form-table">

							<tr>
								<td colspan="2">
									<span class="description">
	<?php printf( __( 'Q&A supports these themes as default: <b>%s</b>. For the rest of themes, the look of the Q&A pages should be adapted to your theme. This can be done by editing the templates or by adjusting the below settings. If you are not having any display issues you can leave them as they are.', QA_TEXTDOMAIN ), qa_supported_themes() ); ?>
									</span>
								</td>
							</tr>
							<tr>
								<th>
									<label for="page_layout"><?php _e( 'Page Layout', QA_TEXTDOMAIN ) ?></label>
								</th>
								<td>
	<?php qa_settings_field_layout() ?>
									<span class="description">
									<?php _e( 'Select the layout that will be applied to Q&A pages. For best result, apply the same layout of the rest of your pages.', QA_TEXTDOMAIN ) ?>
									</span>
								</td>
							</tr>

							<tr>
								<th>
									<label for="page_width"><?php _e( 'Usable Page Width (px)', QA_TEXTDOMAIN ) ?></label>
								</th>
								<td>
									<input style="width:100px" name="page_width" value="<?php echo @$options[ 'page_width' ]; ?>" />
									&nbsp;&nbsp;&nbsp;
									<span class="description">
	<?php _e( 'Enter usable page width of your theme (Typically around 1000). Because of paddings, usable width can be slightly smaller than full page width. Tip: To find the usable page width, using Google Chrome, mouse over left of Q&A menu, right click and select "Inspect element". Dimensions of the net page width will be displayed.', QA_TEXTDOMAIN ) ?>
									</span>
								</td>
							</tr>

							<tr>
								<th>
									<label for="content_width"><?php _e( 'Q&A Content Width (px)', QA_TEXTDOMAIN ) ?></label>
								</th>
								<td>
									<input style="width:100px" name="content_width" value="<?php echo @$options[ 'content_width' ]; ?>" />
									&nbsp;&nbsp;&nbsp;
									<span class="description">
	<?php _e( 'Enter the desired width of main Q&A content box. Recommended minimum width is 584.', QA_TEXTDOMAIN ) ?>
									</span>
								</td>
							</tr>

							<tr>
								<th>
									<label for="content_width"><?php _e( 'Alignment of Q&A Content', QA_TEXTDOMAIN ) ?></label>
								</th>
								<td>
									<select name="content_alignment" class="qa_content_alignment">
										<option value="center" <?php selected( @$options[ 'content_alignment' ], 'center', true ); ?>><?php _e( 'Center', QA_TEXTDOMAIN ) ?></option>
										<option value="left" <?php selected( @$options[ 'content_alignment' ], 'left', true ); ?>><?php _e( 'Left', QA_TEXTDOMAIN ) ?></option>
										<option value="right" <?php selected( @$options[ 'content_alignment' ], 'right', true ); ?>><?php _e( 'Right', QA_TEXTDOMAIN ) ?></option>
									</select>
									&nbsp;&nbsp;&nbsp;
									<span class="description">
	<?php _e( 'Select the desired alignment of Q&A content relative to the page and sidebar.', QA_TEXTDOMAIN ) ?>
									</span>
								</td>
							</tr>

							<tr>
								<th>
									<label for="sidebar_width"><?php _e( 'Sidebar Width (px)', QA_TEXTDOMAIN ) ?></label>
								</th>
								<td>
									<input style="width:100px" name="sidebar_width" value="<?php echo @$options[ 'sidebar_width' ]; ?>" />
									&nbsp;&nbsp;&nbsp;
									<span class="description">
	<?php _e( 'Enter the sidebar width of your theme, if you selected sidebar to be displayed in page layout setting. Depending on margins, you may need to set this a few px greater than the actual width. Tip: To find the width of your sidebar, using Google Chrome, mouse over your sidebar, right click and select "Inspect element". Dimensions of the sidebar will be displayed.', QA_TEXTDOMAIN ) ?>
									</span>
								</td>
							</tr>

							<tr>
								<th>
									<label for="auto_css_button"><img class="ajax-loader" src="<?php echo QA_PLUGIN_URL . 'ui-admin/images/ajax-loader.gif'; ?>" /></label>
								</th>
								<td>
									<input type="button" class="button-secondary qa-auto-css" name="save" value="<?php _e( 'Estimate Additional css Rules', QA_TEXTDOMAIN ); ?>">
									&nbsp;&nbsp;&nbsp;
									<span class="description">
	<?php _e( 'When you click this button, using the above widths and selected layout, css rules will be automatically estimated and saved inside the "Additional ccs Rules" field. These settings may not work on every theme and you may still need to make some fine tunings. Tip: If the result is not satisfactory (e.g. sidebar dislocated), you can change the width settings and try again. Each time Additional css Rules field is reset.', QA_TEXTDOMAIN ) ?>
									</span>
									<script type="text/javascript">
										//<![ CDATA[
											jQuery(do c ument).ready(fun ction($) {
												$("inp ut.qa-auto -css").click(function(){
												var page_layout = $("input[name='qa _page_layout']:checked");
												var page_width = $("in put[name='page_width']");
												var content_width = $("input [name='content_width']");
												var  content_alignment = $('select.qa_content_ali gnment option:selected');
												var sidebar_width = $("input [name='sidebar_width']");
												var additional_css = $("textarea[ name='additional_css']");
												var confirmed = true;
													if ( $.tr im(additional_css.val()) != '' ) {
												confirmed = false;
										}
													if (  $.trim( p age_width.val()) ==  ''){
													alert('<?php echo esc_js( __( 'Page width cannot be empty', QA_TEXTDOMAIN ) ) ?>');
																	page_width.focus();
																return false;
									 }
																	else if ( $.t rim(con t ent_width.val()) ==  ''){
													alert('<?php echo esc_js( __( 'Content width cannot be empty', QA_TEXTDOMAIN ) ) ?>');
																	content_width.focus();
																return false;
									 }
																	else if ( page_layout.val()  != 'content' && $.t rim(sid e bar_width.val()) ==  ''){
													alert('<?php echo esc_js( __( 'With the selected page layout, sidebar width cannot be empty', QA_TEXTDOMAIN ) ) ?>');
																	sidebar_width.focus();
																return false;
									 }
																	else if ( pa rseInt(page_w idth.val()) < parse Int(content_w idth.val()) + parse Int (sidebar_width.val() ) ){
													alert('<?php echo esc_js( __( 'Page width cannot be less than content width + sidebar width', QA_TEXTDOMAIN ) ) ?>');
																	sidebar_width.focus();
																return false;
									 }
																	else if ( !confirmed ) {
																	if ( confirm('<?php echo esc_js( __( 'Your additional css rules field is not empty. If you continue, existing value be overwritten. Are you sure?', QA_TEXTDOMAIN ) ) ?>') ) { 	 											confirmed = true;
																		}
													else {
																return false;
																}
										}
																	if ( confi rmed ) {
																	$('.ajax-loader').show(); 	 							 			var data = {action: 'qa- estimate', page_layout:page_la yout.val(), page_width:page_widt h.val(), content_width:content_width.va l(), content_alignment:content_alignmen t.val(), sidebar_width:sidebar_width.val(), nonce: '<?php echo wp_create_nonce() ?> '};
																						$.post(a jaxurl,  data, function(resp onse) {
																						$('.ajax-loader').hide();
																							if ( response && response.error )  {
																						alert(response.error);
																							} 								 		 	else if ( res ponse && response. css ){
																							$("textarea[ name=' additional_c ss']").val(response.css );
															alert('<?php echo esc_js( __( 'Additional css rules estimated and saved. Now you can check display of QA pages in different browsers.', QA_TEXTDOMAIN ) ) ?>');
																					}
														else  {
															alert('<?php echo esc_js( __( 'A connection error occurred. Please try again.', QA_TEXTDOMAIN ) ) ?>');
														}
																	},'json');
																}
																});
										});
										//]]>
									</script>

								</td>
							</tr>

							<tr>
								<th>
									<label for="additional_css"><?php _e( 'Additional css Rules', QA_TEXTDOMAIN ) ?></label>
								</th>
								<td>
									<textarea class="qa-full" rows="2" name="additional_css"><?php echo @$options[ 'additional_css' ]; ?></textarea>
									<br />
									<span class="description">
	<?php _e( 'You can add your css codes manually or edit the already estimated ones. Ensure that you use valid css. e.g.', QA_TEXTDOMAIN ) ?>&nbsp;<code>#sidebar{width:200px;float:left;}</code>
									</span>

								</td>
							</tr>

							<tr>
								<th>
									<label for="search_input_width"><?php _e( 'Search Input Field Width (px)', QA_TEXTDOMAIN ) ?></label>
								</th>
								<td>
									<input style="width:100px" name="search_input_width" value="<?php echo @$options[ 'search_input_width' ]; ?>" />
									&nbsp;&nbsp;&nbsp;
									<span class="description">
	<?php _e( 'If search input field is displayed below the Q&A menu, reduce this value.', QA_TEXTDOMAIN ) ?>
									</span>
								</td>
							</tr>

						</table>
					</div>
				</div>

<?php } ?>
			<p class="submit">
			<?php echo $wp_nonce_verify; ?>
				<input type="hidden" name="action" value="qa-save" />
				<input type="hidden" name="key" value="general_settings" />
				<input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Everything on this Page', QA_TEXTDOMAIN ); ?>">
				<img class="ajax-loader" src="<?php echo QA_PLUGIN_URL . 'ui-admin/images/ajax-loader.gif'; ?>" />
				<span style="display:none;font-weight:bold;color:darkgreen" class="qa_settings_saved"><?php _e( 'Settings saved', QA_TEXTDOMAIN ); ?></span>
			</p>

			<div class="postbox <?php echo $this->postbox_classes( 'qa_display' ) ?>" id="qa_display">
				<h3 class='hndle'><span><?php _e( 'Other Display Settings', QA_TEXTDOMAIN ) ?></span></h3>


				<div class="inside">

					<table class="form-table">
						<tr>
							<th>
								<label for="questions_per_page"><?php _e( 'Questions Per Page', QA_TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input style="width:100px" name="questions_per_page" value="<?php echo @$options[ 'questions_per_page' ]; ?>" />&nbsp;&nbsp;&nbsp;<span class="description"><?php echo __( 'If left empty, WP setting will be used: ', QA_TEXTDOMAIN ) . get_option( 'posts_per_page' ); ?></span>
								<br />
								<span class="description">
<?php printf( __( 'IMPORTANT: Questions Per Page cannot be less than Wordpress %s setting, because of WP limitations. If you set it like that Wordpress setting will be used instead.', QA_TEXTDOMAIN ), '<a href="' . admin_url( 'options-reading.php' ) . '">' . __( 'Blog pages show at most', QA_TEXTDOMAIN ) . '</a>' ); ?>
								</span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="answers_per_page"><?php _e( 'Answers Per Page', QA_TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input style="width:100px" name="answers_per_page" value="<?php echo @$options[ 'answers_per_page' ]; ?>" />&nbsp;&nbsp;&nbsp;<span class="description"><?php _e( 'If left empty: 20', QA_TEXTDOMAIN ); ?></span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="disable_editor"><?php _e( 'Disable WP Editor', QA_TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type="checkbox" name="disable_editor" <?php if ( @$options[ "disable_editor" ] ) echo "checked='checked'"; ?> />
								&nbsp;&nbsp;&nbsp;
								<span class="description">
<?php _e( 'If you are having issues with Buddypress or if you don\'t want submissions to be formatted, check this checkbox. Then, textarea will be used for question and answer forms instead of the WP editor.', QA_TEXTDOMAIN ); ?>
								</span>
							</td>
						</tr>

					</table>
				</div>
			</div>

			<p class="submit">
				<input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Everything on this Page', QA_TEXTDOMAIN ); ?>">
				<img class="ajax-loader" src="<?php echo QA_PLUGIN_URL . 'ui-admin/images/ajax-loader.gif'; ?>" />
				<span style="display:none;font-weight:bold;color:darkgreen" class="qa_settings_saved"><?php _e( 'Settings saved', QA_TEXTDOMAIN ); ?></span>
			</p>

			<div class="postbox <?php echo $this->postbox_classes( 'qa_access' ) ?>" id="qa_access">
				<h3 class='hndle'><span><?php _e( 'Accessibility Settings', QA_TEXTDOMAIN ) ?></span></h3>


				<div class="inside">

					<table class="form-table">

						<tr>
							<th>
								<label for="roles"><?php _e( 'Assign Capabilities', QA_TEXTDOMAIN ) ?></label>
								<img class="ajax-loader" src="<?php echo QA_PLUGIN_URL . 'ui-admin/images/ajax-loader.gif'; ?>" />
							</th>
							<td>
								<select id="roles" name="roles">
<?php wp_dropdown_roles( @$options[ "selected_role" ] ); ?>
								</select>
								<span class="description"><?php _e( 'This list has all the user roles of your website. As you make a new selection, capability of that role will be displayed. Select a role to which you want to assign WP Q&A capabilities.', QA_TEXTDOMAIN ); ?></span>

								<br /><br />

								<div id="capabilities">
<?php foreach ( $GLOBALS[ '_qa_core_admin' ]->capability_map as $capability => $description ): ?>
										<input id="<?php echo $capability ?>_checkbox" type="checkbox" name="capabilities[<?php echo $capability; ?>]" value="1" />
										<span class="description <?php echo $capability ?>"><?php echo $description; ?></span>
										<br />
<?php endforeach; ?>
								</div>
							</td>
						</tr>

						<tr>
							<th>
								<label for="visitor_method"><?php _e( 'After Visitor Submits a Question or Answer', QA_TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<select id="visitor_method" name="method">
<?php
if ( isset( $options[ 'method' ] ) )
	$method		 = $options[ 'method' ];
else
	$method		 = '';
?>
									<option value="claim" <?php if ( $method != 'assign' ) echo "selected='selected'" ?>><?php _e( 'He is asked for registration', QA_TEXTDOMAIN ) ?></option>
									<option value="assign" <?php if ( $method == 'assign' ) echo "selected='selected'" ?>><?php _e( 'Question is assigned to a user', QA_TEXTDOMAIN ) ?></option>
								</select>
								&nbsp;
								<span id="assigned_to" <?php if ( $method != 'assign' ) echo "style='display:none'" ?>>
<?php
if ( isset( $options[ 'assigned_to' ] ) )
	$selected	 = $options[ 'assigned_to' ];
else
	$selected	 = 0;
_e( 'Assign to: ', QA_TEXTDOMAIN );
wp_dropdown_users( array( 'name' => 'assigned_to', 'selected' => $selected ) );
?>
								</span>
								<br />
								<span class="description">
<?php _e( 'Every question and answer should have an author. If you want to let the visitor submit a question or answer without the need for registration, you can assign a preset author.', QA_TEXTDOMAIN ) ?>
								</span>
							</td>
						</tr>


						<tr>
							<th>
								<label for="thank_you_page"><?php _e( 'Thank You Page', QA_TEXTDOMAIN ) ?></label>
							</th>
							<td>
<?php
if ( isset( $options[ 'thank_you' ] ) )
	$selected	 = $options[ 'thank_you' ];
else
	$selected	 = 0;
wp_dropdown_pages( array( 'name' => 'thank_you', 'selected' => $selected ) );
?>
								<br />
								<span class="description">
									<?php _e( 'If questions and are saved as pending, user will be redirected to this page after submitting a question or answer.', QA_TEXTDOMAIN ) ?>
								</span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="unauthorized"><?php _e( 'Unauthorized Access Page', QA_TEXTDOMAIN ) ?></label>
							</th>

							<td>
								<?php
								if ( isset( $options[ 'unauthorized' ] ) )
									$selected	 = $options[ 'unauthorized' ];
								else
									$selected	 = 0;
								wp_dropdown_pages( array( 'name' => 'unauthorized', 'selected' => $selected ) );
								?>
								<br />
								<span class="description">
									<?php _e( 'If a user tries to access a page he should not access, he will be redirected to this page instead.', QA_TEXTDOMAIN ) ?>
								</span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="report"><?php _e( 'Report reasons', QA_TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type="text" style="width:200px" name="report_reasons" value="<?php if ( isset( $options[ "report_reasons" ] ) ) echo stripslashes( $options[ "report_reasons" ] ) ?>" />
								<br />
								<span class="description">
									<?php _e( 'Enter reasons of reporting to be chosen by the user each one separated by comma, e.g. Spam,Language. If left empty, user will not be asked to select a report reason.', QA_TEXTDOMAIN ) ?>
								</span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="report"><?php _e( 'Use Captcha', QA_TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type="checkbox" name="captcha" value="1" <?php if ( isset( $options[ "captcha" ] ) && $options[ "captcha" ] ) echo 'checked="checked"' ?> />
								&nbsp;
								<span class="description">
									<?php _e( 'Whether to use Captcha verification while submitting.', QA_TEXTDOMAIN ) ?>
									<?php
									if ( !qa_is_captcha_usable() )
										_e( 'Note: Your php installation does not let Captcha usage.', QA_TEXTDOMAIN );
									?>
								</span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="report"><?php _e( 'Email address on report', QA_TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type="text" style="width:200px" name="report_email" value="<?php if ( isset( $options[ "report_email" ] ) ) echo $options[ "report_email" ] ?>" />
								<br />
								<span class="description">
									<?php _e( 'Email address that will be notified in case a question or answer is reported. Leaving empty will disable notification. Note: If a question or answer is reported more than once, only the first report will be emailed, but number of reports and latest reporter will be saved.', QA_TEXTDOMAIN ) ?>
								</span>
							</td>
						</tr>


						<?php
						global $bp;
						if ( is_object( $bp ) ) :
							?>
							<tr>
								<th>
									<label for="bp_comment_hide"><?php _e( 'Disable Reply in Activity Stream', QA_TEXTDOMAIN ) ?></label>
								</th>
								<td>
									<input type="checkbox" name="bp_comment_hide" value="1" <?php if ( @$options[ "bp_comment_hide" ] ) echo "checked='checked'" ?>/>
									&nbsp;&nbsp;&nbsp;
									<span class="description">
										<?php _e( 'Checking this will disable commenting for the question asked notification in Buddypress Activity Stream, forsing user to answer the question through plugin generated pages.', QA_TEXTDOMAIN ) ?>
									</span>
								</td>
							</tr>

						<?php endif; ?>
					</table>
				</div>
			</div>

			<p class="submit">
				<input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Everything on this Page', QA_TEXTDOMAIN ); ?>">
				<img class="ajax-loader" src="<?php echo QA_PLUGIN_URL . 'ui-admin/images/ajax-loader.gif'; ?>" />
				<span style="display:none;font-weight:bold;color:darkgreen" class="qa_settings_saved"><?php _e( 'Settings saved', QA_TEXTDOMAIN ); ?></span>
			</p>

			<div class="postbox <?php echo $this->postbox_classes( 'qa_notification' ) ?>" id="qa_notification">
				<h3 class='hndle'><span><?php _e( 'Notification Settings', QA_TEXTDOMAIN ) ?></span></h3>


				<div class="inside">

					<table class="form-table">

						<tr>
							<th><label for="cc_admin"><?php _e( 'CC the Administrator:', QA_TEXTDOMAIN ); ?></label></th>
							<td>
								<input type="hidden" name="qa_cc_admin" value="0" />
								<input type="checkbox" id="qa_cc_admin" name="qa_cc_admin" value="1" <?php checked( get_option( 'qa_cc_admin', '0' ) ); ?> />
								<span class="description"><?php _e( 'cc the administrator', QA_TEXTDOMAIN ); ?></span>
							</td>
						</tr>
						<tr>
							<th>
								<label for="qa_email_notification_subject"><?php _e( 'Notification E-mail Subject', QA_TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input style="width:200px" id="qa_email_notification_subject" name="qa_email_notification_subject" value="<?php echo get_option( 'qa_email_notification_subject', $qa_email_notification_subject ); ?>" />
								<br/>
								<span class="description">
									<?php _e( 'Variables:', 'messaging' ); ?> SITE_NAME
								</span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="qa_email_notification_content"><?php _e( 'Notification E-mail Content', QA_TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<textarea class="qa-full" id="qa_email_notification_content" name="qa_email_notification_content" rows="6" cols="120"><?php echo get_option( 'qa_email_notification_content', $qa_email_notification_content ); ?></textarea>
								<br/>
								<span class="description">
									<?php _e( 'Variables:', 'messaging' ); ?> TO_USER, SITE_NAME, SITE_URL, QUESTION_TITLE, QUESTION_DESCRIPTION, QUESTION_LINK
								</span>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<p class="submit">
				<input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Everything on this Page', QA_TEXTDOMAIN ); ?>">
				<img class="ajax-loader" src="<?php echo QA_PLUGIN_URL . 'ui-admin/images/ajax-loader.gif'; ?>" />
				<span style="display:none;font-weight:bold;color:darkgreen" class="qa_settings_saved"><?php _e( 'Settings saved', QA_TEXTDOMAIN ); ?></span>
			</p>


		</form>
	</div>
</div>
