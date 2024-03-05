<script setup>
import { data } from './fetch.js';
import ListBody from "./ListBody.vue";
import ListHeader from "./ListHeader.vue";
import List from "./List.vue";
import ListRow from "./ListRow.vue";
import ErrorMessage from "./ErrorMessage.vue";

const jobs = data('completed');
</script>

<template>

  <list v-if="jobs.length">
    <list-header :headers="['Job', 'Queued', 'Completed', 'Runtime']"></list-header>
    <list-body>
      <list-row
          v-for="job in jobs"
          :queue="job.queue"
          :tags="job.tags"
          :items="[job.name, job.queued, job.completed_at, 0.09]">
      </list-row>
    </list-body>
  </list>

  <error-message v-if="!jobs.length">
    No completed jobs are found
  </error-message>

</template>