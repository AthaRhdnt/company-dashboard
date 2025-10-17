<!-- START: layouts.sidebar.blade.php -->
<aside id="sidebar"
    class="bg-gray-900 text-gray-300 shadow-2xl transition-all duration-300 ease-in-out z-50 flex flex-col h-full overflow-y-auto"
    :class="sidebarOpen ? 'w-64' : 'w-20'">

    <!-- Top Section: Logo and Toggle Button -->
    <div class="flex flex-col flex-grow p-4">
        <div class="flex items-center mb-8" :class="sidebarOpen ? 'justify-between' : 'justify-center'">

            <!-- Logo/Title -->
            <h1 class="text-3xl font-black text-indigo-400 tracking-wider transition-opacity duration-300 origin-left"
                :class="!sidebarOpen && 'scale-x-0 opacity-0 h-0 w-0'">
                Placeholder
            </h1>

            <!-- Toggle Button (always visible) -->
            <button @click="sidebarOpen = !sidebarOpen"
                class="p-2 rounded-full text-gray-400 hover:bg-gray-800 hover:text-white focus:outline-none transition-colors duration-200"
                :class="!sidebarOpen && 'mx-auto'">
                <!-- Arrow Icon for Toggle -->
                <svg class="h-6 w-6 transform transition-transform duration-300"
                    :class="sidebarOpen ? 'rotate-0' : 'rotate-180'" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 15l-3-3m0 0l3-3m-3 3h8m-9 1a9 9 0 110-18 9 9 0 010 18z" />
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1 flex-grow">

            <!-- Link: Dashboard -->
            <!-- REMOVED @click.prevent to allow navigation -->
            <a href="{{ route('dashboard') }}" @click="activePage = 'dashboard'; activeDropdown = 'none'"
                class="flex items-center w-full text-left rounded-xl font-medium transition-all duration-200 group"
                :class="sidebarOpen ? 'px-4 py-3' : 'justify-center py-3 px-1'"
                :class="{
                    'bg-indigo-600 text-white shadow-lg': activePage === 'dashboard',
                    'text-gray-300 hover:bg-gray-800 hover:text-indigo-400': activePage !== 'dashboard'
                }">
                <!-- Icon: Home -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" :class="sidebarOpen ? 'mr-3' : 'mr-0'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="flex-1 whitespace-nowrap transition-opacity duration-300"
                    :class="!sidebarOpen && 'hidden'">Dashboard</span>
            </a>

            <!-- Link: Orders (Not dropdown in this combined version) -->
            <!-- REMOVED @click.prevent to allow navigation -->
            <a href="{{ route('orders.index') }}" @click="activePage = 'orders'; activeDropdown = 'none'"
                class="flex items-center w-full text-left rounded-xl font-medium transition-all duration-200 group"
                :class="sidebarOpen ? 'px-4 py-3' : 'justify-center py-3 px-1'"
                :class="{
                    'bg-indigo-600 text-white shadow-lg': activePage === 'orders',
                    'text-gray-300 hover:bg-gray-800 hover:text-indigo-400': activePage !== 'orders'
                }">
                <!-- Icon: Document/Order -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" :class="sidebarOpen ? 'mr-3' : 'mr-0'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="flex-1 whitespace-nowrap transition-opacity duration-300"
                    :class="!sidebarOpen && 'hidden'">Orders</span>
            </a>

            <!-- Dropdown: Invoices -->
            <div x-data="{ dropdownId: 'invoices' }">
                <button
                    @click="activeDropdown = activeDropdown === dropdownId ? 'none' : dropdownId; activePage = activeDropdown === dropdownId ? activePage : 'none'"
                    class="flex items-center w-full text-left rounded-xl font-medium transition-all duration-200"
                    :class="sidebarOpen ? 'px-4 py-3' : 'justify-center py-3 px-1'"
                    :class="{
                        'bg-gray-800 text-indigo-400': activeDropdown === dropdownId,
                        'text-gray-300 hover:bg-gray-800 hover:text-indigo-400': activeDropdown !== dropdownId
                    }">
                    <!-- Icon: Invoice -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" :class="sidebarOpen ? 'mr-3' : 'mr-0'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="flex-1 whitespace-nowrap transition-opacity duration-300"
                        :class="!sidebarOpen && 'hidden'">Invoices</span>
                    <!-- Arrow Icon -->
                    <svg class="h-5 w-5 text-gray-400 transform transition-transform duration-200 flex-shrink-0"
                        :class="[activeDropdown === dropdownId && 'rotate-90', !sidebarOpen && 'hidden']"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10l-3.293-3.293a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <ul x-show="activeDropdown === dropdownId" x-collapse class="space-y-1 overflow-hidden"
                    :class="sidebarOpen ? 'pl-10 py-1' : 'pl-0'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-screen"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 max-h-screen" x-transition:leave-end="opacity-0 max-h-0">
                    <li><a href="{{ route('outgoing-invoices.index') }}" @click="activePage = 'outgoingInvoices'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'outgoingInvoices' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Outgoing</a></li>
                    <li><a href="{{ route('incoming-invoices.index') }}" @click="activePage = 'incomingInvoices'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'incomingInvoices' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Incoming</a></li>
                </ul>
            </div>

            <!-- Dropdown: Master Data -->
            <div x-data="{ dropdownId: 'masterData' }">
                <!-- Dropdown toggle button still uses @click (without .prevent) to manage the local UI state -->
                <button
                    @click="activeDropdown = activeDropdown === dropdownId ? 'none' : dropdownId; activePage = activeDropdown === dropdownId ? activePage : 'none'"
                    class="flex items-center w-full text-left rounded-xl font-medium transition-all duration-200"
                    :class="sidebarOpen ? 'px-4 py-3' : 'justify-center py-3 px-1'"
                    :class="{
                        'bg-gray-800 text-indigo-400': activeDropdown === dropdownId,
                        'text-gray-300 hover:bg-gray-800 hover:text-indigo-400': activeDropdown !== dropdownId
                    }">
                    <!-- Icon: Database -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" :class="sidebarOpen ? 'mr-3' : 'mr-0'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2h14z" />
                    </svg>
                    <span class="flex-1 whitespace-nowrap transition-opacity duration-300"
                        :class="!sidebarOpen && 'hidden'">Master Data</span>
                    <!-- Arrow Icon -->
                    <svg class="h-5 w-5 text-gray-400 transform transition-transform duration-200 flex-shrink-0"
                        :class="[activeDropdown === dropdownId && 'rotate-90', !sidebarOpen && 'hidden']"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10l-3.293-3.293a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <ul x-show="activeDropdown === dropdownId" x-collapse class="space-y-1 overflow-hidden"
                    :class="sidebarOpen ? 'pl-10 py-1' : 'pl-0'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-screen"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 max-h-screen" x-transition:leave-end="opacity-0 max-h-0">
                    <li><a href="{{ route('clients.index') }}" @click="activePage = 'clients'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'clients' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Clients</a></li>
                    <li><a href="{{ route('vendors.index') }}" @click="activePage = 'vendors'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'vendors' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Vendors</a></li>
                    <li><a href="{{ route('departments.index') }}" @click="activePage = 'departments'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'departments' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Departments</a></li>
                    <li><a href="{{ route('taxes.index') }}" @click="activePage = 'taxes'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'taxes' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Taxes</a></li>
                    <li><a href="{{ route('items.index') }}" @click="activePage = 'items'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'items' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Items</a></li>
                    <li><a href="{{ route('item-specs.index') }}" @click="activePage = 'itemSpecs'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'itemSpecs' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Item Specs</a></li>
                </ul>
            </div>

            <!-- Dropdown: Management -->
            <div x-data="{ dropdownId: 'management' }">
                <button
                    @click="activeDropdown = activeDropdown === dropdownId ? 'none' : dropdownId; activePage = activeDropdown === dropdownId ? activePage : 'none'"
                    class="flex items-center w-full text-left rounded-xl font-medium transition-all duration-200"
                    :class="sidebarOpen ? 'px-4 py-3' : 'justify-center py-3 px-1'"
                    :class="{
                        'bg-gray-800 text-indigo-400': activeDropdown === dropdownId,
                        'text-gray-300 hover:bg-gray-800 hover:text-indigo-400': activeDropdown !== dropdownId
                    }">
                    <!-- Icon: Users -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" :class="sidebarOpen ? 'mr-3' : 'mr-0'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="flex-1 whitespace-nowrap transition-opacity duration-300"
                        :class="!sidebarOpen && 'hidden'">Management</span>
                    <!-- Arrow Icon -->
                    <svg class="h-5 w-5 text-gray-400 transform transition-transform duration-200 flex-shrink-0"
                        :class="[activeDropdown === dropdownId && 'rotate-90', !sidebarOpen && 'hidden']"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10l-3.293-3.293a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <ul x-show="activeDropdown === dropdownId" x-collapse class="space-y-1 overflow-hidden"
                    :class="sidebarOpen ? 'pl-10 py-1' : 'pl-0'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-screen"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 max-h-screen" x-transition:leave-end="opacity-0 max-h-0">
                    <li><a href="{{ route('users.index') }}" @click="activePage = 'users'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'users' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Users</a></li>
                    <li><a href="{{ route('permissions.index') }}" @click="activePage = 'permissions'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'permissions' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Permissions</a></li>
                    <li><a href="{{ route('levels.index') }}" @click="activePage = 'levels'"
                            class="block p-2 text-sm rounded-lg transition-colors duration-150"
                            :class="activePage === 'levels' ? 'text-indigo-400 font-semibold' :
                                'text-gray-400 hover:bg-gray-700 hover:text-white'"
                            :class="!sidebarOpen && 'hidden'">Levels</a></li>
                </ul>
            </div>

        </nav>

        <!-- Spacer to push the profile to the bottom -->
        <div class="flex-grow"></div>

        <!-- Bottom Section: User Profile/Settings -->
        <div class="mt-8 pt-4 border-t border-gray-700" :class="sidebarOpen ? 'px-0' : 'mx-auto'">
            <!-- REMOVED @click.prevent to allow navigation to a profile page (assuming '#' is placeholder for route) -->
            <a href="#" @click="activePage = 'profile'; activeDropdown = 'none'"
                class="flex items-center w-full text-left rounded-xl font-medium transition-all duration-200 group"
                :class="sidebarOpen ? 'px-4 py-3' : 'justify-center py-3 px-1'"
                :class="{
                    'bg-indigo-600 text-white shadow-lg': activePage === 'profile',
                    'text-gray-300 hover:bg-gray-800 hover:text-indigo-400': activePage !== 'profile'
                }">
                <!-- User Image -->
                <img class="h-8 w-8 rounded-full object-cover bg-gray-600"
                    src="https://placehold.co/32x32/1f2937/ffffff?text=U" alt="User Profile"
                    :class="sidebarOpen ? 'mr-3' : 'mr-0'">
                <!-- User Info -->
                <div class="overflow-hidden transition-opacity duration-300" :class="!sidebarOpen && 'hidden'">
                    <p class="text-sm font-semibold text-white truncate">Jane Doe</p>
                    <p class="text-xs text-gray-400 truncate">Admin</p>
                </div>
            </a>
        </div>
    </div>

</aside>
