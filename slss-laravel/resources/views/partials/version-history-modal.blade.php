{{-- Single source of truth for the version-history modal (shared by the app
     layout and the login page — previously two drifting copies). --}}
<style>
    .version-item {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-color, #e2e8f0);
    }

    .version-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .version-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .version-badge {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        background: var(--bg-light, #f8fafc);
        color: var(--text-dark, #0f172a);
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .version-badge.current {
        background: var(--primary-color, #4f46e5);
        color: white;
    }

    .version-date {
        font-size: 0.875rem;
        color: var(--text-muted, #64748b);
    }

    .version-features {
        margin: 0;
        padding-left: 1.5rem;
        font-size: 0.875rem;
        color: var(--text-dark, #0f172a);
    }

    .version-features li {
        margin-bottom: 0.375rem;
    }

    .version-features li:last-child {
        margin-bottom: 0;
    }
</style>

<div class="modal fade" id="versionHistoryModal" tabindex="-1" aria-labelledby="versionHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="versionHistoryModalLabel">
                    <i class="fas fa-code-branch me-2" aria-hidden="true"></i>Version History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="version-item">
                    <div class="version-header">
                        <span class="version-badge current">v1.1</span>
                        <span class="version-date">{{ date('F Y') }} - Current</span>
                    </div>
                    <ul class="version-features">
                        <li><strong>Real-time PDF export progress tracking</strong> with live updates</li>
                        <li><strong>Bulk PDF export to ZIP</strong> with all 127 fields per student</li>
                        <li><strong>Detailed error messages</strong> showing specific failure points</li>
                        <li><strong>Optimized progress tracking</strong> with 50-80% reduced cache load</li>
                        <li><strong>Monotonic progress bar</strong> - never decreases or jumps backward</li>
                        <li><strong>Administrator password reset</strong> from user management</li>
                        <li><strong>Storage diagnostics endpoint</strong> for troubleshooting downloads</li>
                        <li><strong>Fixed deployment system</strong> with automatic storage symlink creation</li>
                        <li><strong>Cache failure protection</strong> - exports complete even if tracking fails</li>
                        <li><strong>Production-ready progress tracking</strong> with race condition prevention</li>
                    </ul>
                </div>
                <div class="version-item">
                    <div class="version-header">
                        <span class="version-badge">v1.0</span>
                        <span class="version-date">July 2026</span>
                    </div>
                    <ul class="version-features">
                        <li>Complete mobile responsiveness across all devices</li>
                        <li>127-field comprehensive student profiles</li>
                        <li>Webhook integration with registration form</li>
                        <li>User management with role-based access control</li>
                        <li>PDF generation with official document watermarks</li>
                        <li>Advanced search and filtering capabilities</li>
                        <li>DataTables integration for efficient data management</li>
                    </ul>
                </div>
                <div class="version-item">
                    <div class="version-header">
                        <span class="version-badge">v0.9</span>
                        <span class="version-date">January 2026</span>
                    </div>
                    <ul class="version-features">
                        <li>Beta testing with form integration</li>
                        <li>PDF generation functionality</li>
                        <li>Enhanced security features</li>
                    </ul>
                </div>
                <div class="version-item">
                    <div class="version-header">
                        <span class="version-badge">v0.8</span>
                        <span class="version-date">December 2025</span>
                    </div>
                    <ul class="version-features">
                        <li>Initial development</li>
                        <li>Core student management features</li>
                        <li>Basic CRUD operations</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
