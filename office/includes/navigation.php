<nav>
<img src="../assets/images/oricado logo.jpg" alt="Oricado Logo" class="navigation-logo">
<style>
.navigation {
    background: #333; /* Black background for navigation */
    color: white;
    padding: 20px;
}

.logo-container {
    text-align: center;
    margin-bottom: 20px;
}

.navigation-logo {
    max-width: 150px;
    height: auto;
    display: block;
    margin: 0 auto;
    border-radius: 50%; /* Makes the logo circular */
    border: 2px solid #FFD700; /* Gold color for the border */
    box-shadow: 0 0 10px 2px #FFD700; /* Optional: Add a glowing effect */
}
.nav-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.nav-section li {
    margin-bottom: 12px;
}
.nav-section a {
    color: #fff;
    text-decoration: none;
    font-size: 1.08rem;
    display: flex;
    align-items: center;
    padding: 8px 14px;
    border-radius: 8px;
    transition: background 0.18s, color 0.18s;
    gap: 10px;
}
.nav-section a:hover, .nav-section a.active {
    background: #222;
    color: #FFD700;
}
.nav-section i {
    min-width: 22px;
    text-align: center;
    font-size: 1.15rem;
}
    </style>
    <h2>Office Staff Dashboard</h2>
    <div class="nav-section">
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="create_quotation.php"><i class="fas fa-file-signature"></i> Create Quotation</a></li>
        <li><a href="quotations.php"><i class="fas fa-file-alt"></i> Quotations</a></li>
        <li><a href="updated_quotations.php"><i class="fas fa-edit"></i> Updated Quotations</a></li>
        <li><a href="create_order.php"><i class="fas fa-plus-square"></i> New Order</a></li>
        <li><a href="pending_orders.php"><i class="fas fa-hourglass-half"></i> Pending Orders</a></li>
        <li><a href="reviewed_orders.php"><i class="fas fa-search"></i> Reviewed Orders</a></li>
        <li><a href="confirmed_orders.php"><i class="fas fa-check-circle"></i> Confirmed Orders</a></li>
        <li><a href="completed_orders.php"><i class="fas fa-clipboard-check"></i> Completed Orders</a></li>
        <li><a href="done_orders.php"><i class="fas fa-flag-checkered"></i> Done Orders</a></li>
        <li><a href="invoices.php?type=advance"><i class="fas fa-file-invoice-dollar"></i> Invoices</a></li>
        <li><a href="manage_stock.php"><i class="fas fa-boxes"></i> Manage Stock</a></li>
        <li><a href="material_report.php"><i class="fas fa-clipboard-list"></i> Material Report</a></li>
        <li><a href="add_material.php"><i class="fas fa-plus"></i> Add Material</a></li>
        <li><a href="contacts.php"><i class="fas fa-address-book"></i> Contacts</a></li>
        <li><a href="add_contact.php"><i class="fas fa-user-plus"></i> Add Contact</a></li>
        <li><a href="buy_materials.php"><i class="fas fa-shopping-cart"></i> Buy Materials</a></li>
        <li><a href="supplier_quotations.php"><i class="fas fa-truck"></i> Supplier Quotations</a></li>
        <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        <li><a href="../index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
    </div>
</nav>
