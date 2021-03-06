<?php
/**
 * This file shows the location admin page.
 *
 * @package    TARecord/
 * @author     Tanner Record <tanner.record@gmail.com>
 * @license    GPL2
 * @since      File available since Release 1.0.0
 */

use TARecord\LocationAddonForGravityForms\FormLocationsTable;
?>

<div class="wrap">

	<h1>Form Locations</h1>

	<?php
	$locations_table = new FormLocationsTable();
	$locations_table->prepare_items();
	if ( ! empty( $locations_table->items ) ) {

		$locations_table->display();

	} else {
		?>
		<h2>No Forms Found</h2>
		<?php
	}
	?>

	<form class="lagf-scan-form" method="post">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Scan For Forms">
	</form>

</div>
