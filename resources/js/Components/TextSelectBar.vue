<script setup lang="ts">
import {addIcons} from "oh-vue-icons";
import { BiChatText, RiChatVoiceLine, MdViewkanbanOutlined } from "oh-vue-icons/icons";
addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined);

</script>

<template>
    <div class="navbar bg-base-100">
        <div class="navbar-center hidden lg:flex">
            <button v-for="channel in $page.props.auth.channels" class="menu menu-horizontal px-1">
                {{channel.name}}
            </button>
        </div>
    </div>
</template>
