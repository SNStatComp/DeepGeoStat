<template>
    <manage-sets :data="labelEvidence">
        <template #title>
            <div class="flex justify-between items-center">
                <h1 class="text-2xl col-span-12">Label Evidence</h1>

                <jet-button @click="openLabelEvidenceModal = true">Create Label Evidence</jet-button>
                <create-label-evidence :show="openLabelEvidenceModal" @close="openLabelEvidenceModal = false" :team-datasets="teamDatasets" :team-label-classes="teamLabelClasses" :available-registers="availableRegisters" />
            </div>
        </template>

        <template #data>
            <li v-for="evidence in labelEvidence" :key="evidence.id">
                <set-data name="Label Evidence" :url="route('data.labelEvidence.show', evidence.id)" :delete-url="route('data.labelEvidence.destroy', evidence.id)">
                    <template #svg>
                        <svg v-if="evidence.type === 0" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"/>
                        </svg>
                        <svg v-else class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </template>

                    <template #title>{{ evidence.title }}</template>

                    <template #description>{{ evidence.description }}</template>
                </set-data>
            </li>
        </template>
    </manage-sets>
</template>

<script>
import { defineComponent } from 'vue'
import JetButton from '@/Jetstream/Button.vue'
import ManageSets from '@/Pages/Data/Partials/ManageSets'
import SetData from '@/Pages/Data/Partials/SetData'
import CreateLabelEvidence from '@/Pages/Data/Partials/LabelEvidence/CreateLabelEvidence'

export default defineComponent({
    components: {
        JetButton,
        CreateLabelEvidence,
        ManageSets,
        SetData,
    },

    props: {
        teamLabelClasses: Array,
        teamDatasets: Array,
        labelEvidence: Array,
        availableRegisters: Array,
    },

    mounted() {
        Echo.private(`Team.${ this.$page.props.user.current_team_id }`)
            .listen('LabelEvidenceCreated', (e) => {
                this.$inertia.reload({
                    only: ['team'],
                    preserveScroll: true,
                })
            })
            .listen('LabelEvidenceDeleted', (e) => {
                this.$inertia.reload({
                    only: ['team'],
                    preserveScroll: true,
                })
            })
    },

    unmounted() {
        Echo.private(`Team.${ this.$page.props.user.current_team_id }`)
            .stopListening('LabelEvidenceCreated')
            .stopListening('LabelEvidenceDeleted')
    },

    data() {
        return {
            openLabelEvidenceModal: false,
        }
    },
})
</script>
