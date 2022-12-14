<?php
	include_once(__DIR__ . '/lib.php');
	@include_once(__DIR__ . '/hooks/Employees.php');
	include_once(__DIR__ . '/Employees_dml.php');

	// mm: can the current member access this page?
	$perm = getTablePermissions('Employees');
	if(!$perm['access']) {
		echo error_message($Translation['tableAccessDenied']);
		exit;
	}

	$x = new DataList;
	$x->TableName = 'Employees';

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = [
		"`Employees`.`EmployeeID`" => "EmployeeID",
		"`Employees`.`EmployeeName`" => "EmployeeName",
		"`Employees`.`SSN`" => "SSN",
	];
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = [
		1 => '`Employees`.`EmployeeID`',
		2 => 2,
		3 => '`Employees`.`SSN`',
	];

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = [
		"`Employees`.`EmployeeID`" => "EmployeeID",
		"`Employees`.`EmployeeName`" => "EmployeeName",
		"`Employees`.`SSN`" => "SSN",
	];
	// Fields that can be filtered
	$x->QueryFieldsFilters = [
		"`Employees`.`EmployeeID`" => "EmployeeID",
		"`Employees`.`EmployeeName`" => "Employee Name",
		"`Employees`.`SSN`" => "Employee Social Security Number",
	];

	// Fields that can be quick searched
	$x->QueryFieldsQS = [
		"`Employees`.`EmployeeID`" => "EmployeeID",
		"`Employees`.`EmployeeName`" => "EmployeeName",
		"`Employees`.`SSN`" => "SSN",
	];

	// Lookup fields that can be used as filterers
	$x->filterers = [];

	$x->QueryFrom = "`Employees` ";
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
	$x->ScriptFileName = 'Employees_view.php';
	$x->TableTitle = 'Employees';
	$x->TableIcon = 'table.gif';
	$x->PrimaryKey = '`Employees`.`EmployeeID`';

	$x->ColWidth = [150, 150, 150, ];
	$x->ColCaption = ['EmployeeID', 'Employee Name', 'Employee Social Security Number', ];
	$x->ColFieldName = ['EmployeeID', 'EmployeeName', 'SSN', ];
	$x->ColNumber  = [1, 2, 3, ];

	// template paths below are based on the app main directory
	$x->Template = 'templates/Employees_templateTV.html';
	$x->SelectedTemplate = 'templates/Employees_templateTVS.html';
	$x->TemplateDV = 'templates/Employees_templateDV.html';
	$x->TemplateDVP = 'templates/Employees_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HasCalculatedFields = false;
	$x->AllowConsoleLog = false;
	$x->AllowDVNavigation = true;

	// hook: Employees_init
	$render = true;
	if(function_exists('Employees_init')) {
		$args = [];
		$render = Employees_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: Employees_header
	$headerCode = '';
	if(function_exists('Employees_header')) {
		$args = [];
		$headerCode = Employees_header($x->ContentType, getMemberInfo(), $args);
	}

	if(!$headerCode) {
		include_once(__DIR__ . '/header.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/header.php');
		echo str_replace('<%%HEADER%%>', ob_get_clean(), $headerCode);
	}

	echo $x->HTML;

	// hook: Employees_footer
	$footerCode = '';
	if(function_exists('Employees_footer')) {
		$args = [];
		$footerCode = Employees_footer($x->ContentType, getMemberInfo(), $args);
	}

	if(!$footerCode) {
		include_once(__DIR__ . '/footer.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/footer.php');
		echo str_replace('<%%FOOTER%%>', ob_get_clean(), $footerCode);
	}
