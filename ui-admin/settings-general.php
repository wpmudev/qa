<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php $options = $this->get_options('general_settings'); ?>

<div class="wrap">
    <?php screen_icon('options-general'); ?>

    <?php $this->render_admin( 'navigation', array( 'sub' => 'general' ) ); ?>
    
    <form action="" method="post" class="qa-general">

        <table class="form-table">
            <tr>
                <th>
                    <label for="access"><?php _e('Access', $this->text_domain) ?></label>
                </th>
                <td>
                    <input type="checkbox" id="access" name="access" value="1" <?php if ( !empty( $options['access'] ) ) echo 'checked="checked"'; ?>  />
                    <span class="description"><?php _e('Only members can view answers.', $this->text_domain); ?></span>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="moderation"><?php _e('Moderation', $this->text_domain) ?></label>
                </th>
                <td>
                    <input type="checkbox" id="moderation" name="moderation" value="1" <?php if ( isset( $options['moderation'] ) ) echo 'checked="checked"'; ?>  />
                    <span class="description"><?php _e('Answers are held for moderation.', $this->text_domain); ?></span>
                </td>
            </tr>
        </table>

		<br /><br />
        <p class="submit">
            <?php wp_nonce_field('verify'); ?>
            <input type="hidden" name="key" value="general_settings" />
            <input type="submit" class="button-primary" name="save" value="Save Changes">
        </p>
        
    </form>
    
</div>
