<script setup>
import { data } from './fetch.js';
import ListBody from "./ListBody.vue";
import ListHeader from "./ListHeader.vue";
import List from "./List.vue";
import ListRow from "./ListRow.vue";
import ErrorMessage from "./ErrorMessage.vue";

const jobs = data('failed');
</script>

<template>

  <list v-if="jobs.length">
    <list-header :headers="['Job', 'Queued', 'Failed', 'Runtime']"></list-header>
    <list-body>
      <list-row
          v-for="job in jobs"
          :queue="job.queue"
          :tags="job.tags"
          :items="[job.name, job.queued, job.buried_dt, 0.05]">
      </list-row>
    </list-body>
  </list>

  <error-message v-if="!jobs.length">
    No failed jobs are found
  </error-message>
</template>