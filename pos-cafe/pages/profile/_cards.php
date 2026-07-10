<div class="panel" style="margin-bottom:22px">
    <div class="profile-summary">
        <div class="profile-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
        <div class="profile-info">
            <div class="profile-name"><?= htmlspecialchars($username) ?></div>
            <div class="profile-badges">
                <span class="badge" style="background:<?= $role_color ?>22;color:<?= $role_color ?>;border:1px solid <?= $role_color ?>44">
                    <i class="fa-solid <?= htmlspecialchars($role_icon) ?>"></i>
                    <?= htmlspecialchars($role_display) ?>
                </span>
                <span class="badge <?= $has_sq ? 'badge-ok' : 'badge-missing' ?>">
                    <i class="fa-solid fa-<?= $has_sq ? 'shield-check' : 'shield-exclamation' ?>"></i>
                    Security Q: <?= $has_sq ? 'Set' : 'Not Set' ?>
                </span>
            </div>
        </div>
    </div>
    <div class="panel-divider"></div>
    <div class="profile-stats">
        <div class="pstat">
            <span class="pstat-val"><?= htmlspecialchars($username) ?></span>
            <span class="pstat-lbl">Username</span>
        </div>
        <div class="pstat">
            <span class="pstat-val"><?= htmlspecialchars($role_display) ?></span>
            <span class="pstat-lbl">Role</span>
        </div>
        <div class="pstat">
            <span class="pstat-val"><?= $has_sq ? 'Active' : 'Not set' ?></span>
            <span class="pstat-lbl">Account Recovery</span>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-icon"><i class="fa-solid fa-key"></i></div>
        <div>
            <div class="panel-title">Change Password</div>
            <div class="panel-subtitle">Use a strong password to keep your account secure</div>
        </div>
    </div>
    <div class="panel-body">
        <form method="POST" id="passForm">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="active_tab" value="password">

            <div class="field-row">
                <div class="field-group">
                    <label class="fl-label">Current Password</label>
                    <div class="input-wrap">
                        <input type="password" name="current_password" id="currentPass" class="fl-input" required autocomplete="current-password">
                        <button type="button" class="fl-toggle" data-target="currentPass"><i class="fa-solid fa-eye-slash"></i></button>
                    </div>
                </div>
            </div>

            <div class="field-row two">
                <div class="field-group">
                    <label class="fl-label">New Password</label>
                    <div class="input-wrap">
                        <input type="password" name="new_password" id="newPass" class="fl-input" required autocomplete="new-password">
                        <button type="button" class="fl-toggle" data-target="newPass"><i class="fa-solid fa-eye-slash"></i></button>
                    </div>
                    <div class="strength-wrap">
                        <div class="s-bars">
                            <div class="s-bar" id="p-sb1"></div>
                            <div class="s-bar" id="p-sb2"></div>
                            <div class="s-bar" id="p-sb3"></div>
                            <div class="s-bar" id="p-sb4"></div>
                        </div>
                        <span class="s-text" id="p-slabel">—</span>
                        <div class="s-reqs">
                            <span class="req" id="p-req-len"><i class="fa-solid fa-circle-dot"></i> 8+ chars</span>
                            <span class="req" id="p-req-upper"><i class="fa-solid fa-circle-dot"></i> Uppercase</span>
                            <span class="req" id="p-req-num"><i class="fa-solid fa-circle-dot"></i> Number</span>
                            <span class="req" id="p-req-sym"><i class="fa-solid fa-circle-dot"></i> Symbol</span>
                        </div>
                    </div>
                </div>
                <div class="field-group">
                    <label class="fl-label">Confirm New Password</label>
                    <div class="input-wrap">
                        <input type="password" name="confirm_password" id="confirmPass" class="fl-input" required autocomplete="new-password">
                        <button type="button" class="fl-toggle" data-target="confirmPass"><i class="fa-solid fa-eye-slash"></i></button>
                    </div>
                    <div class="match-hint" id="matchHint"></div>
                </div>
            </div>

            <button type="submit" class="btn-primary" id="passBtn" disabled>
                <i class="fa-solid fa-floppy-disk"></i> Update Password
            </button>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <div>
            <div class="panel-title">Security Question</div>
            <div class="panel-subtitle">Used to verify your identity if you forget your password</div>
        </div>
    </div>
    <div class="panel-body">
        <?php if ($has_sq): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--emerald-dim);border:1px solid rgba(20,184,166,.2);border-radius:var(--r-sm);margin-bottom:16px">
            <i class="fa-solid fa-circle-check" style="color:var(--emerald)"></i>
            <div>
                <div style="font-size:13px;font-weight:600;color:var(--text)">Security question is set</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px"><?= htmlspecialchars($user['security_question']) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" id="sqForm">
            <input type="hidden" name="action" value="save_security">
            <input type="hidden" name="active_tab" value="security">

            <div class="field-row">
                <div class="field-group">
                    <label class="fl-label">Security Question</label>
                    <select name="security_question" id="sqSelect" class="fl-input select-input" required>
                        <option value="" disabled <?= $has_sq ? '' : 'selected' ?>>— Choose a question —</option>
                        <?php
                        $questions = [
                            "What is your mother's maiden name?",
                            "What was the name of your first pet?",
                            "What city were you born in?",
                            "What was the name of your primary school?",
                            "What is your oldest sibling's middle name?",
                            "What was the make of your first car?",
                            "What street did you grow up on?",
                            "What is your favourite movie?",
                        ];
                        foreach ($questions as $q):
                        $sel = ($user['security_question'] === $q) ? 'selected' : '';
                        ?>
                        <option value="<?= htmlspecialchars($q) ?>" <?= $sel ?>><?= htmlspecialchars($q) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field-row two">
                <div class="field-group">
                    <label class="fl-label">Your Answer</label>
                    <input type="text" name="security_answer" id="sqAnswer" class="fl-input" placeholder="Enter your answer" required autocomplete="off">
                    <p class="fl-hint"><i class="fa-solid fa-info-circle" style="color:var(--teal)"></i> Not case-sensitive. Keep it memorable.</p>
                </div>
                <div class="field-group">
                    <label class="fl-label">Confirm Your Password</label>
                    <div class="input-wrap">
                        <input type="password" name="verify_password" id="sqPass" class="fl-input" placeholder="Current password" required autocomplete="current-password">
                        <button type="button" class="fl-toggle" data-target="sqPass"><i class="fa-solid fa-eye-slash"></i></button>
                    </div>
                    <p class="fl-hint">Required to confirm changes.</p>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-shield-check"></i> <?= $has_sq ? 'Update' : 'Save' ?> Security Question
            </button>
        </form>
    </div>
</div>
