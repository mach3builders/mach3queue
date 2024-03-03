<script setup>
import axios from 'axios';
import { ref, onMounted } from 'vue';

const data = ref({
  completedJobs: 0,
  failedJobs: 0,
  pendingJobs: 0,
  active: false,
  supervisors: [],
});

onMounted(async () => {
  const response = await axios.get('?data=dashboard');
  data.value = response.data ? response.data : data.value;
});

</script>

<template>
  <div>
    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">Completed jobs in last 24 hours</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{data.completedJobs}}</dd>
      </div>
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">Failed jobs in the last 7 days</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{data.failedJobs}}</dd>
      </div>
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">Pending jobs</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{data.pendingJobs}}</dd>
      </div>
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">Status</dt>
        <dd v-if="data.active === true" class="mt-1 text-3xl font-semibold tracking-tight text-green-700">Active</dd>
        <dd v-if="data.active === false" class="mt-1 text-3xl font-semibold tracking-tight text-red-700">Inactive</dd>
      </div>
    </dl>
  </div>

  <div class="px-4 sm:px-6 lg:px-8" v-if="data.supervisors.length">
    <div class="mt-8 flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8 sm:rounded-lg bg-white shadow">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <table class="min-w-full divide-y divide-gray-300">

            <thead>
              <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Supervisor</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Queue</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Processes</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Workload</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

              <tr v-for="supervisor in data.supervisors">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{supervisor.options.name}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{supervisor.queue}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{supervisor.processes}}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{supervisor.workload}}</td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>