<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  file: null
});

const isUploading = ref(false);
const fileInput = ref(null);

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.file = file;
  }
};

const submit = () => {
  if (!form.file) return;

  isUploading.value = true;
  form.post('/contacts/import', {
    preserveScroll: true,
    onSuccess: () => {
      form.file = null;
      fileInput.value.value = '';
      isUploading.value = false;
    },
    onError: () => {
      isUploading.value = false;
    }
  });
};
</script>

<template>
  <div class="p-6 bg-white rounded-lg shadow">
    <form @submit.prevent="submit" class="space-y-4">
      <div class="flex items-center justify-center w-full">
        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer hover:bg-gray-50">
          <div class="flex flex-col items-center justify-center pt-5 pb-6">
            <svg class="w-8 h-8 mb-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <p class="mb-2 text-sm text-gray-500">
              <span class="font-semibold">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-gray-500">CSV files only</p>
          </div>
          <input
            ref="fileInput"
            type="file"
            class="hidden"
            accept=".csv"
            @change="handleFileChange"
          />
        </label>
      </div>

      <div v-if="form.file" class="flex items-center justify-between p-4 bg-gray-50 rounded">
        <span class="text-sm text-gray-500">{{ form.file.name }}</span>
        <button
          type="button"
          class="text-red-500 hover:text-red-700"
          @click="() => { form.file = null; fileInput.value = ''; }"
        >
          Remove
        </button>
      </div>

      <div v-if="form.errors.file" class="text-sm text-red-600">
        {{ form.errors.file }}
      </div>

      <button
        type="submit"
        :disabled="!form.file || isUploading"
        class="w-full px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 disabled:opacity-50"
      >
        <span v-if="isUploading">Processing...</span>
        <span v-else>Upload CSV</span>
      </button>
    </form>
  </div>
</template>
