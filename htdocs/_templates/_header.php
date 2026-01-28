<header>
	<div class="collapse" id="navbarHeader">
		<div class="container py-4">
			<div class="row">
				<div class="col-sm-8 col-md-7 py-4">
					<h4 class="text-white">About</h4>
					<p class="text-muted">Photogram is a premium platform for visual storytelling. Connect with creators and preserve your journey in the most elegant way possible.</p>
				</div>
				<div class="col-sm-4 offset-md-1 py-4">
					<h4 class="text-white">Account</h4>
					<ul class="list-unstyled">
						<?if(Session::isAuthenticated()){?>
						<li><a href="<?=get_config('base_path')?>?logout" class="text-danger fw-bold">Logout (@<?=Session::getUser()->getUsername()?>)</a></li>
						<?} else {?>
							<li><a href="<?=get_config('base_path')?>login.php" class="text-white fw-bold">Login</a></li>
							<li><a href="<?=get_config('base_path')?>signup.php" class="text-white mt-2 d-block">Create Account</a></li>
						<?}?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<nav class="navbar navbar-dark sticky-top">
		<div class="container">
			<a href="<?=get_config('base_path')?>" class="navbar-brand d-flex align-items-center">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor"
					stroke-linecap="round" stroke-linejoin="round" stroke-width="2" aria-hidden="true" class="me-2"
					viewBox="0 0 24 24">
					<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
					<circle cx="12" cy="13" r="4" />
				</svg>
				<strong>Photogram</strong>
			</a>
			<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader"
				aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
		</div>
	</nav>
</header>