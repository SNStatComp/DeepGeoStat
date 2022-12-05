<template>
    <app-layout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Step 4. Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <manage-sets :data="predictions">
                    <template #title>
                        <div class="flex justify-between items-center">
                            <h1 class="text-2xl col-span-12">Predictions</h1>

                            <jet-button @click="createPredictions = true">Make Predictions</jet-button>
                            <create-predictions-modal :show="createPredictions" @close="createPredictions = false" :datasets="datasets" :regions="regions" :experiments="experiments" />
                        </div>
                    </template>

                    <template #data>
                        <li v-for="prediction in predictions" :key="prediction.id">
                            <set-data name="Prediction" :url="route('dashboard.show', prediction.id)" :delete-url="route('dashboard.destroy', prediction.id)">
                                <template #svg>
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </template>

                                <template #title>{{ prediction.name }}</template>

                                <template #description>{{ prediction.description }}</template>
                            </set-data>
                        </li>
                    </template>
                </manage-sets>
            </div>
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from 'vue'
import { Link } from '@inertiajs/inertia-vue3';
import AppLayout from '@/Layouts/AppLayout'
import JetButton from '@/Jetstream/Button'
import ManageSets from '@/Pages/Data/Partials/ManageSets';
import SetData from '@/Pages/Data/Partials/SetData';
import CreatePredictionsModal from '@/Pages/Dashboard/Partials/CreatePredictionsModal';

export default defineComponent({
    components: {
        ManageSets,
        SetData,
        AppLayout,
        Link,
        JetButton,
        CreatePredictionsModal,
    },

    props: [
        'datasets',
        'regions',
        'experiments',
        'predictions',
    ],

    data() {
        return {
            createPredictions: false,
        }
    },
})
</script>
