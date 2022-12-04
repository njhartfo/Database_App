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

	/*
	Hook file for overwriting/amending $transformFunctions and $filterFunctions:
	hooks/import-csv.php
	If found, it's included below

	The way this works is by either completely overwriting any of the above 2 arrays,
	or, more commonly, overwriting a single function, for example:
		$transformFunctions['tablename'] = function($data, $options = []) {
			// new definition here
			// then you must return transformed data
			return $data;
		};

	Another scenario is transforming a specific field and leaving other fields to the default
	transformation. One possible way of doing this is to store the original transformation function
	in GLOBALS array, calling it inside the custom transformation function, then modifying the
	specific field:
		$GLOBALS['originalTransformationFunction'] = $transformFunctions['tablename'];
		$transformFunctions['tablename'] = function($data, $options = []) {
			$data = call_user_func_array($GLOBALS['originalTransformationFunction'], [$data, $options]);
			$data['fieldname'] = 'transformed value';
			return $data;
		};
	*/

	@include(__DIR__ . '/hooks/import-csv.php');

	$ui = new CSVImportUI($transformFunctions, $filterFunctions);
