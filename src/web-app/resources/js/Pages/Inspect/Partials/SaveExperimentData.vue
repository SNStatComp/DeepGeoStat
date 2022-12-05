<template>
    <section>
        <h2>Save as Experiment Data</h2>

        <form @submit.prevent="submit" class="space-y-6 max-w-lg">
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="experiment_data_title" value="Experiment Data Title*"/>
                <jet-input id="experiment_data_title" type="text" class="block w-full mt-1" v-model="form.title" />
                <jet-input-error :message="form.errors.title" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <jet-label for="experiment_data_description" value="Experiment Data Description"/>
                <jet-textarea id="experiment_data_description" type="text" class="block w-full mt-1" v-model="form.description" />
                <jet-input-error :message="form.errors.description" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <jet-label for="experiment_data_sample_method" value="Experiment Data Sample Method*"/>

                <jet-select
                    id="experiment_data_sample_method"
                    v-model.number="form.sample_method"
                    class="w-full"
                >
                    <option value="">Full dataset</option>
                    <option value="0">Random Sample</option>
                    <option value="1">Equal Class Size Sample</option>
                    <!-- <option value="2">Stratified Sample</option> -->
                </jet-select>

                <jet-input-error :message="form.errors.sample_method" class="mt-2"/>
            </div>

            <div v-show="form.sample_method === 0" class="col-span-6 sm:col-span-4">
                <jet-label for="experiment_data_random_sample_size" value="Random Sample size*"/>
                <jet-input id="experiment_data_random_sample_size" type="text" class="block w-full mt-1" v-model="form.random_sample_size" />
                <jet-input-error :message="form.errors.random_sample_size" class="mt-2"/>
            </div>

            <jet-button @click="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save as Experiment Data</jet-button>
        </form>
    </section>
</template>

<script>
import { defineComponent } from 'vue'
import JetLabel from '@/Jetstream/Label'
import JetInput from '@/Jetstream/Input'
import JetTextarea from '@/Jetstream/Textarea'
import JetSelect from '@/Jetstream/Select'
import JetInputError from '@/Jetstream/InputError'
import JetButton from '@/Jetstream/Button'

export default defineComponent({
    components: {
        JetLabel,
        JetInput,
        JetTextarea,
        JetSelect,
        JetInputError,
        JetButton,
    },

    props: {
        inspectDatasetId: Number,
    },

    data() {
        return {
            form: this.$inertia.form({
                title: null,
                description: null,
                sample_method: null,
                random_sample_size: null,
            })
        }
    },

    methods: {
        submit() {
            this.form.post(route('inspect.experimentData.store', this.inspectDatasetId), {
                errorBag: 'consolidateDatasets',
                preserveScroll: true,
            })
        }
    }
})
</script>
