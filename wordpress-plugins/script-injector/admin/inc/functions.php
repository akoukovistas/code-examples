<?php

/**
 * This is the callback for the custom meta boxes.
 *
 * @param $post WP_Post
 */
function sin_meta_box_callback( $post ) {
	?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="script-injector-post-schema">Schema</label></th>
			<td>
				<textarea name="script-injector-post-schema" style="min-height:250px;" cols="80"><?php echo get_post_meta( $post->ID,'sin_post_schema', true ); ?></textarea>
			</td>
		</tr>
	</table>
<?php
}