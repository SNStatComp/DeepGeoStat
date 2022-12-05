<template>
    <app-layout :title="`Inspect ${ dataset.data.title }`">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Inspect: {{ dataset.data.title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <section class="border-b pb-6 mb-6">
                            <div class="pt-4 pb-2">
                                <h1 class="text-2xl col-span-12">Inspect: {{
                                        dataset.data.title
                                    }}</h1>
                            </div>

                            <div>
                                <ul class="w-1/3 text-sm text-gray-700">
                                    <li class="flex">
                                        <span class="block w-1/3 font-medium text-gray-800">Dataset:</span>

                                        {{ dataset.data.title }}
                                    </li>

                                    <li class="flex">
                                        <span class="block w-1/3 font-medium text-gray-800">Grid count:</span>

                                        {{ dataset.data.gridCount.toLocaleString() }}
                                    </li>
                                </ul>
                            </div>
                        </section>

                        <section class="border-b pb-6 mb-6">
                            <grid
                                @updateFilterLabelClasses="updateLabelClassFilters"
                                :detection-type="dataset.data.team.detectionType" 
                                :label-classes="dataset.data.team.labelClasses" 
                                :default-label-class="dataset.data.team.defaultLabelClass" 
                                :grids="grids"
                                :labels="labels"
                                :filter-label-classes="filters.labelClasses"
                            />
                        </section>

                        <section class="border-b pb-6 mb-6 grid grid-cols-2 gap-4">
                            <distribution-plot
                                :label-classes="dataset.data.team.labelClasses"
                                :distribution="labelDistribution"
                            />
                        </section>

                        <save-experiment-data :inspect-dataset-id="inspectDataset.id" />
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from 'vue'
import { Link } from "@inertiajs/inertia-vue3"
import AppLayout from '@/Layouts/AppLayout.vue'
import Grid from '@/Components/Grid'
import DistributionPlot from '@/Components/DistributionPlot'
import SaveExperimentData from './Partials/SaveExperimentData'

export default defineComponent({
    components: {
        Link,
        AppLayout,
        Grid,
        DistributionPlot,
        SaveExperimentData,
    },

    props: [
        'filters', 'inspectDataset', 'dataset', 'grids', 'labels', 'labelDistribution',
    ],

    methods: {
        updateLabelClassFilters(filters) {
            this.$inertia.reload({
                data: {
                    labelClasses: filters.join(','),
                },
            })
        },
    },
})
</script>
