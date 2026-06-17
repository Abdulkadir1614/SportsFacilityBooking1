<?php
session_start();
require_once "../auth/session_timeout.php";
require_once '../config/db.php';

// ── Log to DB (AJAX) 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'log') {
    $uid  = $_SESSION['user_id'] ?? null;
    $q    = trim($_POST['query']    ?? '');
    $r    = trim($_POST['response'] ?? '');
    if ($uid && $q && $r) {
        $s = $conn->prepare("INSERT INTO chatbot_log (user_id, query_text, response_text, log_date) VALUES (?,?,?,NOW())");
        $s->bind_param("iss", $uid, $q, $r);
        $s->execute();
    }
    echo json_encode(['ok' => true]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>BDBot – Assistant</title>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/customer_header.css">
        <link rel="stylesheet" href="../assets/css/chatbot.css">
        <style>
        
        </style>
    </head>
    <body>
        <?php include '../includes/customer_header.php'; ?>

        <div class="chat-page">
            <div class="chat-header-bar">
                <div class="bot-avatar">🤖</div>
                <div class="bot-info">
                    <h2>BD<span>Bot</span></h2>
                    <p><span class="online-dot"></span>Smart Assistant · Always online</p>
                </div>
            </div>

            <div class="quick-prompts">
                <button class="quick-btn" onclick="quickSend('How do I book a facility?')">📅 How to book</button>
                <button class="quick-btn" onclick="quickSend('What facilities are available?')">🏟 Facilities</button>
                <button class="quick-btn" onclick="quickSend('How do I pay?')">💳 How to pay</button>
                <button class="quick-btn" onclick="quickSend('What time slots are available?')">⏰ Time slots</button>
                <button class="quick-btn" onclick="quickSend('How do I cancel a booking?')">❌ Cancel</button>
                <button class="quick-btn" onclick="quickSend('Show me my dashboard')"> 📊 Dashboard </button>
            </div>

            <div class="chat-window">
                <div class="chat-messages" id="chatMessages">
                    <div class="msg bot">
                        <div class="msg-avatar">🤖</div>
                        <div>
                            <div class="msg-bubble">
                                👋 Hello<?= isset($_SESSION['name']) ? ', <strong>' . htmlspecialchars($_SESSION['name']) . '</strong>' : '' ?>! I'm <strong>BDBot</strong>.<br><br>
                                I can help with <strong>bookings</strong>, <strong>payments</strong>, <strong>facilities</strong>, <strong>account help</strong> and more. What do you need?
                            </div>
                            <div class="msg-time"><?= date('h:i A') ?></div>
                        </div>
                    </div>
                </div>

                <div class="chat-input-bar">
                    <div class="chat-input-wrap">
                        <input type="text" id="chatInput" placeholder="Ask me anything…" autocomplete="off">
                    </div>
                    <button class="btn-send" onclick="sendMessage()">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </div>

        <script src="chatbot.js"></script>
        <script>
        const messagesEl = document.getElementById('chatMessages');
        const inputEl    = document.getElementById('chatInput');

        function getTime(){return new Date().toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});}

        function appendMsg(role,html){
            const div=document.createElement('div');
            div.className=`msg ${role}`;
            div.innerHTML=`<div class="msg-avatar">${role==='bot'?'🤖':'<i class="bi bi-person-fill"></i>'}</div>
                <div><div class="msg-bubble">${html}</div><div class="msg-time">${getTime()}</div></div>`;
            messagesEl.appendChild(div);
            messagesEl.scrollTop=messagesEl.scrollHeight;
        }

        function showTyping(){
            const div=document.createElement('div');
            div.className='msg bot';div.id='typing';
            div.innerHTML=`<div class="msg-avatar">🤖</div><div class="msg-bubble" style="padding:10px 14px;"><div class="typing-indicator"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div></div>`;
            messagesEl.appendChild(div);messagesEl.scrollTop=messagesEl.scrollHeight;
        }

        function removeTyping(){const t=document.getElementById('typing');if(t)t.remove();}

        function quickSend(text){inputEl.value=text;sendMessage();}

        function sendMessage(){
            const text=inputEl.value.trim();
            if(!text)return;
            inputEl.value='';
            appendMsg('user',text);
            showTyping();
            setTimeout(()=>{
                removeTyping();
                const reply=generateResponse(text);
                appendMsg('bot',reply);
                fetch('chatbot.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
                    body:`action=log&query=${encodeURIComponent(text)}&response=${encodeURIComponent(reply)}`});
            }, 150 + Math.random()*300);
        }
        </script>
    </body>
</html>