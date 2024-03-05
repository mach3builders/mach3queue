<script setup>
defineProps({
  headers: Array,
})
</script>

<template>
<thead>
  <tr>
    <th
        v-for="(header, index) in headers"
        scope="col"
        :class="{
          'text-left': index !== headers.length - 1,
          'text-right': index === headers.length - 1,
          'pr-3': index === 0,
          'px-3': index !== 0,
        }"
        class="py-3.5 text-sm font-semibold text-gray-900">
        {{header}}
    </th>
  </tr>
</thead>
</template>

<style scoped>

</style>