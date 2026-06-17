<?php
session_start();
session_unset();
session_destroy();

// Hapus cookie
setcookie("user_id", "", time() - 3600, "/");
setcookie("user_name", "", time() - 3600, "/");
setcookie("user_role", "", time() - 3600, "/");

header("Location: ?page=home");
exit();
