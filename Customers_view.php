<?php
	include_once(__DIR__ . '/lib.php');
	@include_once(__DIR__ . '/hooks/Customers.php');
	include_once(__DIR__ . '/Customers_dml.php');

	// mm: can the current member access this page?
	$perm = getTablePermissions('Customers');
	if(!$perm['access']) {
		echo error_message($Translation['tableAccessDenied']);
		exit;
	}

	$x = new DataList;
	$x->TableName = 'Customers';

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = [
		"`Customers`.`CustomerID`" => "CustomerID",
		"`Customers`.`CustomerName`" => "CustomerName",
		"`Customers`.`Address`" => "Address",
		"`Customers`.`State`" => "State",
		"`Customers`.`Country`" => "Country",
	];
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = [
		1 => '`Customers`.`CustomerID`',
		2 => 2,
		3 => 3,
		4 => 4,
		5 => 5,
	];

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = [
		"`Customers`.`CustomerID`" => "CustomerID",
		"`Customers`.`CustomerName`" => "CustomerName",
		"`Customers`.`Address`" => "Address",
		"`Customers`.`State`" => "State",
		"`Customers`.`Country`" => "Country",
	];
	// Fields that can be filtered
	$x->QueryFieldsFilters = [
		"`Customers`.`CustomerID`" => "CustomerID",
		"`Customers`.`CustomerName`" => "Customer Name",
		"`Customers`.`Address`" => "Address",
		"`Customers`.`State`" => "State",
		"`Customers`.`Country`" => "Country",
	];

	// Fields that can be quick searched
	$x->QueryFieldsQS = [
		"`Customers`.`CustomerID`" => "CustomerID",
		"`Customers`.`CustomerName`" => "CustomerName",
		"`Customers`.`Address`" => "Address",
		"`Customers`.`State`" => "State",
		"`Customers`.`Country`" => "Country",
	];

	// Lookup fields that can be used as filterers
	$x->filterers = [];

	$x->QueryFrom = "`Customers` ";
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
	$x->ScriptFileName = 'Customers_view.php';
	$x->TableTitle = 'Customers';
	$x->TableIcon = 'table.gif';
	$x->PrimaryKey = '`Customers`.`CustomerID`';

	$x->ColWidth = [150, 150, 150, 150, 150, ];
	$x->ColCaption = ['CustomerID', 'Customer Name', 'Address', 'State', 'Country', ];
	$x->ColFieldName = ['CustomerID', 'CustomerName', 'Address', 'State', 'Country', ];
	$x->ColNumber  = [1, 2, 3, 4, 5, ];

	// template paths below are based on the app main directory
	$x->Template = 'templates/Customers_templateTV.html';
	$x->SelectedTemplate = 'templates/Customers_templateTVS.html';
	$x->TemplateDV = 'templates/Customers_templateDV.html';
	$x->TemplateDVP = 'templates/Customers_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HasCalculatedFields = false;
	$x->AllowConsoleLog = false;
	$x->AllowDVNavigation = true;

	// hook: Customers_init
	$render = true;
	if(function_exists('Customers_init')) {
		$args = [];
		$render = Customers_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: Customers_header
	$headerCode = '';
	if(function_exists('Customers_header')) {
		$args = [];
		$headerCode = Customers_header($x->ContentType, getMemberInfo(), $args);
	}

	if(!$headerCode) {
		include_once(__DIR__ . '/header.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/header.php');
		echo str_replace('<%%HEADER%%>', ob_get_clean(), $headerCode);
	}

	echo $x->HTML;

	// hook: Customers_footer
	$footerCode = '';
	if(function_exists('Customers_footer')) {
		$args = [];
		$footerCode = Customers_footer($x->ContentType, getMemberInfo(), $args);
	}

	if(!$footerCode) {
		include_once(__DIR__ . '/footer.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/footer.php');
		echo str_replace('<%%FOOTER%%>', ob_get_clean(), $footerCode);
	}
