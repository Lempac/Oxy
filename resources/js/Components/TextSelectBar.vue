<script setup lang="ts">
import {addIcons} from "oh-vue-icons";
import { BiChatText, RiChatVoiceLine, MdViewkanbanOutlined } from "oh-vue-icons/icons";
addIcons(BiChatText, RiChatVoiceLine, MdViewkanbanOutlined);

</script>

<template>
  <div class="navbar bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-around" v-if="$page.props.channels && $page.props.channels.length > 0">
      <button v-for="channel in $page.props.channels" :key="channel.id" class="btn btn-wide">
        {{ channel.name }}
      </button>
  </div>
</template>
