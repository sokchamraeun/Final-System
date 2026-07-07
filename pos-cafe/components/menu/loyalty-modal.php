<div id="loyaltyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(8px);z-index:9999;justify-content:center;align-items:center;">
  <div style="background:var(--bg-card,#fff);border-radius:16px;padding:28px;max-width:420px;width:90%;position:relative;border:1px solid var(--border,#e0d4c4);box-shadow:0 12px 48px rgba(90,60,20,.16);">
    <span onclick="closeLoyaltyModal()" style="position:absolute;right:14px;top:10px;font-size:22px;color:var(--text-muted,#9a8070);cursor:pointer;"><i class="fa-solid fa-xmark"></i></span>
    <div style="text-align:center;margin-bottom:18px;">
      <i class="fa-solid fa-star" style="font-size:36px;color:var(--orange, #14b8a6);"></i>
      <h2 style="font-size:18px;margin:6px 0 3px;color:var(--text,#1a1410);">Loyalty Card</h2>
      <p style="font-size:12px;color:var(--text-sec,#5a4a3a);">Enter your loyalty ID to view points and redeem rewards</p>
    </div>
    <div style="display:flex;gap:8px;margin-bottom:14px;">
      <input type="text" id="loyaltyIdInput" placeholder="e.g. CARD-12345" style="flex:1;padding:9px 12px;border-radius:9px;border:1px solid var(--border,#e0d4c4);background:var(--bg,#f4efe9);color:var(--text,#1a1410);font-family:'Poppins',sans-serif;font-size:13px;outline:none;">
      <button onclick="lookupLoyalty()" style="background:var(--orange, #14b8a6);color:#fff;border:none;padding:9px 16px;border-radius:9px;font-weight:600;cursor:pointer;font-family:'Poppins',sans-serif;"><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <div id="loyaltyResult" style="display:none;padding:14px;background:rgba(255,255,255,.03);border-radius:10px;border:1px solid var(--border,#e0d4c4);">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <div><div style="font-size:11px;color:var(--text-sec,#5a4a3a);">Loyalty ID</div><div id="loyaltyDisplayId" style="font-size:15px;font-weight:700;color:var(--orange, #14b8a6);"></div></div>
        <div style="text-align:right;"><div style="font-size:11px;color:var(--text-sec,#5a4a3a);">Points</div><div id="loyaltyPoints" style="font-size:20px;font-weight:700;color:var(--text,#1a1410);">0</div></div>
      </div>
      <div id="loyaltyRewards" style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px;"></div>
      <div id="loyaltyHistory" style="margin-top:10px;max-height:100px;overflow-y:auto;font-size:11px;color:var(--text-sec,#5a4a3a);border-top:1px solid var(--border,#e0d4c4);padding-top:6px;"></div>
    </div>
    <div id="loyaltyError" style="display:none;padding:10px;background:rgba(231,76,60,.08);border-radius:8px;border:1px solid rgba(231,76,60,.2);color:#e74c3c;text-align:center;font-size:12px;"><i class="fa-solid fa-circle-exclamation"></i> Card not found.</div>
  </div>
</div>
