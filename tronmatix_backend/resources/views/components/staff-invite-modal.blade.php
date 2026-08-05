{{-- resources/views/components/staff-invite-modal.blade.php --}}
{{-- Redesigned invite modal using the dashboard's existing design tokens --}}

@php
    $isStaffTab = ($activeTab ?? 'staff') === 'staff';
    $targetRoute = $isStaffTab ? route('dashboard.staff.invite') : route('dashboard.admin.invite');
    $isAdminTab = ! $isStaffTab;

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
    $roles = $isStaffTab ? $staffRoles : $adminRoles;
    $defaultRole = $isStaffTab ? 'editor' : 'admin';
@endphp

{{-- ══════════════════════════════════════════════════════════════════════════
     INVITE STAFF / ADMIN MODAL
══════════════════════════════════════════════════════════════════ --}}
<div id="invite-modal"
    style="display:none;position:fixed;inset:0;z-index:9000;
           align-items:center;justify-content:center;padding:16px;
           background:var(--overlay);backdrop-filter:blur(6px);"
    x-data="inviteModalData()"
    @keydown.escape.window="closeModal()"
>
    <div
        @click.outside="closeModal()"
        style="width:100%;max-width:520px;max-height:calc(100vh - 32px);overflow-y:auto;
               border-radius:16px;background:var(--surface);border:1px solid var(--border);
               box-shadow:0 25px 60px rgba(0,0,0,0.5);font-family:Rajdhani, var(--font-kh), sans-serif;
               animation:stModalIn .3s cubic-bezier(0.34,1.2,0.64,1);"
    >
        {{-- ── Header --}}
        <div style="padding:20px 24px 16px;display:flex;align-items:center;gap:14px;
                    background:linear-gradient(135deg,#4338CA,#3730A3);border-radius:16px 16px 0 0;position:relative;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.15);
                        display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                👤
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:var(--text-lg);font-weight:800;color:#fff;letter-spacing:1px;line-height:1.2;"
                     x-text="isStaffTab ? '{{ strtoupper(__('dashboard.staff.inviteStaff')) }}' : '{{ strtoupper(__('dashboard.staff.admin')) }}'">
                </div>
                <div style="font-size:var(--text-sm);color:rgba(255,255,255,0.7);margin-top:2px;">
                    {{ $isStaffTab ? __('dashboard.staff.noStaffDesc') : 'Add a new administrator to Tronmatix Computer' }}
                </div>
            </div>
            <button type="button" @click="closeModal()"
                style="width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,0.2);
                       background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.8);cursor:pointer;
                       display:flex;align-items:center;justify-content:center;font-size:18px;
                       transition:all .2s;flex-shrink:0;"
                onmouseover="this.style.background='rgba(255,255,255,0.2)';this.style.color='#fff'"
                onmouseout="this.style.background='rgba(255,255,255,0.1)';this.style.color='rgba(255,255,255,0.8)'">
                ×
            </button>
        </div>

        {{-- ── Form --}}
        <form method="POST" action="{{ $targetRoute }}" id="invite-form" @submit.prevent="submitForm($el)">
            @csrf
            <input type="hidden" name="role_field" :value="selectedRole" />
            <div style="padding:20px 24px 8px;display:flex;flex-direction:column;gap:14px;">

                {{-- Full Name + Username --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                            {{ strtoupper(__('dashboard.staff.fullName')) }} <span style="color:#ef4444;">*</span>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;pointer-events:none;">👤</span>
                            <input type="text" name="name" x-model="name" placeholder="e.g. John Doe" required
                                style="width:100%;padding:10px 12px 10px 38px;border-radius:10px;
                                       background:var(--surface-2);border:1px solid var(--border-input);color:var(--text);
                                       font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                                       outline:none;transition:all .2s;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.15)'"
                                onblur="this.style.borderColor='var(--border-input)';this.style.boxShadow='none'" />
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                            {{ strtoupper(__('dashboard.staff.username')) }} <span style="color:#ef4444;">*</span>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;pointer-events:none;">@</span>
                            <input type="text" name="username" x-model="username" placeholder="e.g. johndoe" required
                                style="width:100%;padding:10px 12px 10px 38px;border-radius:10px;
                                       background:var(--surface-2);border:1px solid var(--border-input);color:var(--text);
                                       font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                                       outline:none;transition:all .2s;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.15)'"
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
                            <input type="email" name="email" x-model="email" placeholder="e.g. john@example.com"
                                style="width:100%;padding:10px 12px 10px 36px;border-radius:10px;
                                       background:var(--surface-2);border:1px solid var(--border-input);color:var(--text);
                                       font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                                       outline:none;transition:all .2s;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.15)'"
                                onblur="this.style.borderColor='var(--border-input)';this.style.boxShadow='none'" />
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                            {{ strtoupper(__('dashboard.table.phone')) }} <span style="color:rgba(255,255,255,0.25);font-size:10px;">(opt)</span>
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;pointer-events:none;">📱</span>
                            <input type="tel" name="phone" x-model="phone" placeholder="e.g. +855 12 345 678"
                                style="width:100%;padding:10px 12px 10px 36px;border-radius:10px;
                                       background:var(--surface-2);border:1px solid var(--border-input);color:var(--text);
                                       font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);
                                       outline:none;transition:all .2s;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.15)'"
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

                {{-- Role selector (staff only) --}}
                @if ($isStaffTab)
                <div>
                    <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:10px;">
                        ASSIGN ROLE
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        @foreach ($roles as $rKey => $rMeta)
                            <button type="button"
                                @click="selectRole('{{ $rKey }}')"
                                @keydown.enter="selectRole('{{ $rKey }}')"
                                style="position:relative;display:flex;flex-direction:column;align-items:center;gap:6px;
                                       padding:14px 10px;border-radius:12px;cursor:pointer;text-align:center;
                                       border:2px solid var(--border-input);background:var(--surface-2);
                                       transition:all .2s;font-family:Rajdhani,var(--font-kh),sans-serif;
                                       outline:none;"
                                :class="selectedRole === '{{ $rKey }}' ? 'ring-2 ring-offset-1' : ''"
                                :style="selectedRole === '{{ $rKey }}'
                                    ? 'border-color:{{ $rMeta['color'] }};background:{{ $rMeta['color'] }}0d;transform:scale(1.02);box-shadow:0 4px 16px {{ $rMeta['color'] }}22;'
                                    : ''"
                                onmouseover="if(this.dataset.selected!='1'){this.style.borderColor='rgba(255,255,255,0.2)';this.style.transform='scale(1.02)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)';}"
                                onmouseout="if(this.dataset.selected!='1'){this.style.borderColor='var(--border-input)';this.style.transform='';this.style.boxShadow='';}"
                                data-selected="{{ $rKey === $defaultRole ? '1' : '0' }}"
                            >
                                {{-- Checkmark badge --}}
                                <div x-show="selectedRole === '{{ $rKey }}'"
                                     x-transition.scale
                                     style="position:absolute;top:8px;right:8px;width:20px;height:20px;border-radius:50%;
                                            background:{{ $rMeta['color'] }};color:#fff;display:flex;align-items:center;
                                            justify-content:center;font-size:11px;line-height:1;">✓</div>

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
                @endif

                {{-- Admin password section --}}
                <div id="admin-password-section" style="{{ $isAdminTab ? '' : 'display:none;' }}">
                    <label style="display:block;font-size:var(--label-size);font-weight:700;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:6px;">
                        {{ strtoupper(__('dashboard.staff.tempPassword')) }} <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="password" id="inv-pass" value="{{ \App\Services\PasswordGenerator::generate() }}" required
                        style="width:100%;padding:10px 14px;border-radius:10px;background:var(--surface-2);
                               border:1px solid var(--border-input);color:var(--text);font-family:monospace;
                               font-size:var(--text-base);outline:none;transition:all .2s;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.15)'"
                        onblur="this.style.borderColor='var(--border-input)';this.style.boxShadow='none'" />
                    <div style="font-size:var(--text-xs);color:var(--text-muted);margin-top:4px;">
                        {{ __('dashboard.staff.tempPasswordNote') }}
                    </div>
                </div>

                {{-- Staff footer tip --}}
                <div id="staff-invite-hint" style="{{ $isStaffTab ? '' : 'display:none;' }}">
                    <div style="padding:10px 14px;border-radius:8px;background:rgba(99,102,241,0.06);
                                border:1px solid rgba(99,102,241,0.15);font-size:var(--text-xs);color:var(--text-muted);line-height:1.5;">
                        💡 After creating the invite, <strong style="color:#6366f1;">copy the link</strong> from the success
                        message and send it to this person. They'll set their own password and the account is activated automatically.
                    </div>
                </div>
            </div>

            {{-- ── Footer --}}
            <div style="padding:12px 24px 20px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border);">
                <button type="button" @click="closeModal()"
                    style="padding:10px 20px;border-radius:9px;border:1px solid var(--border-input);
                           background:transparent;color:var(--text-muted);font-family:Rajdhani,var(--font-kh),sans-serif;
                           font-size:var(--text-base);font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .2s;"
                    onmouseover="this.style.color='var(--text)';this.style.borderColor='rgba(255,255,255,0.25)'"
                    onmouseout="this.style.color='var(--text-muted)';this.style.borderColor='var(--border-input)'">
                    CANCEL
                </button>
                <button type="submit" id="invite-submit-btn"
                    :disabled="!canSubmit()"
                    :style="canSubmit()
                        ? 'padding:10px 22px;border-radius:9px;border:none;cursor:pointer;background:linear-gradient(135deg,#4F46E5,#4338CA);color:#fff;font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);font-weight:800;letter-spacing:1px;box-shadow:0 4px 16px rgba(79,70,229,0.3);transition:all .2s;'
                        : 'padding:10px 22px;border-radius:9px;border:1px solid var(--border-input);cursor:not-allowed;background:var(--surface-2);color:var(--text-muted);font-family:Rajdhani,var(--font-kh),sans-serif;font-size:var(--text-base);font-weight:700;letter-spacing:1px;'"
                    onmouseover="if(this.disabled!==true){this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(79,70,229,0.4)';}"
                    onmouseout="if(this.disabled!==true){this.style.transform='';this.style.boxShadow='0 4px 16px rgba(79,70,229,0.3)';}">
                    <span x-text="isStaffTab ? '✉ {{ strtoupper(__('dashboard.staff.inviteStaff')) }}' : '🛡️ {{ strtoupper(__('dashboard.staff.inviteAdmin')) }}'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     ALPINE.JS LOGIC (Alpine loaded once in dashboard layout)
