{{-- resources/views/components/staff-invite-modal.blade.php --}}
{{-- Invite modal for staff & admin. Tab-aware: shows the staff form or the
     admin form depending on which tab was active when the modal was opened.
     Vanilla JS (no Alpine) — matches the pattern of every other dashboard modal. --}}

@php
    $isStaffTab = ($activeTab ?? 'staff') === 'staff';
    $staffRoute  = route('dashboard.staff.invite');
    $adminRoute  = route('dashboard.admin.invite');

    // Role data for the selector
    $staffRoles = [
        'editor'    => ['icon' => '✏️',  'label' => __('dashboard.staff.editor'),    'desc' => __('dashboard.staff.editorDesc'),    'color' => '#3b82f6'],
        'seller'    => ['icon' => '🏪',  'label' => __('dashboard.staff.seller'),    'desc' => __('dashboard.staff.sellerDesc'),    'color' => '#10b981'],
        'delivery'  => ['icon' => '🚚',  'label' => __('dashboard.staff.delivery'),  'desc' => __('dashboard.staff.deliveryDesc'),  'color' => '#a855f7'],
        'developer' => ['icon' => '💻',  'label' => __('dashboard.staff.developer'), 'desc' => __('dashboard.staff.developerDesc'), 'color' => '#06b6d4'],
    ];
    $adminRoles = [
        'superadmin' => ['icon' => '👑', 'label' => __('dashboard.staff.superAdmin'), 'desc' => __('dashboard.staff.fullSystemOwner'), 'color' => '#F97316'],
        'admin'      => ['icon' => '🛡️', 'label' => __('dashboard.staff.admin'),       'desc' => __('dashboard.staff.fullAccess'),     'color' => '#fb923c'],
    ];
    $defaultStaffRole  = 'editor';
    $defaultAdminRole  = 'admin';
    $_brandGrad = 'linear-gradient(135deg,#F97316,#ea580c)';
@endphp

{{-- ══════════════════════════════════════════════════════════════════════════
     INVITE STAFF / ADMIN MODAL
══════════════════════════════════════════════════════════════════ --}}
<div id="invite-modal"
    style="display:none !important;position:fixed;inset:0;z-index:99999;
           align-items:center;justify-content:center;padding:16px;
           background:var(--overlay);backdrop-filter:blur(6px);"
    onclick="if(event.target===this) closeInviteModal();"
