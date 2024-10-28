<script setup lang="ts">
import {defaultIcon} from "@/bootstrap";

const baseUrl = window.location.origin;

</script>

<template>
    <div class="btm-nav h-16">
        <div class="stats shadow h-auto">
            <div class="stat py-0.5">
                <div class="avatar">
                    <div class="w-10 rounded-full">
                        <img :src="$page.props.auth.user.icon ? `${baseUrl}${$page.props.auth.user.icon}` : defaultIcon" />
                    </div>
                </div>
                <div class="flex items-center justify-center">user</div>
            </div>

            <div class="stat py-0.5">
                <div class="avatar">
                    <div class="w-10 rounded-full">
                        <img :src="$page.props.auth.user.icon ? `${baseUrl}${$page.props.auth.user.icon}` : defaultIcon" />
                    </div>
                </div>
                <div class="flex items-center justify-center">user</div>
            </div>

            <div class="stat py-0.5">
                <div class="avatar">
                    <div class="w-10 rounded-full">
                        <img :src="$page.props.auth.user.icon ? `${baseUrl}${$page.props.auth.user.icon}` : defaultIcon" />
                    </div>
                </div>
                <div class="flex items-center justify-center">user</div>
            </div>
        </div>
    </div>
</template>
