<script setup>
import Message from "./Message.vue";
import { List, ListBody, ListRow, ListHeader } from './list/ListComponents.js';
import { data } from './fetch.js';

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
        :items="[job.name, job.added_dt, job.buried_dt, job.runtime+'s']">
      </list-row>
    </list-body>
  </list>

  <message v-if="!jobs.length">
    No failed jobs are found
  </message>
</template>