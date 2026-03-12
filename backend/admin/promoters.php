<?php
/**
 * Admin Panel - Promoters (Affiliate) Management
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Promoters';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-users-cog"></i> Promoters</h2>
    </div>
    <div class="table-wrapper">
        <div id="promotersTable">Loading...</div>
    </div>
</div>

<script>
async function loadPromoters() {
    try {
        const res = await fetch('../api/admin/promoters.php', { credentials: 'include' });
        const data = await res.json();
        if (data.success && data.data) {
            displayPromoters(data.data);
        } else {
            document.getElementById('promotersTable').innerHTML = '<p>Failed to load promoters.</p>';
        }
    } catch (e) {
        document.getElementById('promotersTable').innerHTML = '<p>Error loading promoters.</p>';
    }
}

function displayPromoters(list) {
    if (!list.length) {
        document.getElementById('promotersTable').innerHTML = '<p>No promoters yet.</p>';
        return;
    }
    let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Code</th><th>Clicks</th><th>Orders</th><th>Sales</th><th>Pending</th><th>Approved</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
    list.forEach(p => {
        html += `<tr>
            <td>${p.id}</td>
            <td>${escapeHtml(p.name)}</td>
            <td>${escapeHtml(p.email)}</td>
            <td><code>${escapeHtml(p.code)}</code></td>
            <td>${p.total_clicks || 0}</td>
            <td>${p.total_orders || 0}</td>
            <td>₹${Number(p.total_sales || 0).toFixed(0)}</td>
            <td>₹${Number(p.pending_commission || 0).toFixed(0)}</td>
            <td>₹${Number(p.approved_commission || 0).toFixed(0)}</td>
            <td><span class="badge badge-${p.status === 'approved' ? 'success' : (p.status === 'pending' ? 'warning' : 'secondary')}">${p.status}</span></td>
            <td>`;
        if (p.status === 'pending') {
            html += `<button class="btn btn-primary btn-sm" onclick="approvePromoter(${p.id})">Approve</button> `;
            html += `<button class="btn btn-secondary btn-sm" onclick="rejectPromoter(${p.id})">Reject</button>`;
        } else if (p.status === 'approved') {
            html += `<button class="btn btn-secondary btn-sm" onclick="suspendPromoter(${p.id})">Suspend</button>`;
        }
        html += `</td></tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('promotersTable').innerHTML = html;
}

function approvePromoter(id) {
    doAction(id, 'approve');
}
function rejectPromoter(id) {
    if (!confirm('Reject this promoter?')) return;
    doAction(id, 'reject');
}
function suspendPromoter(id) {
    doAction(id, 'suspend');
}

async function doAction(id, action) {
    try {
        const res = await fetch('../api/admin/promoters.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ promoter_id: id, action })
        });
        const data = await res.json();
        if (data.success) loadPromoters();
        else alert(data.message || 'Action failed');
    } catch (e) {
        alert('Request failed');
    }
}

function escapeHtml(s) {
    if (!s) return '';
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

loadPromoters();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
