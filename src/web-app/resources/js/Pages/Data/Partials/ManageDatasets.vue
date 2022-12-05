<template>
    <manage-sets :data="teamDatasets">
        <template #title>
            <div class="flex justify-between items-center">
                <h1 class="text-2xl col-span-12">Datasets</h1>

                <jet-button @click="openDatasetModal = true">Create Dataset</jet-button>
                <create-dataset :show="openDatasetModal" @close="openDatasetModal = false" :available-datasets="availableDatasets" :available-filters="availableFilters" />
            </div>
        </template>

        <template #data>
            <li v-for="dataset in teamDatasets" :key="dataset.id">
                <set-data name="Dataset" :url="route('data.datasets.show', dataset.id)" :delete-url="route('data.datasets.destroy', dataset.id)">
                    <template #svg>
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                             stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-400">
                            <path
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </template>

                    <template #title>{{ dataset.title }}</template>

                    <template #description>{{ dataset.description }}</template>
                </set-data>
            </li>
        </template>
    </manage-sets>
</template>

<script>
import {defineComponent} from 'vue'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import Modal from '@/Jetstream/Modal'
import ManageSets from '@/Pages/Data/Partials/ManageSets'
import SetData from '@/Pages/Data/Partials/SetData'
import CreateDataset from '@/Pages/Data/Partials/Datasets/CreateDataset'

export default defineComponent({
    components: {
        JetButton,
        JetSecondaryButton,
        Modal,
        ManageSets,
        SetData,
        CreateDataset,
    },

    props: {
        teamDatasets: Array,
        availableDatasets: Array,
        availableFilters: Object,
    },

    mounted() {
        Echo.private(`Team.${ this.$page.props.user.current_team_id }`)
            .listen('DatasetCreated', (e) => {
                this.$inertia.reload({
                    only: ['team'],
                    preserveScroll: true,
                })
            })
            .listen('DatasetDeleted', (e) => {
                this.$inertia.reload({
                    only: ['team'],
                    preserveScroll: true,
                })
            })
    },

    unmounted() {
        Echo.private(`Team.${ this.$page.props.user.current_team_id }`)
            .stopListening('DatasetCreated')
            .stopListening('DatasetDeleted')
    },

    data() {
      return {
          openDatasetModal: false,
      }
    },
})
</script>
