<?php
// admin/logout.php

// PENTING: Hubungkan ke controller yang ada di root folder (tingkat atas)
require_once '../auth_controller.php';

// Panggil fungsi logout. Fungsi ini akan menghancurkan sesi dan mengarahkan user
// ke halaman login di folder yang sesuai ('admin/login.php')
logoutUser(); 
?>