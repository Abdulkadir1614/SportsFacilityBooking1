// ============================================================
//  Sports Facility Booking System
// ============================================================

// ── State ──────────────────────────────────────────────────
const state = {
    step: null,       // multi-turn flow step
    context: null,    
    bookingData: {}   
};

// ── Boot ───────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("chatInput") || document.getElementById("message");
    if (input) {
        input.addEventListener("keydown", e => {
            if (e.key === "Enter") { e.preventDefault(); sendMessage(); }
        });
    }
});

// ── Main response engine ───────────────────────────────────
function generateResponse(raw) {
    const text  = raw.toLowerCase().trim();
    const words = text.split(/\s+/);

    // ── Multi-turn flow handler ──
    if (state.step) return handleFlow(raw, text);

    // ── GREETINGS ──
    if (match(text, ["hi","hello","hey","salam","assalam","asc","marhaba","howdy",
        "good morning","good afternoon","good evening","morning","evening","yo","sup",
        "hiya","selam","waan","nabadgelyo","greetings","howzit","whats up","what's up"])) {
        state.context = "greeting";
        return rand([
            `👋 Hello! I'm <strong>BDBot</strong>, your guide for Beerta Daarusalaam Sports. I can help you with <strong>bookings</strong>, <strong>payments</strong>, <strong>facilities</strong>, and more. What do you need?`,
            `Salaam! 👋 Welcome to <strong>Beerta Daarusalaam</strong>. I'm here to help you book facilities, manage payments, and navigate the system. What can I do for you?`,
            `Hey there! 😊 Ready to help you book your next session. Ask me anything about <strong>facilities</strong>, <strong>bookings</strong>, or <strong>payments</strong>.`
        ]);
    }

    // ── HOW ARE YOU ──
    if (match(text, ["how are you","how r u","you okay","u good","hows it going"])) {
        return "I'm always online and ready to help! 🤖 What can I do for you today?";
    }

    // ── WHAT IS THIS SYSTEM ──
    if (match(text, ["what is this","about system","what is beerta","what does this do","what can you do",
        "tell me about","explain system","system overview","what is bd"])) {
        return `
🏟 <strong>Beerta Daarusalaam Sports Facility Booking System</strong> is an online platform that lets you:<br><br>
• 📅 Book <strong>football fields</strong>, <strong>basketball courts</strong>, and <strong>swimming pools</strong><br>
• 💳 Pay securely online<br>
• 📜 Track booking history<br>
• 🔔 Receive real-time notifications<br>
• 💬 Get AI-powered support (that's me!)<br><br>
All available 24/7 from any device.`;
    }

    // ── FACILITIES ──
    if (match(text, ["facility","facilities","what facilities","court","field","pool","swimming",
        "football","basketball","futsal","sport","venue","arena","available facility","show facilities"])) {
        state.context = "facilities";
        return `
🏟 <strong>Our Facilities</strong><br><br>
• ⚽ <strong>Football Field</strong> — Full-size outdoor field<br>
• 🏀 <strong>Basketball Court</strong> — Indoor/outdoor courts<br>
• 🏊 <strong>Swimming Pool</strong> — Olympic-size pool<br><br>
To check <strong>live availability & pricing</strong>, visit <a href="../customer/view_facilities.php" style="color:var(--accent)">View Facilities</a>.<br><br>
Would you like to know how to book one? 😊`;
    }

    // ── AVAILABILITY ──
    if (match(text, ["available","availability","is it open","open now","check slot","free slot","any slot"])) {
        return `
📅 To check <strong>real-time availability</strong>:<br><br>
1. Go to <a href="../customer/view_facilities.php" style="color:var(--accent)"><strong>View Facilities</strong></a><br>
2. Each facility shows its current status<br>
3. Click <strong>"Book Now"</strong> on any available one<br><br>
Slots fill up fast — book early! ⚡`;
    }

    // ── PRICING ──
    if (match(text, ["price","pricing","cost","rate","how much","fee","charge","rm","ringgit","expensive","cheap"])) {
        state.context = "pricing";
        return `
💰 <strong>Pricing</strong><br><br>
Prices are shown per hour and vary by facility type. To see exact rates:<br><br>
👉 Visit <a href="../customer/view_facilities.php" style="color:var(--accent)"><strong>View Facilities</strong></a> — each card shows the price per hour.<br><br>
💡 Tip: You only pay a <strong>deposit</strong> to confirm, which is verified by our staff.`;
    }

    // ── BOOKING — HOW TO ──
    if (match(text, ["how to book","how do i book","make booking","want to book","book facility",
        "booking process","steps to book","guide me","how book","i want to book"])) {
        state.context = "booking";
        return `
📅 <strong>How to Book a Facility</strong><br><br>
<strong>Step 1:</strong> Go to <a href="../customer/view_facilities.php" style="color:var(--accent)">View Facilities</a><br>
<strong>Step 2:</strong> Choose a facility and click <strong>"Book Now"</strong><br>
<strong>Step 3:</strong> Select your <strong>date</strong> and <strong>time slot</strong><br>
<strong>Step 4:</strong> Click <strong>"Confirm Booking"</strong><br>
<strong>Step 5:</strong> Wait for <strong>staff approval</strong><br>
<strong>Step 6:</strong> Once approved, go to <strong>Manage Payment</strong> to pay<br><br>
Need help with a specific step? 😊`;
    }

    // ── TIME SLOTS ──
    if (match(text, ["time slot","slots","what time","timing","schedule","what hours","available time","opening hours",
        "when can","time available","book time"])) {
        return `
⏰ <strong>Available Time Slots</strong><br><br>
• 08:00 – 09:00<br>
• 09:00 – 10:00<br>
• 11:00 – 12:00<br>
• 15:00 – 16:00<br>
• 17:00 – 18:00<br>
• 19:00 – 20:00<br>
• 21:00 – 22:00<br><br>
Each slot is <strong>1 hour</strong>. Select your preferred slot when booking.`;
    }

    // ── BOOKING STATUS ──
    if (match(text, ["booking status","status of booking","is my booking","approved","pending booking",
        "confirmed","rejected booking","check booking","my booking status"])) {
        return `
📋 <strong>Booking Status Guide</strong><br><br>
• 🟡 <strong>Pending</strong> — Submitted, waiting for staff review<br>
• ✅ <strong>Approved</strong> — Confirmed! You can now pay<br>
• ❌ <strong>Rejected</strong> — Not approved (contact staff)<br>
• 🔵 <strong>Cancelled</strong> — You cancelled it<br><br>
Check your status at <a href="../customer/booking_history.php" style="color:var(--accent)"><strong>Booking History</strong></a>.`;
    }

    // ── BOOKING HISTORY ──
    if (match(text, ["history","past booking","my booking","previous booking","booking list",
        "show bookings","view bookings","all bookings","booking record"])) {
        state.context = "history";
        return `
📜 <strong>Booking History</strong><br><br>
Your booking history shows all <strong>past and current</strong> bookings including:<br>
• Facility name, date & time<br>
• Booking status (Pending/Approved/Cancelled)<br>
• Payment status<br>
• Cancel option for Pending bookings<br><br>
👉 Go to <a href="../customer/booking_history.php" style="color:var(--accent)"><strong>Booking History</strong></a>`;
    }

    // ── CANCEL BOOKING ──
    if (match(text, ["cancel","cancel booking","delete booking","remove booking","undo booking","cancel reservation",
        "how cancel","can i cancel"])) {
        state.context = "cancel";
        return `
❌ <strong>How to Cancel a Booking</strong><br><br>
You can only cancel bookings with <strong>Pending</strong> status:<br><br>
<strong>Step 1:</strong> Go to <a href="../customer/booking_history.php" style="color:var(--accent)">Booking History</a><br>
<strong>Step 2:</strong> Find the booking you want to cancel<br>
<strong>Step 3:</strong> Click the <strong>"Cancel"</strong> button<br><br>
⚠️ <strong>Note:</strong> Once a booking is <strong>Approved</strong>, you cannot cancel it directly — contact staff.`;
    }

    // ── PAYMENT — GENERAL ──
    if (match(text, ["payment","pay","paying","how to pay","make payment","deposit","submit payment",
        "payment process","pay now","when to pay","unpaid"])) {
        state.context = "payment";
        return `
💳 <strong>How to Pay</strong><br><br>
<strong>Step 1:</strong> Your booking must be <strong>Approved</strong> first<br>
<strong>Step 2:</strong> Go to <a href="../customer/manage_payment.php" style="color:var(--accent)">Manage Payments</a><br>
<strong>Step 3:</strong> Click <strong>"Pay Now"</strong> on your booking<br>
<strong>Step 4:</strong> Choose your payment method<br>
<strong>Step 5:</strong> Submit — staff will verify it<br><br>
💡 Payment is <strong>deposit-based</strong> and verified within 24 hours.`;
    }

    // ── PAYMENT METHODS ──
    if (match(text, ["payment method","how can i pay","what payment","card","credit card","debit card",
        "online banking","ewallet","e-wallet"])) {
        return `
💳 <strong>Accepted Payment Methods</strong><br><br>
💳 <strong>Card</strong> — Visa, Mastercard<br>
🏦 <strong>Online Banking</strong> — Salaam bank , Premier bank ,  Amal bank, IBS <br>
📱 <strong>E-Wallet</strong> — Evc Plus,  Zaad,  Sahal,  E-dahab<br><br>
All payments are <strong>secure and encrypted</strong>. 🔒`;
    }

    // ── PAYMENT STATUS ──
    if (match(text, ["payment status","paid","unpaid","verified payment","payment verified",
        "payment pending","check payment","is payment done"])) {
        return `
📊 <strong>Payment Status</strong><br><br>
• ⏳ <strong>Unpaid</strong> — Payment not submitted yet<br>
• 🟡 <strong>Pending</strong> — Submitted, awaiting staff verification<br>
• ✅ <strong>Verified/Paid</strong> — Confirmed by staff<br><br>
Check at <a href="../customer/manage_payment.php" style="color:var(--accent)"><strong>Manage Payments</strong></a>.`;
    }

    // ── NOTIFICATIONS ──
    if (match(text, ["notification","notify","alert","bell","message","update","inform",
        "email notification","when notified","get notified"])) {
        return `
🔔 <strong>Notifications</strong><br><br>
You receive notifications for:<br>
• ✅ Booking <strong>approved</strong> or <strong>rejected</strong><br>
• 💳 Payment <strong>verified</strong><br>
• ❌ Booking <strong>cancelled</strong><br><br>
View all at <a href="../notifications/notify.php" style="color:var(--accent)"><strong>Notifications</strong></a>. The bell icon in the header shows unread count.`;
    }

    // ── REGISTER ──
    if (match(text, ["register","sign up","create account","new account","join","how to register",
        "make account","registration"])) {
        return `
📝 <strong>How to Register</strong><br><br>
<strong>Step 1:</strong> Go to <a href="../auth/register.php" style="color:var(--accent)">Register</a><br>
<strong>Step 2:</strong> Fill in: <strong>Full Name, Email, Password, Phone</strong><br>
<strong>Step 3:</strong> Click <strong>"Create Account"</strong><br>
<strong>Step 4:</strong> Log in and start booking! 🎉<br><br>
Registration is <strong>free</strong> and takes under 1 minute.`;
    }

    // ── LOGIN ──
    if (match(text, ["login","log in","sign in","cant login","forgot login","access account",
        "how to login","my account"])) {
        return `
🔐 <strong>How to Login</strong><br><br>
<strong>Step 1:</strong> Go to <a href="../auth/login.php" style="color:var(--accent)">Login</a><br>
<strong>Step 2:</strong> Enter your <strong>email</strong> and <strong>password</strong><br>
<strong>Step 3:</strong> Click <strong>"Sign In"</strong><br><br>
Forgot your password? Click <a href="../auth/forgot_password.php" style="color:var(--accent)"><strong>Forgot Password</strong></a> to reset it via email.`;
    }

    // ── FORGOT PASSWORD ──
    if (match(text, ["forgot password","reset password","change password","cant remember password",
        "lost password","password reset","recover password","new password"])) {
        return `
🔑 <strong>Reset Your Password</strong><br><br>
<strong>Step 1:</strong> Go to <a href="../auth/forgot_password.php" style="color:var(--accent)">Forgot Password</a><br>
<strong>Step 2:</strong> Enter your <strong>registered email</strong><br>
<strong>Step 3:</strong> Check your inbox for the <strong>reset link</strong><br>
<strong>Step 4:</strong> Click the link and set a new password<br><br>
⚠️ The link expires in <strong>30 minutes</strong>.`;
    }

    // ── STAFF ROLE ──
    if (match(text, ["staff","what staff do","staff role","staff member","employee"])) {
        return `
👷 <strong>Staff Role</strong><br><br>
Staff members handle the day-to-day operations:<br>
• ✅ Approve or reject customer bookings<br>
• 💳 Verify customer payments<br>
• 🏟 Update facility availability status<br><br>
Staff access the <strong>Staff Panel</strong> separately from customer accounts.`;
    }

    // ── ADMIN ROLE ──
    if (match(text, ["admin","administrator","admin role","what admin","admin panel","admin access"])) {
        return `
🛡️ <strong>Admin Role</strong><br><br>
Admins have full system control:<br>
• 🏟 Add/edit/delete facilities<br>
• 👥 Manage staff accounts<br>
• 📊 Generate reports<br>
• 📈 View system statistics`;
    }

    // ── SECURITY / PRIVACY ──
    if (match(text, ["secure","security","safe","privacy","data","encrypted","protect","ssl","my data"])) {
        return `
🔒 <strong>Security & Privacy</strong><br><br>
Your data is fully protected:<br>
• 🔐 Passwords are <strong>encrypted</strong> (bcrypt hashing)<br>
• 🔒 Payments use <strong>256-bit SSL encryption</strong><br>
• 🛡️ Sessions are secured and expire on logout<br>
• 📋 Data is only used for booking management<br><br>
We never share your personal information with third parties.`;
    }

    // ── CONTACT ──
    if (match(text, ["contact","reach","phone number","email address","support","helpdesk",
        "customer service","speak to human","call","whatsapp"])) {
        return `
📞 <strong>Contact Us</strong><br><br>
• 📍 <strong>Location:</strong> Mogadishu, Somalia<br>
• 📱 <strong>Phone:</strong> +252 617 1614 414<br>
• 📧 <strong>Email:</strong> support@beertadaarusalaam.com<br><br>
Our team is available during working hours. For urgent booking issues, contact us directly.`;
    }

    // ── OPENING HOURS ──
    if (match(text, ["opening hours","open","close","when open","working hours","business hours","hours of operation"])) {
        return `
🕐 <strong>Operating Hours</strong><br><br>
The system is available <strong>24/7</strong> online — book anytime!<br><br>
Physical facility hours depend on the specific venue. Check the <a href="../customer/view_facilities.php" style="color:var(--accent)">Facilities page</a> for details or contact us for venue-specific hours.`;
    }

    // ── DASHBOARD ──
    if (match(text, ["dashboard","home","main page","homepage","where to start","menu","navigate"])) {
        return `
🏠 <strong>Your Dashboard</strong><br><br>
Your dashboard is your control center with quick access to:<br>
• 🏟 <strong>View Facilities</strong> — Browse & book<br>
• 📅 <strong>Make Booking</strong> — Reserve a slot<br>
• 📜 <strong>Booking History</strong> — Track bookings<br>
• 💳 <strong>Manage Payments</strong> — Pay & track<br><br>
👉 <a href="../customer/dashboard.php" style="color:var(--accent)"><strong>Go to Dashboard</strong></a>`;
    }

    // ── DOUBLE BOOKING ──
    if (match(text, ["double book","same slot","conflict","already booked","slot taken","overlap"])) {
        return `
⚠️ <strong>Slot Conflicts</strong><br><br>
Our system <strong>prevents double bookings</strong> automatically. If a slot is already taken, you'll see an error and can choose a different time.<br><br>
To find available slots, go to <a href="../customer/view_facilities.php" style="color:var(--accent)">View Facilities</a> and check current availability.`;
    }

    // ── CHATBOT ITSELF ──
    if (match(text, ["who are you","what are you","are you ai","are you robot","chatbot","bot",
        "bdbot","you human","are you real","what can you do"])) {
        return `
🤖 I'm <strong>BDBot</strong> — an AI-powered assistant built for Beerta Daarusalaam Sports!<br><br>
I can help you with:<br>
• 📅 <strong>Booking</strong> facilities step by step<br>
• 💳 <strong>Payment</strong> guidance<br>
• 🏟 <strong>Facility</strong> information<br>
• 🔐 <strong>Account</strong> help (login, register, password reset)<br>
• 📜 <strong>Booking history</strong> & cancellations<br>
• 🔔 <strong>Notifications</strong> & system features`;
    }

    // ── HELP ──
    if (match(text, ["help","guide","assist","support","i need help","confused","lost","what to do",
        "where to go","show me","how to use"])) {
        return `
🆘 <strong>How can I help?</strong><br><br>
Here's what I can guide you with:<br><br>
📅 <strong>Bookings</strong> — "How do I book a facility?"<br>
💳 <strong>Payments</strong> — "How do I pay?"<br>
🏟 <strong>Facilities</strong> — "What facilities are available?"<br>
❌ <strong>Cancellation</strong> — "How do I cancel a booking?"<br>
📜 <strong>History</strong> — "Show my bookings"<br>
🔐 <strong>Account</strong> — "I forgot my password"<br><br>
Just type your question naturally! 😊`;
    }

    // ── THANK YOU ──
    if (match(text, ["thanks","thank you","thank u","tq","ty","thx","shukran","mahadsanid",
        "gracias","merci","appreciate","great help","perfect","awesome","got it","ok thanks",
        "okay thanks","cheers","wonderful","brilliant","excellent"])) {
        return rand([
            "You're welcome! 😊 Is there anything else I can help you with?",
            "Happy to help! 🎉 Let me know if you have more questions.",
            "Anytime! 🤖 Feel free to ask if you need anything else."
        ]);
    }

    // ── BYE ──
    if (match(text, ["bye","goodbye","see you","later","gtg","cya","take care","have a good","farewell","adios"])) {
        return "Goodbye! 👋 Come back anytime. Have a great session! 🏟";
    }

    // ── YES / CONTINUE ──
    if (match(text, ["yes","yeah","yep","yup","sure","ok","okay","alright","go ahead","continue","proceed"])) {
        if (state.context === "booking") {
            return `Great! Start by visiting <a href="../customer/view_facilities.php" style="color:var(--accent)"><strong>View Facilities</strong></a> to pick a venue. 🏟`;
        }
        if (state.context === "payment") {
            return `Head to <a href="../customer/manage_payment.php" style="color:var(--accent)"><strong>Manage Payments</strong></a> to pay for your approved bookings. 💳`;
        }
        return "Got it! What would you like to do? I can help with bookings, payments, or facilities. 😊";
    }

    // ── NO ──
    if (match(text, ["no","nope","nah","not now","maybe later","no thanks"])) {
        return "No problem! 😊 Let me know whenever you need help. I'm always here.";
    }

    // ── DEFAULT ──
    state.context = null;
    return rand([
        `❓ I didn't quite catch that. Try asking about:<br><br>• <strong>Bookings</strong> — "How do I book?"<br>• <strong>Payments</strong> — "How do I pay?"<br>• <strong>Facilities</strong> — "What facilities are available?"<br>• <strong>Account</strong> — "I forgot my password"`,
        `🤔 I'm not sure about that one. You can ask me about <strong>bookings</strong>, <strong>facilities</strong>, <strong>payments</strong>, or <strong>account help</strong>. What do you need?`,
        `❓ Hmm, I couldn't understand that. Try rephrasing, or type <strong>"help"</strong> to see what I can do! 😊`
    ]);
}

// ── Multi-turn flow handler ────────────────────────────────
function handleFlow(raw, text) {
    // Extend with guided flows if needed
    state.step = null;
    return generateResponse(raw);
}

// ── Helpers ───────────────────────────────────────────────
function match(text, keywords) {
    return keywords.some(word => text.includes(word));
}

function rand(arr) {
    return arr[Math.floor(Math.random() * arr.length)];
}