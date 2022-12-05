<template>
    <modal>
        <template #title>
            Create Predictions
        </template>

        <template #content>
            <form @submit.prevent="createPredictions" class="space-y-6">
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="prediction_datasets" value="Prediction Dataset(s)*"/>

                    <v-autocomplete
                        id="datasets"
                        v-model="form.datasets"
                        :options="datasetOptions"
                        placeholder="Select dataset"
                        multiple
                    />

                    <jet-input-error :message="form.errors.datasets" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="prediction_title" value="Prediction Title*"/>

                    <jet-input id="prediction_title" type="text" class="block w-full mt-1" v-model="form.title" />

                    <jet-input-error :message="form.errors.title" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="prediction_surface_usage_filters" value="Prediction Surface Usage Filter(s)"/>

                    <v-autocomplete
                        id="prediction_surface_usage_filters"
                        v-model="form.surface_usage_filters"
                        :options="surfaceUsageFilterOptions"
                        placeholder="Select Surface Usage filter(s)"
                        multiple
                    />

                    <jet-input-error :message="form.errors.surface_usage_filters" class="mt-2"/>
                </div>

                <div v-show="form.surface_usage_filters.length" class="col-span-6 sm:col-span-4">
                    <jet-label for="dataset_mask" value="Dataset Mask"/>

                    <input type="checkbox" id="dataset_mask" v-model="form.surface_usage_filter_mask">

                    <jet-input-error :message="form.errors.surface_usage_filter_mask" class="mt-2"/>
                </div>

                <div v-show="form.surface_usage_filter_mask" class="col-span-6 sm:col-span-4">
                    <jet-label for="dataset_mask_size" value="Dataset Mask Size*"/>
                    <jet-input id="dataset_mask_size" type="text" class="block w-full mt-1" v-model="form.surface_usage_filter_mask_size" />
                    <jet-input-error :message="form.errors.surface_usage_filter_mask_size" class="mt-2"/>
                </div>
            </form>
        </template>

        <template #footer>
            <jet-secondary-button @click="$emit('close')">Close</jet-secondary-button>

            <jet-button class="ml-3" @click="createPredictions" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Create Predictions</jet-button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import Modal from '@/Jetstream/DialogModal'
import JetLabel from '@/Jetstream/Label'
import JetInput from '@/Jetstream/Input'
import JetSelect from '@/Jetstream/Select'
import JetInputError from '@/Jetstream/InputError'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import VAutocomplete from '@/Components/Input/Autocomplete'

export default defineComponent({
    components: {
        Modal,
        JetLabel,
        JetInput,
        JetSelect,
        JetInputError,
        JetButton,
        JetSecondaryButton,
        VAutocomplete,
    },

    props: {
        experiment: Object,
        availableDatasets: Array,
        availableFilters: Object,
    },

    data() {
        return {
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
