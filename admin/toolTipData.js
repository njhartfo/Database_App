var FiltersEnabled = 0; // if your not going to use transitions or filters in any of the tips set this to 0
var spacer="&nbsp; &nbsp; &nbsp; ";

// email notifications to admin
notifyAdminNewMembers0Tip=["", spacer+"No email notifications to admin."];
notifyAdminNewMembers1Tip=["", spacer+"Notify admin only when a new member is waiting for approval."];
notifyAdminNewMembers2Tip=["", spacer+"Notify admin for all new sign-ups."];

// visitorSignup
visitorSignup0Tip=["", spacer+"If this option is selected, visitors will not be able to join this group unless the admin manually moves them to this group from the admin area."];
visitorSignup1Tip=["", spacer+"If this option is selected, visitors can join this group but will not be able to sign in unless the admin approves them from the admin area."];
visitorSignup2Tip=["", spacer+"If this option is selected, visitors can join this group and will be able to sign in instantly with no need for admin approval."];

// Orders table
Orders_addTip=["",spacer+"This option allows all members of the group to add records to the 'Orders' table. A member who adds a record to the table becomes the 'owner' of that record."];

Orders_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Orders' table."];
Orders_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Orders' table."];
Orders_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Orders' table."];
Orders_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Orders' table."];

Orders_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Orders' table."];
Orders_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Orders' table."];
Orders_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Orders' table."];
Orders_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Orders' table, regardless of their owner."];

Orders_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Orders' table."];
Orders_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Orders' table."];
Orders_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Orders' table."];
Orders_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Orders' table."];

// Products table
Products_addTip=["",spacer+"This option allows all members of the group to add records to the 'Products' table. A member who adds a record to the table becomes the 'owner' of that record."];

Products_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Products' table."];
Products_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Products' table."];
Products_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Products' table."];
Products_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Products' table."];

Products_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Products' table."];
Products_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Products' table."];
Products_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Products' table."];
Products_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Products' table, regardless of their owner."];

Products_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Products' table."];
Products_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Products' table."];
Products_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Products' table."];
Products_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Products' table."];

// Customers table
Customers_addTip=["",spacer+"This option allows all members of the group to add records to the 'Customers' table. A member who adds a record to the table becomes the 'owner' of that record."];

Customers_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Customers' table."];
Customers_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Customers' table."];
Customers_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Customers' table."];
Customers_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Customers' table."];

Customers_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Customers' table."];
Customers_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Customers' table."];
Customers_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Customers' table."];
Customers_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Customers' table, regardless of their owner."];

Customers_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Customers' table."];
Customers_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Customers' table."];
Customers_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Customers' table."];
Customers_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Customers' table."];

// Employees table
Employees_addTip=["",spacer+"This option allows all members of the group to add records to the 'Employees' table. A member who adds a record to the table becomes the 'owner' of that record."];

Employees_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Employees' table."];
Employees_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Employees' table."];
Employees_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Employees' table."];
Employees_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Employees' table."];

Employees_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Employees' table."];
Employees_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Employees' table."];
Employees_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Employees' table."];
Employees_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Employees' table, regardless of their owner."];

Employees_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Employees' table."];
Employees_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Employees' table."];
Employees_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Employees' table."];
Employees_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Employees' table."];

/*
	Style syntax:
	-------------
	[TitleColor,TextColor,TitleBgColor,TextBgColor,TitleBgImag,TextBgImag,TitleTextAlign,
	TextTextAlign,TitleFontFace,TextFontFace, TipPosition, StickyStyle, TitleFontSize,
	TextFontSize, Width, Height, BorderSize, PadTextArea, CoordinateX , CoordinateY,
	TransitionNumber, TransitionDuration, TransparencyLevel ,ShadowType, ShadowColor]

*/

toolTipStyle=["white","#00008B","#000099","#E6E6FA","","images/helpBg.gif","","","","\"Trebuchet MS\", sans-serif","","","","3",400,"",1,2,10,10,51,1,0,"",""];

applyCssFilter();
