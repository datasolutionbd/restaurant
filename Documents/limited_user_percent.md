##User Name & Password

Superadmin: superadmin@example.com
Password: 123456

admin: admin@example.com
Password: 123456

|----------------------
| Order Visibility Rules
        
| admin-second  => 50%
| admin-third   => 40%
| admin-fourth  => 80%
        

// 50% Orders
'admin-second@gmail.com' => '(orders.id % 2) = 0',

// 40% Orders
'admin-third@gmail.com'  => '(orders.id % 5) IN (0,1)',

// 80% Orders
'admin-fourth@gmail.com' => '(orders.id % 5) != 0',


## Use file and diretory
1. Create file "LimitedOrderScope.php"
Directory: app/Scopes/LimitedOrderScope.php
2. Create file "OrderController.php"
Directory: app/Http/Controllers/OrderController.php
3. Create file "Order.php"
Directory: app/Models/Order.php
4. Create file "TaxReportExport.php"
Directory: app/Exports/TaxReportExport.php