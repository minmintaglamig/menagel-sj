<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Submitted</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #FEF9E1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 0px;
        }

        .container {
            border-radius: 12px;
            text-align: center;
            background: #E5D0AC;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        .checkmark {
            font-size: 60px;
            color: #28a745;
        }

        h2 {
            margin: 20px 0 10px;
            font-size: 24px;
            color: #333;
        }

        p {
            color: #555;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: nowrap;
        }

        .button-group a {
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            background-color: #6D2323;
            color: white;
            font-weight: 500;
            transition: background-color 0.3s ease;
            flex: 0 0 auto;
            text-align: center;
        }

        .button-group a:hover {
            background-color: #A31D1D;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px;
                margin: 0 10px;
                width: 100%;
                max-width: 450px;
            }

            h2 {
                font-size: 26px;
            }

            p {
                font-size: 18px;
            }

            .button-group a {
                padding: 12px 25px;
                font-size: 16px; 
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
                margin: 0 5px;
                width: 100%;
                max-width: 100%;
            }

            h2 {
                font-size: 30px;
            }

            p {
                font-size: 18px;
            }

            .button-group {
                justify-content: space-evenly;
            }

            .button-group a {
                width: 48%; 
                padding: 12px 20px;
                font-size: 18px;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkmark">✅</div>
        <h2>Application Information Submitted</h2>
        <p>
            Thank you for submitting your application. You can now proceed to Login if you already have an account, or Signup if you don’t.
        </p>
        <div class="button-group">
            <a href="login.php">Login</a>
            <a href="signup.php">Signup</a>
        </div>
    </div>
</body>
</html>