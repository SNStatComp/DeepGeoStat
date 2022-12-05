<template>
    <app-layout title="Experiments">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Step 3. Experiments
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <div class="pt-4 pb-2">
                            <div class="flex items-center justify-between">
                                <h1 class="text-2xl col-span-12">
                                    Experiments
                                </h1>

                                <jet-button @click="createExperiment = true">Create Experiment</jet-button>
                                <create-experiment :show="createExperiment" @close="createExperiment = false" :experiment-data="experiment_data" :model-types="experiment_model_types" />
                            </div>
                        </div>
                    </div>

                    <div v-if="experiments" class="bg-gray-200 bg-opacity-25 grid grid-cols-1">
                        <div v-for="experiment in experiments.data" :key="experiment.id">
                            <experiment-item :experiment="experiment" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from 'vue'
import { Link } from '@inertiajs/inertia-vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import CreateExperiment from '@/Pages/Experiments/Partials/CreateExperiment'
import JetButton from '@/Jetstream/Button'
import ExperimentItem from "@/Pages/Experiments/Partials/ExperimentItem";

export default defineComponent({
    components: {
        ExperimentItem,
        Link,
        AppLayout,
        CreateExperiment,
        JetButton,
    },

    props: {
        experiments: Object,
        experiment_model_types: Object,
        experiment_data: Array,
    },

    data() {
        return {
            createExperiment: false,
        }
    },
})
</script>
