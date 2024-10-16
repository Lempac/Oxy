<script setup lang="ts">
import {Link} from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

</script>

<template>
    <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <div class="navbar-start">
            <Link :href="route('home')">
                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"/>
            </Link>
        </div>
        <div v-for="server in $page.props.servers" :key="server.id" class="navbar-center">
            <div class="hidden space-x-5 sm:-my-px sm:m-3 sm:flex">
                <Link :href="route('home.server', server.id)">
                    <div class="tooltip tooltip-bottom" :data-tip="server.name">
                        <div class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 rounded-full">
                                <img alt="Server"
                                     src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s"/>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
        <div class="navbar-end gap-2">
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="flex items-center btn btn-ghost">
                    <div class="mr-2">{{ $page.props.auth.user.name }}</div>
                    <div class="avatar">
                        <div class="w-10 rounded-full">
                            <img
                                alt="User Avatar"
                                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s"/>
                        </div>
                    </div>
                </div>
                <ul
                    tabindex="0"
                    class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                    <li>
                        <Link :href="route('profile.edit')">Profile</Link>
                    </li>
                    <li>
                        <Link :href="route('logout')" method="post">Log Out</Link>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