>
    <div
        onclick="event.stopPropagation();"
        style="width:100%;max-width:520px;max-height:calc(100vh - 32px);overflow-y:auto;
               border-radius:16px;background:var(--surface);border:1px solid var(--border);
               box-shadow:0 25px 60px rgba(0,0,0,0.5);font-family:Rajdhani, var(--font-kh), sans-serif;
               animation:stModalIn .3s cubic-bezier(0.34,1.2,0.64,1);"
    >
        {{-- ── Header (brand orange) --}}
        <div style="padding:20px 24px 16px;display:flex;align-items:center;gap:14px;
                    background:{{ $_brandGrad }};border-radius:16px 16px 0 0;position:relative;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.2);
                        display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                👤
            </div>
            <div style="flex:1;min-width:0;">
                <div id="invite-modal-title"
                    style="font-size:var(--text-lg);font-weight:800;color:#fff;letter-spacing:1px;line-height:1.2;">
                    {{ strtoupper(__('dashboard.staff.inviteStaff')) }}
                </div>
                <div id="invite-modal-sub" style="font-size:var(--text-sm);color:rgba(255,255,255,0.85);margin-top:2px;">
                    {{ __('dashboard.staff.noStaffDesc') }}
                </div>
            </div>
            <button type="button" onclick="closeInviteModal()"
                style="width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,0.25);
                       background:rgba(255,255,255,0.15);color:#fff;cursor:pointer;
                       display:flex;align-items:center;justify-content:center;font-size:18px;
                       transition:all .2s;flex-shrink:0;"
                onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                ×
            </button>
        </div>

        {{-- ══ STAFF FORM (tab: staff) ══ --}}
        <form method="POST" action="{{ $staffRoute }}" id="invite-form-staff"
            style="{{ $isStaffTab ? '' : 'display:none;' }}"
            onsubmit="return inviteSubmitForm(this, 'staff')">
            @csrf
            <input type="hidden" name="role" id="invite-staff-role" value="{{ $defaultStaffRole }}" />
            <div style="padding:20px 24px 8px;display:flex;flex-direction:column;gap:14px;">

                {{-- Full Name + Username --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                            {{ strtoupper(__('dashboard.staff.fullName')) }} <span style="color:#ef4444;">*</span>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;pointer-events:none;">👤</span>
                            <input type="text" name="name" id="invite-staff-name" placeholder="e.g. John Doe" required
                                oninput="inviteUpdateSubmit()"
                                style="width:100%;padding:10px 12px 10px 38px;border-radius:10px;
                                       background:var(--surface-2);border:1px solid var(--border-input);color:var(--text);
                                       font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                                       outline:none;transition:all .2s;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#F97316';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.15)'"
                                onblur="this.style.borderColor='var(--border-input)';this.style.boxShadow='none'" />
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                            {{ strtoupper(__('dashboard.staff.username')) }} <span style="color:#ef4444;">*</span>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;pointer-events:none;">@</span>
                            <input type="text" name="username" id="invite-staff-username" placeholder="e.g. johndoe" required
                                oninput="inviteUpdateSubmit()"
                                style="width:100%;padding:10px 12px 10px 38px;border-radius:10px;
                                       background:var(--surface-2);border:1px solid var(--border-input);color:var(--text);
                                       font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                                       outline:none;transition:all .2s;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#F97316';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.15)'"
                                onblur="this.style.borderColor='var(--border-input)';this.style.boxShadow='none'" />
                        </div>
                    </div>
                </div>

                {{-- Email + Phone --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                            {{ strtoupper(__('dashboard.table.email')) }} <span style="color:rgba(255,255,255,0.25);font-size:10px;">(opt)</span>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none;">✉</span>
                            <input type="email" name="email" id="invite-staff-email" placeholder="e.g. john@example.com"
                                oninput="inviteUpdateSubmit()"
                                style="width:100%;padding:10px 12px 10px 36px;border-radius:10px;
                                       background:var(--surface-2);border:1px solid var(--border-input);color:var(--text);
                                       font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                                       outline:none;transition:all .2s;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#F97316';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.15)'"
                                onblur="this.style.borderColor='var(--border-input)';this.style.boxShadow='none'" />
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                            {{ strtoupper(__('dashboard.table.phone')) }} <span style="color:rgba(255,255,255,0.25);font-size:10px;">(opt)</span>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;pointer-events:none;">📱</span>
                            <input type="tel" name="phone" id="invite-staff-phone" placeholder="e.g. +855 12 345 678"
                                oninput="inviteUpdateSubmit()"
                                style="width:100%;padding:10px 12px 10px 36px;border-radius:10px;
                                       background:var(--surface-2);border:1px solid var(--border-input);color:var(--text);
                                       font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                                       outline:none;transition:all .2s;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#F97316';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.15)'"
                                onblur="this.style.borderColor='var(--border-input)';this.style.boxShadow='none'" />
                        </div>
                    </div>
                </div>

                {{-- Contact helper text --}}
                <div style="display:flex;align-items:flex-start;gap:8px;font-size:var(--text-xs);color:var(--text-muted);line-height:1.4;">
                    <span style="font-size:13px;flex-shrink:0;margin-top:1px;">ℹ️</span>
                    <span>
                        {{ __('Provide at least one — the invited person can add the other after accepting.', [], 'en') != 'Provide at least one — the invited person can add the other after accepting.'
                            ? __('Provide at least one — the invited person can add the other after accepting.')
                            : 'Provide at least one — the invited person can add the other after accepting.'
                        }}
                    </span>
                </div>

                {{-- Role selector (staff) --}}
                <div>
                    <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:10px;">
                        ASSIGN ROLE
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        @foreach ($staffRoles as $rKey => $rMeta)
                            <button type="button"
                                class="invite-role-opt"
                                data-table="staff"
                                data-role="{{ $rKey }}"
                                data-color="{{ $rMeta['color'] }}"
                                data-selected="{{ $rKey === $defaultStaffRole ? '1' : '0' }}"
                                onclick="inviteSelectRole(this,'staff')"
                                onmouseover="inviteRoleHover(this,true)"
                                onmouseout="inviteRoleHover(this,false)"
                                style="position:relative;display:flex;flex-direction:column;align-items:center;gap:6px;
                                       padding:14px 10px;border-radius:12px;cursor:pointer;text-align:center;
                                       border:2px solid {{ $rKey === $defaultStaffRole ? $rMeta['color'] : 'var(--border-input)' }};
                                       background:{{ $rKey === $defaultStaffRole ? $rMeta['color'] . '0d' : 'var(--surface-2)' }};
                                       transition:all .2s;font-family:Rajdhani,var(--font-kh),sans-serif;
                                       outline:none;{{ $rKey === $defaultStaffRole ? 'transform:scale(1.02);box-shadow:0 4px 16px ' . $rMeta['color'] . '22;' : '' }}"
                            >
                                <div class="invite-role-check"
                                    style="position:absolute;top:8px;right:8px;width:20px;height:20px;border-radius:50%;
                                           background:{{ $rMeta['color'] }};color:#fff;display:{{ $rKey === $defaultStaffRole ? 'flex' : 'none' }};
                                           align-items:center;justify-content:center;font-size:11px;line-height:1;">✓</div>
                                <div style="width:36px;height:36px;border-radius:50%;background:{{ $rMeta['color'] }}18;
                                            display:flex;align-items:center;justify-content:center;font-size:18px;">
                                    {{ $rMeta['icon'] }}
                                </div>
                                <div style="font-size:var(--text-base);font-weight:800;color:{{ $rMeta['color'] }};letter-spacing:0.5px;">
                                    {{ strtoupper($rMeta['label']) }}
                                </div>
                                <div style="font-size:var(--text-xs);color:var(--text-muted);line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $rMeta['desc'] }}
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Staff footer tip --}}
                <div>
                    <div style="padding:10px 14px;border-radius:8px;background:rgba(249,115,22,0.06);
                                border:1px solid rgba(249,115,22,0.15);font-size:var(--text-xs);color:var(--text-muted);line-height:1.5;">
                        💡 After creating the invite, <strong style="color:#F97316;">copy the link</strong> from the success
                        message and send it to this person. They'll set their own password and the account is activated automatically.
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div style="padding:12px 24px 20px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border);">
                <button type="button" onclick="closeInviteModal()"
                    style="padding:10px 20px;border-radius:9px;border:1px solid var(--border-input);
                           background:transparent;color:var(--text-muted);font-family:Rajdhani,var(--font-kh),sans-serif;
                           font-size:var(--text-base);font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .2s;"
                    onmouseover="this.style.color='var(--text)';this.style.borderColor='rgba(255,255,255,0.25)'"
                    onmouseout="this.style.color='var(--text-muted)';this.style.borderColor='var(--border-input)'">
                    CANCEL
                </button>
                <button type="submit" id="invite-staff-submit" disabled
                    style="padding:10px 22px;border-radius:9px;border:none;cursor:not-allowed;opacity:.55;
                           background:{{ $_brandGrad }};color:#fff;
                           font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                           font-weight:800;letter-spacing:1px;box-shadow:0 4px 16px rgba(249,115,22,0.3);transition:all .2s;">
                    ✉ {{ strtoupper(__('dashboard.staff.inviteStaff')) }}
                </button>
            </div>
        </form>

        {{-- ══ ADMIN FORM (tab: admins) ══ --}}
        <form method="POST" action="{{ $adminRoute }}" id="invite-form-admin"
            style="{{ $isStaffTab ? 'display:none;' : '' }}"
            onsubmit="return inviteSubmitForm(this, 'admin')">
            @csrf
            <input type="hidden" name="admin_role" id="invite-admin-role" value="{{ $defaultAdminRole }}" />
            <div style="padding:20px 24px 8px;display:flex;flex-direction:column;gap:14px;">

                {{-- Full Name --}}
                <div>
                    <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                        {{ strtoupper(__('dashboard.staff.fullName')) }} <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;pointer-events:none;">👤</span>
                        <input type="text" name="name" id="invite-admin-name" placeholder="e.g. John Doe" required
                            oninput="inviteUpdateSubmit()"
                            style="width:100%;padding:10px 12px 10px 38px;border-radius:10px;
                                   background:var(--surface-2);border:1px solid var(--border-input);color:var(--text);
                                   font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                                   outline:none;transition:all .2s;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#F97316';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.15)'"
                            onblur="this.style.borderColor='var(--border-input)';this.style.boxShadow='none'" />
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                        {{ strtoupper(__('dashboard.table.email')) }} <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none;">✉</span>
                        <input type="email" name="email" id="invite-admin-email" placeholder="e.g. john@example.com" required
                            oninput="inviteUpdateSubmit()"
                            style="width:100%;padding:10px 12px 10px 36px;border-radius:10px;
                                   background:var(--surface-2);border:1px solid var(--border-input);color:var(--text);
                                   font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                                   outline:none;transition:all .2s;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#F97316';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.15)'"
                            onblur="this.style.borderColor='var(--border-input)';this.style.boxShadow='none'" />
                    </div>
                </div>

                {{-- Admin role selector --}}
                <div>
                    <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:10px;">
                        ASSIGN ROLE
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        @foreach ($adminRoles as $rKey => $rMeta)
                            <button type="button"
                                class="invite-role-opt"
                                data-table="admins"
                                data-role="{{ $rKey }}"
                                data-color="{{ $rMeta['color'] }}"
                                data-selected="{{ $rKey === $defaultAdminRole ? '1' : '0' }}"
                                onclick="inviteSelectRole(this,'admins')"
                                onmouseover="inviteRoleHover(this,true)"
                                onmouseout="inviteRoleHover(this,false)"
                                style="position:relative;display:flex;flex-direction:column;align-items:center;gap:6px;
                                       padding:14px 10px;border-radius:12px;cursor:pointer;text-align:center;
                                       border:2px solid {{ $rKey === $defaultAdminRole ? $rMeta['color'] : 'var(--border-input)' }};
                                       background:{{ $rKey === $defaultAdminRole ? $rMeta['color'] . '0d' : 'var(--surface-2)' }};
                                       transition:all .2s;font-family:Rajdhani,var(--font-kh),sans-serif;
                                       outline:none;{{ $rKey === $defaultAdminRole ? 'transform:scale(1.02);box-shadow:0 4px 16px ' . $rMeta['color'] . '22;' : '' }}"
                            >
                                <div class="invite-role-check"
                                    style="position:absolute;top:8px;right:8px;width:20px;height:20px;border-radius:50%;
                                           background:{{ $rMeta['color'] }};color:#fff;display:{{ $rKey === $defaultAdminRole ? 'flex' : 'none' }};
                                           align-items:center;justify-content:center;font-size:11px;line-height:1;">✓</div>
                                <div style="width:36px;height:36px;border-radius:50%;background:{{ $rMeta['color'] }}18;
                                            display:flex;align-items:center;justify-content:center;font-size:18px;">
                                    {{ $rMeta['icon'] }}
                                </div>
                                <div style="font-size:var(--text-base);font-weight:800;color:{{ $rMeta['color'] }};letter-spacing:0.5px;">
                                    {{ strtoupper($rMeta['label']) }}
                                </div>
                                <div style="font-size:var(--text-xs);color:var(--text-muted);line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $rMeta['desc'] }}
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Temporary password --}}
                <div>
                    <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                        {{ strtoupper(__('dashboard.staff.tempPassword')) }} <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="password" id="invite-admin-pass" value="{{ \App\Services\PasswordGenerator::generate() }}" required
                        style="width:100%;padding:10px 14px;border-radius:10px;background:var(--surface-2);
                               border:1px solid var(--border-input);color:var(--text);font-family:monospace;
                               font-size:var(--text-base);outline:none;transition:all .2s;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#F97316';this.style.boxShadow='0 0 0 3px rgba(249,115,22,0.15)'"
                        onblur="this.style.borderColor='var(--border-input)';this.style.boxShadow='none'" />
                    <div style="font-size:var(--text-xs);color:var(--text-muted);margin-top:4px;">
                        {{ __('dashboard.staff.tempPasswordNote') }}
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div style="padding:12px 24px 20px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border);">
                <button type="button" onclick="closeInviteModal()"
                    style="padding:10px 20px;border-radius:9px;border:1px solid var(--border-input);
                           background:transparent;color:var(--text-muted);font-family:Rajdhani,var(--font-kh),sans-serif;
                           font-size:var(--text-base);font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .2s;"
                    onmouseover="this.style.color='var(--text)';this.style.borderColor='rgba(255,255,255,0.25)'"
                    onmouseout="this.style.color='var(--text-muted)';this.style.borderColor='var(--border-input)'">
                    CANCEL
                </button>
                <button type="submit" id="invite-admin-submit" disabled
                    style="padding:10px 22px;border-radius:9px;border:none;cursor:not-allowed;opacity:.55;
                           background:{{ $_brandGrad }};color:#fff;
                           font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                           font-weight:800;letter-spacing:1px;box-shadow:0 4px 16px rgba(249,115,22,0.3);transition:all .2s;">
                    🛡️ {{ strtoupper(__('dashboard.staff.inviteAdmin')) }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Vanilla JS LOGIC ─────────────────────────────────────────────────────── --}}
