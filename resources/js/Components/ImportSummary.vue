<script setup>
defineProps({
  totalRows: {
    type: Number,
    default: 0
  },
  importedRows: {
    type: Number,
    default: 0
  },
  duplicateRows: {
    type: Number,
    default: 0
  },
  invalidRows: {
    type: Number,
    default: 0
  }
});
</script>

<template>
  <div class="bg-white p-6 rounded-lg shadow mb-6">
    <h2 class="text-lg font-semibold mb-4">Import Summary</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="p-4 bg-gray-50 rounded">
        <div class="text-2xl font-bold text-gray-900">{{ totalRows }}</div>
        <div class="text-sm text-gray-500">Total Rows</div>
      </div>
      <div class="p-4 bg-green-50 rounded">
        <div class="text-2xl font-bold text-green-600">{{ importedRows }}</div>
        <div class="text-sm text-gray-500">Successfully Imported</div>
      </div>
      <div class="p-4 bg-yellow-50 rounded">
        <div class="text-2xl font-bold text-yellow-600">{{ duplicateRows }}</div>
        <div class="text-sm text-gray-500">Duplicates</div>
      </div>
      <div class="p-4 bg-red-50 rounded">
        <div class="text-2xl font-bold text-red-600">{{ invalidRows }}</div>
        <div class="text-sm text-gray-500">Invalid Rows</div>
      </div>
    </div>
  </div>
</template>
