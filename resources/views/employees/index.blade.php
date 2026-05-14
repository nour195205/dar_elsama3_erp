<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees Dashboard</title>
    <style>
        :root {
            --primary: #00ffd5;
            --bg-color: #0d1117;
            --card-bg: rgba(22, 27, 34, 0.85);
            --text-color: #c9d1d9;
            --accent: #ff0055;
            --success: #2ea043;
        }

        body {
            margin: 0;
            padding: 40px;
            background: radial-gradient(circle at top left, #1b222c, #07090b);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            min-height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        h1 {
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }

        .btn {
            background: rgba(0, 255, 213, 0.1);
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .btn:hover {
            background: var(--primary);
            color: var(--bg-color);
            box-shadow: 0 0 15px rgba(0, 255, 213, 0.5);
        }

        .btn-warning {
            color: #d29922;
            border-color: #d29922;
            background: rgba(210, 153, 34, 0.1);
        }

        .btn-warning:hover {
            background: #d29922;
            color: white;
            box-shadow: 0 0 15px rgba(210, 153, 34, 0.5);
        }

        .btn-danger {
            color: var(--accent);
            border-color: var(--accent);
            background: rgba(255, 0, 85, 0.1);
        }

        .btn-danger:hover {
            background: var(--accent);
            color: white;
            box-shadow: 0 0 15px rgba(255, 0, 85, 0.5);
        }

        .grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
        }

        .card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        .card h2 {
            margin-top: 0;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #8b949e;
        }

        input {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 12px;
            border-radius: 8px;
            box-sizing: border-box;
            outline: none;
            transition: border 0.3s ease;
        }

        input:focus {
            border-color: var(--primary);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        th {
            color: #8b949e;
            font-size: 13px;
            text-transform: uppercase;
        }

        .badge {
            background: rgba(0, 255, 213, 0.1);
            color: var(--primary);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-danger {
            background: rgba(255, 0, 85, 0.1);
            color: var(--accent);
        }

        .alert {
            background: rgba(46, 160, 67, 0.2);
            color: var(--success);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--success);
        }
        
        .action-flex {
            display: flex;
            gap: 10px;
        }

        form {
            margin: 0;
        }

        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid var(--primary);
            box-shadow: 0 0 40px rgba(0, 255, 213, 0.3);
        }
        .modal-content h3 {
            margin-top: 0;
            color: white;
        }
        #modalQR {
            margin: 20px auto;
            background: white;
            padding: 15px;
            border-radius: 8px;
            display: inline-block;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Employees Dashboard</h1>
        <a href="/" class="btn">View Daily Attendance QR</a>
    </div>

    @if(session('success'))
        <div class="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid">
        <!-- Add Employee Form -->
        <div class="card">
            <h2>Add New Employee</h2>
            <form action="{{ route('employees.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Ahmed Ali">
                </div>
                
                <div class="form-group">
                    <label>Email (Used for System Login)</label>
                    <input type="email" name="email" required placeholder="e.g. ahmed@example.com">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Min 6 characters">
                </div>
                
                <div class="form-group">
                    <label>Phone / Contact</label>
                    <input type="text" name="phone" placeholder="e.g. 01012345678">
                </div>

                <div class="form-group">
                    <label>Hourly Rate (EGP)</label>
                    <input type="number" step="0.5" name="hourly_rate" required placeholder="e.g. 50">
                </div>

                <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Save Employee</button>
            </form>
        </div>

        <!-- Employees List -->
        <div class="card">
            <h2>Employees Directory</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Rate / Hr</th>
                        <th>Device Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                    <tr>
                        <td>#{{ $employee->id }}</td>
                        <td>
                            <strong>{{ $employee->name }}</strong><br>
                            <small style="color: #8b949e;">{{ $employee->email }}</small>
                        </td>
                        <td>{{ $employee->phone ?? 'N/A' }}</td>
                        <td>{{ $employee->hourly_rate }} EGP</td>
                        <td>
                            @if($employee->device_id)
                                <span class="badge">Bound to Device</span>
                            @else
                                <span class="badge badge-danger">Not Bound</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-flex">
                                <!-- Setup Phone Button -->
                                <button type="button" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;" onclick="showSetupQR({{ $employee->id }}, '{{ $employee->name }}')">
                                    Pair Phone
                                </button>

                                @if($employee->device_id)
                                <form action="{{ route('employees.resetDevice', $employee) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn" style="padding: 5px 10px; font-size: 12px;" title="Reset Device Login">Reset</button>
                                </form>
                                @endif
                                
                                <form action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Delete this employee?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    
                    @if($employees->isEmpty())
                    <tr>
                        <td colspan="6" style="text-align: center; color: #8b949e;">No employees found. Add one on the left.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- The QR Library we downloaded locally -->
    <script src="/qrcode.min.js"></script>

    <!-- Modal for showing Binding QR -->
    <div id="qrModal" class="modal">
        <div class="modal-content">
            <h3 id="modalName">Pair Employee Phone</h3>
            <p>Make the employee scan this to link their app to this account.</p>
            <div id="modalQR"></div>
            <button class="btn" onclick="closeModal()" style="margin-top: 15px;">Close</button>
        </div>
    </div>

    <script>
        let qrCodeObj = null;

        function showSetupQR(employeeId, employeeName) {
            document.getElementById('modalName').textContent = 'Pair Phone: ' + employeeName;
            const container = document.getElementById('modalQR');
            container.innerHTML = ''; 
            
            // Build the absolute URL for pairing the device
            const setupUrl = window.location.origin + '/setup-phone?user_id=' + encodeURIComponent(employeeId) + '&name=' + encodeURIComponent(employeeName);

            qrCodeObj = new QRCode(container, {
                text: setupUrl,
                width: 250,
                height: 250,
                colorDark : "#0d1117",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });

            document.getElementById('qrModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('qrModal').style.display = 'none';
        }
    </script>
</body>
</html>