<script>
// Which tab's form is currently shown in the modal.
var inviteModalMode = '{{ $isStaffTab ? "staff" : "admin" }}';

function inviteRoleHover(btn, entering) {
    // Only apply hover styling to the non-selected option.
    if (btn.dataset.selected === '1') return;
    if (entering) {
        btn.style.borderColor = 'rgba(255,255,255,0.2)';
        btn.style.transform = 'scale(1.02)';
        btn.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
    } else {
        btn.style.borderColor = 'var(--border-input)';
        btn.style.transform = '';
        btn.style.boxShadow = '';
    }
}

function inviteSelectRole(btn, table) {
    var hiddenId = table === 'admins' ? 'invite-admin-role' : 'invite-staff-role';
    document.querySelectorAll('.invite-role-opt[data-table="' + table + '"]').forEach(function(opt) {
        var isSel = opt === btn;
        opt.dataset.selected = isSel ? '1' : '0';
        var check = opt.querySelector('.invite-role-check');
        if (check) check.style.display = isSel ? 'flex' : 'none';
        var c = opt.dataset.color;
        opt.style.borderColor = isSel ? c : 'var(--border-input)';
        opt.style.background = isSel ? (c + '0d') : 'var(--surface-2)';
        opt.style.transform = isSel ? 'scale(1.02)' : '';
        opt.style.boxShadow = isSel ? ('0 4px 16px ' + c + '22') : '';
    });
    var hi = document.getElementById(hiddenId);
    if (hi) hi.value = btn.dataset.role;
}

