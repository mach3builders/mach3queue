<script setup>
const props = defineProps(['menu']);

const disableEveryMenuItem = () => {
  for (let i = 0; i < props.menu.length; i++) {
    props.menu[i].current = false;
  }
}

const clickHandler = (index) => {
  disableEveryMenuItem();

  props.menu[index].current = true;
}

</script>

<template>
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
  <div class="flex h-16 items-center justify-between">
    <div class="flex items-center">

<!--      <div class="flex-shrink-0">-->
<!--        <img class="h-8 w-8" src="https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=500" alt="Your Company">-->
<!--      </div>-->

      <div class="hidden md:block">
        <div class="flex items-baseline space-x-4">
          <a  v-for="(item, index) in props.menu"
              href="#"
              @click="clickHandler(index)"
              :class="{'bg-gray-900': item.current}"
              class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">
              {{ item.name }}
          </a>
        </div>
      </div>

    </div>
  </div>
</div>
</template>