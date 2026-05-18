<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Conversation</title>
</head>
<body style="margin:0;padding:0;background:#05070f;">

    <!-- MAIN WRAPPER (Radial Gradient Background) -->
    <div style="font-family:'Segoe UI',Roboto,Arial,sans-serif;min-height:100vh;background:radial-gradient(circle at top,#0b1220,#05070f);padding:30px;color:#e5e7eb;display:flex;justify-content:center;">
        
        <!-- CENTRAL CONTENT CONTAINER -->
        <div style="width:100%;max-width:950px;display:flex;flex-direction:column;gap:18px;">
            
            <!-- SECTION 1: TOP NAVIGATION BAR -->
            <div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;">
                <div>
                    <div style="font-size:26px;font-weight:800;color:white;">
                        🎫 Ticket Conversation
                    </div>
                    <div style="font-size:13px;color:#94a3b8;margin-top:5px;">
                        Support communication center
                    </div>
                </div>
                <div style="display:flex;gap:10px;align-items:center;">
                    <button style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;padding:10px 14px;border-radius:12px;font-weight:600;cursor:pointer;">
                        ← Back
                    </button>
                    <button style="background:#ef4444;border:none;color:white;padding:10px 14px;border-radius:12px;font-weight:700;cursor:pointer;">
                        Close Ticket
                    </button>
                </div>
            </div>

            <!-- SECTION 2: TICKET META INFO CARD -->
            <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);backdrop-filter:blur(12px);border-radius:18px;padding:22px;box-shadow:0 10px 40px rgba(0,0,0,0.35);">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:22px;font-weight:800;color:#f8fafc;">
                            Ticket #1024 — Login Authentication Failure
                        </div>
                        <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:10px;font-size:13px;color:#94a3b8;">
                            <div>👤 Created by: John Doe</div>
                            <div>🧑‍💻 Assigned: Agent Smith</div>
                            <div>📅 Created: Jul 18, 2026</div>
                        </div>
                    </div>
                    <!-- STATUS PILL -->
                    <div style="padding:8px 16px;border-radius:999px;background:rgba(245,158,11,0.15);color:#fbbf24;font-size:12px;font-weight:800;letter-spacing:1px;">
                        OPEN
                    </div>
                </div>
            </div>

            <!-- SECTION 3: SCROLLABLE CHAT HISTORY -->
            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);backdrop-filter:blur(12px);border-radius:18px;padding:20px;height:500px;overflow-y:auto;display:flex;flex-direction:column;gap:18px;box-shadow:0 10px 35px rgba(0,0,0,0.25);">
                
                <!-- USER MESSAGE (Left Aligned) -->
                <div style="display:flex;justify-content:flex-start;">
                    <div style="max-width:70%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);padding:14px;border-radius:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;gap:20px;">
                            <div style="font-size:13px;font-weight:700;color:#cbd5e1;">John Doe</div>
                            <div style="font-size:11px;color:#64748b;">10:12 AM</div>
                        </div>
                        <div style="font-size:14px;line-height:1.6;color:#e2e8f0;">
                            Hi, I cannot log into my account after resetting my password. The system keeps saying invalid credentials.
                        </div>
                    </div>
                </div>

                <!-- AGENT MESSAGE (Right Aligned) -->
                <div style="display:flex;justify-content:flex-end;">
                    <div style="max-width:70%;background:rgba(59,130,246,0.14);border:1px solid rgba(59,130,246,0.25);padding:14px;border-radius:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;gap:20px;">
                            <div style="font-size:13px;font-weight:700;color:#93c5fd;">Agent Smith</div>
                            <div style="font-size:11px;color:#60a5fa;">10:15 AM</div>
                        </div>
                        <div style="font-size:14px;line-height:1.6;color:#eff6ff;">
                            Thank you for reporting this. Please try clearing browser cache and attempt login again.
                        </div>
                    </div>
                </div>

                <!-- USER MESSAGE (Left Aligned) -->
                <div style="display:flex;justify-content:flex-start;">
                    <div style="max-width:70%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);padding:14px;border-radius:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;gap:20px;">
                            <div style="font-size:13px;font-weight:700;color:#cbd5e1;">John Doe</div>
                            <div style="font-size:11px;color:#64748b;">10:18 AM</div>
                        </div>
                        <div style="font-size:14px;line-height:1.6;color:#e2e8f0;">
                            I tried that already but the issue still persists.
                        </div>
                    </div>
                </div>

            </div>

            <!-- SECTION 4: TEXT AREA INPUT & ACTIONS -->
            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);padding:16px;border-radius:18px;display:flex;flex-direction:column;gap:14px;">
                <textarea placeholder="Type your reply..." style="width:100%;height:110px;resize:none;border-radius:16px;padding:14px 16px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;font-size:14px;outline:none;box-sizing:border-box;"></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    <div style="font-size:12px;color:#64748b;">
                        Replies are visible to ticket owner and assigned agents
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;padding:12px 16px;border-radius:12px;font-weight:700;cursor:pointer;">
                            Attach File
                        </button>
                        <button style="background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;border:none;padding:12px 20px;border-radius:12px;font-weight:800;cursor:pointer;box-shadow:0 10px 25px rgba(37,99,235,0.35);">
                            Send Reply
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
