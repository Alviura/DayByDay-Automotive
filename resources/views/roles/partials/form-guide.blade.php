<aside class="mi-guide">
    <div class="mi-guide-head">
        <div class="mi-guide-icon"><i class="fas fa-shield-halved"></i></div>
        <div>
            <p class="mi-guide-title">Role permissions</p>
            <p class="mi-guide-subtitle">How access control works</p>
        </div>
    </div>
    <div class="mi-guide-body">
        <div class="mi-guide-section mi-guide-section-first">
            <p class="mi-guide-section-title"><i class="fas fa-key"></i> Permission model</p>
            <p class="mi-guide-text">Each permission follows <code>module.action</code> format (e.g. <code>sales.create</code>). Users inherit permissions through their assigned role.</p>
        </div>
        <div class="mi-guide-section">
            <p class="mi-guide-section-title"><i class="fas fa-layer-group"></i> Core roles</p>
            <ul class="mi-guide-list">
                <li><strong>Administrator</strong><span>All permissions — full system control.</span></li>
                <li><strong>Shop Manager</strong><span>Shop sales, inventory, transfers, and reports.</span></li>
                <li><strong>Warehouse Manager</strong><span>Warehouse stock, procurement, and dispatch.</span></li>
                <li><strong>Shop Attendant</strong><span>POS and order entry at the counter.</span></li>
            </ul>
        </div>
        <div class="mi-guide-section">
            <p class="mi-guide-section-title"><i class="fas fa-gavel"></i> Approver permissions</p>
            <p class="mi-guide-text">Permissions ending in <code>.approve</code> assign who receives approval tasks for that document type. Users still need <code>approvals.act</code> to open the inbox and act on them.</p>
        </div>
        <div class="mi-guide-section">
            <p class="mi-guide-section-title"><i class="fas fa-lightbulb"></i> Tips</p>
            <ul class="mi-guide-tips">
                <li><i class="fas fa-check"></i> Use <strong>Select all</strong> on a module group to grant every action in that area.</li>
                <li><i class="fas fa-check"></i> Custom roles are useful for specialised staff (e.g. finance-only).</li>
                <li><i class="fas fa-check"></i> Core roles cannot be deleted to protect system integrity.</li>
            </ul>
        </div>
        <div class="mi-guide-note mi-guide-note-amber">
            <i class="fas fa-triangle-exclamation"></i>
            <p>Changing a role's permissions affects every user assigned to that role immediately.</p>
        </div>
    </div>
</aside>
