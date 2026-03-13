<?php
/**
 * Admin Panel - Creator Partner Management
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Creator Partners';
require_once __DIR__ . '/includes/header.php';
?>

<style>
.creator-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
.creator-stat { background: #f8f9fa; padding: 16px; border-radius: 8px; text-align: center; }
.creator-stat .value { font-size: 24px; font-weight: 700; color: #333; }
.creator-stat .label { font-size: 12px; color: #666; margin-top: 4px; }
.commission-input { width: 60px; padding: 4px 6px; border: 1px solid #ddd; border-radius: 4px; text-align: center; }
.btn-xs { padding: 2px 8px; font-size: 12px; }
.membership-badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
.mb-active { background: #d4edda; color: #155724; }
.mb-expired { background: #f8d7da; color: #721c24; }
.mb-pending { background: #fff3cd; color: #856404; }
.mb-none { background: #e2e3e5; color: #383d41; }
.tab-row { display: flex; gap: 8px; margin-bottom: 16px; }
.tab-btn { padding: 8px 16px; border: 1px solid #ddd; background: #fff; border-radius: 6px; cursor: pointer; font-weight: 500; }
.tab-btn.active { background: #667eea; color: #fff; border-color: #667eea; }
</style>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-user-astronaut"></i> Creator Partner Membership Program</h2>
    </div>
    <div class="creator-stats" id="statsRow">Loading...</div>
    <div class="tab-row">
        <button class="tab-btn active" onclick="showTab('all')">All Creators</button>
        <button class="tab-btn" onclick="showTab('pending')">Pending Approval</button>
        <button class="tab-btn" onclick="showTab('active')">Active Members</button>
        <button class="tab-btn" onclick="showTab('expired')">Expired</button>
    </div>
    <div class="table-wrapper">
        <div id="creatorsTable">Loading...</div>
    </div>
</div>

<script>
let allCreators = [];
let currentTab = 'all';

async function loadCreators() {
    try {
        const res = await fetch('../api/admin/creators.php', { credentials: 'include' });
        const data = await res.json();
        if (data.success && data.data) {
            allCreators = data.data;
            renderStats();
            showTab(currentTab);
        } else {
            document.getElementById('creatorsTable').innerHTML = '<p>Failed to load creators.</p>';
        }
    } catch (e) {
        document.getElementById('creatorsTable').innerHTML = '<p>Error loading creators.</p>';
    }
}

function renderStats() {
    const total = allCreators.length;
    const pending = allCreators.filter(c => c.approval_status === 'pending').length;
    const active = allCreators.filter(c => c.membership_status === 'active').length;
    const expired = allCreators.filter(c => c.membership_status === 'expired').length;
    const totalEarned = allCreators.reduce((s, c) => s + parseFloat(c.total_earned || 0), 0);
    const totalSales = allCreators.reduce((s, c) => s + parseFloat(c.total_sales || 0), 0);

    document.getElementById('statsRow').innerHTML = `
        <div class="creator-stat"><div class="value">${total}</div><div class="label">Total Creators</div></div>
        <div class="creator-stat"><div class="value" style="color:#e65100">${pending}</div><div class="label">Pending Approval</div></div>
        <div class="creator-stat"><div class="value" style="color:#2e7d32">${active}</div><div class="label">Active Members</div></div>
        <div class="creator-stat"><div class="value" style="color:#c62828">${expired}</div><div class="label">Expired Members</div></div>
        <div class="creator-stat"><div class="value">₹${totalSales.toFixed(0)}</div><div class="label">Total Sales</div></div>
        <div class="creator-stat"><div class="value">₹${totalEarned.toFixed(0)}</div><div class="label">Total Commissions</div></div>
    `;
}

function showTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach((btn, i) => {
        btn.classList.toggle('active', ['all','pending','active','expired'][i] === tab);
    });
    let filtered = allCreators;
    if (tab === 'pending') filtered = allCreators.filter(c => c.approval_status === 'pending' || c.pending_membership_id);
    else if (tab === 'active') filtered = allCreators.filter(c => c.membership_status === 'active');
    else if (tab === 'expired') filtered = allCreators.filter(c => c.membership_status === 'expired');
    displayCreators(filtered);
}

function displayCreators(list) {
    if (!list.length) {
        document.getElementById('creatorsTable').innerHTML = '<p>No creators in this category.</p>';
        return;
    }
    let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Code</th><th>Commission %</th><th>Membership</th><th>Days Left</th><th>Clicks</th><th>Sales</th><th>Wallet</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
    list.forEach(c => {
        const mbClass = c.membership_status === 'active' ? 'mb-active' : (c.membership_status === 'expired' ? 'mb-expired' : 'mb-none');
        html += `<tr>
            <td>${c.id}</td>
            <td>${esc(c.name)}</td>
            <td>${esc(c.email)}</td>
            <td><code>${esc(c.creator_code)}</code></td>
            <td>
                <input type="number" class="commission-input" id="rate-${c.id}" value="${c.commission_rate}" min="0" max="100" step="0.5">
                <button class="btn btn-primary btn-xs" onclick="setCommission(${c.id})">Set</button>
            </td>
            <td><span class="membership-badge ${mbClass}">${c.membership_status}</span></td>
            <td>${c.days_left || 0}</td>
            <td>${c.total_clicks || 0}</td>
            <td>₹${Number(c.total_sales || 0).toFixed(0)}</td>
            <td>₹${Number(c.wallet_balance || 0).toFixed(0)}</td>
            <td><span class="badge badge-${c.approval_status === 'approved' ? 'success' : (c.approval_status === 'pending' ? 'warning' : 'secondary')}">${c.approval_status}</span></td>
            <td>${getActions(c)}</td>
        </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('creatorsTable').innerHTML = html;
}

function getActions(c) {
    let btns = '';
    if (c.approval_status === 'pending') {
        btns += `<button class="btn btn-primary btn-sm" onclick="doAction('approve_creator', {creator_id:${c.id}})">Approve</button> `;
        btns += `<button class="btn btn-secondary btn-sm" onclick="doAction('reject_creator', {creator_id:${c.id}})">Reject</button> `;
    }
    if (c.pending_membership_id) {
        const payLabel = c.pending_payment_status === 'paid' ? '✓Paid' : '⏳Pay pending';
        btns += `<br><small>${payLabel}</small> `;
        if (c.pending_payment_status !== 'paid') {
            btns += `<button class="btn btn-primary btn-xs" onclick="doAction('mark_payment', {membership_id:${c.pending_membership_id}})">Mark Paid</button> `;
        }
        btns += `<button class="btn btn-primary btn-sm" onclick="doAction('approve_membership', {membership_id:${c.pending_membership_id}})">Approve 28d</button> `;
        btns += `<button class="btn btn-secondary btn-xs" onclick="doAction('reject_membership', {membership_id:${c.pending_membership_id}})">Reject Mem</button> `;
    }
    if (c.approval_status === 'approved' && c.membership_status === 'active') {
        btns += `<button class="btn btn-secondary btn-xs" onclick="doAction('suspend_creator', {creator_id:${c.id}})">Suspend</button>`;
    }
    return btns || '<em>-</em>';
}

async function setCommission(creatorId) {
    const rate = parseFloat(document.getElementById('rate-' + creatorId).value);
    if (isNaN(rate) || rate < 0 || rate > 100) { alert('Enter 0-100'); return; }
    await doAction('set_commission', { creator_id: creatorId, commission_rate: rate });
}

async function doAction(action, payload) {
    try {
        const res = await fetch('../api/admin/creators.php', {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, ...payload })
        });
        const data = await res.json();
        if (data.success) { loadCreators(); } else { alert(data.message || 'Action failed'); }
    } catch (e) { alert('Request failed'); }
}

function esc(s) {
    if (!s) return '';
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

loadCreators();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
