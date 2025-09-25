<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  file: null
});

const isUploading = ref(false);
const fileInput = ref(null);
const showNotification = ref(false);
const isSuccess = ref(false);
const notificationMessage = ref('');

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.file = file;
  }
};

const handleShowNotification = (success, message) => {
  showNotification.value = true;
  isSuccess.value = success;
  notificationMessage.value = message;

  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};

const submit = () => {
  if (!form.file) return;

  isUploading.value = true;
  form.post('/contacts/import', {
    preserveScroll: true,
    onSuccess: (response) => {
      form.file = null;
      if (fileInput.value) {
        fileInput.value.value = '';
      }
      isUploading.value = false;
      handleShowNotification(true, response?.props?.flash?.message || 'File uploaded successfully!');
    },
    onError: (errors) => {
      isUploading.value = false;
      handleShowNotification(false, errors.message || 'Error uploading file.');
    }
  });
};
</script>

<template>
  <div class="p-6 bg-white rounded-lg shadow">
    <div
      v-if="showNotification"
      :class="[
        'fixed top-4 right-4 p-4 rounded-lg shadow-lg transition-all duration-300 z-50',
        isSuccess ? 'bg-green-500' : 'bg-red-500'
      ]"
    >
      <div class="flex items-center">
        <svg
          v-if="isSuccess"
          class="w-5 h-5 text-white mr-2"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
        <svg
          v-else
          class="w-5 h-5 text-white mr-2"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
            clip-rule="evenodd"
          />
        </svg>
        <p class="text-white">
          {{ notificationMessage }}
        </p>
      </div>
    </div>

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
            <p class="text-xs text-gray-500">Only CSV files</p>
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
