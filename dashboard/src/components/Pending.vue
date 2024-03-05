<script setup>
import ErrorMessage from "./ErrorMessage.vue";
import ListBody from "./ListBody.vue";
import ListHeader from "./ListHeader.vue";
import List from "./List.vue";
import ListRow from "./ListRow.vue";
import { data } from './fetch.js';

const jobs = data('pending');
</script>

<template>

  <list v-if="jobs.length">
    <list-header :headers="['Job', 'Queued']"></list-header>
    <list-body>
      <list-row
          v-for="job in jobs"
          :queue="job.queue"
          :tags="job.tags"
          :items="[job.name, job.queued]">
      </list-row>
    </list-body>
  </list>

  <error-message v-if="!jobs.length">
    No pending jobs are found
  </error-message>

</template>

<style scoped>

</style>