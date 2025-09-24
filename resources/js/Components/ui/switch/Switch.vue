<script setup>
import { ref } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  defaultChecked: {
    type: Boolean,
    default: false
  },
  checked: {
    type: Boolean,
    default: undefined
  },
  disabled: {
    type: Boolean,
    default: false
  },
  class: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:checked'])

const isChecked = ref(props.checked !== undefined ? props.checked : props.defaultChecked)

function toggle() {
  if (props.disabled) return
  const newValue = !isChecked.value
  isChecked.value = newValue
  emit('update:checked', newValue)
}
</script>

<template>
  <button
    type="button"
    role="switch"
    :aria-checked="isChecked"
    :data-state="isChecked ? 'checked' : 'unchecked'"
    :disabled="disabled"
    @click="toggle"
    :class="cn(
      'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=unchecked]:bg-input',
      props.class
    )"
  >
    <span
      :class="cn(
        'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0'
      )"
      :data-state="isChecked ? 'checked' : 'unchecked'"
    />
  </button>
</template>
