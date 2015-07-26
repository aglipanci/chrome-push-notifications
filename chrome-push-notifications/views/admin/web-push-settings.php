<div class="wrap">

<script>
    var file_frame;

    jQuery(document).ready(function ($) {

        $('.upload').click(function () {
            var that = $(this);
            var img = $(this).siblings('.img');
            var url = $(this).siblings('.image_url');

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Select Image',
                button: {
                    text: 'Select Image'
                },
                multiple: false  // Set to true to allow multiple files to be selected
            });

            // When an image is selected, run a callback.
            file_frame.on('select', function () {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();

                img.html('<img style="width:200px;height:200px" src="' + attachment.url + '" />');

                url.val(attachment.url);
                // Do something with attachment.id and/or attachment.url here
            });

            // Finally, open the modal
            file_frame.open();
        });
    });
</script>

<h2>Chrome Web Push Settings</h2>

<?php

if(isset($_POST) && isset($_POST['wp_chrome_settings']) && wp_verify_nonce($_POST['wp_chrome_settings'], 'wp_chrome_settings' )) {
    if(isset($_POST['web_push_project_number']) && !empty($_POST['web_push_project_number']) && is_numeric($_POST['web_push_project_number'])) {
        update_option('web_push_project_number', sanitize_text_field($_POST['web_push_project_number']));
        $form_url = 'admin.php?page=chrome-push';

        $manifest_file = '{"gcm_sender_id": "'.get_option('web_push_project_number').'"}';
        WPChromePush::writeFile($form_url, $manifest_file, 'manifest.json');
    }

    if(isset($_POST['web_push_api_key']) && !empty($_POST['web_push_api_key'])) {
        update_option('web_push_api_key', sanitize_text_field($_POST['web_push_api_key']));
    }

    if(isset($_POST['web_push_debuger']) && !empty($_POST['web_push_debuger'])) {
        update_option('web_push_debuger', true);
    } else {
        update_option('web_push_debuger', false);
    }
    if(isset($_POST['web_push_icon']) && !empty($_POST['web_push_icon'])) {
        update_option('web_push_icon', sanitize_text_field($_POST['web_push_icon']));
        WPChromePush::writeServiceWorker();
    }
    if(isset($_POST['web_push_post_types']) && !empty($_POST['web_push_post_types']) && is_array($_POST['web_push_post_types'])) {
        update_option('web_push_post_types', $_POST['web_push_post_types']);
    }
}

?>

<form method="post" action="">
    <?php wp_nonce_field('wp_chrome_settings', 'wp_chrome_settings'); ?>
    <h3>Web Push GCM API Credentials</h3>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Project Number</th>
        <td><input type="text" name="web_push_project_number" value="<?php echo esc_attr( get_option('web_push_project_number') ); ?>" placeholder="Project Number"/></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">GCM API Key</th>
        <td><input type="password" name="web_push_api_key" value="<?php echo esc_attr( get_option('web_push_api_key') ); ?>" placeholder="API Key"/></td>
        </tr>
    </table>

    <h3>General Configurations</h3>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Enable Debuger</th>
        <td><input type="checkbox" name="web_push_debuger" value="true" <?php checked(get_option('web_push_debuger')); ?> /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Post Types</th>
        <td>  
            <?php
            $post_types = get_post_types( array( 'public' => true ), 'objects' );
            $selected_post_types = get_option('web_push_post_types');

            foreach ( $post_types as $key => $post_type ) { 
                ?>

                <input type="checkbox" value="<?php echo $key ?>" name="web_push_post_types[]" <?php echo is_array($selected_post_types) && in_array($key, $selected_post_types) ? 'checked' : ''; ?>> <b> <?php echo $post_type->labels->name ?> </b><br/>

                <?php

            }

            ?>

        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Icon</th>
        <td>  
            <input type="text" name="web_push_icon" id="logo" class="logo image_url" value="<?php echo get_option('web_push_icon') ;?>"/>
                        <a href="javascript:void(0)" class="button upload">Upload</a>

                        <div class="img" width="200px">
                           <img style="width:200px;height:200px" src="<?php echo get_option('web_push_icon'); ?>"> 
                        </div>

        </td>
        </tr>
    </table>


    <?php submit_button(); ?>


</div>
