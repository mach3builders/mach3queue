<script setup>
import { data } from './fetch.js';
import { List, ListBody, ListRow, ListHeader } from './list/ListComponents.js';
import Message from "./Message.vue";

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
        :items="[job.name, job.added_dt]">
      </list-row>
    </list-body>
  </list>

  <message v-if="!jobs.length">
    No pending jobs are found
  </message>

</template>