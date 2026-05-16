<main class="main-min-height">
	<div class="f404">
		<img src="<?php echo ZIB_TEMPLATE_DIRECTORY_URI ?>/img/404.svg">
	</div>
	<div class="theme-box box-body main-search">
		<?php
		if (_pz('404_search_s', true)) {
			echo zib_get_main_search();
		}
		?>
	</div>
</main>