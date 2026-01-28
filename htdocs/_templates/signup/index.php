<?php

$signup = false;
if (isset($_POST['username']) and isset($_POST['password']) and !empty($_POST['password']) and isset($_POST['email_address']) and isset($_POST['phone'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email_address']);
    $phone = trim($_POST['phone']);
    $error = User::signup($username, $password, $email, $phone);
    $signup = true;
}

if ($signup && $error === true) {
    ?>
    <script>
        window.location.href = "<?=get_config('base_path')?>login.php?signup=success"
    </script>
    <?php
    exit();
}

?>

<main class="form-signup">
	<form method="post" action="<?=get_config('base_path')?>signup.php">
		<img class="mb-4 d-block mx-auto" src="https://git.selfmade.ninja/uploads/-/system/appearance/logo/1/Logo_Dark.png" alt=""
			height="60">
		<h1 class="h3 mb-4 fw-normal text-center">Join Photogram</h1>

        <?php if ($signup && $error !== true) { ?>
            <div class="alert border-danger text-danger text-center mb-4 py-2" style="background: rgba(248, 81, 73, 0.05); border-radius: 8px;">
                <small><?=$error?></small>
            </div>
        <?php } ?>

		<div class="form-floating mb-3">
			<input name="username" type="text" class="form-control" id="floatingInputUsername"
				placeholder="Username" required autocomplete="off" value="<?=isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''?>">
			<label for="floatingInputUsername">Username</label>
		</div>
		<div class="form-floating mb-3">
			<input name="phone" type="text" class="form-control" id="floatingInputPhone"
				placeholder="Phone" required value="<?=isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''?>">
			<label for="floatingInputPhone">Phone Number</label>
		</div>
		<div class="form-floating mb-3">
			<input name="email_address" type="email" class="form-control" id="floatingInput"
				placeholder="name@example.com" required value="<?=isset($_POST['email_address']) ? htmlspecialchars($_POST['email_address']) : ''?>">
			<label for="floatingInput">Email address</label>
		</div>
		<div class="form-floating mb-4">
			<input name="password" type="password" class="form-control" id="floatingPassword" placeholder="Password" required>
			<label for="floatingPassword">Password</label>
		</div>
		<button class="w-100 btn btn-lg btn-primary mb-4" type="submit">Create Account</button>
        <div class="text-center">
            <p class="text-muted mb-0">Already a member? <a href="<?=get_config('base_path')?>login.php" class="fw-bold">Login Here</a></p>
        </div>
	</form>
</main>
