<?php

	include_once(__DIR__ . '/lib.php');
	@include_once(__DIR__ . '/hooks/Orders.php');
	include_once(__DIR__ . '/Orders_dml.php');

	// mm: can the current member access this page?
	$perm = getTablePermissions('Orders');
	if(!$perm['access']) {
		echo error_message($Translation['tableAccessDenied']);
		exit;
	}

	$x = new DataList;
	$x->TableName = 'Orders';

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = [
		"`Orders`.`OrderID`" => "OrderID",
		"IF(    CHAR_LENGTH(`Customers1`.`CustomerName`), CONCAT_WS('',   `Customers1`.`CustomerName`), '') /* CustomerID */" => "Customer",
		"IF(    CHAR_LENGTH(`Employees1`.`EmployeeName`), CONCAT_WS('',   `Employees1`.`EmployeeName`), '') /* EmployeeID */" => "Employee",
		"IF(    CHAR_LENGTH(`Products1`.`ProductName`), CONCAT_WS('',   `Products1`.`ProductName`), '') /* Product */" => "Product",
		"if(`Orders`.`OrderDate`,date_format(`Orders`.`OrderDate`,'%m/%d/%Y %H:%i'),'')" => "OrderDate",
	];
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = [
		1 => '`Orders`.`OrderID`',
		2 => '`Customers1`.`CustomerName`',
		3 => '`Employees1`.`EmployeeName`',
		4 => '`Products1`.`ProductName`',
		5 => '`Orders`.`OrderDate`',
	];

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = [
		"`Orders`.`OrderID`" => "OrderID",
		"IF(    CHAR_LENGTH(`Customers1`.`CustomerName`), CONCAT_WS('',   `Customers1`.`CustomerName`), '') /* CustomerID */" => "Customer",
		"IF(    CHAR_LENGTH(`Employees1`.`EmployeeName`), CONCAT_WS('',   `Employees1`.`EmployeeName`), '') /* EmployeeID */" => "Employee",
		"IF(    CHAR_LENGTH(`Products1`.`ProductName`), CONCAT_WS('',   `Products1`.`ProductName`), '') /* Product */" => "Product",
		"if(`Orders`.`OrderDate`,date_format(`Orders`.`OrderDate`,'%m/%d/%Y %H:%i'),'')" => "OrderDate",
	];
	// Fields that can be filtered
	$x->QueryFieldsFilters = [
		"`Orders`.`OrderID`" => "OrderID",
		"IF(    CHAR_LENGTH(`Customers1`.`CustomerName`), CONCAT_WS('',   `Customers1`.`CustomerName`), '') /* CustomerID */" => "CustomerID",
		"IF(    CHAR_LENGTH(`Employees1`.`EmployeeName`), CONCAT_WS('',   `Employees1`.`EmployeeName`), '') /* EmployeeID */" => "EmployeeID",
		"IF(    CHAR_LENGTH(`Products1`.`ProductName`), CONCAT_WS('',   `Products1`.`ProductName`), '') /* Product */" => "Product",
		"`Orders`.`OrderDate`" => "OrderDate",
	];

	// Fields that can be quick searched
	$x->QueryFieldsQS = [
		"`Orders`.`OrderID`" => "OrderID",
		"IF(    CHAR_LENGTH(`Customers1`.`CustomerName`), CONCAT_WS('',   `Customers1`.`CustomerName`), '') /* CustomerID */" => "Customer",
		"IF(    CHAR_LENGTH(`Employees1`.`EmployeeName`), CONCAT_WS('',   `Employees1`.`EmployeeName`), '') /* EmployeeID */" => "Employee",
		"IF(    CHAR_LENGTH(`Products1`.`ProductName`), CONCAT_WS('',   `Products1`.`ProductName`), '') /* Product */" => "Product",
		"if(`Orders`.`OrderDate`,date_format(`Orders`.`OrderDate`,'%m/%d/%Y %H:%i'),'')" => "OrderDate",
	];

	// Lookup fields that can be used as filterers
	$x->filterers = ['Customer' => 'CustomerID', 'Employee' => 'EmployeeID', 'Product' => 'Product', ];

	$x->QueryFrom = "`Orders` LEFT JOIN `Customers` as Customers1 ON `Customers1`.`CustomerID`=`Orders`.`Customer` LEFT JOIN `Employees` as Employees1 ON `Employees1`.`EmployeeID`=`Orders`.`Employee` LEFT JOIN `Products` as Products1 ON `Products1`.`ProductNo`=`Orders`.`Product` ";
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
	$x->ScriptFileName = 'Orders_view.php';
	$x->TableTitle = 'Orders';
	$x->TableIcon = 'table.gif';
	$x->PrimaryKey = '`Orders`.`OrderID`';

	$x->ColWidth = [150, 150, 150, 150, 150, ];
	$x->ColCaption = ['OrderID', 'CustomerID', 'EmployeeID', 'Product', 'OrderDate', ];
	$x->ColFieldName = ['OrderID', 'Customer', 'Employee', 'Product', 'OrderDate', ];
	$x->ColNumber  = [1, 2, 3, 4, 5, ];

	// template paths below are based on the app main directory
	$x->Template = 'templates/Orders_templateTV.html';
	$x->SelectedTemplate = 'templates/Orders_templateTVS.html';
	$x->TemplateDV = 'templates/Orders_templateDV.html';
	$x->TemplateDVP = 'templates/Orders_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HasCalculatedFields = false;
	$x->AllowConsoleLog = false;
	$x->AllowDVNavigation = true;

	// hook: Orders_init
	$render = true;
	if(function_exists('Orders_init')) {
		$args = [];
		$render = Orders_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: Orders_header
	$headerCode = '';
	if(function_exists('Orders_header')) {
		$args = [];
		$headerCode = Orders_header($x->ContentType, getMemberInfo(), $args);
	}

	if(!$headerCode) {
		include_once(__DIR__ . '/header.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/header.php');
		echo str_replace('<%%HEADER%%>', ob_get_clean(), $headerCode);
	}

	echo $x->HTML;

	// hook: Orders_footer
	$footerCode = '';
	if(function_exists('Orders_footer')) {
		$args = [];
		$footerCode = Orders_footer($x->ContentType, getMemberInfo(), $args);
	}

	if(!$footerCode) {
		include_once(__DIR__ . '/footer.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/footer.php');
		echo str_replace('<%%FOOTER%%>', ob_get_clean(), $footerCode);
	}
