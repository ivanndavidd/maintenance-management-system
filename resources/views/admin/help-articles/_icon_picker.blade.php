{{-- Icon Picker Modal --}}
<div class="modal fade" id="iconPickerModal" tabindex="-1" aria-labelledby="iconPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="iconPickerModalLabel"><i class="fas fa-icons me-1"></i> Pick an Icon</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="iconSearch" class="form-control form-control-sm mb-3" placeholder="Search icon...">
                <div id="iconGrid" class="row g-2">
                    @php
                    $icons = [
                        'fa-question-circle'   => 'FAQ',
                        'fa-clipboard-list'    => 'Checklist',
                        'fa-tools'             => 'Tools',
                        'fa-cogs'              => 'Settings',
                        'fa-exclamation-triangle' => 'Warning',
                        'fa-book'              => 'Book',
                        'fa-graduation-cap'    => 'Tutorial',
                        'fa-file-alt'          => 'Document',
                        'fa-file-pdf'          => 'PDF',
                        'fa-file-excel'        => 'Excel',
                        'fa-info-circle'       => 'Info',
                        'fa-lightbulb'         => 'Tip',
                        'fa-wrench'            => 'Wrench',
                        'fa-hammer'            => 'Hammer',
                        'fa-hard-hat'          => 'Safety',
                        'fa-shield-alt'        => 'Shield',
                        'fa-check-circle'      => 'Done',
                        'fa-times-circle'      => 'Cancel',
                        'fa-clock'             => 'Clock',
                        'fa-calendar-alt'      => 'Calendar',
                        'fa-bell'              => 'Alert',
                        'fa-bullhorn'          => 'Announce',
                        'fa-user'              => 'User',
                        'fa-users'             => 'Team',
                        'fa-user-cog'          => 'User Settings',
                        'fa-chart-bar'         => 'Chart',
                        'fa-chart-line'        => 'Trend',
                        'fa-tasks'             => 'Tasks',
                        'fa-list-ul'           => 'List',
                        'fa-list-ol'           => 'Ordered List',
                        'fa-sitemap'           => 'Sitemap',
                        'fa-project-diagram'   => 'Diagram',
                        'fa-boxes'             => 'Inventory',
                        'fa-box'               => 'Box',
                        'fa-warehouse'         => 'Warehouse',
                        'fa-industry'          => 'Factory',
                        'fa-bolt'              => 'Electric',
                        'fa-fire-extinguisher' => 'Fire Safety',
                        'fa-first-aid'         => 'First Aid',
                        'fa-microscope'        => 'Inspect',
                        'fa-search'            => 'Search',
                        'fa-eye'               => 'View',
                        'fa-edit'              => 'Edit',
                        'fa-trash-alt'         => 'Delete',
                        'fa-download'          => 'Download',
                        'fa-upload'            => 'Upload',
                        'fa-sync-alt'          => 'Refresh',
                        'fa-print'             => 'Print',
                        'fa-envelope'          => 'Email',
                        'fa-phone'             => 'Phone',
                        'fa-map-marker-alt'    => 'Location',
                        'fa-tag'               => 'Tag',
                        'fa-tags'              => 'Tags',
                        'fa-barcode'           => 'Barcode',
                        'fa-qrcode'            => 'QR Code',
                        'fa-lock'              => 'Lock',
                        'fa-key'               => 'Key',
                        'fa-database'          => 'Database',
                        'fa-server'            => 'Server',
                        'fa-network-wired'     => 'Network',
                    ];
                    @endphp
                    @foreach($icons as $cls => $label)
                    <div class="col-3 col-sm-2 icon-item" data-label="{{ strtolower($label) }} {{ $cls }}">
                        <button type="button"
                                class="btn btn-outline-secondary w-100 p-2 icon-pick-btn"
                                data-icon="{{ $cls }}"
                                title="{{ $label }}: {{ $cls }}">
                            <i class="fas {{ $cls }} d-block" style="font-size:18px;"></i>
                            <small style="font-size:9px;line-height:1.2;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $label }}</small>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer py-2">
                <small class="text-muted me-auto">Click an icon to select it</small>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // Filter icons on search
    const searchInput = document.getElementById('iconSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.icon-item').forEach(function(item) {
                item.style.display = item.dataset.label.includes(q) ? '' : 'none';
            });
        });
    }

    // On icon click: set value, update preview, close modal
    document.querySelectorAll('.icon-pick-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const icon = this.dataset.icon;
            const iconInput = document.getElementById('icon');
            iconInput.value = icon;

            // Update preview
            const preview = document.getElementById('iconPreview');
            if (preview) {
                preview.className = 'fas ' + icon + ' me-1';
                preview.closest('.input-group-text').classList.remove('d-none');
            }

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('iconPickerModal'));
            if (modal) modal.hide();
        });
    });
})();
</script>
