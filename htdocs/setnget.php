<?php
include 'libs/load.php';

// New Style authentication check
if (!Session::isAuthenticated()) {
    header("Location: " . get_config('base_path') . "login.php");
    exit();
}

$user = Session::getUser();
$message = '';
$error = false;

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save') {
            try {
                $user->setFirstname($_POST['firstname'] ?? '');
                $user->setLastname($_POST['lastname'] ?? '');
                $user->setBio($_POST['bio'] ?? '');
                $user->setAvatar($_POST['avatar'] ?? '');
                $user->setInstagram($_POST['instagram'] ?? '');
                $user->setTwitter($_POST['twitter'] ?? '');
                $user->setFacebook($_POST['facebook'] ?? '');
                
                if (!empty($_POST['dob'])) {
                    $parts = explode('-', $_POST['dob']);
                    if (count($parts) === 3) {
                        $user->setDob($parts[0], $parts[1], $parts[2]);
                    }
                }
                
                $message = "Profile updated successfully!";
            } catch (Exception $e) {
                $message = "Update failed: " . $e->getMessage();
                $error = true;
            }
        } elseif ($_POST['action'] === 'clear') {
            try {
                $user->setFirstname('');
                $user->setLastname('');
                $user->setBio('');
                $user->setAvatar('');
                $user->setInstagram('');
                $user->setTwitter('');
                $user->setFacebook('');
                $message = "Profile cleared!";
            } catch (Exception $e) {
                $message = "Clear failed: " . $e->getMessage();
                $error = true;
            }
        }
    }
}

// Prepare UI data
$displayName = ($user->getFirstname()) ? $user->getFirstname() . ' ' . $user->getLastname() : $user->getUsername();
$avatar = ($user->getAvatar()) ? $user->getAvatar() : "https://git.selfmade.ninja/uploads/-/system/appearance/logo/1/Logo_Dark.png";

?>
<!DOCTYPE html>
<html lang="en">
<?php Session::loadTemplate('_head'); ?>
<body class="bg-dark">
    <?php Session::loadTemplate('_header'); ?>
    
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-container">
                    <div class="d-flex align-items-center mb-5 pb-4 border-bottom border-secondary">
                        <img src="<?=$avatar?>" alt="Avatar" class="user-info-avatar me-4" style="width: 100px; height: 100px;">
                        <div>
                            <h1 class="hero-title mb-0"><?=$displayName?></h1>
                            <p class="lead text-muted">@<?=$user->getUsername()?> &middot; Account Profile Management</p>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert <?= $error ? 'border-danger text-danger' : 'border-success text-success' ?> mb-4 py-3 text-center" style="background: rgba(255, 255, 255, 0.05); border-radius: 12px;">
                            <strong><?= $message ?></strong>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="row g-4">
                        <input type="hidden" name="action" value="save">
                        
                        <!-- Name Section -->
                        <div class="col-md-6">
                            <h5 class="mb-3 text-success">Personal Information</h5>
                            <div class="form-floating mb-3">
                                <input name="firstname" type="text" class="form-control" id="fName" placeholder="First Name" value="<?=htmlspecialchars($user->getFirstname())?>">
                                <label for="fName">First Name</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input name="lastname" type="text" class="form-control" id="lName" placeholder="Last Name" value="<?=htmlspecialchars($user->getLastname())?>">
                                <label for="lName">Last Name</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input name="dob" type="date" class="form-control" id="dob" placeholder="Birthday" value="<?=htmlspecialchars($user->getDob())?>">
                                <label for="dob">Birthday</label>
                            </div>
                        </div>

                        <!-- Social Section -->
                        <div class="col-md-6">
                            <h5 class="mb-3 text-success">Online Presence</h5>
                            <div class="form-floating mb-3">
                                <input name="avatar" type="text" class="form-control" id="avatar" placeholder="Avatar URL" value="<?=htmlspecialchars($user->getAvatar())?>">
                                <label for="avatar">Profile Picture URL</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input name="instagram" type="text" class="form-control" id="insta" placeholder="Instagram" value="<?=htmlspecialchars($user->getInstagram())?>">
                                <label for="insta">Instagram Handle</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input name="twitter" type="text" class="form-control" id="twitter" placeholder="Twitter" value="<?=htmlspecialchars($user->getTwitter())?>">
                                <label for="twitter">Twitter / X Handle</label>
                            </div>
                        </div>

                        <!-- Bio Section -->
                        <div class="col-12">
                            <h5 class="mb-3 text-success">About You</h5>
                            <div class="form-floating mb-4">
                                <textarea name="bio" class="form-control" id="bio" placeholder="Bio" style="height: 120px"><?=htmlspecialchars($user->getBio())?></textarea>
                                <label for="bio">Tell us something about yourself...</label>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between pt-4 border-top border-secondary">
                            <button type="submit" name="action" value="clear" class="btn btn-secondary px-5" onclick="return confirm('Clear all profile data?')">Clear All</button>
                            <button type="submit" name="action" value="save" class="btn btn-primary px-5">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php Session::loadTemplate('_footer'); ?>
</body>
</html>