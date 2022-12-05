<template>
    <app-layout title="Data">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Step 1. Data
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <manage-datasets :team-datasets="team.data.datasets" :available-datasets="availableDatasets" :available-filters="availableFilters" />

                <manage-label-evidence v-show="team.data.datasets.length" :team-label-classes="team.data.labelClasses" :team-datasets="team.data.datasets" :label-evidence="team.data.labelEvidence" :available-registers="availableRegisters.data" />
            </div>
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import ManageDatasets from '@/Pages/Data/Partials/ManageDatasets'
import ManageLabelEvidence from '@/Pages/Data/Partials/ManageLabelEvidence'

export default defineComponent({
    components: {
        AppLayout,
        ManageDatasets,
        ManageLabelEvidence,
    },

    props: [
        'team',
        'availableDatasets',
        'availableFilters',
        'availableRegisters',
    ],
})
</script>
