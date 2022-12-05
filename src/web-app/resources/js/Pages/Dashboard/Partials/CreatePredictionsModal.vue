<template>
    <modal>
        <template #title>
            Make Predictions
        </template>

        <template #content>
            <div class="space-y-6">
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="prediction_name" value="Predictions Title (*)"/>
                    <jet-input id="prediction_name" type="text" class="block w-full mt-1" v-model="form.name"/>
                    <jet-input-error :message="form.errors.name" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="prediction_description" value="Predictions Description"/>
                    <jet-textarea id="prediction_description" class="block w-full mt-1" v-model="form.description"/>
                    <jet-input-error :message="form.errors.description" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="prediction_experiment" value="Predictions Experiment Model (*)"/>
                    <jet-select id="prediction_experiment" class="block w-full mt-1" v-model="form.experiment">
                        <option v-for="experiment in experiments" :key="experiment.id" :value="experiment.id">{{ experiment.name }}</option>
                    </jet-select>
                    <jet-input-error :message="form.errors.experiment" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="prediction_datasets" value="Predictions Dataset(s)*"/>

                    <select id="prediction_datasets" v-model.number="form.datasets" class="w-full" multiple>
                        <option v-for="dataset in datasets" :key="dataset.id" :value="dataset.id">{{ dataset.title }}</option>
                    </select>
                    <jet-input-error :message="form.errors.datasets" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="prediction_subset_method" value="Predictions Subset Method (*)"/>
                    <jet-select id="prediction_subset_method" class="block w-full mt-1" v-model.number="form.subset_method">
                        <option value="">Full dataset</option>
                        <option value="0">Random Sample</option>
                        <option value="1">File</option>
                        <option value="2">Regions</option>
                    </jet-select>
                    <jet-input-error :message="form.errors.subset_method" class="mt-2"/>
                </div>

                <div v-show="form.subset_method === 0">
                    <div class="col-span-6 sm:col-span-4">
                        <jet-label for="random_sample_sample_size" value="Sample Size (*)"/>
                        <jet-input id="random_sample_sample_size" type="text" class="block w-full mt-1"
                                   v-model="form.sample_size"/>
                        <jet-input-error :message="form.errors.sample_size" class="mt-2"/>
                    </div>
                </div>

                <div v-show="form.subset_method === 1">
                    <div class="col-span-6 sm:col-span-4">
                        <jet-label for="subset_file" value="File (*)"/>
                        <jet-input id="subset_file" type="file" class="block w-full mt-1" @input="form.file = $event.target.files[0]" />
                        <jet-input-error :message="form.errors.file" class="mt-2"/>
                    </div>
                </div>

                <div v-show="form.subset_method === 2" class="space-y-6">
                    <div class="col-span-6 sm:col-span-4">
                        <jet-label for="subset_region_type" value="Region Type (*)"/>
                        <jet-select id="subset_region_type" class="block w-full mt-1" v-model.number="form.region_type">
                            <option v-for="(regionType, i) in Object.keys(regions)" :key="i" :value="regionType">{{ capitalizeFirstLetter(regionType) }}</option>
                        </jet-select>
                        <jet-input-error :message="form.errors.region_type" class="mt-2"/>
                    </div>

                    <div v-show="form.region_type" class="col-span-6 sm:col-span-4">
                        <jet-label for="subset_regions" value="Region(s) (*)"/>
                        <select id="subset_regions" class="block w-full mt-1" v-model.number="form.regions"  multiple>
                            <option v-for="(region, i) in regions[form.region_type]" :key="i" :value="region.id">{{ region.name }}</option>
                        </select>
                        <jet-input-error :message="form.errors.regions" class="mt-2"/>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <jet-secondary-button @click="$emit('close')">Close</jet-secondary-button>

            <jet-button class="ml-2" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                        @click="createPredictions">
                Make Predictions
            </jet-button>
        </template>
    </modal>
</template>

<script>
import {defineComponent} from 'vue'
import JetLabel from '@/Jetstream/Label'
import JetInput from '@/Jetstream/Input'
import JetTextarea from '@/Jetstream/Textarea'
import JetSelect from '@/Jetstream/Select'
import JetInputError from '@/Jetstream/InputError'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import Modal from '@/Jetstream/DialogModal'

export default defineComponent({
    components: {
        JetLabel,
        JetInput,
        JetTextarea,
        JetSelect,
        JetInputError,
        JetButton,
        JetSecondaryButton,
        Modal,
    },

    props: {
        experiments: Array,
        datasets: Array,
        regions: Object,
    },

    emits: [
        'success',
    ],

    data() {
        return {
            form: this.$inertia.form({
                experiment: null,
                datasets: [],
                subset_method: null,
                sample_size: null,
                file: null,
                region_type: null,
                regions: [],
                name: null,
                description: null,
            }),
        }
    },

    methods: {
        createPredictions() {
            this.form.post(route('dashboard.store'), {
                errorBag: 'createPredictions',
                onSuccess: () => {
                    this.$emit('success');
                }
            });
        },

        capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },
    },
})
</script>
