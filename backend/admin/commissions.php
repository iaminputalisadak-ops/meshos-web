<?php
/**
 * Admin Panel - Commissions
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Commissions';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-rupee-sign"></i> Commissions</h2>
        <div>
            <button class="btn btn-primary btn-sm" onclick="loadCommissions('')">All</button>
            <button class="btn btn-secondary btn-sm" onclick="loadCommissions('pending')">Pending</button>
            <button class="btn btn-secondary btn-sm" onclick="loadCommissions('approved')">Approved</button>
            <button class="btn btn-secondary btn-sm" onclick="loadCommissions('paid')">Paid</button>
        </div>
    </div>
    <div id="totalsBar" style="margin-bottom:16px;"></div>
    <div class="table-wrapper">
        <div id="commissionsTable">Loading...</div>
    </div>
</div>

<script>
let currentStatus = '';

async function loadCommissions(status) {
    currentStatus = status;
    try {
        const url = status ? `../api/admin/commissions.php?status=${encodeURIComponent(status)}` : '../api/admin/commissions.php';
        const res = await fetch(url, { credentials: 'include' });
        const data = await res.json();
        if (data.success) {
            if (data.totals && data.totals.length) {
                let t = '';
                data.totals.forEach(r => { t += `<span style="margin-right:16px"><strong>${r.status}</strong>: ${r.cnt} (₹${Number(r.total || 0).toFixed(0)})</span>`; });
                document.getElementById('totalsBar').innerHTML = t;
            }
            displayCommissions(data.data || []);
        } else {
            document.getElementById('commissionsTable').innerHTML = '<p>Failed to load.</p>';
        }
    } catch (e) {
        document.getElementById('commissionsTable').innerHTML = '<p>Error loading commissions.</p>';
    }
}

function displayCommissions(list) {
    if (!list.length) {
        document.getElementById('commissionsTable').innerHTML = '<p>No commissions.</p>';
        return;
    }
    let html = '<table><thead><tr><th>ID</th><th>Order</th><th>Promoter</th><th>Order Amt</th><th>Commission</th><th>Status</th><th>Date</th></tr></thead><tbody>';
    list.forEach(c => {
        html += `<tr>
            <td>${c.id}</td>
            <td>#${c.order_id}</td>
            <td>${escapeHtml(c.promoter_name)} (${escapeHtml(c.promoter_code)})</td>
            <td>₹${Number(c.order_amount).toFixed(2)}</td>
            <td>₹${Number(c.commission_amount).toFixed(2)}</td>
            <td><span class="badge badge-${c.status === 'approved' ? 'success' : (c.status === 'pending' ? 'warning' : 'secondary')}">${c.status}</span></td>
            <td>${c.created_at || ''}</td>
        </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('commissionsTable').innerHTML = html;
}

function escapeHtml(s) {
    if (!s) return '';
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

loadCommissions();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
