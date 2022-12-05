<template>
    <app-layout :title="`${ prediction.data.name } prediction`">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Predictions: {{ prediction.data.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <section class="border-b pb-2 mb-6">
                            <div class="flex items-center justify-between pt-4 pb-2">
                                <h1 class="text-2xl col-span-12">Prediction: {{
                                        prediction.data.name
                                    }}</h1>

                                <a :href="route('predictions.download', prediction.data.id)"
                                    target="_blank"
                                >
                                    <jet-button>
                                        <svg
                                            class="mr-2 h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                            />
                                        </svg>
                                        Download
                                    </jet-button>
                                </a>
                            </div>

                            <div class="text-gray-500">
                                {{ prediction.data.description }}
                            </div>
                        </section>

                        <GridCellGrid :datasets="prediction.data.datasets" :label_classes="prediction.data.team.label_classes" :default_label_class="prediction.data.team.default_label_class" :grid_cells="grid_cells" :label_evidence="labels" />
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import {defineComponent} from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import GridCellGrid from '@/Pages/Data/Partials/GridCellGrid'
import JetButton from "@/Jetstream/Button"

export default defineComponent({
    components: {
        AppLayout,
        GridCellGrid,
        JetButton,
    },

    props: [
        'prediction', 'grid_cells', 'labels',
    ],
})
</script>
