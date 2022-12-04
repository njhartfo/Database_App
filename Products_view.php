<?php

	include_once(__DIR__ . '/lib.php');
	@include_once(__DIR__ . '/hooks/Products.php');
	include_once(__DIR__ . '/Products_dml.php');

	// mm: can the current member access this page?
	$perm = getTablePermissions('Products');
	if(!$perm['access']) {
		echo error_message($Translation['tableAccessDenied']);
		exit;
	}

	$x = new DataList;
	$x->TableName = 'Products';

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = [
		"`Products`.`ProductNo`" => "ProductNo",
		"`Products`.`ProductName`" => "ProductName",
		"`Products`.`Manufacturer`" => "Manufacturer",
	];
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = [
		1 => '`Products`.`ProductNo`',
		2 => 2,
		3 => 3,
	];

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = [
		"`Products`.`ProductNo`" => "ProductNo",
		"`Products`.`ProductName`" => "ProductName",
		"`Products`.`Manufacturer`" => "Manufacturer",
	];
	// Fields that can be filtered
	$x->QueryFieldsFilters = [
		"`Products`.`ProductNo`" => "ProductNo",
		"`Products`.`ProductName`" => "ProductName",
		"`Products`.`Manufacturer`" => "Manufacturer",
	];

	// Fields that can be quick searched
	$x->QueryFieldsQS = [
		"`Products`.`ProductNo`" => "ProductNo",
		"`Products`.`ProductName`" => "ProductName",
		"`Products`.`Manufacturer`" => "Manufacturer",
	];

	// Lookup fields that can be used as filterers
	$x->filterers = [];

	$x->QueryFrom = "`Products` ";
	$x->QueryWhere = '';
	$x->QueryOrder = '';

	$x->AllowSelection = 1;
	$x->HideTableView = ($perm['view'] == 0 ? 1 : 0);
	$x->AllowDelete = $perm['delete'];
	$x->AllowMassDelete = (getLoggedAdmin() !== false);
	$x->AllowInsert = $perm['insert'];
	$x->AllowUpdate = $perm['edit'];
	$x->SeparateDV = 1;
	$x->AllowDeleteOfParents = 0;
	$x->AllowFilters = 1;
	$x->AllowSavingFilters = (getLoggedAdmin() !== false);
	$x->AllowSorting = 1;
	$x->AllowNavigation = 1;
	$x->AllowPrinting = 1;
	$x->AllowPrintingDV = 1;
	$x->AllowCSV = 1;
	$x->RecordsPerPage = 10;
	$x->QuickSearch = 1;
	$x->QuickSearchText = $Translation['quick search'];
	$x->ScriptFileName = 'Products_view.php';
	$x->TableTitle = 'Products';
	$x->TableIcon = 'table.gif';
	$x->PrimaryKey = '`Products`.`ProductNo`';

	$x->ColWidth = [150, 150, 150, ];
	$x->ColCaption = ['ProductNo', 'ProductName', 'Manufacturer', ];
	$x->ColFieldName = ['ProductNo', 'ProductName', 'Manufacturer', ];
	$x->ColNumber  = [1, 2, 3, ];

	// template paths below are based on the app main directory
	$x->Template = 'templates/Products_templateTV.html';
	$x->SelectedTemplate = 'templates/Products_templateTVS.html';
	$x->TemplateDV = 'templates/Products_templateDV.html';
	$x->TemplateDVP = 'templates/Products_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HasCalculatedFields = false;
	$x->AllowConsoleLog = false;
	$x->AllowDVNavigation = true;

	// hook: Products_init
	$render = true;
	if(function_exists('Products_init')) {
		$args = [];
		$render = Products_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: Products_header
	$headerCode = '';
	if(function_exists('Products_header')) {
		$args = [];
		$headerCode = Products_header($x->ContentType, getMemberInfo(), $args);
	}

	if(!$headerCode) {
		include_once(__DIR__ . '/header.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/header.php');
		echo str_replace('<%%HEADER%%>', ob_get_clean(), $headerCode);
	}

	echo $x->HTML;

	// hook: Products_footer
	$footerCode = '';
	if(function_exists('Products_footer')) {
		$args = [];
		$footerCode = Products_footer($x->ContentType, getMemberInfo(), $args);
	}

	if(!$footerCode) {
		include_once(__DIR__ . '/footer.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/footer.php');
		echo str_replace('<%%FOOTER%%>', ob_get_clean(), $footerCode);
	}
