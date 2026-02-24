<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Vault | Login & Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <img src="logo.png" alt="VaultX Logo" class="logo">
<div>
<div class="auth-box">
    <h1>VaultX</h1>
    <center><h6>Secure - crypted - reliable</h6></center>
</div><br>
<div class="auth-box">
    <h2>Login</h2>
    <form action="../auth/login.php" method="post">
        <input
            type="email"
            name="username"
            placeholder="E-Mail"
            required
            autocomplete="email"
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
            autocomplete="current-password"
        >

        <button type="submit">Login</button>
    </form>
    <p style="color:white;">New user?<a href="register.php">register here</a></p>

    <!--<hr style="margin:20px 0; border:0; height:1px; background:rgba(255,255,255,0.1);">-->

    
</div>
</div>
</body>
</html>

