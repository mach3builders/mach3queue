<script setup>
defineProps({
  active: Boolean,
})
</script>

<template>
<div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
  <dt class="truncate text-sm font-medium text-gray-500">Status</dt>
  <dd v-if="active === true" class="mt-1 text-3xl font-semibold tracking-tight text-green-700">Active</dd>
  <dd v-if="active === false" class="mt-1 text-3xl font-semibold tracking-tight text-red-700">Inactive</dd>
</div>
</template>