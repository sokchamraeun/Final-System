<?php
declare(strict_types=1);
/* Orders board — announcement banners (populated by JS from the fetch payload). */
?>
<div id="annContainer" style="max-width:900px;margin:0 auto 0;padding:0 20px;"></div>
<style>
@keyframes annIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
</style>
<script>
var _annColors = {
    info:    ['#5bc0de','rgba(91,192,222,.08)','rgba(91,192,222,.2)','info-circle'],
    warning: ['#f39c12','rgba(243,156,18,.08)','rgba(243,156,18,.2)','triangle-exclamation'],
    urgent:  ['#e74c3c','rgba(231,76,60,.1)',  'rgba(231,76,60,.25)','circle-exclamation'],
};
function _annDismissed() { return JSON.parse(localStorage.getItem('ann_dismissed') || '[]'); }
function dismissAnn(btn, id) {
    id = parseInt(id);
    var banner = btn.closest('.ann-banner');
    banner.style.transition = 'opacity .3s, transform .3s';
    banner.style.opacity = '0';
    banner.style.transform = 'translateY(-6px)';
    setTimeout(function(){ banner.remove(); }, 300);
    var dismissed = _annDismissed();
    if (!dismissed.includes(id)) { dismissed.push(id); localStorage.setItem('ann_dismissed', JSON.stringify(dismissed)); }
}
function _buildAnnBanner(ann) {
    var c = _annColors[ann.type] || _annColors.info;
    var ac = c[0], abg = c[1], abr = c[2], ai = c[3];
    var div = document.createElement('div');
    div.className = 'ann-banner';
    div.dataset.id = ann.id;
    div.style.cssText = 'display:flex;align-items:flex-start;gap:14px;padding:14px 18px;margin-bottom:10px;border-radius:14px;background:'+abg+';border:1px solid '+abr+';animation:annIn .4s ease both;';
    div.innerHTML =
        '<i class="fa-solid fa-'+ai+'" style="color:'+ac+';font-size:16px;margin-top:2px;flex-shrink:0"></i>' +
        '<div style="flex:1;min-width:0">' +
          '<div style="font-size:13px;font-weight:700;color:var(--ink);margin-bottom:3px">'+_escAnn(ann.title)+'</div>' +
          '<div style="font-size:12.5px;color:var(--muted);line-height:1.55">'+_escAnn(ann.message).replace(/\n/g,'<br>')+'</div>' +
        '</div>' +
        '<button onclick="dismissAnn(this,'+ann.id+')" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:14px;padding:2px 4px;flex-shrink:0;margin-top:1px;transition:color .2s" title="Dismiss"><i class="fa-solid fa-xmark"></i></button>';
    return div;
}
function _escAnn(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function updateAnnouncements(list) {
    var container = document.getElementById('annContainer');
    if (!container) return;
    var dismissed = _annDismissed().map(function(x){ return parseInt(x); });
    var activeIds = list.map(function(a){ return parseInt(a.id); }).filter(function(id){ return !dismissed.includes(id); });
    // Remove banners no longer active or hidden
    container.querySelectorAll('.ann-banner').forEach(function(b){
        if (!activeIds.includes(parseInt(b.dataset.id))) {
            b.style.transition = 'opacity .3s';
            b.style.opacity = '0';
            setTimeout(function(){ b.remove(); }, 300);
        }
    });
    // Add new banners not yet shown
    var shownIds = Array.from(container.querySelectorAll('.ann-banner')).map(function(b){ return parseInt(b.dataset.id); });
    list.forEach(function(ann) {
        var id = parseInt(ann.id);
        if (!dismissed.includes(id) && !shownIds.includes(id)) {
            container.insertBefore(_buildAnnBanner(ann), container.firstChild);
        }
    });
}
</script>
