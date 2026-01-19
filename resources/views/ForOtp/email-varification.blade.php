<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Verification</title>
</head>
<body style="font-family: Arial, sans-serif;">

    <h2>Hello {{ $data['name'] }}</h2>
    <p>Your Otp is<strong>{{ $data['otp'] }}</strong></p>
    <p>You have successfully logged in as admin.</p>

    <p><strong>Email:</strong> {{ $data['email'] }}</p>
    

    <br>
    <p>Thanks,<br>
    

</body>
</html>
