<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-semibold mb-4">Bem-vindo ao CSV Contact Importer!</h2>
                    <p class="mb-4">
                        Aqui você pode gerenciar seus contatos e importar novos através de arquivos CSV.
                    </p>
                    <div class="mt-6">
                        <a
                            :href="route('contacts.index')"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Ir para Contatos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
