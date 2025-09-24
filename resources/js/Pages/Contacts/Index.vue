<script setup>
import { Head } from '@inertiajs/vue3';
import CsvUpload from '@/Components/CsvUpload.vue';
import ImportSummary from '@/Components/ImportSummary.vue';

defineProps({
  contacts: {
    type: Object,
    required: true
  },
  importSummary: {
    type: Object,
    required: true
  }
});
</script>

<template>
  <Head title="Contacts" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="mb-8">
        <CsvUpload />
      </div>

      <ImportSummary v-bind="importSummary" />

      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="contact in contacts.data" :key="contact.id" class="bg-white p-6 rounded-lg shadow">
              <div class="flex items-center space-x-4">
                <img :src="contact.gravatar_url" :alt="contact.name" class="w-16 h-16 rounded-full">
                <div>
                  <h3 class="text-lg font-semibold">{{ contact.name }}</h3>
                  <p class="text-gray-600">{{ contact.email }}</p>
                  <p class="text-gray-600">{{ contact.phone }}</p>
                  <p class="text-gray-600">{{ contact.birthdate }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="contacts.links" class="mt-6">
            <div class="flex justify-between">
              <Link
                v-for="link in contacts.links"
                :key="link.label"
                :href="link.url"
                v-html="link.label"
                class="px-4 py-2 border rounded"
                :class="{
                  'bg-blue-500 text-white': link.active,
                  'text-gray-700': !link.active,
                  'opacity-50 cursor-not-allowed': !link.url
                }"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
