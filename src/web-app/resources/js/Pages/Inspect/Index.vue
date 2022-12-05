<template>
    <app-layout title="Inspect">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Step 2. Inspect
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="space-y-8">
                    <select-datasets :datasets="availableDatasets.data" />

                    <existing-experiment-data :experiment-data="experimentData" />
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SelectDatasets from "@/Pages/Inspect/Partials/SelectDatasets";
import SaveExperimentData from "@/Pages/Inspect/Partials/SaveExperimentData";
import InspectInfo from "@/Pages/Inspect/Partials/InspectInfo";
import ExistingExperimentData from "@/Pages/Inspect/Partials/ExistingExperimentData";

export default defineComponent({
    components: {
        ExistingExperimentData,
        AppLayout,
        SelectDatasets,
        InspectInfo,
        SaveExperimentData,
    },

    props: {
        filters: Object,
        availableDatasets: Object,
        experimentData: Object,
        labelClasses: Array,
        datasets: Object,
        gridCells: Object,
        labelEvidence: Array,
        labelDistribution: Object,
    },
});
</script>
