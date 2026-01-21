<?php

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$error_message = '';
$logged_in = false;
$logged_username = null;

// Handle logout
if (isset($_GET['logout'])) {
    if (Session::isset("session_token")) {
        $token = Session::get("session_token");
        try {
            $userSession = new UserSession($token);
            $userSession->removeSession();
        } catch (Exception $e) {
            // Session might not exist in DB - try direct delete by token
            try {
                $conn = Database::getConnection();
                $tokenEsc = $conn->real_escape_string($token);
                $conn->query("DELETE FROM `session` WHERE `token` = '$tokenEsc'");
            } catch (Exception $e2) {
                // Ignore DB errors during logout cleanup
            }
        }
    }
    Session::destroy();
    header('Location: login.php');
    exit;
}

// Check if already logged in via session token
if (Session::isset("session_token")) {
    try {
        if (UserSession::authorize(Session::get("session_token"))) {
            $logged_in = true;
            $userSession = new UserSession(Session::get("session_token"));
            $userObj = $userSession->getUser();
            $logged_username = $userObj->getUsername();
            // Ensure session variables are set for setnget.php
            Session::set('is_loggedin', true);
            Session::set('session_user', ['username' => $logged_username]);
        } else {
            // Invalid session - clear it
            Session::destroy();
        }
    } catch (Exception $e) {
        Session::destroy();
    }
}

// Handle login form submission
if (!$logged_in && $username !== '' && $password !== '') {
    $token = UserSession::authenticate($username, $password);
    if ($token) {
        $logged_in = true;
        $logged_username = $username;
        // Also set session variables that setnget.php expects
        Session::set('is_loggedin', true);
        Session::set('session_user', ['username' => $username]);
    } else {
        $error_message = 'Invalid username or password';
    }
}

if ($logged_in) {
    ?>
<main class="container">
    <div class="bg-light p-5 rounded mt-3">
        <h1>Login Success<?php echo $logged_username ? ', ' . htmlspecialchars($logged_username) : ''; ?></h1>
        <p class="lead">You are now logged in. Your session is securely stored.</p>
        <a href="login.php?logout" class="btn btn-secondary">Logout</a>
        <a href="setnget.php" class="btn btn-primary">Edit Profile</a>
    </div>
</main>
<?php
} else {
        ?>



<main class="form-signin">
    <form method="post" action="login.php">
        <img class="mb-4" src="https://git.selfmade.ninja/uploads/-/system/appearance/logo/1/Logo_Dark.png" alt=""
            height="50">
        <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

        <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <div class="form-floating">
                <input name="username" type="text" class="form-control" id="floatingInput"
                    placeholder="username" value="<?php echo htmlspecialchars($username); ?>">
                <label for="floatingInput">Username</label>
            </div>
        <div class="form-floating">
            <input name="password" type="password" class="form-control" id="floatingPassword" placeholder="Password">
            <label for="floatingPassword">Password</label>
        </div>

        <div class="checkbox mb-3">
            <label>
                <input type="checkbox" value="remember-me"> Remember me
            </label>
        </div>
        <button class="w-100 btn btn-lg btn-primary hvr-grow-rotate" type="submit">Sign in</button>
    </form>
</main>

<?php
    }