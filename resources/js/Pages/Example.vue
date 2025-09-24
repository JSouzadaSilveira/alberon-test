<script setup>
import { ref } from 'vue'
import Switch from '@/Components/ui/switch/Switch.vue'

const enabled = ref(false)
</script>

<template>
  <div class="p-6">
    <div class="flex items-center space-x-2">
      <Switch v-model:checked="enabled" />
      <label>{{ enabled ? 'Ligado' : 'Desligado' }}</label>
    </div>
  </div>
</template>
