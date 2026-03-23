<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoMove - Sign Up</title>
  <link rel="icon" type="image/svg+xml" href="../../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="register.css">
</head>

<body>
  <main class="page">
    <aside class="hero" aria-label="CoMove Message">
      <div class="hero-content">
        <h2>JOIN THE<br><span>REVOLUTION</span></h2>
        <p>Every shared ride is a vote for a cleaner planet. Be part of the solution, not the pollution.</p>
      </div>
    </aside>

    <section class="panel">
      <nav class="top-nav" aria-label="Page Navigation">
        <a href="../../../index.php" class="back-link">
          <img src="../../public-assets/icons/arrow-left.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> BACK TO HOME
        </a>
      </nav>

      <div class="form-wrapper">
        <header class="section-header">
          <h1>Join CoMove</h1>
          <p>Choose how you want to contribute to a greener future</p>
        </header>

        <div class="role-list">
          <button class="role-card" onclick="window.location.href='register-as-rider.php'">
            <div class="role-icon"><img src="../../public-assets/icons/user.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></div>
            <span class="role-text">Register as a Rider</span>
            <img src="../../public-assets/icons/arrow-right.svg" width="20" height="20" class="role-arrow icon-img" alt="" aria-hidden="true">
          </button>
          <button class="role-card" onclick="window.location.href='register-as-driver.php'">
            <div class="role-icon"><img src="../../public-assets/icons/car.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></div>
            <span class="role-text">Register as a Driver</span>
            <img src="../../public-assets/icons/arrow-right.svg" width="20" height="20" class="role-arrow icon-img" alt="" aria-hidden="true">
          </button>
        </div>
        <footer class="section-footer">
          <p>Already have an account? <a href="../login/login.php"><strong>Log in</strong></a></p>
        </footer>
      </div>
    </section>
  </main>

  <script src="../../public-assets/script.js"></script>
</body>

</html>

<<<<<<< HEAD
=======
    return mysqli_real_escape_string($dbConn, $rawContent);
}

function getDefaultProfilePhoto(mysqli $dbConn): ?string
{
    $defaultPath = __DIR__ . '/../../public-assets/images/profile-icon.png';
    if (!is_file($defaultPath)) {
        return null;
    }

    $rawContent = file_get_contents($defaultPath);
    if ($rawContent === false) {
        return null;
    }

    return mysqli_real_escape_string($dbConn, $rawContent);
}

function isApuEmail(string $email): bool
{
    return (bool) preg_match('/^[A-Za-z0-9._%+-]+@mail\.apu\.edu\.my$/i', $email);
}

function ensureValidOtp(mysqli $dbConn, string $email, string $otpCode): void
{
    $sql = "SELECT 1 FROM OTP WHERE email_address = '" . $email . "' AND otp_code = '" . $otpCode . "' AND is_used = FALSE AND expires_at >= NOW() LIMIT 1";
    $result = mysqli_query($dbConn, $sql);

    if (!$result || mysqli_num_rows($result) <= 0) {
        failBack('Invalid or expired OTP code. Please request a new OTP.');
    }

    mysqli_free_result($result);
}

function consumeOtp(mysqli $dbConn, string $email, string $otpCode): void
{
    mysqli_query(
        $dbConn,
        "UPDATE OTP SET is_used = TRUE WHERE email_address = '" . $email . "' AND otp_code = '" . $otpCode . "' AND is_used = FALSE AND expires_at >= NOW() ORDER BY created_at DESC LIMIT 1"
    );
}

function ensureEmailNotExists(mysqli $dbConn, string $tableName, string $email): void
{
    $sql = "SELECT 1 FROM " . $tableName . " WHERE email = '" . $email . "' LIMIT 1";
    $result = mysqli_query($dbConn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        mysqli_free_result($result);
        failBack('Email is already registered. Please login instead.');
    }

    if ($result) {
        mysqli_free_result($result);
    }
}

$role = strtolower(trim(isset($_POST['role']) ? (string) $_POST['role'] : ''));
$password = mysqli_real_escape_string($dbConn, isset($_POST['password']) ? (string) $_POST['password'] : '');
$confirmPassword = mysqli_real_escape_string($dbConn, isset($_POST['confirmPassword']) ? (string) $_POST['confirmPassword'] : '');
$otpCode = mysqli_real_escape_string($dbConn, isset($_POST['otp_code']) ? trim((string) $_POST['otp_code']) : '');

if ($role !== 'rider' && $role !== 'driver') {
    failBack('Invalid register request.');
}

if ($password !== $confirmPassword) {
    failBack('Password and confirmed password not same!');
}

if (!preg_match('/^\d{6}$/', $otpCode)) {
    failBack('OTP code is required before registration.');
}

