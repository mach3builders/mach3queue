<script setup>
import { data } from './fetch.js';
import { List, ListBody, ListRow, ListHeader } from './list/ListComponents.js';
import MetricCard from "./MetricCard.vue";
import MetricCardStatus from "./MetricCardStatus.vue";

const dashboard = data('dashboard');
</script>

<template>
  <div>
    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-4">
      <metric-card :data="dashboard.pendingJobs" title="Pending jobs"/>
      <metric-card :data="dashboard.completedJobs" title="Completed jobs in last 24 hours"/>
      <metric-card :data="dashboard.failedJobs" title="Failed jobs in the last 7 days"/>
      <metric-card-status :active="dashboard.active"/>
    </dl>
  </div>

  <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <list v-if="dashboard.queues && dashboard.queues.length">
      <list-header :headers="['Queue', 'Jobs']"></list-header>
      <list-body>
        <list-row
            v-for="queue in dashboard.queues"
            :items="[queue.name, queue.count]">
        </list-row>
      </list-body>
    </list>

    <list v-if="dashboard.supervisors && dashboard.supervisors.length">
      <list-header :headers="['Supervisor', 'Queue', 'Processes']"></list-header>
      <list-body>
        <list-row
            v-for="supervisor in dashboard.supervisors"
            :items="[supervisor.options.name, supervisor.queues, supervisor.processes]">
        </list-row>
      </list-body>
    </list>
  </div>
</template>