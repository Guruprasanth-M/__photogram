<?php
include 'libs/load.php';

$message = '';
$show_autologin_form = false;
$db_support = true;
$conn = null;

// Try to establish DB connection
try {
    $conn = Database::getConnection();
} catch (Exception $e) {
    $conn = null;
    $db_support = false;
    error_log('DB connect: ' . $e->getMessage());
}

// Handle autologin (test-only) - check before session validation
if (isset($_GET['autologin'])) {
    $show_autologin_form = true;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_autologin'])) {
        $u = $_POST['username'] ?? '';
        $p = $_POST['password'] ?? '';
        
        try {
            $res = User::login($u, $p);
            if ($res) {
                Session::set('is_loggedin', true);
                Session::set('session_user', $res);
                header('Location: setnget.php');
                exit;
            }
        } catch (Exception $ex) {
            error_log('autologin error: ' . $ex->getMessage());
            
            // Fallback ephemeral login when DB unavailable
            if (!$db_support) {
                Session::set('is_loggedin', true);
                Session::set('session_user', ['username' => $u]);
                header('Location: setnget.php');
                exit;
            }
        }
        
        $message = 'Login failed';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    Session::destroy();
    header('Location: login.php');
    exit;
}

// Require login for normal access
if (!Session::get('is_loggedin') && !isset($_GET['autologin'])) {
    http_response_code(403);
    echo "Not logged in. Please login first.";
    exit;
}

// Get session user
$sessionUser = Session::get('session_user') ?: [];
$username = $sessionUser['username'] ?? null;

if (!$username) {
    echo "Invalid session user. Please login again.";
    exit;
}

// Prepare escaped username
$userEsc = $conn ? $conn->real_escape_string($username) : addslashes($username);

// Initialize profile variables
$bio = $dob = $avatar = '';
$firstname = $lastname = $instagram = $twitter = $facebook = '';
$userObj = null;

// Populate current values (prefer users table via User class)
if ($db_support && $conn) {
    try {
        $userObj = new User($username);
        
        // Load user data from users table (will be null if row doesn't exist yet)
        $bio = $userObj->getBio() ?? '';
        $dob = $userObj->getDob() ?? '';
        $avatar = $userObj->getAvatar() ?? '';
        $firstname = $userObj->getFirstname() ?? '';
        $lastname = $userObj->getLastname() ?? '';
        $instagram = $userObj->getInstagram() ?? '';
        $twitter = $userObj->getTwitter() ?? '';
        $facebook = $userObj->getFacebook() ?? '';
        
    } catch (Exception $e) {
        error_log('Error creating User object: ' . $e->getMessage());
        // Fallback to auth table
        try {
            $q = $conn->query("SELECT * FROM `auth` WHERE `username`='" . $userEsc . "' LIMIT 1");
            if ($q && $row = $q->fetch_assoc()) {
                $bio = $row['bio'] ?? '';
                $dob = $row['dob'] ?? '';
                $avatar = $row['avatar'] ?? '';
                $firstname = $row['firstname'] ?? '';
                $lastname = $row['lastname'] ?? '';
                $instagram = $row['instagram'] ?? '';
                $twitter = $row['twitter'] ?? '';
                $facebook = $row['facebook'] ?? '';
            }
        } catch (Exception $e2) {
            $db_support = false;
        }
    }
} else {
    // DB not available - use session values
    $bio = $sessionUser['bio'] ?? '';
    $dob = $sessionUser['dob'] ?? '';
    $avatar = $sessionUser['avatar'] ?? '';
    $firstname = $sessionUser['firstname'] ?? '';
    $lastname = $sessionUser['lastname'] ?? '';
    $instagram = $sessionUser['instagram'] ?? '';
    $twitter = $sessionUser['twitter'] ?? '';
    $facebook = $sessionUser['facebook'] ?? '';
}