function inviteReadInputs() {
    function val(id) {
        var el = document.getElementById(id);
        return el ? (el.value || '') : '';
    }
    if (inviteModalMode === 'admin') {
        return { name: val('invite-admin-name').trim(), email: val('invite-admin-email').trim() };
    }
    return {
        name: val('invite-staff-name').trim(),
        username: val('invite-staff-username').trim(),
        email: val('invite-staff-email').trim(),
        phone: val('invite-staff-phone').trim()
    };
}

function inviteCanSubmit() {
    var v = inviteReadInputs();
    if (inviteModalMode === 'admin') {
        // Admin: name + email (password auto-generated, always present)
        return v.name.length > 0 && v.email.length > 0;
    }
    // Staff: name + username + (email OR phone)
    var hasContact = v.email.length > 0 || v.phone.length > 0;
    return v.name.length > 0 && v.username.length >= 3 && hasContact;
}

function inviteUpdateSubmit() {
    var btnId = inviteModalMode === 'admin' ? 'invite-admin-submit' : 'invite-staff-submit';
    var btn = document.getElementById(btnId);
    if (!btn) return;
    var ok = inviteCanSubmit();
    btn.disabled = !ok;
    btn.style.opacity = ok ? '1' : '0.55';
    btn.style.cursor = ok ? 'pointer' : 'not-allowed';
}

