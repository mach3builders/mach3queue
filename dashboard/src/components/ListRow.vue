<script setup>
import Tag from "./Tag.vue";

defineProps({
  items: Array,
  tags: Array,
  queue: String,
})
</script>

<template>
<tr>
  <td
      v-for="(item, index) in items"
      :class="{
        'text-left': index !== items.length - 1,
        'text-right': index === items.length - 1,
        'px-3': index !== 0,
        'pr-3': index === 0,
        'font-medium': index === 0,
        'text-gray-900': index === 0,
        'text-gray-500': index !== 0,
      }"
      class="whitespace-nowrap py-4 text-sm">
      {{item}}
      <div class="pt-1" v-if="index === 0">
        <tag v-if="queue">Queue: {{queue}}</tag>
        <tag v-for="tag in tags">{{tag.name}}:{{tag.value}}</tag>
      </div>
  </td>
</tr>
</template>

<style scoped>

</style>