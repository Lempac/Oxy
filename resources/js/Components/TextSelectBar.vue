<script setup lang="ts">
import {addIcons} from "oh-vue-icons";
import { BiChatText, RiChatVoiceLine, MdViewkanbanOutlined } from "oh-vue-icons/icons";
addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined);

</script>

<template>
  <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700" v-if="$page.props.auth.channels && $page.props.auth.channels.length > 0">
    <div class="navbar-center hidden lg:flex">
      <button v-for="channel in $page.props.auth.channels" :key="channel.id" class="menu menu-horizontal px-1">
        {{ channel.name }}
      </button>
    </div>
  </div>
</template>