function inviteSubmitForm(form, mode) {
    if (!inviteCanSubmit()) return false;

    var btn = document.getElementById(mode === 'admin' ? 'invite-admin-submit' : 'invite-staff-submit');
    if (btn) { btn.disabled = true; btn.textContent = '...'; }

    setTimeout(function() { form.submit(); }, 150);
    return false;
}
</script>

{{-- ── OPEN / CLOSE HELPERS ─────────────────────────────────────────────────── --}}
<script>
var inviteTitleStaff = '{{ strtoupper(__("dashboard.staff.inviteStaff")) }}';
var inviteSubStaff = '{{ __("dashboard.staff.noStaffDesc") }}';
var inviteTitleAdmin = '{{ strtoupper(__("dashboard.staff.inviteAdmin")) }}';
var inviteSubAdmin = 'Add a new administrator to Tronmatix Computer';

function openInviteModal() {
    const tab = typeof currentTab !== 'undefined' ? currentTab : 'staff';
    if (typeof switchTab === 'function') {
        switchTab(tab);
    }

    // Show the form that matches the currently-active tab.
    const showStaff = tab !== 'admins';
    inviteModalMode = showStaff ? 'staff' : 'admin';
    const fStaff = document.getElementById('invite-form-staff');
    const fAdmin = document.getElementById('invite-form-admin');
    if (fStaff) fStaff.style.display = showStaff ? '' : 'none';
    if (fAdmin) fAdmin.style.display = showStaff ? 'none' : '';

    // Update header text.
    const title = document.getElementById('invite-modal-title');
    const sub = document.getElementById('invite-modal-sub');
    if (title) title.textContent = showStaff ? inviteTitleStaff : inviteTitleAdmin;
    if (sub) sub.textContent = showStaff ? inviteSubStaff : inviteSubAdmin;

    // Move to top of body to avoid z-index conflicts.
    const m = document.getElementById('invite-modal');
    if (m) {
        document.body.appendChild(m);
        m.style.setProperty('display', 'flex', 'important');
        document.body.style.overflow = 'hidden';
    }

    inviteUpdateSubmit();
}

function closeInviteModal() {
    const m = document.getElementById('invite-modal');
    if (m) {
        m.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
    }
}
</script>
