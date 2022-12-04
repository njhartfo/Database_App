			<!-- Add footer template above here -->
			<div class="clearfix"></div>
			<?php if(!Request::val('Embedded')) { ?>
				<div style="height: 70px;" class="hidden-print"></div>
			<?php } ?>

			<?php if(!Request::val('Embedded')) { ?>
				<!-- AppGini powered by notice -->
				<div style="height: 60px;" class="hidden-print"></div>
				<nav class="navbar navbar-default navbar-fixed-bottom" role="navigation">
					<p class="navbar-text"><small>
						This application was created using the free trial version of <a class="navbar-link" href="https://bigprof.com/appgini/?free_trial_footnote" target="_blank">AppGini 22.14</a>. <a class="btn btn-success btn-sm" href="https://bigprof.com/appgini/order?free_trial_footnote">Upgrade to AppGini Pro</a>
					</small></p>
				</nav>
			<?php } ?>

		</div> <!-- /div class="container" -->
		<?php if(!defined('APPGINI_SETUP') && is_file(__DIR__ . '/hooks/footer-extras.php')) { include(__DIR__ . '/hooks/footer-extras.php'); } ?>
		<script src="<?php echo PREPEND_PATH; ?>resources/lightbox/js/lightbox.min.js"></script>
	</body>
</html>