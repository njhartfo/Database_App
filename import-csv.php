<?php
	define('PREPEND_PATH', '');
	include_once(__DIR__ . '/lib.php');

	// accept a record as an assoc array, return transformed row ready to insert to table
	$transformFunctions = [
		'Orders' => function($data, $options = []) {
			if(isset($data['Customer'])) $data['Customer'] = pkGivenLookupText($data['Customer'], 'Orders', 'Customer');
			if(isset($data['Employee'])) $data['Employee'] = pkGivenLookupText($data['Employee'], 'Orders', 'Employee');
			if(isset($data['Product'])) $data['Product'] = pkGivenLookupText($data['Product'], 'Orders', 'Product');
			if(isset($data['OrderDate'])) $data['OrderDate'] = guessMySQLDateTime($data['OrderDate']);

			return $data;
		},
		'Products' => function($data, $options = []) {

			return $data;
		},
		'Customers' => function($data, $options = []) {

			return $data;
		},
		'Employees' => function($data, $options = []) {

			return $data;
		},
	];

	// accept a record as an assoc array, return a boolean indicating whether to import or skip record
	$filterFunctions = [
		'Orders' => function($data, $options = []) { return true; },
		'Products' => function($data, $options = []) { return true; },
		'Customers' => function($data, $options = []) { return true; },
		'Employees' => function($data, $options = []) { return true; },
	];


	@include(__DIR__ . '/hooks/import-csv.php');

	$ui = new CSVImportUI($transformFunctions, $filterFunctions);
