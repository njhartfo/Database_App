<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'Orders';

		/* data for selected record, or defaults if none is selected */
		var data = {
			Customer: <?php echo json_encode(['id' => $rdata['Customer'], 'value' => $rdata['Customer'], 'text' => $jdata['Customer']]); ?>,
			Employee: <?php echo json_encode(['id' => $rdata['Employee'], 'value' => $rdata['Employee'], 'text' => $jdata['Employee']]); ?>,
			Product: <?php echo json_encode(['id' => $rdata['Product'], 'value' => $rdata['Product'], 'text' => $jdata['Product']]); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for Customer */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'Customer' && d.id == data.Customer.id)
				return { results: [ data.Customer ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for Employee */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'Employee' && d.id == data.Employee.id)
				return { results: [ data.Employee ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for Product */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'Product' && d.id == data.Product.id)
				return { results: [ data.Product ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

