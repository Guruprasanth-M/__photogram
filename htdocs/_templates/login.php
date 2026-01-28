<?php

$login = true;

if (isset($_POST['email_address']) and isset($_POST['password'])) {
    $email_address = $_POST['email_address'];
    $password = $_POST['password'];

    $result = UserSession::authenticate($email_address, $password);
    if ($result) {
        Session::$usersession = UserSession::authorize($result);
    }
    $login = false;
}

if (!$login && $result && Session::$usersession) {
    ?>
    <script>
        window.location.href = "<?=get_config('base_path')?>"
    </script>
    <?php
    exit();
}

if (!$login && !($result && Session::$usersession)) {
    $error_msg = "Invalid username/email or password.";
}
?>

<main class="form-signin">
	<form method="post" action="<?=get_config('base_path')?>login.php">
		<img class="mb-4 d-block mx-auto" src="https://git.selfmade.ninja/uploads/-/system/appearance/logo/1/Logo_Dark.png" alt=""
			height="60">
		<input name="fingerprint" type="hidden" id="fingerprint" value="">
		
        <h1 class="h3 mb-4 fw-normal text-center">Login</h1>

        <?php if (isset($error_msg)) { ?>
            <div class="alert border-danger text-danger text-center mb-4 py-2" style="background: rgba(248, 81, 73, 0.05); border-radius: 8px;">
                <small><?=$error_msg?></small>
            </div>
        <?php } ?>

        <?php if (isset($_GET['signup']) && $_GET['signup'] == 'success') { ?>
            <div class="alert border-success text-success text-center mb-4 py-2" style="background: rgba(35, 134, 54, 0.05); border-radius: 8px;">
                <small>Registration successful! Please login.</small>
            </div>
        <?php } ?>

		<div class="form-floating mb-3">
			<input name="email_address" type="text" class="form-control" id="floatingInput"
				placeholder="name@example.com" required autocomplete="off" value="<?=isset($_POST['email_address']) ? htmlspecialchars($_POST['email_address']) : ''?>">
			<label for="floatingInput">Username or Email</label>
		</div>
		<div class="form-floating mb-4">
			<input name="password" type="password" class="form-control" id="floatingPassword" placeholder="Password" required>
			<label for="floatingPassword">Password</label>
		</div>

		<button class="w-100 btn btn-lg btn-primary mb-4" type="submit">Sign in</button>
        
        <div class="text-center">
            <p class="text-muted mb-0">New here? <a href="<?=get_config('base_path')?>signup.php" class="fw-bold">Create an account</a></p>
        </div>
	</form>
</main>
