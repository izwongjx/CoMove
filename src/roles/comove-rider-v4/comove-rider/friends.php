<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comove – Friends</title>
  <link rel="icon" type="image/svg+xml" href="../../../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="rider.css">
</head>
<body>
  <nav class="top-nav rider-nav-bg">
    <div class="nav-inner">
      <a href="../../index.php" class="logo">Co<span>move</span></a>
      <div class="nav-items">
        <a href="dashboard.php" class="nav-item"><img src="icons/home.svg" width="16" height="16" class="icon-img" alt=""> Dashboard</a>
        <a href="find-rides.php" class="nav-item"><img src="icons/search.svg" width="16" height="16" class="icon-img" alt=""> Find Rides</a>
        <a href="my-trips.php" class="nav-item"><img src="icons/map.svg" width="16" height="16" class="icon-img" alt=""> My Trips</a>
        <a href="friends.php" class="nav-item active"><img src="icons/users.svg" width="16" height="16" class="icon-img" alt=""> Friends</a>
        <a href="rewards.php" class="nav-item"><img src="icons/gift.svg" width="16" height="16" class="icon-img" alt=""> Rewards</a>
        <a href="profile.php" class="nav-item"><img src="icons/user.svg" width="16" height="16" class="icon-img" alt=""> Profile</a>
      </div>
      <div class="nav-actions">
        <a href="../../../../index.php" class="nav-logout-btn"><img src="icons/log-out.svg" width="16" height="16" class="icon-img" alt=""> Log out</a>
      </div>
    </div>
  </nav>

  <main class="dashboard-main">
    <h1 class="page-title">Friends</h1>
    <p class="page-subtitle">Connect and commute together</p>

    <!-- Search -->
    <div class="search-bar">
      <input class="search-input" type="text" id="friendSearch" placeholder="Search friends by name or student ID...">
      <button class="btn-primary" onclick="searchFriends()">Search</button>
    </div>
    <div id="friendSearchResults"></div>

    <!-- Tabs -->
    <div class="tabs-row">
      <button class="tab-btn active" onclick="switchFriendTab(this,'my-friends')">My Friends (<span id="friendCount">0</span>)</button>
      <button class="tab-btn" onclick="switchFriendTab(this,'requests')">Requests <span class="tab-badge" id="friendRequestCount">0</span></button>
    </div>

    <!-- My Friends -->
    <div id="tab-my-friends"></div>

    <!-- Requests -->
    <div id="tab-requests" style="display:none;"></div>
  </main>

  <!-- Friend Detail Modal -->
  <div class="modal-overlay" id="friendModal">
    <div class="modal friend-detail-modal">
      <div id="fdAva" class="friend-detail-ava">
        <img id="fdAvaImg" src="assets/avatars/ahmad-hakim.svg" alt="Friend profile photo">
      </div>
      <div id="fdName" class="friend-detail-name"></div>
      <div style="text-align:center;margin:8px 0 16px;"><span id="fdRole" class="profile-role"></span></div>
      <div class="friend-detail-row"><span class="friend-detail-lbl">Student ID</span><span id="fdID" class="friend-detail-val"></span></div>
      <div class="friend-detail-row"><span class="friend-detail-lbl">Phone</span><span id="fdPhone" class="friend-detail-val"></span></div>
      <div class="friend-detail-row"><span class="friend-detail-lbl">Trips Together</span><span id="fdTrips" class="friend-detail-val"></span></div>
      <div class="friend-detail-row" style="border-bottom:none;"><span class="friend-detail-lbl">Green Points</span><span id="fdPts" class="friend-detail-val" style="color:var(--lime);"></span></div>
      <div class="modal-btns" style="margin-top:20px;">
        <button class="btn-danger" onclick="deleteFriendFromModal()">Remove Friend</button>
        <button class="btn-primary" style="justify-content:center;" onclick="closeFriendModal()">Close</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirm Modal -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal">
      <div class="modal-title">Remove Friend?</div>
      <div class="modal-sub" id="deleteModalSub"></div>
      <div class="modal-btns">
        <button class="btn-outline" onclick="closeDeleteModal()">Cancel</button>
        <button class="btn-danger" onclick="doDelete()">Remove</button>
      </div>
    </div>
  </div>

  <nav class="bottom-nav rider-nav-bg">
    <a href="dashboard.php"><img src="icons/home.svg" width="24" height="24" class="icon-img" alt=""></a>
    <a href="find-rides.php"><img src="icons/search.svg" width="24" height="24" class="icon-img" alt=""></a>
    <a href="my-trips.php"><img src="icons/map.svg" width="24" height="24" class="icon-img" alt=""></a>
    <a href="friends.php" class="active"><img src="icons/users.svg" width="24" height="24" class="icon-img" alt=""></a>
    <a href="rewards.php"><img src="icons/gift.svg" width="24" height="24" class="icon-img" alt=""></a>
    <a href="profile.php"><img src="icons/user.svg" width="24" height="24" class="icon-img" alt=""></a>
  </nav>
  <div class="toast" id="toast"></div>
  <script src="script.js"></script>
  <script src="friends.js"></script>
</body>
</html>

