<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        :root {
            --primary-color: #673AB7; 
            --primary-hover: #512DA8;
            --primary-light: #D1C4E9;
            --secondary-color: #FFC107; 
            --background-light: #fafafa;
            --background-dark: #212121;
            --text-light: #212121;
            --text-dark: #f5f5f5;
            --sidebar-width: 260px;
            --border-radius: 12px; 
            --box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); 
            --transition-speed: 0.3s;
            --header-height: 60px;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-light);
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: background-color var(--transition-speed) ease;
        }

        body.dark-mode {
            background-color: var(--background-dark);
            color: var(--text-dark);
        }

        .container {
            display: flex;
            flex: 1;
            flex-direction: row;
           
        }

        .sidebar {
            background-color: var(--primary-color);
            color: #fff;
            width: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            box-shadow: var(--box-shadow);
            transition: width var(--transition-speed) ease;
            position: sticky; 
            top: 0;           
            height: 100vh;   
            z-index: 10; 
            overflow-y: auto; 
        }


        .sidebar-collapsed {
            width: 72px; 
        }

        .sidebar-collapsed .sidebar-header h1,
        .sidebar-collapsed .nav-links li span,
        .sidebar-collapsed .dark-mode-toggle-wrapper span
         {
            display: none;
        }

        .sidebar-collapsed .nav-links li a {
            justify-content: center;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between; 
            padding: 1rem;  
            margin-bottom: 1.5rem;

        }

        .sidebar-header h1 {
            margin: 0;
            font-size: 1.6rem; 
            font-weight: 600;
            white-space: nowrap;
        }
        .sidebar-header h1 span {
             color: var(--secondary-color);
        }

        .sidebar-toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            padding: 0.25rem; 
            transition: color var(--transition-speed) ease;

        }

        .sidebar-toggle-btn:hover,
        .sidebar-toggle-btn:focus {
            color: var(--primary-light);
        }

        .nav-links {
            list-style-type: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .nav-links li {
            margin-bottom: 0.25rem; 
        }

        .nav-links li a {
            text-decoration: none;
            color: #fff;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            border-radius: var(--border-radius);
            transition: background-color var(--transition-speed) ease, color var(--transition-speed) ease;
        }

        .nav-links li a i {
            margin-right: 1rem;
            font-size: 1.4rem; 
            width: 24px; 
            text-align: center;
        }

        .nav-links li a:hover {
            background-color: var(--primary-hover);
        }

        .dark-mode-toggle-wrapper {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;

        }

        #dark-mode-toggle {
            background-color: transparent;
            color: #fff;
            border: 2px solid var(--primary-light); 
            padding: 0.6rem 0.8rem; 
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: background-color var(--transition-speed) ease, border-color var(--transition-speed) ease;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }


        #dark-mode-toggle i {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

        #dark-mode-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1); 
            border-color: #fff;
        }


        .main-content {
            flex-grow: 1;
            padding: 1.5rem;
            background-color: var(--background-light);
            display: flex;
            flex-direction: column;
            transition: background-color var(--transition-speed) ease;
            margin-top: var(--header-height); 
        }


        body.dark-mode .main-content {
            background-color: var(--background-dark);
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;  
            width: 100%; 
            background-color: white; 
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1.5rem; 
            height: var(--header-height); 
            box-shadow: var(--box-shadow);
            z-index: 5; 
        }
         body.dark-mode header {
              background-color: var(--background-dark); 
          }
         body.dark-mode .admin-profile span{
              color: var(--text-dark);
          }

        .dashboard-title {
            font-size: 2rem; 
            font-weight: 700;
            margin: 0;
             white-space: nowrap;  
        }

        .admin-profile {
            display: flex;
            align-items: center;
            background-color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            white-space: nowrap;

        }


        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 0.75rem;
            object-fit: cover;
        }

        .admin-profile span {
            font-weight: 500;
            color: var(--text-light); 
        }

        .content {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            flex-grow: 1;
            overflow-y: auto;  

        }

        body.dark-mode .content {
            background-color: #333;
        }

       
       @media (max-width: 800px) {
    .container {
        flex-direction: column; 
    }

    .sidebar {
        position: static; 
        width: 100%; 
        height: auto; 
         overflow-y: visible; 
        margin-bottom: 1rem;
        box-shadow: none;
    }
    .sidebar-header{
                flex-direction: row-reverse;
            }

    .sidebar-toggle-btn {
        display: block; 
    }

    .nav-links {
         display: none; 
         margin-top: 0.5rem;
         padding: 0 1rem;
     }
      .nav-links.open {
         display: block; 
     }

    .nav-links li {
        margin-bottom: 0.1rem;
    }

    .nav-links li a {
        padding: 0.7rem 0.5rem;
    }

    .nav-links li a i{
        font-size: 1.2rem;
        margin-right: 0.5rem;
    }

    .sidebar.sidebar-collapsed .nav-links {
            display: none;
        }
    .main-content {
        padding: 1rem;
        margin-top: 0; 
    }

    header {
        position: static; 
         padding: 1rem;  

    }
    .admin-profile {
        padding: 0.4rem 0.8rem; 
    }
    
     .sidebar.sidebar-collapsed {
         width: 100%; 
     }

}


    </style>
