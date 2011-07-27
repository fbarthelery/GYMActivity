<?php
// Variable to configure global behaviour
$header_title = 'GenY Mobile - Home';
$required_group_rights = 5;

include_once 'header.php';
include_once 'menu.php';


?>

<div class="page_title">
	<img src="images/default/home.png"/><p>Home</p>
</div>

<div id="maindock">
	<ul>
		<?php
			include 'backend/widgets/notifications.dock.widget.php';
			include 'backend/widgets/cra_add.dock.widget.php';
			include 'backend/widgets/cra_validation.dock.widget.php';
			include 'backend/widgets/conges_add.dock.widget.php';
			include 'backend/widgets/conges_validation.dock.widget.php';
		?>
	</ul>
</div>

<?php
include_once 'footer.php';
?>
