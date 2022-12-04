<?php
	// check this file's MD5 to make sure it wasn't called before
	$tenantId = Authentication::tenantIdPadded();
	$setupHash = __DIR__ . "/setup{$tenantId}.md5";

	$prevMD5 = @file_get_contents($setupHash);
	$thisMD5 = md5_file(__FILE__);

	// check if this setup file already run
	if($thisMD5 != $prevMD5) {
		// set up tables
		setupTable(
			'Orders', " 
			CREATE TABLE IF NOT EXISTS `Orders` ( 
				`OrderID` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`OrderID`),
				`Customer` INT(10) ZEROFILL NULL,
				`Employee` INT(10) ZEROFILL NULL,
				`Product` INT(10) ZEROFILL NULL,
				`OrderDate` DATETIME NULL
			) CHARSET utf8mb4", [
				"ALTER TABLE `Orders` CHANGE `CustomerID` `Customer` INT(10) ZEROFILL NULL ",
				"ALTER TABLE `Orders` CHANGE `EmployeeID` `Employee` INT(10) NULL ",
				"ALTER TABLE `Orders` CHANGE `ProductNo` `Product` INT(15) ZEROFILL NULL ",
				" ALTER TABLE `Orders` CHANGE `Employee` `Employee` VARCHAR(40) NULL ",
				"ALTER TABLE `Orders` ADD UNIQUE `OrderID_unique` (`OrderID`)",
				" ALTER TABLE `Orders` CHANGE `OrderID` `OrderID` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT ",
				"ALTER TABLE Orders ADD `field5` VARCHAR(40)",
				"ALTER TABLE `Orders` CHANGE `field5` `OrderDate` VARCHAR(40) NULL ",
				" ALTER TABLE `Orders` CHANGE `OrderDate` `OrderDate` DATETIME NULL ",
			]
		);
		setupIndexes('Orders', ['Customer','Employee','Product',]);

		setupTable(
			'Products', " 
			CREATE TABLE IF NOT EXISTS `Products` ( 
				`ProductNo` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`ProductNo`),
				`ProductName` VARCHAR(40) NULL,
				`Manufacturer` VARCHAR(40) NULL
			) CHARSET utf8mb4", [
				"ALTER TABLE `Products` ADD UNIQUE `ProductNo_unique` (`ProductNo`)",
				" ALTER TABLE `Products` CHANGE `ProductNo` `ProductNo` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT ",
			]
		);

		setupTable(
			'Customers', " 
			CREATE TABLE IF NOT EXISTS `Customers` ( 
				`CustomerID` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`CustomerID`),
				`CustomerName` VARCHAR(40) NULL,
				`Address` VARCHAR(40) NULL,
				`State` VARCHAR(2) NULL,
				`Country` VARCHAR(40) NULL
			) CHARSET utf8mb4", [
				"ALTER TABLE `Customers` DROP `LastName`",
				"ALTER TABLE `Customers` CHANGE `FirstName` `CustomerName` VARCHAR(40) NULL ",
				"ALTER TABLE `Customers` ADD UNIQUE `CustomerID_unique` (`CustomerID`)",
				" ALTER TABLE `Customers` CHANGE `CustomerID` `CustomerID` INT(10) NOT NULL AUTO_INCREMENT ",
				" ALTER TABLE `Customers` CHANGE `CustomerID` `CustomerID` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT ",
				"ALTER TABLE Customers ADD `field3` VARCHAR(40)",
				"ALTER TABLE `Customers` CHANGE `field3` `Address` VARCHAR(40) NULL ",
				"ALTER TABLE Customers ADD `field4` VARCHAR(40)",
				"ALTER TABLE Customers ADD `field5` VARCHAR(40)",
				"ALTER TABLE `Customers` CHANGE `field4` `State` VARCHAR(40) NULL ",
				"ALTER TABLE `Customers` CHANGE `field5` `Country` VARCHAR(40) NULL ",
				" ALTER TABLE `Customers` CHANGE `State` `State` VARCHAR(2) NULL ",
			]
		);

		setupTable(
			'Employees', " 
			CREATE TABLE IF NOT EXISTS `Employees` ( 
				`EmployeeID` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`EmployeeID`),
				`EmployeeName` VARCHAR(40) NULL,
				`SSN` INT(9) NULL,
				UNIQUE `SSN_unique` (`SSN`)
			) CHARSET utf8mb4", [
				"ALTER TABLE `Employees` DROP `FirstName`",
				"ALTER TABLE `Employees` CHANGE `LastName` `EmployeeName` VARCHAR(40) NULL ",
				" ALTER TABLE `Employees` CHANGE `EmployeeID` `EmployeeID` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT ",
				"ALTER TABLE `Employees` ADD UNIQUE `EmployeeID_unique` (`EmployeeID`)",
			]
		);



		// save MD5
		@file_put_contents($setupHash, $thisMD5);
	}


	function setupIndexes($tableName, $arrFields) {
		if(!is_array($arrFields) || !count($arrFields)) return false;

		foreach($arrFields as $fieldName) {
			if(!$res = @db_query("SHOW COLUMNS FROM `$tableName` like '$fieldName'")) continue;
			if(!$row = @db_fetch_assoc($res)) continue;
			if($row['Key']) continue;

			@db_query("ALTER TABLE `$tableName` ADD INDEX `$fieldName` (`$fieldName`)");
		}
	}


	function setupTable($tableName, $createSQL = '', $arrAlter = '') {
		global $Translation;
		$oldTableName = '';
		ob_start();

		echo '<div style="padding: 5px; border-bottom:solid 1px silver; font-family: verdana, arial; font-size: 10px;">';

		// is there a table rename query?
		if(is_array($arrAlter)) {
			$matches = [];
			if(preg_match("/ALTER TABLE `(.*)` RENAME `$tableName`/i", $arrAlter[0], $matches)) {
				$oldTableName = $matches[1];
			}
		}

		if($res = @db_query("SELECT COUNT(1) FROM `$tableName`")) { // table already exists
			if($row = @db_fetch_array($res)) {
				echo str_replace(['<TableName>', '<NumRecords>'], [$tableName, $row[0]], $Translation['table exists']);
				if(is_array($arrAlter)) {
					echo '<br>';
					foreach($arrAlter as $alter) {
						if($alter != '') {
							echo "$alter ... ";
							if(!@db_query($alter)) {
								echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
								echo '<div class="text-danger">' . $Translation['mysql said'] . ' ' . db_error(db_link()) . '</div>';
							} else {
								echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
							}
						}
					}
				} else {
					echo $Translation['table uptodate'];
				}
			} else {
				echo str_replace('<TableName>', $tableName, $Translation['couldnt count']);
			}
		} else { // given tableName doesn't exist

			if($oldTableName != '') { // if we have a table rename query
				if($ro = @db_query("SELECT COUNT(1) FROM `$oldTableName`")) { // if old table exists, rename it.
					$renameQuery = array_shift($arrAlter); // get and remove rename query

					echo "$renameQuery ... ";
					if(!@db_query($renameQuery)) {
						echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
						echo '<div class="text-danger">' . $Translation['mysql said'] . ' ' . db_error(db_link()) . '</div>';
					} else {
						echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
					}

					if(is_array($arrAlter)) setupTable($tableName, $createSQL, false, $arrAlter); // execute Alter queries on renamed table ...
				} else { // if old tableName doesn't exist (nor the new one since we're here), then just create the table.
					setupTable($tableName, $createSQL, false); // no Alter queries passed ...
				}
			} else { // tableName doesn't exist and no rename, so just create the table
				echo str_replace("<TableName>", $tableName, $Translation["creating table"]);
				if(!@db_query($createSQL)) {
					echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
					echo '<div class="text-danger">' . $Translation['mysql said'] . db_error(db_link()) . '</div>';

					// create table with a dummy field
					@db_query("CREATE TABLE IF NOT EXISTS `$tableName` (`_dummy_deletable_field` TINYINT)");
				} else {
					echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
				}
			}

			// set Admin group permissions for newly created table if membership_grouppermissions exists
			if($ro = @db_query("SELECT COUNT(1) FROM `membership_grouppermissions`")) {
				// get Admins group id
				$ro = @db_query("SELECT `groupID` FROM `membership_groups` WHERE `name`='Admins'");
				if($ro) {
					$adminGroupID = intval(db_fetch_row($ro)[0]);
					if($adminGroupID) @db_query("INSERT IGNORE INTO `membership_grouppermissions` SET
						`groupID`='$adminGroupID',
						`tableName`='$tableName',
						`allowInsert`=1, `allowView`=1, `allowEdit`=1, `allowDelete`=1
					");
				}
			}
		}

		echo '</div>';

		$out = ob_get_clean();
		if(defined('APPGINI_SETUP') && APPGINI_SETUP) echo $out;
	}
