<?php
$status = 'done';
require 'orders_template.php';
?>

<div class="order-actions">
    <?php if ($order['paid_amount'] == 0): ?>
        <a href="create_invoice.php?id=<?php echo $order['id']; ?>" class="button invoice-btn">
            Create Advance Invoice
        </a>
    <?php elseif ($order['balance_amount'] > 0): ?>
        <a href="create_invoice.php?id=<?php echo $order['id']; ?>" class="button invoice-btn">
            Create Final Invoice
        </a>
        <div class="payment-status">
            <p><strong>Paid:</strong> Rs. <?php echo number_format($order['paid_amount'], 2); ?></p>
            <p><strong>Balance:</strong> Rs. <?php echo number_format($order['balance_amount'], 2); ?></p>
        </div>
    <?php else: ?>
        <div class="payment-status paid">
            <p><strong>Fully Paid:</strong> Rs. <?php echo number_format($order['paid_amount'], 2); ?></p>
        </div>
    <?php endif; ?>

    <a href="download_order.php?id=<?php echo $order['id']; ?>" class="button download-btn">
        Download Order
    </a>
</div>
