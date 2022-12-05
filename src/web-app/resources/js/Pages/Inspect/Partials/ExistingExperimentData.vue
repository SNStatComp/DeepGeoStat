<template>
    <manage-sets v-if="experimentData.length > 0" :data="experimentData">
        <template #title>
            <h1 class="text-2xl col-span-12">Experiment Data</h1>
        </template>

        <template #data>
            <li v-for="data in experimentData" :key="data.id">
                <set-data name="Experiment Data" :url="route('experiments.data.show', data.id)" :delete-url="route('experiments.data.destroy', data.id)">
                    <template #svg>
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </template>

                    <template #title>{{ data.title }}</template>

                    <template #description>{{ data.description }}</template>
                </set-data>
            </li>
        </template>
    </manage-sets>
</template>

<script>
import {defineComponent} from 'vue'
import ManageSets from '@/Pages/Data/Partials/ManageSets'
import SetData from '@/Pages/Data/Partials/SetData'

export default defineComponent({
    components: {
        ManageSets,
        SetData,
    },

    props: {
        experimentData: Array,
    },
})
</script>