</head>

<body>
    <div class="container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1>Admin <span>Panel</span></h1>
                 <button class="sidebar-toggle-btn" id="sidebar-toggle-btn" aria-label="Toggle Sidebar">
                    <span class="material-icons">menu</span>
                </button>
            </div>
            <ul class="nav-links" id="nav-links">
                <li><a href="dashboard.php"><i class="material-icons">dashboard</i> <span>Dashboard</span></a></li>
                <li><a href="add_product.php"><i class="material-icons">add_box</i> <span>Add Product</span></a></li>
                <li><a href="manage_records.php"><i class="material-icons">list</i> <span>Manage Products</span></a></li>
                <li><a href="add_category.php"><i class="material-icons">category</i> <span>Categories</span></a></li>
                <li><a href="orders.php"><i class="material-icons">shopping_cart</i> <span>Orders</span></a></li>
                <li><a href="users.php"><i class="material-icons">people</i> <span>Users</span></a></li>
                <li><a href="logout.php"><i class="material-icons">logout</i> <span>Logout</span></a></li>
            </ul>

            <div class="dark-mode-toggle-wrapper">
                 <button id="dark-mode-toggle"><i class="material-icons">dark_mode</i><span>Dark Mode</span></button>
            </div>
        </aside>

        <section class="main-content">
            <header>
                <h2 class="dashboard-title">Dashboard</h2>
                <div class="admin-profile">

                    <span>Admin</span>
                </div>
            </header>
            <div class="content">
                <!-- Page content goes here -->
          

    <script>
     document.addEventListener('DOMContentLoaded', function () {
         const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
         const sidebar = document.getElementById('sidebar');
         const navLinks = document.getElementById('nav-links');
         const darkModeToggle = document.getElementById('dark-mode-toggle');

      
         sidebarToggleBtn.addEventListener('click', function () {
             sidebar.classList.toggle('sidebar-collapsed');
             navLinks.classList.toggle('open');  // For mobile responsiveness
          });

        
          if(darkModeToggle){
              darkModeToggle.addEventListener('click', function () {
                document.body.classList.toggle('dark-mode');
                
                if (document.body.classList.contains('dark-mode')) {
                    localStorage.setItem('darkMode', 'enabled');
                    darkModeToggle.innerHTML = '<i class="material-icons">light_mode</i><span>Light Mode</span>';

                } else {
                    localStorage.setItem('darkMode', 'disabled');
                     darkModeToggle.innerHTML = '<i class="material-icons">dark_mode</i><span>Dark Mode</span>';
                }
            });
          }


       
         const savedDarkMode = localStorage.getItem('darkMode');
         if (savedDarkMode === 'enabled') {
             document.body.classList.add('dark-mode');
              if(darkModeToggle){
                  darkModeToggle.innerHTML = '<i class="material-icons">light_mode</i><span>Light Mode</span>';
              }
         }else{
              if(darkModeToggle){
                  darkModeToggle.innerHTML = '<i class="material-icons">dark_mode</i><span>Dark Mode</span>';
              }
         }
     });
    </script>
