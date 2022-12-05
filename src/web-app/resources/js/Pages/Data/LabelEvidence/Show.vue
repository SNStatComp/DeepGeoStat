<template>
    <app-layout :title="`Label Evidence ${ labelEvidence.data.title }`">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Label Evidence: {{ labelEvidence.data.title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <section class="border-b pb-6 mb-6">
                            <div class="pt-4 pb-2">
                                <h1 class="text-2xl col-span-12">Label Evidence: {{
                                        labelEvidence.data.title
                                    }}</h1>
                            </div>

                            <div v-if="labelEvidence.data.description" class="text-gray-500 pb-2">
                                {{ labelEvidence.data.description }}
                            </div>

                            <div>
                                <ul class="w-1/3 text-sm text-gray-700">
                                    <li class="flex">
                                        <span class="block w-1/3 font-medium text-gray-800">Dataset:</span>

                                        <Link :href="route('data.datasets.show', labelEvidence.data.dataset.id)" class="hover:underline">
                                            {{ labelEvidence.data.dataset.title }}
                                        </Link>
                                    </li>
                                    <li class="flex">
                                        <span class="block w-1/3 font-medium text-gray-800">Grid count:</span>
                                        {{ labelEvidence.data.dataset.gridCount.toLocaleString() }}
                                    </li>
                                </ul>
                            </div>
                        </section>

                        <grid 
                            @updateLabels="updateLabels"
                            :detection-type="labelEvidence.data.team.detectionType" 
                            :label-classes="labelEvidence.data.team.labelClasses" 
                            :default-label-class="labelEvidence.data.team.defaultLabelClass" 
                            :grids="grids"
                            :labels="labels.data"
                            :editable="can.createLabels"
                        >
                            <template #saveButton v-if="can.createLabels"> 
                                <JetSecondaryButton v-show="! savedChanges" class="relative flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Saving
                                </JetSecondaryButton>

                                <JetSecondaryButton v-show="savedChanges" class="relative flex items-center">
                                    <svg class="-ml-1 mr-3 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    Saved
                                </JetSecondaryButton>
                            </template>
                        </grid>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from 'vue'
import { Link } from "@inertiajs/inertia-vue3"
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import Grid from '@/Components/Grid'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'

export default defineComponent({
    components: {
        Link,
        AppLayout,
        Grid,
        JetSecondaryButton,
    },

    props: [
        'can', 'labelEvidence', 'grids', 'labels',
    ],

    data() {
        return {
            savedChanges: true,
            form: this.$inertia.form({
                labels: [],
            })
        }
    },

    methods: {
        updateLabels(labels) {
            this.savedChanges = false
            this.form.labels = labels

            this.saveLabels()
        },

        saveLabels: _.throttle(function () {
            axios.post(route('data.labelEvidence.grids.store', this.labelEvidence.data.id), this.form)
                .then(() => {
                    console.log('Successfully saved data.')
                    this.savedChanges = true
                }).catch(() => {
                    console.log('Could not save data.')
                })
        }, 5000)
    }
})
</script>
