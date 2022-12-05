<template>
    <app-layout :title="`Dataset ${ dataset.data.title }`">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dataset: {{ dataset.data.title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <section class="border-b pb-6 mb-6">
                            <div class="pt-4 pb-2">
                                <h1 class="text-2xl col-span-12">Label Evidence: {{
                                        dataset.data.title
                                    }}</h1>
                            </div>

                            <div v-if="dataset.data.description" class="text-gray-500 pb-2">
                                {{ dataset.data.description }}
                            </div>

                            <div>
                                <ul class="w-1/3 text-sm text-gray-700">
                                    <li class="flex">
                                        <span class="block w-1/3 font-medium text-gray-800">Source(s):</span>
                                        N/A
                                    </li>
                                    <li class="flex">
                                        <span class="block w-1/3 font-medium text-gray-800">Grid count:</span>
                                        {{ dataset.data.gridCount.toLocaleString() }}
                                    </li>
                                </ul>
                            </div>
                        </section>

                        <grid :detection-type="dataset.data.team.detectionType" :grids="grid" />
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import {defineComponent} from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Grid from '@/Components/Grid'

export default defineComponent({
    components: {
        AppLayout,
        Grid,
    },

    props: [
        'dataset', 'grid',
    ],
})
</script>
