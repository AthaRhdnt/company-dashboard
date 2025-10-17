<header class="bg-grey text-black p-4 flex justify-end items-center rounded-2xl mb-6">
    <div class="relative group">
        <button class="flex items-center space-x-2 focus:outline-none">
            <span class="font-medium">Hello User</span>
            <div class="relative w-8 h-8 rounded-full bg-white flex items-center justify-center text-purple-dark">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.93 0 3.5 1.57 3.5 3.5S13.93 12 12 12 8.5 10.43 8.5 8.5 10.07 5 12 5zm0 14.2c-2.67 0-5.69-1.57-8-3.62.05-1.12 2.12-3.18 8-3.18 5.88 0 7.95 2.06 8 3.18-2.31 2.05-5.33 3.62-8 3.62z"/>
                </svg>
            </div>
        </button>
        <div id="dropdownMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden z-10">
            <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                <div class="flex items-center space-x-2">
                    <div class="relative w-8 h-8 rounded-full bg-purple-light flex items-center justify-center text-purple-dark">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.93 0 3.5 1.57 3.5 3.5S13.93 12 12 12 8.5 10.43 8.5 8.5 10.07 5 12 5zm0 14.2c-2.67 0-5.69-1.57-8-3.62.05-1.12 2.12-3.18 8-3.18 5.88 0 7.95 2.06 8 3.18-2.31 2.05-5.33 3.62-8 3.62z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold">Username</div>
                        <div class="text-sm text-gray-500">User Role</div>
                    </div>
                </div>
            </a>
            <hr class="my-2">
            <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Profile Settings</a>
            <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Change Password</a>
            <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Log Out</a>
        </div>
    </div>
</header>