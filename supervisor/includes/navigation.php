<nav>
    <h2>Supervisor Dashboard</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="pending_orders.php">Pending Orders</a></li>
        <li><a href="reviewed_orders.php">Reviewed Orders</a></li>
        <li><a href="confirmed_orders.php">Confirmed Orders</a></li>
        <li><a href="completed_orders.php">Completed Orders</a></li>
        <li><a href="done_orders.php">Done Orders</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>
<script>
// Highlight current page in navigation
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop().replace('.php', '');
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        if (link.href.includes(currentPage)) {
            link.classList.add('active');
        }
    });
});
</script>
