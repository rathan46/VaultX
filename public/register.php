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
    <h2>Register</h2>
    <form action="../auth/register.php" method="post">
        <input
            type="text"
            name="username"
            placeholder="Choose Username"
            required
            autocomplete="username"
        >

        <input
            type="password"
            name="password"
            placeholder="Choose Password"
            required
            minlength="6"
            autocomplete="new-password"
        >

        <button type="submit">Create Account</button>
    </form>
    <p style="color:white;">Already have an account?<a href="index.php">login here</a></p>
</div>
</div>
</body>
</html>