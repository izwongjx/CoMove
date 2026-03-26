<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoMove - Log In</title>
  <link rel="icon" type="image/svg+xml" href="../../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="login.css">
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
        <a href="login.php" class="back-link">
          <img src="../../public-assets/icons/arrow-left.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> BACK TO ROLE SELECTION
        </a>
      </nav>

      <div class="form-wrapper">
        <header class="section-header">
          <h1>Welcome Back</h1>
          <p>Sign in to continue your eco-friendly journey</p>
        </header>

        <form class="login-form" method="post" action="login-handler.php">
          <input type="hidden" name="role" value="rider">
          <table class="form-table" role="presentation">
            <tr>
              <th class="field-label-cell" scope="row">
                <label class="form-label" for="loginEmail">APU Email Address</label>
              </th>
              <td class="field-input-cell">
                <div class="input-wrap">
                  <input type="email" class="input-control" id="loginEmail" name="user" placeholder="xxx@mail.apu.edu.my" required>
                </div>
              </td>
            </tr>
            <tr>
              <th class="field-label-cell" scope="row">
                <label class="form-label" for="loginPassword">Password</label>
              </th>
              <td class="field-input-cell">
                <div class="input-wrap">
                  <input type="password" class="input-control" id="loginPassword" name="pass" placeholder="********" required>
                </div>
              </td>
            </tr>
          </table>

          <button type="submit" class="submit-button">
            LOG IN AS RIDER
          </button>

          <footer class="section-footer">
            <p>Don't have an account? <a href="../register/register-as-rider.php"><strong>Sign up now</strong></a></p>
          </footer>
        </form>
      </div>
    </section>
  </main>

  <script src="../../public-assets/script.js"></script>
</body>

</html>


