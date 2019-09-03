<div class="wrap">

	<h2>Script Injector Settings</h2>

	<form method="post" action="options.php">

		<?php @settings_fields( 'script-injector-settings' ); ?>
		<?php @do_settings_sections( 'script-injector-settings' ); ?>

		<hr/>

		<h3>Head</h3>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="script-injector-head-schema">Schema</label></th>
				<td>
                    <textarea name="script-injector-head-schema" style="min-height:250px;" cols="80"><?php echo $schema = get_option( 'script-injector-head-schema' ); ?></textarea>
				</td>
			</tr>
		</table>
        <table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="script-injector-head-tracking">Tracking Codes</label>
                    <p>Please do not include any HTML tags, they will be stripped out</p>
                </th>
				<td>
                    <textarea name="script-injector-head-tracking"  cols="80" style="min-height:150px;"><?php echo $schema = get_option( 'script-injector-head-tracking' ); ?></textarea>
				</td>
			</tr>
		</table>

		<hr/>

        <h3>Body</h3>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="script-injector-body-scripts">Tracking Codes</label>
                    <p>Warning: whatever you insert in this field will show up on the front end immediately and may break the site :)</p>
                </th>
                <td>
                    <textarea name="script-injector-body-scripts" style="min-height:250px;" cols="80"><?php echo $schema = get_option( 'script-injector-body-scripts' ); ?></textarea>
                </td>
            </tr>
        </table>
        <hr/>

		<?php @submit_button(); ?>

	</form>

</div>