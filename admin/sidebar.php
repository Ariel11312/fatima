<?php


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elegant Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f7;
            display: flex;
            min-height: 100vh;
        }
        
        .side-bar-container {
            height: 100vh;
            position: fixed;
            margin-top: -30px;
        }
        
        .side-bar {
            width: 280px;
            height: 100%;
            background: linear-gradient(135deg, #2c3e50, #1a2533);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            position: relative;
            color: #fff;
            overflow: hidden;
        }
        
        .side-bar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .side-bar-header h2 {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 1px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .side-bar-header h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: #3498db;
            border-radius: 2px;
        }
        
        .side-bar-content {
            padding: 20px 0;
        }
        
        .side-bar-content ul {
            list-style: none;
        }
        
        .side-bar-content ul li {
            position: relative;
            margin-bottom: 5px;
        }
        
        .side-bar-content ul li a {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 16px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .side-bar-content ul li a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: rgba(52, 152, 219, 0.2);
            z-index: -1;
            transition: width 0.3s ease;
        }
        
        .side-bar-content ul li a:hover {
            color: #fff;
        }
        
        .side-bar-content ul li a:hover::before {
            width: 100%;
        }
        
        .side-bar-content ul li.active a {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
            font-weight: 600;
            border-left: 4px solid #3498db;
        }
        
        .side-bar-content ul li.active a::before {
            display: none;
        }
        
        .toggle-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .toggle-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .toggle-btn span {
            width: 15px;
            height: 2px;
            background: #fff;
            position: relative;
        }
        
        .toggle-btn span::before,
        .toggle-btn span::after {
            content: '';
            position: absolute;
            width: 15px;
            height: 2px;
            background: #fff;
            transition: all 0.3s ease;
        }
        
        .toggle-btn span::before {
            transform: translateY(-5px);
        }
        
        .toggle-btn span::after {
            transform: translateY(5px);
        }
        
        .side-bar.collapsed {
            width: 80px;
        }
        
        .side-bar.collapsed .side-bar-header h2 {
            opacity: 0;
        }
        
        .side-bar.collapsed .side-bar-content ul li a {
            padding: 15px;
            justify-content: center;
        }
        
        .side-bar.collapsed .side-bar-content ul li a span {
            display: none;
        }
        
        .side-bar.collapsed .toggle-btn span {
            background: transparent;
        }
        
        .side-bar.collapsed .toggle-btn span::before {
            transform: rotate(45deg);
        }
        
        .side-bar.collapsed .toggle-btn span::after {
            transform: rotate(-45deg);
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .side-bar {
                width: 80px;
            }
            
            .side-bar.expanded {
                width: 280px;
            }
            
            .side-bar:not(.expanded) .side-bar-header h2 {
                opacity: 0;
            }
            
            .side-bar:not(.expanded) .side-bar-content ul li a {
                padding: 15px;
                justify-content: center;
            }
            
            .side-bar:not(.expanded) .side-bar-content ul li a span {
                display: none;
            }
        }
        /* Navbar styles (included for context) */
        .navbar {
            background-color: #fff;
            padding: 10px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #D80027;
        }
    </style>
</head>
<body>
<div class="navbar">
        <div class="logo">FATIMA HOME WORLD CENTER</div>
    </div>
    <div class="side-bar-container">
        <div class="side-bar">
            <div class="side-bar-header">
                <h2>Admin Panel</h2>
                <div class="toggle-btn">
                    <span></span>
                </div>
            </div>
            <div class="side-bar-content">
                <ul>
                    <li data-page="products"><a href="products.php"><i class="icon">📦</i> <span>Products</span></a></li>
                    <li data-page="orders"><a href="orders.php"><i class="icon">🛒</i> <span>Orders</span></a></li>
                    <li data-page="deliver_confirm"><a href="deliver_confirm.php"><i class="icon">👥</i> <span>Delivery Confirmation</span></a></li>
                    <li data-page="logout"><a href="./logout.php"><i class="icon">🚪</i> <span>Logout</span></a></li>
                </ul>
            </div>
        </div>
    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.toggle-btn');
            const sidebar = document.querySelector('.side-bar');
            const menuItems = document.querySelectorAll('.side-bar-content ul li');
            
            // Check if sidebar state is stored in localStorage
            const sidebarState = localStorage.getItem('sidebarCollapsed');
            if (sidebarState === 'true') {
                sidebar.classList.add('collapsed');
            }
            
            // Toggle sidebar and save state to localStorage
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
            
            // Get current page from URL
            const currentPage = window.location.pathname.split('/').pop().split('.')[0];
            
            // Set active menu item based on localStorage or current page
            const storedActivePage = localStorage.getItem('activeSidebarItem');
            
            // Check if we have a stored active page or use current page
            const activePageId = storedActivePage || currentPage || 'products';
            
            // Remove active class from all items first
            menuItems.forEach(item => item.classList.remove('active'));
            
            // Add active class to the correct item
            const activeItem = document.querySelector(`.side-bar-content ul li[data-page="${activePageId}"]`);
            if (activeItem) {
                activeItem.classList.add('active');
            } else {
                // Default to first item if no match
                menuItems[0].classList.add('active');
            }
            
            // Add click event to store active item
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Store the clicked item's data-page value
                    const pageId = this.getAttribute('data-page');
                    localStorage.setItem('activeSidebarItem', pageId);
                    
                    // Remove active class from all items
                    menuItems.forEach(i => i.classList.remove('active'));
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                });
            });
            
            // Add hover effects with JavaScript for smoother animations
            menuItems.forEach(item => {
                const link = item.querySelector('a');
                link.addEventListener('mouseenter', function() {
                    this.style.paddingLeft = sidebar.classList.contains('collapsed') ? '15px' : '40px';
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.paddingLeft = sidebar.classList.contains('collapsed') ? '15px' : '30px';
                });
            });
        });
    </script>
</body>
</html>