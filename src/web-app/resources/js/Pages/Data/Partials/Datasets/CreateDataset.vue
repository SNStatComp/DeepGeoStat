<template>
    <modal>
        <template #title>
            Create Dataset
        </template>

        <template #content>
            <form @submit.prevent="createDataset" class="space-y-6">
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="datasets" value="Dataset(s)*"/>

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
                    <jet-label for="dataset_title" value="Dataset Title*"/>
                    <jet-input id="dataset_title" type="text" class="block w-full mt-1" v-model="form.title" />
                    <jet-input-error :message="form.errors.title" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="dataset_description" value="Dataset Description"/>
                    <jet-textarea id="dataset_description" type="text" class="block w-full mt-1" v-model="form.description" />
                    <jet-input-error :message="form.errors.description" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="dataset_surface_usage_filters" value="Surface Usage Filter(s)"/>

                    <v-autocomplete
                        id="dataset_sruface_usage_filters"
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
                    <p>Enter a valid percentage for example: 20%.</p>
                    <jet-input id="dataset_mask_size" type="text" class="block w-full mt-1" v-model="form.surface_usage_filter_mask_size" />
                    <jet-input-error :message="form.errors.surface_usage_filter_mask_size" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="dataset_region_type" value="Region Type"/>

                    <!-- <v-select
                        id="dataset_region_type"
                        v-model="region_type"
                        :options="[]"
                    /> -->

                    <jet-select id="dataset_region_type" class="block w-full mt-1" v-model.number="region_type">
                        <option v-for="(regionType, i) in Object.keys(availableFilters.regions)" :key="i" :value="regionType">{{ regionType }}</option>
                    </jet-select>
                </div>

                <div v-show="region_type" class="col-span-6 sm:col-span-4">
                    <jet-label for="dataset_regions" value="Region(s)"/>

                    <!-- <v-autocomplete
                        id="dataset_regions"
                        v-model="form.region_filters"
                        :options="regionFilterOptions"
                        placeholder="Select Region filter(s)"
                        multiple
                    /> -->
                    <select id="dataset_regions" class="block w-full mt-1" v-model.number="form.region_filters"  multiple>
                        <option v-for="(region, i) in availableFilters.regions[region_type]" :key="i" :value="region.id">{{ region.name }}</option>
                    </select>

                    <jet-input-error :message="form.errors.region_filters" class="mt-2"/>
                </div>
            </form>
        </template>

        <template #footer>
            <jet-secondary-button @click="$emit('close')">Close</jet-secondary-button>

            <jet-button class="ml-3" @click="createDataset" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Create Dataset</jet-button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import JetLabel from '@/Jetstream/Label'
import JetInput from '@/Jetstream/Input'
import JetTextarea from '@/Jetstream/Textarea'
import JetSelect from '@/Jetstream/Select'
import JetInputError from '@/Jetstream/InputError'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import Modal from '@/Jetstream/DialogModal'
import VSelect from '@/Components/Input/Select'
import VAutocomplete from '@/Components/Input/Autocomplete'

export default defineComponent({
    components: {
        Modal,
        JetLabel,
        JetInput,
        JetTextarea,
        JetSelect,
        JetInputError,
        JetButton,
        JetSecondaryButton,
        Modal,
        VSelect,
        VAutocomplete,
    },

    props: {
        availableDatasets: Array,
        availableFilters: Object,
    },

    data() {
        return {
            form: this.$inertia.form({
                datasets: [],
                title: '',
                description: '',
                surface_usage_filters: [],
                surface_usage_filter_mask: false,
                surface_usage_filter_mask_size: '',
                region_filters: [],
            }),
            region_type: null,
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
            return this.availableFilters.surfaceUsage.data.filter((filter) => {
                if (this.form.datasets.length) {
                    let years = [...new Set(this.availableDatasets.filter((dataset) => {
                        return this.form.datasets.includes(dataset.id)
                    }).map((dataset) => {
                        return dataset.year
                    }))]

                    if (years.includes(filter.year)) {
                        return true
                    }

                    return false
                }

                return true
            }).map((filter) => {
                return {
                    'value': filter['id'],
                    'label': filter['title'],
                }
            })
        },

        regionTypeFilterOptions() {
            if (Object.keys(this.availableRegions).length === 0) return []

            return Object.keys(this.availableRegions).map((regionType) => {
                return {
                    'value': regionType,
                    'label': regionType.toString().charAt(0).toUpperCase() + regionType.toString().slice(1),
                }
            })
        },

        regionFilterOptions() {
            if (this.region_type === null) return []

            return this.availableRegions[this.region_type].map((region) => {
                return {
                    'value': region['id'],
                    'label': region['name'],
                }
            })
        }
    },

    methods: {
        createDataset() {
            this.form.post(route('data.datasets.store'), {
                errorBag: 'createDataset',
                onSuccess: () => {
                    this.$emit('close')

                    this.form.reset()
                },
            })
        }
    }
})
</script>
