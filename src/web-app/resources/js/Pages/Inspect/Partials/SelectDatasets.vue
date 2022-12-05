<template>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <div class="pt-4 pb-2">
                <h1 class="text-2xl col-span-12">
                    Inspect
                </h1>
            </div>
        </div>

        <div class="bg-gray-200 bg-opacity-25">
            <div class="p-6 sm:px-20">
                <div class="space-y-6 max-w-md">
                    <div class="col-span-6 sm:col-span-4">
                        <jet-label for="dataset" value="Dataset To Consolidate"/>

                        <jet-select id="dataset" v-model.number="form.dataset" class="w-full">
                            <option v-for="dataset in datasets" :key="dataset.id"
                                    :value="dataset.id">{{ dataset.title }}
                            </option>
                        </jet-select>

                        <jet-input-error :message="form.errors.dataset" class="mt-2"/>
                    </div>

                    <jet-button @click="submit" :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing">Inspect Datasets
                    </jet-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {defineComponent} from 'vue'
import JetLabel from '@/Jetstream/Label'
import JetSelect from '@/Jetstream/Select'
import JetInputError from '@/Jetstream/InputError'
import JetButton from '@/Jetstream/Button'

export default defineComponent({
    components: {
        JetLabel,
        JetInputError,
        JetButton,
        JetSelect,
    },

    props: {
        datasets: Array,
    },

    data() {
        return {
            form: this.$inertia.form({
                dataset: null,
            }),
        }
    },

    methods: {
        submit() {
            this.form.post(route('inspect.store'), {
                errorBag: 'selectConsolidateDatasets',
            });
        }
    }
})
</script>