// Handle POST (save/clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['do_autologin'])) {
    $action = $_POST['action'] ?? 'save';
    
    if ($action === 'clear') {
        if ($db_support && $conn && $userObj) {
            // Clear all profile fields by setting to empty
            // The INSERT ... ON DUPLICATE KEY will handle row creation if needed
            $userObj->setBio('');
            $userObj->setAvatar('');
            $userObj->setFirstname('');
            $userObj->setLastname('');
            $userObj->setInstagram('');
            $userObj->setTwitter('');
            $userObj->setFacebook('');
            // Set DOB to a default empty date or NULL equivalent
            $userObj->setDob(1970, 1, 1);
        } elseif ($db_support && $conn) {
            // Fallback: Clear in auth table if those columns exist
            $userEsc = $conn->real_escape_string($username);
            $conn->query("UPDATE `auth` SET `bio`='',`avatar`='',`firstname`='',`lastname`='',`instagram`='',`twitter`='',`facebook`='',`dob`='' WHERE `username`='" . $userEsc . "'");
        } else {
            // Session-only fallback
            Session::set('session_user', ['username' => $username]);
        }
        header('Location: setnget.php');
        exit;
    }
    
    // Save action - collect form data
    $nbio = $_POST['bio'] ?? '';
    $ndob = $_POST['dob'] ?? '';
    $navatar = $_POST['avatar'] ?? '';
    $nfirstname = $_POST['firstname'] ?? '';
    $nlastname = $_POST['lastname'] ?? '';
    $ninstagram = $_POST['instagram'] ?? '';
    $ntwitter = $_POST['twitter'] ?? '';
    $nfacebook = $_POST['facebook'] ?? '';
    
    if ($db_support && $conn && $userObj) {
        // Check if users row exists first
        $uid = (int)$userObj->id;
        $rowExists = $conn->query("SELECT `id` FROM `users` WHERE `id`=$uid LIMIT 1");
        
        if (!$rowExists || $rowExists->num_rows == 0) {
            // First-time user - INSERT all fields at once
            $colsRes = $conn->query("SHOW COLUMNS FROM `users`");
            $existing = [];
            if ($colsRes) {
                while ($c = $colsRes->fetch_assoc()) {
                    $existing[] = $c['Field'];
                }
            }
            
            // Build INSERT with all profile fields
            $fields = [
                'id' => $uid,
                'bio' => $nbio,
                'dob' => $ndob,
                'avatar' => $navatar,
                'firstname' => $nfirstname,
                'lastname' => $nlastname,
                'instagram' => $ninstagram,
                'twitter' => $ntwitter,
                'facebook' => $nfacebook
            ];
            
            $cols = [];
            $vals = [];
            foreach ($fields as $col => $val) {
                if (in_array($col, $existing)) {
                    $cols[] = "`$col`";
                    if ($col === 'id') {
                        $vals[] = $val;
                    } else {
                        $vals[] = "'" . $conn->real_escape_string($val) . "'";
                    }
                }
            }
            
            if (count($cols) > 0) {
                $sql = "INSERT INTO `users` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
                if ($conn->query($sql)) {
                    header('Location: setnget.php');
                    exit;
                } else {
                    $message = 'Failed to create user profile: ' . $conn->error;
                    error_log('setnget.php INSERT failed: ' . $conn->error);
                }
            } else {
                $message = 'No valid columns found in users table';
            }
        } else {
            // Existing user - UPDATE via User class methods
            $failed = [];
            
            if (!$userObj->setBio($nbio)) $failed[] = 'bio';
            if (!$userObj->setAvatar($navatar)) $failed[] = 'avatar';
            if (!$userObj->setFirstname($nfirstname)) $failed[] = 'firstname';
            if (!$userObj->setLastname($nlastname)) $failed[] = 'lastname';
            if (!$userObj->setInstagram($ninstagram)) $failed[] = 'instagram';
            if (!$userObj->setTwitter($ntwitter)) $failed[] = 'twitter';
            if (!$userObj->setFacebook($nfacebook)) $failed[] = 'facebook';
            
            if ($ndob !== '') {
                $parts = explode('-', $ndob);
                if (count($parts) === 3) {
                    list($y, $m, $d) = $parts;
                    if (!$userObj->setDob((int)$y, (int)$m, (int)$d)) {
                        $failed[] = 'dob';
                    }
                }
            }
            
            if (count($failed) === 0) {
                header('Location: setnget.php');
                exit;
            } else {
                $message = 'Failed to save fields: ' . implode(', ', $failed);
                error_log('setnget.php: Failed fields: ' . implode(', ', $failed));
            }
        }
    } elseif ($db_support && $conn) {
        // Fallback: Update auth table only for existing columns
        $res = $conn->query("SHOW COLUMNS FROM `auth`");
        $authCols = [];
        if ($res) {
            while ($c = $res->fetch_assoc()) {
                $authCols[] = $c['Field'];
            }
        }
        
        $cols = [];
        $addIf = function($col, $val) use ($conn, &$cols, $authCols) {
            if (in_array($col, $authCols)) {
                $cols[] = "`$col`='" . $conn->real_escape_string($val) . "'";
            }
        };
        
        $addIf('bio', $nbio);
        $addIf('dob', $ndob);
        $addIf('avatar', $navatar);
        $addIf('firstname', $nfirstname);
        $addIf('lastname', $nlastname);
        $addIf('instagram', $ninstagram);
        $addIf('twitter', $ntwitter);
        $addIf('facebook', $nfacebook);
        
        if (count($cols) > 0) {
            $sql = "UPDATE `auth` SET " . implode(',', $cols) . " WHERE `username`='" . $userEsc . "' LIMIT 1";
            if ($conn->query($sql) === true) {
                header('Location: setnget.php');
                exit;
            } else {
                $message = 'DB error: ' . $conn->error;
            }
        } else {
            $message = 'No updatable columns present in auth table.';
        }
    } else {
        // Session-only fallback
        $sessionUser['bio'] = $nbio;
        $sessionUser['dob'] = $ndob;
        $sessionUser['avatar'] = $navatar;
        $sessionUser['firstname'] = $nfirstname;
        $sessionUser['lastname'] = $nlastname;
        $sessionUser['instagram'] = $ninstagram;
        $sessionUser['twitter'] = $ntwitter;
        $sessionUser['facebook'] = $nfacebook;
        Session::set('session_user', $sessionUser);
        header('Location: setnget.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - Set & Get</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: inline-block; width: 120px; font-weight: bold; }
        input, textarea { width: 300px; padding: 5px; }
        button { padding: 8px 15px; margin-right: 10px; cursor: pointer; }
        .message { padding: 10px; background: #ffffcc; border: 1px solid #ccc; margin-bottom: 15px; }
        .current-values { margin-top: 30px; padding: 15px; background: #f5f5f5; border: 1px solid #ddd; }
        .autologin-form { max-width: 400px; margin: 100px auto; padding: 20px; border: 1px solid #ccc; }
    </style>
</head>
<body>

<?php if ($show_autologin_form): ?>
    <div class="autologin-form">
        <h2>Test Login</h2>
        <p>Enter credentials to sign in for testing.</p>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="do_autologin" value="1">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Sign in</button>
        </form>
    </div>
<?php else: ?>
    <div class="nav">
        <a href="login.php">Login page</a>
        <a href="?logout">Logout</a>
    </div>

    <h1>Profile for <?php echo htmlspecialchars($username); ?></h1>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>First name:</label>
            <input type="text" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>">
        </div>
        
        <div class="form-group">
            <label>Last name:</label>
            <input type="text" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>">
        </div>
        
        <div class="form-group">
            <label>Bio:</label>
            <textarea name="bio" rows="4"><?php echo htmlspecialchars($bio); ?></textarea>
        </div>
        
        <div class="form-group">
            <label>DOB:</label>
            <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
        </div>
        
        <div class="form-group">
            <label>Avatar URL:</label>
            <input type="text" name="avatar" value="<?php echo htmlspecialchars($avatar); ?>">
        </div>
        
        <div class="form-group">
            <label>Instagram:</label>
            <input type="text" name="instagram" value="<?php echo htmlspecialchars($instagram); ?>">
        </div>
        
        <div class="form-group">
            <label>Twitter:</label>
            <input type="text" name="twitter" value="<?php echo htmlspecialchars($twitter); ?>">
        </div>
        
        <div class="form-group">
            <label>Facebook:</label>
            <input type="text" name="facebook" value="<?php echo htmlspecialchars($facebook); ?>">
        </div>
        
        <div class="form-group">
            <button type="submit" name="action" value="save">Save</button>
            <button type="submit" name="action" value="clear" onclick="return confirm('Clear all profile data?')">Clear profile</button>
        </div>
    </form>

    <div class="current-values">
        <h3>Current values</h3>
        <p><strong>First name:</strong> <?php echo $firstname ? htmlspecialchars($firstname) : '<em>(not set)</em>'; ?></p>
        <p><strong>Last name:</strong> <?php echo $lastname ? htmlspecialchars($lastname) : '<em>(not set)</em>'; ?></p>
        <p><strong>Bio:</strong> <?php echo $bio ? htmlspecialchars($bio) : '<em>(not set)</em>'; ?></p>
        <p><strong>DOB:</strong> <?php echo $dob ? htmlspecialchars($dob) : '<em>(not set)</em>'; ?></p>
        <p><strong>Avatar:</strong> <?php echo $avatar ? htmlspecialchars($avatar) : '<em>(not set)</em>'; ?></p>
        <p><strong>Instagram:</strong> <?php echo $instagram ? htmlspecialchars($instagram) : '<em>(not set)</em>'; ?></p>
        <p><strong>Twitter:</strong> <?php echo $twitter ? htmlspecialchars($twitter) : '<em>(not set)</em>'; ?></p>
        <p><strong>Facebook:</strong> <?php echo $facebook ? htmlspecialchars($facebook) : '<em>(not set)</em>'; ?></p>
    </div>
<?php endif; ?>

</body>
</html>