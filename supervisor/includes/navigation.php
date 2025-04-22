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
    </style>
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