if ($role === 'rider') {
    $name = mysqli_real_escape_string($dbConn, isset($_POST['fullName']) ? trim((string) $_POST['fullName']) : '');
    $emailRaw = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
    $email = mysqli_real_escape_string($dbConn, strtolower($emailRaw));
    $phone = mysqli_real_escape_string($dbConn, isset($_POST['phone']) ? trim((string) $_POST['phone']) : '');
    $profilePhoto = readUpload($dbConn, 'profile_photo', false);
    if ($profilePhoto === null) {
        $profilePhoto = getDefaultProfilePhoto($dbConn);
    }

    if ($name === '' || $email === '' || $phone === '' || $password === '') {
        failBack('Please fill all required fields!');
    }

    if (!isApuEmail($email)) {
        failBack('Only APU email is allowed.');
    }

    ensureEmailNotExists($dbConn, 'RIDER', $email);
    ensureValidOtp($dbConn, $email, $otpCode);

    $profilePhotoSql = $profilePhoto === null ? "NULL" : "'" . $profilePhoto . "'";

    $createdAtSql = "NOW()";
    // TODO: re-enable password hashing when ready (example: md5($password) or password_hash)
    // $hashedPassword = md5($password);
    $sql = "Insert into RIDER (name, email, password, phone_number, profile_photo, created_at, rider_status) VALUES ('" .
        $name . "','" . $email . "','" . $password . "','" . $phone . "'," . $profilePhotoSql . "," . $createdAtSql . ",'active')";
    mysqli_query($dbConn, $sql);

    if (mysqli_affected_rows($dbConn) <= 0) {
        failBack('Unable to register! Please Try Again!');
    }
    $riderId = (int) mysqli_insert_id($dbConn);
    consumeOtp($dbConn, $email, $otpCode);

    $_SESSION['user'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'rider';
    $_SESSION['user_id'] = $riderId;

    echo "<script>alert('Registration completed successfully!');";
    echo "window.location.href='../../roles/comove-rider-v4/comove-rider/dashboard.php';</script>";
    exit;
}

$name = mysqli_real_escape_string($dbConn, isset($_POST['name']) ? trim((string) $_POST['name']) : '');
$emailRaw = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$email = mysqli_real_escape_string($dbConn, strtolower($emailRaw));
$phoneNumber = mysqli_real_escape_string($dbConn, isset($_POST['phone_number']) ? trim((string) $_POST['phone_number']) : '');
$nricNumber = mysqli_real_escape_string($dbConn, isset($_POST['nric_number']) ? trim((string) $_POST['nric_number']) : '');
$licenseExpiryDate = mysqli_real_escape_string($dbConn, isset($_POST['lisence_expiry_date']) ? trim((string) $_POST['lisence_expiry_date']) : '');
$vehicleModel = mysqli_real_escape_string($dbConn, isset($_POST['vehicle_type']) ? trim((string) $_POST['vehicle_type']) : '');
$plateNumber = mysqli_real_escape_string($dbConn, isset($_POST['plate_number']) ? trim((string) $_POST['plate_number']) : '');
$color = mysqli_real_escape_string($dbConn, isset($_POST['color']) ? trim((string) $_POST['color']) : '');

if (
    $name === '' ||
    $email === '' ||
    $phoneNumber === '' ||
    $password === '' ||
    $nricNumber === '' ||
    $licenseExpiryDate === '' ||
    $plateNumber === ''
) {
    failBack('Please fill all required fields!');
}

if (!isApuEmail($email)) {
    failBack('Only APU email is allowed.');
}

ensureEmailNotExists($dbConn, 'DRIVER', $email);
ensureValidOtp($dbConn, $email, $otpCode);

$nricFrontImage = readUpload($dbConn, 'nric_front_image', true);
$nricBackImage = readUpload($dbConn, 'nric_back_image', true);
$licenseFrontImage = readUpload($dbConn, 'lisence_front_image', true);
$licenseBackImage = readUpload($dbConn, 'lisence_back_image', true);
$profilePhoto = readUpload($dbConn, 'profile_photo', false);
if ($profilePhoto === null) {
    $profilePhoto = getDefaultProfilePhoto($dbConn);
}

$profilePhotoSql = $profilePhoto === null ? "NULL" : "'" . $profilePhoto . "'";

$createdAtSql = "NOW()";
// TODO: re-enable password hashing when ready (example: md5($password) or password_hash)
// $hashedPassword = md5($password);
$sql = "Insert into DRIVER (name, email, password, phone_number, profile_photo, created_at, driver_status, nric_number, nric_front_image, nric_back_image, lisence_front_image, lisence_back_image, lisence_expiry_date, vehicle_model, plate_number, color) VALUES ('" .
    $name . "','" . $email . "','" . $password . "','" . $phoneNumber . "'," . $profilePhotoSql . "," . $createdAtSql . ",'pending','" . $nricNumber . "','" . $nricFrontImage . "','" . $nricBackImage . "','" . $licenseFrontImage . "','" . $licenseBackImage . "','" . $licenseExpiryDate . "','" . $vehicleModel . "','" . $plateNumber . "','" . $color . "')";
mysqli_query($dbConn, $sql);

if (mysqli_affected_rows($dbConn) <= 0) {
    failBack('Unable to register! Please Try Again!');
}
$driverId = (int) mysqli_insert_id($dbConn);
consumeOtp($dbConn, $email, $otpCode);

$_SESSION['user'] = $name;
$_SESSION['email'] = $email;
$_SESSION['role'] = 'driver';
$_SESSION['user_id'] = $driverId;

echo "<script>alert('Registration completed successfully!');";
echo "window.location.href='../../roles/driver/dashboard.html';</script>";
?>
>>>>>>> bf9252d (edit)
