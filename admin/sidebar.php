<!-- sidebar.php -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-indigo-700 text-white p-6 flex flex-col justify-between transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40 md:relative">
    <!-- Brand & Navigation -->
    <div>
        <div class="mb-8">
            <h1 class="text-2xl font-bold">Learning Portal</h1>
            <p class="text-indigo-200">Admin Dashboard</p>
        </div>
        <nav>
            <ul class="space-y-2">
                <li>
                    <a href="test.php" class="block py-2 px-4 rounded bg-indigo-600 hover:bg-indigo-500 transition duration-200">
                        Courses
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Logout Dropdown -->
    <div class="relative">
        <button onclick="toggleDropdown()" class="w-full flex items-center justify-between px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-500 focus:outline-none">
            <span class="font-semibold">Admin</span>
            <i class="fas fa-user-circle text-2xl"></i>
        </button>

        <!-- Dropdown menu positioned absolutely to the viewport -->
        <div id="dropdownMenu" class="hidden fixed md:absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
            <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
        </div>
    </div>
</aside>

<!-- Overlay for mobile view -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden md:hidden z-30" onclick="toggleSidebar()"></div>

<!-- Mobile menu button -->
<button onclick="toggleSidebar()" class="md:hidden fixed top-4 left-4 z-50 bg-indigo-700 p-2 rounded text-white">
    <i class="fas fa-bars text-xl"></i>
</button>

<script>
    // Toggle sidebar visibility for mobile
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");
        sidebar.classList.toggle("-translate-x-full");
        overlay.classList.toggle("hidden");
    }

    // Toggle dropdown menu visibility
    function toggleDropdown() {
        const dropdown = document.getElementById("dropdownMenu");
        dropdown.classList.toggle("hidden");
    }

    // Close dropdown if clicked outside
    window.onclick = function(event) {
        const dropdown = document.getElementById("dropdownMenu");
        const sidebarButton = document.querySelector(".relative button");
        if (!sidebarButton.contains(event.target) && !dropdown.classList.contains("hidden")) {
            dropdown.classList.add("hidden");
        }
    };
</script>
