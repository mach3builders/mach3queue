<script setup>
import { ref } from 'vue'

import Completed from './components/Completed.vue'
import Dashboard from './components/Dashboard.vue'
import Navigation from './components/Navigation.vue'
import Pending from './components/Pending.vue'
import Failed from './components/Failed.vue'

const menu = ref([
  { name: 'Dashboard', current: true },
  { name: 'Pending jobs', current: false },
  { name: 'Completed jobs', current: false },
  { name: 'Failed jobs', current: false },
]);
</script>

<template>
  <div class="min-h-full">

    <nav class="bg-gray-800">
      <navigation :menu="menu" />
    </nav>

    <main>
      <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
        <dashboard v-if="menu[0].current"/>
        <pending v-if="menu[1].current"/>
        <completed v-if="menu[2].current"/>
        <failed v-if="menu[3].current"/>
      </div>
    </main>

  </div>

</template>