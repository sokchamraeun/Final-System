<script>
function toggleTheme() {
    var html = document.documentElement;
    var light = html.getAttribute('data-theme') === 'light';
    if (light) { html.removeAttribute('data-theme'); localStorage.setItem('theme', 'dark'); }
    else       { html.setAttribute('data-theme', 'light'); localStorage.setItem('theme', 'light'); }
    syncThemeToggle();
}
function syncThemeToggle() {
    var light = document.documentElement.getAttribute('data-theme') === 'light';
    var icon = document.getElementById('themeIcon');
    var text = document.getElementById('themeText');
    if (icon) icon.className = light ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    if (text) text.textContent = light ? 'Light' : 'Dark';
}
syncThemeToggle();

document.querySelectorAll('.fl-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var inp  = document.getElementById(btn.dataset.target);
        var icon = btn.querySelector('i');
        if (!inp) return;
        var show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        icon.className = show ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
    });
});

var newPass     = document.getElementById('newPass');
var confirmPass = document.getElementById('confirmPass');
var passBtn     = document.getElementById('passBtn');
var matchHint   = document.getElementById('matchHint');

var colors  = ['#e74c3c','#f39c12','#f1c40f','#14B8A6'];
var sLabels = ['Too Weak','Weak','Fair','Strong','Very Strong'];
var bars    = [1,2,3,4].map(function(i){ return document.getElementById('p-sb'+i); });
var slabel  = document.getElementById('p-slabel');
var reqs    = {
    len:   document.getElementById('p-req-len'),
    upper: document.getElementById('p-req-upper'),
    num:   document.getElementById('p-req-num'),
    sym:   document.getElementById('p-req-sym'),
};

function getScore(v) {
    var s = 0;
    if (v.length >= 8) s++;
    if (/[A-Z]/.test(v)) s++;
    if (/[0-9]/.test(v)) s++;
    if (/[^a-zA-Z0-9]/.test(v)) s++;
    return s;
}

function updateMeter() {
    var v = newPass.value;
    var score = getScore(v);

    bars.forEach(function(b, i) {
        b.style.background = i < score ? colors[score - 1] : 'rgba(255,255,255,0.08)';
    });
    slabel.textContent = v.length ? sLabels[score] : '—';
    slabel.style.color = v.length && score > 0 ? colors[score - 1] : 'var(--text-muted)';

    reqs.len.classList.toggle('met',   v.length >= 8);
    reqs.upper.classList.toggle('met', /[A-Z]/.test(v));
    reqs.num.classList.toggle('met',   /[0-9]/.test(v));
    reqs.sym.classList.toggle('met',   /[^a-zA-Z0-9]/.test(v));

    checkPassReady();
}

function checkPassReady() {
    var v = newPass.value;
    var c = confirmPass.value;
    var strong = getScore(v) >= 3;
    var match  = v === c && v.length > 0;

    passBtn.disabled = !(strong && match && document.getElementById('currentPass').value.length > 0);

    if (c.length > 0) {
        if (match) {
            matchHint.innerHTML = '<span style="color:var(--success)"><i class="fa-solid fa-check"></i> Passwords match</span>';
        } else {
            matchHint.innerHTML = '<span style="color:var(--danger)"><i class="fa-solid fa-xmark"></i> Passwords do not match</span>';
        }
    } else {
        matchHint.innerHTML = '';
    }
}

newPass.addEventListener('input', updateMeter);
confirmPass.addEventListener('input', checkPassReady);
document.getElementById('currentPass').addEventListener('input', checkPassReady);
</script>
