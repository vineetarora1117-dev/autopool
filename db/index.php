<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Snap & Restore</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #1e1e24;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        .container {
            background: #2a2a35;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            text-align: center;
        }
        h1 { margin-top: 0; }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-snap { background: #4caf50; color: white; }
        .btn-snap:hover { background: #45a049; transform: scale(1.05); }
        .btn-restore { background: #f44336; color: white; }
        .btn-restore:hover { background: #e53935; transform: scale(1.05); }
        #status { margin-top: 20px; font-size: 16px; color: #ffeb3b; }
    </style>
</head>
<body>

<div class="container">
    <h1>Database Snapshot Tool</h1>
    <p>Use these buttons to instantly backup or restore your Autopool database.</p>
    
    <button class="btn btn-snap" onclick="executeAction('snap')">Take Snapshot</button>
    <button class="btn btn-restore" onclick="executeAction('restore')">Restore Snapshot</button>
    
    <div id="status"></div>
</div>

<script>
    async function executeAction(action) {
        const passcode = prompt("Please enter the database passcode:");
        if (!passcode) return;

        const statusEl = document.getElementById('status');
        statusEl.innerText = action === 'snap' ? 'Taking snapshot...' : 'Restoring database...';

        const fd = new FormData();
        fd.append('action', action);
        fd.append('passcode', passcode);

        try {
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const data = await res.json();
            
            if (data.success) {
                statusEl.innerText = "Success! " + data.message;
                statusEl.style.color = "#4caf50";
            } else {
                statusEl.innerText = "Error: " + data.error;
                statusEl.style.color = "#f44336";
            }
        } catch (e) {
            statusEl.innerText = "Error communicating with server.";
            statusEl.style.color = "#f44336";
        }
    }
</script>

</body>
</html>
