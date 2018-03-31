<?php
include 'bo/assets/inc/parameters.php';
include 'assets/php/db_con.php';
include 'assets/php/app.class.php';
include 'assets/php/user.class.php';

App::SetDB($db);

// LOGIN
if (isset($_POST['login'])) {
    $login = User::Login($_POST['username'], $_POST['password']);
    if ($login[0]) {
        header("Location: bo/#");
    } else {
        App::Respond('Login', $login[1]);
    }
}

// CONFIRM ACCOUNT
if (isset($_GET['valid']) && isset($_GET['token'])) {
    $confirm_account = User::ConfirmAccount($_GET['valid'], $_GET['token']);
    if ($confirm_account[0]) {
        App::Message('Votre compte est confirmé, vous pouvez vous connecter.');
    } else {
        App::Respond('Confirmation', $confirm_account[1]);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/rsc/Font-Awesome-Pro-master/web-fonts-with-css/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/style/home.min.css">
    <title>Smartfolio</title>
</head>
<body>
    <header>
        <h1>GTS</h1>
        <nav>
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
        </nav>
    </header>
    <main>
        <div class="screen" id="home">
            <p>
                Start low,<br>
                Earn high
            </p>
        </div>
        <div class="screen" id="about">
            <div class="about">
                <p>
                    <i class="far fa-chart-bar fa-3x"></i>
                    <sup><i class="far fa-check-circle fa-2x"></i></sup><br><br>
                    Portfolio growth optimized by our intelligent folio manager
                </p>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quisquam assumenda molestiae accusantium ex quam. Ut minus tempore necessitatibus cupiditate expedita magni deserunt eius, fugit distinctio! Nam aut laudantium mollitia quis.
                </p>
            </div>
            <div class="about">
                <p>
                    <i class="fab fa-bitcoin fa-3x"></i>
                    <i class="fab fa-ethereum fa-3x"></i>
                    <i class="fab fa-monero fa-3x"></i><br><br>
                    Choose the coins you want to earn, our agents do the rest
                </p>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quisquam assumenda molestiae accusantium ex quam. Ut minus tempore necessitatibus cupiditate expedita magni deserunt eius, fugit distinctio! Nam aut laudantium mollitia quis.
                </p>
            </div>
        </div>
        <div class="screen" id="contact">
            <form action="index.php" method="post">
                <h1>Contact us</h1>
                <?php App::DisplayMessages(); ?>
                <label for="name">Name:</label>
                <input type="text" name="name" required value="<?php echo $_POST['name'] ?? ''; ?>">
                <label for="email">Email:</label>
                <input type="email" name="email" required value="<?php echo $_POST['email'] ?? ''; ?>">
                <label for="message">Message</label>
                <textarea name="message" required value="<?php echo $_POST['message'] ?? ''; ?>"></textarea>
                <input type="submit" name="contact_us" value="Send message">
            </form>
        </div>
    </main>
    <footer id="footer">
        <nav>
            <a href="#">Lorem</a>
            <a href="#">ipsum</a>
            <a href="#">dolor</a>
        </nav>
        <p><i class="far fa-copyright"></i> General Trading Services</p>
        <form action="index.php#footer" method="post">
            <label for="username">Username</label>
            <input type="text" name="username" required>
            <label for="password">Password</label>
            <input type="password" name="password" required>
            <input type="submit" name="login" value="Login">
        </form>
    </footer>
</body>
</html>