══════════════════════════════════════════════════════════════════════════ --}}
<script>
function inviteModalData() {
    return {
        isStaffTab: {{ $isStaffTab ? 'true' : 'false' }},
        name: '',
        username: '',
        email: '',
        phone: '',
        selectedRole: '{{ $defaultRole }}',

        selectRole(key) {
            this.selectedRole = key;
        },

        canSubmit() {
            return this.name.trim().length > 0
                && this.username.trim().length >= 3
                && (this.email.trim().length > 0 || this.phone.trim().length > 0);
        },

        submitForm(form) {
            if (!this.canSubmit()) return;

            // Keep the correct hidden role input synced (staff uses `role`, admin uses `admin_role`)
            const roleInput = form.querySelector('input[name="role_field"]');
            if (roleInput) {
                roleInput.name = this.isStaffTab ? 'role' : 'admin_role';
                roleInput.value = this.selectedRole;
            }

            // Close modal after short delay to let user see button state
            const btn = document.getElementById('invite-submit-btn');
            if (btn) { btn.disabled = true; btn.textContent = '...'; }

            setTimeout(() => {
                form.submit();
            }, 150);
        },

        closeModal() {
            const modal = document.getElementById('invite-modal');
            if (modal) modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
}
</script>

{{-- ══════════════════════════════════════════════════════════════════════════
     OPEN / CLOSE HELPERS — call these from the parent page
══════════════════════════════════════════════════════════════════════════ --}}
<script>
function openInviteModal() {
    const tab = currentTab || 'staff';
    switchTab(tab);
    const m = document.getElementById('invite-modal');
    if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
}

function closeInviteModal() {
    const m = document.getElementById('invite-modal');
    if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
}

document.getElementById('invite-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeInviteModal();
});
</script>
