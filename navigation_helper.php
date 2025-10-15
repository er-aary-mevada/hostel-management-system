<?php
// Navigation consistency helper
function getNavigationInfo($session_data) {
    $is_admin = (isset($session_data["username"]) && $session_data["username"] === 'admin') || 
                (isset($session_data["email"]) && $session_data["email"] === 'admin1@gmail.com');
    
    return [
        'is_admin' => $is_admin,
        'dashboard_link' => $is_admin ? 'admin_dashboard.php' : 'student_dashboard.php',
        'dashboard_title' => $is_admin ? 'Admin Dashboard' : 'Student Dashboard',
        'user_type' => $is_admin ? 'admin' : 'student'
    ];
}

// Breadcrumb helper
function getBreadcrumb($current_page, $nav_info) {
    $breadcrumbs = [
        'dashboard.php' => ['Dashboard'],
        'students.php' => [$nav_info['dashboard_title'], 'Students Management'],
        'rooms.php' => [$nav_info['dashboard_title'], 'Rooms Management'],
        'payments.php' => [$nav_info['dashboard_title'], 'Payments'],
        'admin_settings.php' => ['Admin Dashboard', 'Settings'],
        'student_settings.php' => ['Student Dashboard', 'Settings']
    ];
    
    return isset($breadcrumbs[$current_page]) ? $breadcrumbs[$current_page] : ['Dashboard'];
}

// Page title consistency
function getPageTitle($current_page) {
    $titles = [
        'dashboard.php' => 'Dashboard - HMS',
        'students.php' => 'Students Management - HMS',
        'rooms.php' => 'Rooms Management - HMS',
        'payments.php' => 'Payments - HMS',
        'admin_settings.php' => 'Admin Settings - HMS',
        'student_settings.php' => 'Student Settings - HMS'
    ];
    
    return isset($titles[$current_page]) ? $titles[$current_page] : 'HMS';
}
?>