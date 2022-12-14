<?php

// Data functions (insert, update, delete, form) for table Orders


function Orders_insert(&$error_message = '') {
	global $Translation;

	// mm: can member insert record?
	$arrPerm = getTablePermissions('Orders');
	if(!$arrPerm['insert']) return false;

	$data = [
		'Customer' => Request::lookup('Customer', ''),
		'Employee' => Request::lookup('Employee', ''),
		'Product' => Request::lookup('Product', ''),
		'OrderDate' => mysql_datetime(Request::val('OrderDate', '')),
	];


	// hook: Orders_before_insert
	if(function_exists('Orders_before_insert')) {
		$args = [];
		if(!Orders_before_insert($data, getMemberInfo(), $args)) {
			if(isset($args['error_message'])) $error_message = $args['error_message'];
			return false;
		}
	}

	$error = '';
	// set empty fields to NULL
	$data = array_map(function($v) { return ($v === '' ? NULL : $v); }, $data);
	insert('Orders', backtick_keys_once($data), $error);
	if($error) {
		$error_message = $error;
		return false;
	}

	$recID = db_insert_id(db_link());
	// enforce pk zerofill
	$recID = str_pad($recID, sqlValue("SELECT LENGTH(`OrderID`) FROM `Orders` LIMIT 1"), '0', STR_PAD_LEFT);

	update_calc_fields('Orders', $recID, calculated_fields()['Orders']);

	// hook: Orders_after_insert
	if(function_exists('Orders_after_insert')) {
		$res = sql("SELECT * FROM `Orders` WHERE `OrderID`='" . makeSafe($recID, false) . "' LIMIT 1", $eo);
		if($row = db_fetch_assoc($res)) {
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = makeSafe($recID, false);
		$args = [];
		if(!Orders_after_insert($data, getMemberInfo(), $args)) { return $recID; }
	}

	// mm: save ownership data
	set_record_owner('Orders', $recID, getLoggedMemberID());

	// if this record is a copy of another record, copy children if applicable
	if(strlen(Request::val('SelectedID'))) Orders_copy_children($recID, Request::val('SelectedID'));

	return $recID;
}

function Orders_copy_children($destination_id, $source_id) {
	global $Translation;
	$requests = []; // array of curl handlers for launching insert requests
	$eo = ['silentErrors' => true];
	$safe_sid = makeSafe($source_id);

	// launch requests, asynchronously
	curl_batch($requests);
}

function Orders_delete($selected_id, $AllowDeleteOfParents = false, $skipChecks = false) {
	// insure referential integrity ...
	global $Translation;
	$selected_id = makeSafe($selected_id);

	// mm: can member delete record?
	if(!check_record_permission('Orders', $selected_id, 'delete')) {
		return $Translation['You don\'t have enough permissions to delete this record'];
	}

	// hook: Orders_before_delete
	if(function_exists('Orders_before_delete')) {
		$args = [];
		if(!Orders_before_delete($selected_id, $skipChecks, getMemberInfo(), $args))
			return $Translation['Couldn\'t delete this record'] . (
				!empty($args['error_message']) ?
					'<div class="text-bold">' . strip_tags($args['error_message']) . '</div>'
					: '' 
			);
	}

	sql("DELETE FROM `Orders` WHERE `OrderID`='{$selected_id}'", $eo);

	// hook: Orders_after_delete
	if(function_exists('Orders_after_delete')) {
		$args = [];
		Orders_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("DELETE FROM `membership_userrecords` WHERE `tableName`='Orders' AND `pkValue`='{$selected_id}'", $eo);
}

function Orders_update(&$selected_id, &$error_message = '') {
	global $Translation;

	// mm: can member edit record?
	if(!check_record_permission('Orders', $selected_id, 'edit')) return false;

	$data = [
		'Customer' => Request::lookup('Customer', ''),
		'Employee' => Request::lookup('Employee', ''),
		'Product' => Request::lookup('Product', ''),
		'OrderDate' => mysql_datetime(Request::val('OrderDate', '')),
	];

	// get existing values
	$old_data = getRecord('Orders', $selected_id);
	if(is_array($old_data)) {
		$old_data = array_map('makeSafe', $old_data);
		$old_data['selectedID'] = makeSafe($selected_id);
	}

	$data['selectedID'] = makeSafe($selected_id);

	// hook: Orders_before_update
	if(function_exists('Orders_before_update')) {
		$args = ['old_data' => $old_data];
		if(!Orders_before_update($data, getMemberInfo(), $args)) {
			if(isset($args['error_message'])) $error_message = $args['error_message'];
			return false;
		}
	}

	$set = $data; unset($set['selectedID']);
	foreach ($set as $field => $value) {
		$set[$field] = ($value !== '' && $value !== NULL) ? $value : NULL;
	}

	if(!update(
		'Orders', 
		backtick_keys_once($set), 
		['`OrderID`' => $selected_id], 
		$error_message
	)) {
		echo $error_message;
		echo '<a href="Orders_view.php?SelectedID=' . urlencode($selected_id) . "\">{$Translation['< back']}</a>";
		exit;
	}


	$eo = ['silentErrors' => true];

	update_calc_fields('Orders', $data['selectedID'], calculated_fields()['Orders']);

	// hook: Orders_after_update
	if(function_exists('Orders_after_update')) {
		$res = sql("SELECT * FROM `Orders` WHERE `OrderID`='{$data['selectedID']}' LIMIT 1", $eo);
		if($row = db_fetch_assoc($res)) $data = array_map('makeSafe', $row);

		$data['selectedID'] = $data['OrderID'];
		$args = ['old_data' => $old_data];
		if(!Orders_after_update($data, getMemberInfo(), $args)) return;
	}

	// mm: update ownership data
	sql("UPDATE `membership_userrecords` SET `dateUpdated`='" . time() . "' WHERE `tableName`='Orders' AND `pkValue`='" . makeSafe($selected_id) . "'", $eo);
}

function Orders_form($selected_id = '', $AllowUpdate = 1, $AllowInsert = 1, $AllowDelete = 1, $separateDV = 0, $TemplateDV = '', $TemplateDVP = '') {
	// function to return an editable form for a table records
	// and fill it with data of record whose ID is $selected_id. If $selected_id
	// is empty, an empty form is shown, with only an 'Add New'
	// button displayed.

	global $Translation;
	$eo = ['silentErrors' => true];
	$noUploads = null;
	$row = $urow = $jsReadOnly = $jsEditable = $lookups = null;

	$noSaveAsCopy = false;

	// mm: get table permissions
	$arrPerm = getTablePermissions('Orders');
	if(!$arrPerm['insert'] && $selected_id == '')
		// no insert permission and no record selected
		// so show access denied error unless TVDV
		return $separateDV ? $Translation['tableAccessDenied'] : '';
	$AllowInsert = ($arrPerm['insert'] ? true : false);
	// print preview?
	$dvprint = false;
	if(strlen($selected_id) && Request::val('dvprint_x') != '') {
		$dvprint = true;
	}

	$filterer_Customer = Request::val('filterer_Customer');
	$filterer_Employee = Request::val('filterer_Employee');
	$filterer_Product = Request::val('filterer_Product');

	// populate filterers, starting from children to grand-parents

	// unique random identifier
	$rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
	// combobox: Customer
	$combo_Customer = new DataCombo;
	// combobox: Employee
	$combo_Employee = new DataCombo;
	// combobox: Product
	$combo_Product = new DataCombo;

	if($selected_id) {
		// mm: check member permissions
		if(!$arrPerm['view']) return $Translation['tableAccessDenied'];

		// mm: who is the owner?
		$ownerGroupID = sqlValue("SELECT `groupID` FROM `membership_userrecords` WHERE `tableName`='Orders' AND `pkValue`='" . makeSafe($selected_id) . "'");
		$ownerMemberID = sqlValue("SELECT LCASE(`memberID`) FROM `membership_userrecords` WHERE `tableName`='Orders' AND `pkValue`='" . makeSafe($selected_id) . "'");

		if($arrPerm['view'] == 1 && getLoggedMemberID() != $ownerMemberID) return $Translation['tableAccessDenied'];
		if($arrPerm['view'] == 2 && getLoggedGroupID() != $ownerGroupID) return $Translation['tableAccessDenied'];

		// can edit?
		$AllowUpdate = 0;
		if(($arrPerm['edit'] == 1 && $ownerMemberID == getLoggedMemberID()) || ($arrPerm['edit'] == 2 && $ownerGroupID == getLoggedGroupID()) || $arrPerm['edit'] == 3) {
			$AllowUpdate = 1;
		}

		$res = sql("SELECT * FROM `Orders` WHERE `OrderID`='" . makeSafe($selected_id) . "'", $eo);
		if(!($row = db_fetch_array($res))) {
			return error_message($Translation['No records found'], 'Orders_view.php', false);
		}
		$combo_Customer->SelectedData = $row['Customer'];
		$combo_Employee->SelectedData = $row['Employee'];
		$combo_Product->SelectedData = $row['Product'];
		$urow = $row; /* unsanitized data */
		$row = array_map('safe_html', $row);
	} else {
		$filterField = Request::val('FilterField');
		$filterOperator = Request::val('FilterOperator');
		$filterValue = Request::val('FilterValue');
		$combo_Customer->SelectedData = $filterer_Customer;
		$combo_Employee->SelectedData = $filterer_Employee;
		$combo_Product->SelectedData = $filterer_Product;
	}
	$combo_Customer->HTML = '<span id="Customer-container' . $rnd1 . '"></span><input type="hidden" name="Customer" id="Customer' . $rnd1 . '" value="' . html_attr($combo_Customer->SelectedData) . '">';
	$combo_Customer->MatchText = '<span id="Customer-container-readonly' . $rnd1 . '"></span><input type="hidden" name="Customer" id="Customer' . $rnd1 . '" value="' . html_attr($combo_Customer->SelectedData) . '">';
	$combo_Employee->HTML = '<span id="Employee-container' . $rnd1 . '"></span><input type="hidden" name="Employee" id="Employee' . $rnd1 . '" value="' . html_attr($combo_Employee->SelectedData) . '">';
	$combo_Employee->MatchText = '<span id="Employee-container-readonly' . $rnd1 . '"></span><input type="hidden" name="Employee" id="Employee' . $rnd1 . '" value="' . html_attr($combo_Employee->SelectedData) . '">';
	$combo_Product->HTML = '<span id="Product-container' . $rnd1 . '"></span><input type="hidden" name="Product" id="Product' . $rnd1 . '" value="' . html_attr($combo_Product->SelectedData) . '">';
	$combo_Product->MatchText = '<span id="Product-container-readonly' . $rnd1 . '"></span><input type="hidden" name="Product" id="Product' . $rnd1 . '" value="' . html_attr($combo_Product->SelectedData) . '">';

	ob_start();
	?>

	<script>
		// initial lookup values
		AppGini.current_Customer__RAND__ = { text: "", value: "<?php echo addslashes($selected_id ? $urow['Customer'] : htmlspecialchars($filterer_Customer, ENT_QUOTES)); ?>"};
		AppGini.current_Employee__RAND__ = { text: "", value: "<?php echo addslashes($selected_id ? $urow['Employee'] : htmlspecialchars($filterer_Employee, ENT_QUOTES)); ?>"};
		AppGini.current_Product__RAND__ = { text: "", value: "<?php echo addslashes($selected_id ? $urow['Product'] : htmlspecialchars($filterer_Product, ENT_QUOTES)); ?>"};

		jQuery(function() {
			setTimeout(function() {
				if(typeof(Customer_reload__RAND__) == 'function') Customer_reload__RAND__();
				if(typeof(Employee_reload__RAND__) == 'function') Employee_reload__RAND__();
				if(typeof(Product_reload__RAND__) == 'function') Product_reload__RAND__();
			}, 50); /* we need to slightly delay client-side execution of the above code to allow AppGini.ajaxCache to work */
		});
		function Customer_reload__RAND__() {
		<?php if(($AllowUpdate || $AllowInsert) && !$dvprint) { ?>

			$j("#Customer-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c) {
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_Customer__RAND__.value, t: 'Orders', f: 'Customer' },
						success: function(resp) {
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="Customer"]').val(resp.results[0].id);
							$j('[id=Customer-container-readonly__RAND__]').html('<span class="match-text" id="Customer-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Customers_view_parent]').hide(); } else { $j('.btn[id=Customers_view_parent]').show(); }


							if(typeof(Customer_update_autofills__RAND__) == 'function') Customer_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term) { return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 5,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page) { return { s: term, p: page, t: 'Orders', f: 'Customer' }; },
					results: function(resp, page) { return resp; }
				},
				escapeMarkup: function(str) { return str; }
			}).on('change', function(e) {
				AppGini.current_Customer__RAND__.value = e.added.id;
				AppGini.current_Customer__RAND__.text = e.added.text;
				$j('[name="Customer"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Customers_view_parent]').hide(); } else { $j('.btn[id=Customers_view_parent]').show(); }


				if(typeof(Customer_update_autofills__RAND__) == 'function') Customer_update_autofills__RAND__();
			});

			if(!$j("#Customer-container__RAND__").length) {
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_Customer__RAND__.value, t: 'Orders', f: 'Customer' },
					success: function(resp) {
						$j('[name="Customer"]').val(resp.results[0].id);
						$j('[id=Customer-container-readonly__RAND__]').html('<span class="match-text" id="Customer-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Customers_view_parent]').hide(); } else { $j('.btn[id=Customers_view_parent]').show(); }

						if(typeof(Customer_update_autofills__RAND__) == 'function') Customer_update_autofills__RAND__();
					}
				});
			}

		<?php } else { ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_Customer__RAND__.value, t: 'Orders', f: 'Customer' },
				success: function(resp) {
					$j('[id=Customer-container__RAND__], [id=Customer-container-readonly__RAND__]').html('<span class="match-text" id="Customer-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Customers_view_parent]').hide(); } else { $j('.btn[id=Customers_view_parent]').show(); }

					if(typeof(Customer_update_autofills__RAND__) == 'function') Customer_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
		function Employee_reload__RAND__() {
		<?php if(($AllowUpdate || $AllowInsert) && !$dvprint) { ?>

			$j("#Employee-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c) {
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_Employee__RAND__.value, t: 'Orders', f: 'Employee' },
						success: function(resp) {
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="Employee"]').val(resp.results[0].id);
							$j('[id=Employee-container-readonly__RAND__]').html('<span class="match-text" id="Employee-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Employees_view_parent]').hide(); } else { $j('.btn[id=Employees_view_parent]').show(); }


							if(typeof(Employee_update_autofills__RAND__) == 'function') Employee_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term) { return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 5,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page) { return { s: term, p: page, t: 'Orders', f: 'Employee' }; },
					results: function(resp, page) { return resp; }
				},
				escapeMarkup: function(str) { return str; }
			}).on('change', function(e) {
				AppGini.current_Employee__RAND__.value = e.added.id;
				AppGini.current_Employee__RAND__.text = e.added.text;
				$j('[name="Employee"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Employees_view_parent]').hide(); } else { $j('.btn[id=Employees_view_parent]').show(); }


				if(typeof(Employee_update_autofills__RAND__) == 'function') Employee_update_autofills__RAND__();
			});

			if(!$j("#Employee-container__RAND__").length) {
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_Employee__RAND__.value, t: 'Orders', f: 'Employee' },
					success: function(resp) {
						$j('[name="Employee"]').val(resp.results[0].id);
						$j('[id=Employee-container-readonly__RAND__]').html('<span class="match-text" id="Employee-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Employees_view_parent]').hide(); } else { $j('.btn[id=Employees_view_parent]').show(); }

						if(typeof(Employee_update_autofills__RAND__) == 'function') Employee_update_autofills__RAND__();
					}
				});
			}

		<?php } else { ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_Employee__RAND__.value, t: 'Orders', f: 'Employee' },
				success: function(resp) {
					$j('[id=Employee-container__RAND__], [id=Employee-container-readonly__RAND__]').html('<span class="match-text" id="Employee-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Employees_view_parent]').hide(); } else { $j('.btn[id=Employees_view_parent]').show(); }

					if(typeof(Employee_update_autofills__RAND__) == 'function') Employee_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
		function Product_reload__RAND__() {
		<?php if(($AllowUpdate || $AllowInsert) && !$dvprint) { ?>

			$j("#Product-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c) {
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_Product__RAND__.value, t: 'Orders', f: 'Product' },
						success: function(resp) {
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="Product"]').val(resp.results[0].id);
							$j('[id=Product-container-readonly__RAND__]').html('<span class="match-text" id="Product-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Products_view_parent]').hide(); } else { $j('.btn[id=Products_view_parent]').show(); }


							if(typeof(Product_update_autofills__RAND__) == 'function') Product_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term) { return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 5,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page) { return { s: term, p: page, t: 'Orders', f: 'Product' }; },
					results: function(resp, page) { return resp; }
				},
				escapeMarkup: function(str) { return str; }
			}).on('change', function(e) {
				AppGini.current_Product__RAND__.value = e.added.id;
				AppGini.current_Product__RAND__.text = e.added.text;
				$j('[name="Product"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Products_view_parent]').hide(); } else { $j('.btn[id=Products_view_parent]').show(); }


				if(typeof(Product_update_autofills__RAND__) == 'function') Product_update_autofills__RAND__();
			});

			if(!$j("#Product-container__RAND__").length) {
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_Product__RAND__.value, t: 'Orders', f: 'Product' },
					success: function(resp) {
						$j('[name="Product"]').val(resp.results[0].id);
						$j('[id=Product-container-readonly__RAND__]').html('<span class="match-text" id="Product-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Products_view_parent]').hide(); } else { $j('.btn[id=Products_view_parent]').show(); }

						if(typeof(Product_update_autofills__RAND__) == 'function') Product_update_autofills__RAND__();
					}
				});
			}

		<?php } else { ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_Product__RAND__.value, t: 'Orders', f: 'Product' },
				success: function(resp) {
					$j('[id=Product-container__RAND__], [id=Product-container-readonly__RAND__]').html('<span class="match-text" id="Product-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=Products_view_parent]').hide(); } else { $j('.btn[id=Products_view_parent]').show(); }

					if(typeof(Product_update_autofills__RAND__) == 'function') Product_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
	</script>
	<?php

	$lookups = str_replace('__RAND__', $rnd1, ob_get_clean());


	// code for template based detail view forms

	// open the detail view template
	if($dvprint) {
		$template_file = is_file("./{$TemplateDVP}") ? "./{$TemplateDVP}" : './templates/Orders_templateDVP.html';
		$templateCode = @file_get_contents($template_file);
	} else {
		$template_file = is_file("./{$TemplateDV}") ? "./{$TemplateDV}" : './templates/Orders_templateDV.html';
		$templateCode = @file_get_contents($template_file);
	}

	// process form title
	$templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Order details', $templateCode);
	$templateCode = str_replace('<%%RND1%%>', $rnd1, $templateCode);
	$templateCode = str_replace('<%%EMBEDDED%%>', (Request::val('Embedded') ? 'Embedded=1' : ''), $templateCode);
	// process buttons
	if($AllowInsert) {
		if(!$selected_id) $templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-success" id="insert" name="insert_x" value="1" onclick="return Orders_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save New'] . '</button>', $templateCode);
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="insert" name="insert_x" value="1" onclick="return Orders_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
	} else {
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
	}

	// 'Back' button action
	if(Request::val('Embedded')) {
		$backAction = 'AppGini.closeParentModal(); return false;';
	} else {
		$backAction = '$j(\'form\').eq(0).attr(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;';
	}

	if($selected_id) {
		if(!Request::val('Embedded')) $templateCode = str_replace('<%%DVPRINT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="dvprint" name="dvprint_x" value="1" onclick="$j(\'form\').eq(0).prop(\'novalidate\', true); document.myform.reset(); return true;" title="' . html_attr($Translation['Print Preview']) . '"><i class="glyphicon glyphicon-print"></i> ' . $Translation['Print Preview'] . '</button>', $templateCode);
		if($AllowUpdate) {
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '<button type="submit" class="btn btn-success btn-lg" id="update" name="update_x" value="1" onclick="return Orders_validateData();" title="' . html_attr($Translation['Save Changes']) . '"><i class="glyphicon glyphicon-ok"></i> ' . $Translation['Save Changes'] . '</button>', $templateCode);
		} else {
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		}
		if(($arrPerm['delete'] == 1 && $ownerMemberID == getLoggedMemberID()) || ($arrPerm['delete'] == 2 && $ownerGroupID == getLoggedGroupID()) || $arrPerm['delete'] == 3) { // allow delete?
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '<button type="submit" class="btn btn-danger" id="delete" name="delete_x" value="1" title="' . html_attr($Translation['Delete']) . '"><i class="glyphicon glyphicon-trash"></i> ' . $Translation['Delete'] . '</button>', $templateCode);
		} else {
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		}
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>', $templateCode);
	} else {
		$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);

		// if not in embedded mode and user has insert only but no view/update/delete,
		// remove 'back' button
		if(
			$arrPerm['insert']
			&& !$arrPerm['update'] && !$arrPerm['delete'] && !$arrPerm['view']
			&& !Request::val('Embedded')
		)
			$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '', $templateCode);
		elseif($separateDV)
			$templateCode = str_replace(
				'<%%DESELECT_BUTTON%%>', 
				'<button
					type="submit" 
					class="btn btn-default" 
					id="deselect" 
					name="deselect_x" 
					value="1" 
					onclick="' . $backAction . '" 
					title="' . html_attr($Translation['Back']) . '">
						<i class="glyphicon glyphicon-chevron-left"></i> ' .
						$Translation['Back'] .
				'</button>',
				$templateCode
			);
		else
			$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '', $templateCode);
	}

	// set records to read only if user can't insert new records and can't edit current record
	if(($selected_id && !$AllowUpdate && !$AllowInsert) || (!$selected_id && !$AllowInsert)) {
		$jsReadOnly = '';
		$jsReadOnly .= "\tjQuery('#Customer').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
		$jsReadOnly .= "\tjQuery('#Customer_caption').prop('disabled', true).css({ color: '#555', backgroundColor: 'white' });\n";
		$jsReadOnly .= "\tjQuery('#Employee').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
		$jsReadOnly .= "\tjQuery('#Employee_caption').prop('disabled', true).css({ color: '#555', backgroundColor: 'white' });\n";
		$jsReadOnly .= "\tjQuery('#Product').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
		$jsReadOnly .= "\tjQuery('#Product_caption').prop('disabled', true).css({ color: '#555', backgroundColor: 'white' });\n";
		$jsReadOnly .= "\tjQuery('#OrderDate').parents('.input-group').replaceWith('<div class=\"form-control-static\" id=\"OrderDate\">' + (jQuery('#OrderDate').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('.select2-container').hide();\n";

		$noUploads = true;
	} elseif($AllowInsert) {
		$jsEditable = "\tjQuery('form').eq(0).data('already_changed', true);"; // temporarily disable form change handler
		$locale = isset($Translation['datetimepicker locale']) ? ", locale: '{$Translation['datetimepicker locale']}'" : '';
		$jsEditable .= "\tjQuery('#OrderDate').addClass('always_shown').parents('.input-group').datetimepicker({ toolbarPlacement: 'top', sideBySide: true, showClear: true, showTodayButton: true, showClose: true, icons: { close: 'glyphicon glyphicon-ok' }, format: AppGini.datetimeFormat('dt') {$locale} });";
		$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', false);"; // re-enable form change handler
	}

	// process combos
	$templateCode = str_replace('<%%COMBO(Customer)%%>', $combo_Customer->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(Customer)%%>', $combo_Customer->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(Customer)%%>', urlencode($combo_Customer->MatchText), $templateCode);
	$templateCode = str_replace('<%%COMBO(Employee)%%>', $combo_Employee->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(Employee)%%>', $combo_Employee->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(Employee)%%>', urlencode($combo_Employee->MatchText), $templateCode);
	$templateCode = str_replace('<%%COMBO(Product)%%>', $combo_Product->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(Product)%%>', $combo_Product->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(Product)%%>', urlencode($combo_Product->MatchText), $templateCode);

	/* lookup fields array: 'lookup field name' => ['parent table name', 'lookup field caption'] */
	$lookup_fields = ['Customer' => ['Customers', 'CustomerID'], 'Employee' => ['Employees', 'EmployeeID'], 'Product' => ['Products', 'Product'], ];
	foreach($lookup_fields as $luf => $ptfc) {
		$pt_perm = getTablePermissions($ptfc[0]);

		// process foreign key links
		if($pt_perm['view'] || $pt_perm['edit']) {
			$templateCode = str_replace("<%%PLINK({$luf})%%>", '<button type="button" class="btn btn-default view_parent" id="' . $ptfc[0] . '_view_parent" title="' . html_attr($Translation['View'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-eye-open"></i></button>', $templateCode);
		}

		// if user has insert permission to parent table of a lookup field, put an add new button
		if($pt_perm['insert'] /* && !Request::val('Embedded')*/) {
			$templateCode = str_replace("<%%ADDNEW({$ptfc[0]})%%>", '<button type="button" class="btn btn-default add_new_parent" id="' . $ptfc[0] . '_add_new" title="' . html_attr($Translation['Add New'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-plus text-success"></i></button>', $templateCode);
		}
	}

	// process images
	$templateCode = str_replace('<%%UPLOADFILE(OrderID)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(Customer)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(Employee)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(Product)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(OrderDate)%%>', '', $templateCode);

	// process values
	if($selected_id) {
		if( $dvprint) $templateCode = str_replace('<%%VALUE(OrderID)%%>', safe_html($urow['OrderID']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(OrderID)%%>', html_attr($row['OrderID']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(OrderID)%%>', urlencode($urow['OrderID']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(Customer)%%>', safe_html($urow['Customer']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(Customer)%%>', html_attr($row['Customer']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(Customer)%%>', urlencode($urow['Customer']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(Employee)%%>', safe_html($urow['Employee']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(Employee)%%>', html_attr($row['Employee']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(Employee)%%>', urlencode($urow['Employee']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(Product)%%>', safe_html($urow['Product']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(Product)%%>', html_attr($row['Product']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(Product)%%>', urlencode($urow['Product']), $templateCode);
		$templateCode = str_replace('<%%VALUE(OrderDate)%%>', app_datetime($row['OrderDate'], 'dt'), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(OrderDate)%%>', urlencode(app_datetime($urow['OrderDate'], 'dt')), $templateCode);
	} else {
		$templateCode = str_replace('<%%VALUE(OrderID)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(OrderID)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(Customer)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(Customer)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(Employee)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(Employee)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(Product)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(Product)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(OrderDate)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(OrderDate)%%>', urlencode(''), $templateCode);
	}

	// process translations
	$templateCode = parseTemplate($templateCode);

	// clear scrap
	$templateCode = str_replace('<%%', '<!-- ', $templateCode);
	$templateCode = str_replace('%%>', ' -->', $templateCode);

	// hide links to inaccessible tables
	if(Request::val('dvprint_x') == '') {
		$templateCode .= "\n\n<script>\$j(function() {\n";
		$arrTables = getTableList();
		foreach($arrTables as $name => $caption) {
			$templateCode .= "\t\$j('#{$name}_link').removeClass('hidden');\n";
			$templateCode .= "\t\$j('#xs_{$name}_link').removeClass('hidden');\n";
		}

		$templateCode .= $jsReadOnly;
		$templateCode .= $jsEditable;

		if(!$selected_id) {
		}

		$templateCode.="\n});</script>\n";
	}

	// ajaxed auto-fill fields
	$templateCode .= '<script>';
	$templateCode .= '$j(function() {';


	$templateCode.="});";
	$templateCode.="</script>";
	$templateCode .= $lookups;

	// handle enforced parent values for read-only lookup fields
	$filterField = Request::val('FilterField');
	$filterOperator = Request::val('FilterOperator');
	$filterValue = Request::val('FilterValue');

	// don't include blank images in lightbox gallery
	$templateCode = preg_replace('/blank.gif" data-lightbox=".*?"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	/* default field values */
	$rdata = $jdata = get_defaults('Orders');
	if($selected_id) {
		$jdata = get_joined_record('Orders', $selected_id);
		if($jdata === false) $jdata = get_defaults('Orders');
		$rdata = $row;
	}
	$templateCode .= loadView('Orders-ajax-cache', ['rdata' => $rdata, 'jdata' => $jdata]);

	// hook: Orders_dv
	if(function_exists('Orders_dv')) {
		$args = [];
		Orders_dv(($selected_id ? $selected_id : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}