
<?php
// In logout_process.php:
session_start();
unset($_SESSION['isAdmin']);
echo "<script>
  localStorage.setItem('activeSidebarItem', 'products');
  // Signal to PHP that localStorage has been set

    window.location.href = 'http://localhost/fatima/login.php';

  </script>
";
?>