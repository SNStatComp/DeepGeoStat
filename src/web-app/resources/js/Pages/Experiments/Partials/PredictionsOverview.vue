<template>
    <modal>
        <template #title>
            <div class="flex justify-between items-center">
                Predictions

                <jet-button @click="openCreatePredictions = true">Create Predictions</jet-button>
                <create-predictions 
                    :show="openCreatePredictions"
                    @close="openCreatePredictions = false"
                    :available-datasets="availableDatasets"
                    :available-filters="availableFilters"
                    :experiment="experiment"
                />
            </div>
        </template>

        <template #content>
            <div class="space-y-6">
                <div v-for="prediction in experiment.predictions" :key="prediction.id">
                    <div class="flex justify-between">
                        {{ prediction.title }}

                        <a :href="route('predictions.download', [experiment, prediction])" target="_blank"><jet-secondary-button>Download</jet-secondary-button></a>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <jet-secondary-button @click="$emit('close')">Close</jet-secondary-button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import Modal from '@/Jetstream/DialogModal'
import CreatePredictions from '@/Pages/Experiments/Partials/CreatePredictions'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'

export default defineComponent({
    components: {
        Modal,
        CreatePredictions,
        JetButton,
        JetSecondaryButton,
    },

    props: {
        experiment: Object,
        availableDatasets: Array,
        availableFilters: Object,
    },

    data() {
        return {
            openCreatePredictions: false,
            form: this.$inertia.form({
                datasets: [],
                title: '',
                surface_usage_filters: [],
                surface_usage_filter_mask: false,
                surface_usage_filter_mask_size: '',
            }),
        }
    },

    computed: {
        datasetOptions() {
            return this.availableDatasets.map((dataset) => {
                return {
                    'value': dataset['id'],
                    'label': dataset['title'],
                }
            })
        },

        surfaceUsageFilterOptions() {
            return this.availableFilters.surfaceUsage.data.map((filter) => {
                return {
                    'value': filter['id'],
                    'label': filter['title'],
                }
            })
        },
    },

    methods: {
        createPredictions() {
            this.form.post(route('predictions.store', this.experiment), {
                errorBag: 'createPredictions',
                onSuccess: () => {
                    this.$emit('close')

                    this.form.reset()
                },
            })
        },
    }
})
</script>
>
