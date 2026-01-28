<?php
$user = Session::isAuthenticated() ? Session::getUser() : null;
$avatar = ($user && $user->getAvatar()) ? $user->getAvatar() : null; 
$displayName = ($user && $user->getFirstname()) ? $user->getFirstname() . ' ' . $user->getLastname() : ($user ? $user->getUsername() : "Guest");
?>

<header>
	<?if(Session::isAuthenticated()){?>
	<!-- Profile Sidebar (Offcanvas) -->
	<div class="offcanvas offcanvas-end" tabindex="-1" id="profileSidebar" aria-labelledby="profileSidebarLabel">
		<div class="offcanvas-header">
			<h5 class="offcanvas-title" id="profileSidebarLabel">Account</h5>
			<button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body">
			<div class="user-info-section mb-3">
                <?if($avatar){?>
				    <img src="<?=$avatar?>" alt="Avatar" class="user-info-avatar">
                <?} else {?>
                    <div class="user-info-avatar d-flex align-items-center justify-content-center bg-secondary" style="border-style: dashed;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white-50"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                <?}?>
				<div class="user-info-details">
					<span class="user-info-name"><?=$displayName?></span>
					<span class="user-info-username">@<?=$user->getUsername()?></span>
				</div>
			</div>

			<div class="sidebar-separator"></div>

			<a href="<?=get_config('base_path')?>setnget.php" class="sidebar-link">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
				Your Profile
			</a>
			<a href="<?=get_config('base_path')?>" class="sidebar-link">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
				Feed
			</a>

			<div class="sidebar-separator"></div>

			<a href="<?=get_config('base_path')?>?logout" class="sidebar-link text-danger">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
				Sign out
			</a>
		</div>
	</div>
	<?}?>

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
			
			<div class="d-flex align-items-center">
				<?if(Session::isAuthenticated()){?>
					<button class="nav-avatar-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#profileSidebar" aria-controls="profileSidebar">
						<?if($avatar){?>
                            <img src="<?=$avatar?>" alt="User" class="nav-avatar-img">
                        <?} else {?>
                            <div class="nav-avatar-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                            </div>
                        <?}?>
					</button>
				<?} else {?>
					<a href="<?=get_config('base_path')?>login.php" class="btn btn-link text-white fw-bold me-2">Login</a>
					<a href="<?=get_config('base_path')?>signup.php" class="btn btn-primary btn-sm px-3">Join</a>
				<?}?>
			</div>
		</div>
	</nav>
</header>