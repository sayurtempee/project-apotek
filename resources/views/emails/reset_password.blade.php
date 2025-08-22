<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>

<body>
    <h2>Reset Password</h2>
    <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
    <p>Klik link di bawah ini untuk mengatur password baru:</p>
    <a href="{{ url('/reset-password/' . $token) }}">
        Reset Password
    </a>
    <br><br>
    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
</body>

</html>
