<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BlueOasis Resort | Reservation Success</title>

    <!-- Bootstrap CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <style>
        body {
            background: linear-gradient(135deg, #e9f3f8, #f7fbfd);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .reservation-card {
            max-width: 420px;
            width: 100%;
            border-radius: 14px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .card-header {
            background-color: #0a3d62;
            color: #ffffff;
            text-align: center;
            padding: 22px;
            border-radius: 14px 14px 0 0;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 30px 25px;
        }

        .card-body h4 {
            font-weight: 600;
        }

        .card-footer {
            background-color: #f4f8fb;
            border-top: 1px solid #e3ebf2;
            text-align: center;
            padding: 18px;
            border-radius: 0 0 14px 14px;
        }

        .btn-oasis {
            background-color: #0a6fbf;
            color: #ffffff;
            border-radius: 25px;
            padding: 10px 26px;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
        }

        .btn-oasis:hover {
            background-color: #084e8a;
            color: #ffffff;
        }

        .footer-text {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="card reservation-card">

        <div class="card-header">
            <h2>BlueOasis Resort</h2>
        </div>

        <div class="card-body text-center">
            <h4 class="text-success mb-3">Reservation Confirmed</h4>

            <p class="text-muted mb-0">
                Thank you for choosing <strong>BlueOasis Resort</strong>.  
                Your reservation has been successfully completed and recorded in our system.
            </p>
        </div>

        <div class="card-footer">
            <a 
                href="http://127.0.0.1:8000/reservations-list" 
                class="btn btn-oasis"
            >
                View Reservation
            </a>

            <div class="footer-text">
                We look forward to welcoming you
            </div>
        </div>

    </div>

</body>
</html>
