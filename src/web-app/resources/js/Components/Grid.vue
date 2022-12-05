<template>
    <section v-if="props.grids !== null || props.labels !== null">
        <!-- Label Classes -->
        <section v-if="labelClasses.length" class="flex justify-between items-center mb-4">
            <div class="space-x-4">
                <JetSecondaryButton @click="handleLabelClassClick(i)" v-for="(labelClass, i) in labelClasses" :key="labelClass.id" :class="{
                    'bg-gray-200': selectedLabelClasses.includes(labelClass.id)
                }">
                    <div class="flex items-center">
                        <div
                            class="-ml-1 mr-3 h-4 w-4 rounded-full"
                            :style="`background-color: ${ labelClass.color };`"
                        ></div>
                        <span>{{ labelClass.title }}</span>
                        <span class="text-xs ml-1 text-gray-400 italic lowercase"
                            v-text="(labelClass.id === defaultLabelClass?.id) ? '(default)' : ''"></span>
                    </div>
                </JetSecondaryButton>
            </div>

            <slot name="saveButton"></slot>
        </section>

        <!-- Grids -->
        <section 
            class="grid"
            :class="[detectionType === 2 ? 'grid-cols-2 gap-3' : 'grid-cols-3 gap-4']"        
        >
            <div 
                @click="handleGridClick(i)"
                v-for="(grid, i) in props.grids.data" 
                :key="grid.id" 
                class="relative w-full h-full rounded-sm p-1 overflow-hidden"
                :style="`min-height: 200px; background-color: ${ getLabelBackground(grid.id) };`"
            >
                <div class="relative h-full w-full rounded-sm overflow-hidden shadow-sm">
                    <div class="absolute w-full h-full bg-black/[0.6] text-gray-100 p-2 opacity-0 z-10 hover:opacity-100 transition-opacity ease-in-out duration-300">
                        <p class="text-base text-white">{{ grid.gridCells[0].title }}</p>
                        <p v-if="getLabel(grid.id)" class="text-sm text-gray-300">Label: {{ getLabel(grid.id).title }}</p>
                        <div v-if="getLabelEvidence(grid.id)?.information">
                            <p class="text-sm text-gray-300">Confidence:</p>
                            <div class="ml-2">
                                <p class="text-sm text-gray-300">{{ getLabelEvidence(grid.id).information.confidence.level }}%</p>
                                <p class="text-sm text-gray-300">{{ getLabelEvidence(grid.id).information.confidence.reason }}</p>
                            </div>
                            
                        </div>
                    </div>

                    <div :class="`min-h-full bg-black grid grid-cols-${ grid.gridCells.length }`">
                        <img v-for="gridCell in grid.gridCells" 
                            :key="gridCell.title" 
                            :src="gridCell.url" 
                            :alt="`Grid Cell ${ gridCell.title }`" 
                            class="h-full w-full z-0"
                            :style="`clip-path: polygon(${ gridCell.polygon })`" />
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Navigation  -->
        <nav class="flex items-start justify-between space-x-6 mt-8">
            <div class="flex-none w-28">
                <Link
                    :href="props.grids.meta.links[0].url"
                    v-show="props.grids.meta.current_page !== 1"
                    preserve-scroll
                >
                    <JetSecondaryButton>Previous</JetSecondaryButton>
                </Link>
            </div>


            <div class="relative grow">
                <div class="w-full flex flex-wrap gap-1">
                    <Link
                        v-for="link in props.grids.meta.links.slice(1, props.grids.meta.links.length - 1)"
                        :key="link.url"
                        :href="link.url"
                        v-show="props.grids.meta.total > 1"
                        preserve-scroll
                    >
                        <JetSecondaryButton :class="{ 'bg-gray-100 font-bold': link.active }">
                            {{ (parseFloat(link.label)) ? parseFloat(link.label).toLocaleString() : link.label }}
                        </JetSecondaryButton>
                    </Link>
                </div>
                
            </div>

            <div class="flex-none w-28 flex justify-end">
                <Link
                    :href="props.grids.meta.links[props.grids.meta.links.length - 1].url"
                    v-show="props.grids.meta.current_page !== props.grids.meta.last_page"
                    preserve-scroll
                >
                    <JetSecondaryButton>Next</JetSecondaryButton>
                </Link>
            </div>
        </nav>
    </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from "@inertiajs/inertia-vue3"
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'

const emit = defineEmits([
    'updateLabels',
    'updateFilterLabelClasses',
])

const props = defineProps({
    detectionType: {
        type: Number,
        required: true,
    },
    grids: {
        type: Object,
        required: true,
    },
    labelClasses: {
        type: Array,
        required: false,
        default: [],
    },
    defaultLabelClass: {
        type: Object,
        required: false,
        default: null,
    },
    labels: {
        type: Array,
        required: false,
        default: [],
    },
    filterLabelClasses: {
        type: Array,
        required: false,
        default: [],
    },
    editable: Boolean,
})

onMounted(() => {
    if (props.editable) {
        selectedLabelClasses.value = [ props.labelClasses[0].id ]
    }

    if (props.filterLabelClasses.length) {
        selectedLabelClasses.value = props.filterLabelClasses
    } 
})

function handleLabelClassClick (i) {
    if (! props.editable && ! props.filterLabelClasses.length) {
        return
    }

    let labelClass = props.labelClasses[i]
    if (! selectedLabelClasses.value.includes(labelClass.id)) {
        if (props.editable) {
            selectedLabelClasses.value = [ labelClass.id ]
        } else {
            selectedLabelClasses.value.push(labelClass.id)
        }
    } else {
        if (props.filterLabelClasses.length && selectedLabelClasses.value.length === 1) {
            return
        }

        selectedLabelClasses.value = selectedLabelClasses.value.filter(labelClassFilter => labelClassFilter !== labelClass.id)
    }

    if (props.filterLabelClasses.length) {
        emit('updateFilterLabelClasses', selectedLabelClasses.value)
    }
}

function handleGridClick (i) {
    if (! props.editable ||selectedLabelClasses.value.length !== 1) {
        return
    }

    let grid = props.grids.data[i]
    let labelEvidence = getLabelEvidence(grid.id)
    let selectedLabelClass = props.labelClasses.filter((labelClass) => selectedLabelClasses.value.includes(labelClass.id))[0]
    if (labelEvidence) {
        // If Label Evidence exists overwrite the evidence.
        labelEvidence.label_class_id = selectedLabelClass.id
    } else {
        // If no Label Evidence exists create evidence.
        props.labels.push({
            grid_id: grid.id,
            label_class_id: selectedLabelClass.id,
        })
    }

    emit('updateLabels', props.labels)
}

function getLabelEvidence(gridId) {
    return props.labels.find((label) => label.grid_id === gridId)
}

function getLabel(gridId) {
    let labelEvidence = getLabelEvidence(gridId)

    if (labelEvidence) {
        // If Label Evidence is available return the Label Class.
        return props.labelClasses.find((labelClass) => labelClass.id === labelEvidence.label_class_id)
    } else if (props.defaultLabelClass !== null) {
        // If no Label Evidence is available but there is a default Label Class return the default.
        return props.defaultLabelClass
    }

    // No Label Evidence or default Label class so return no label.
    return null
}

function getLabelBackground(gridId) {
    let label = getLabel(gridId)
    if (label) {
        return label.color
    }

    return 'transparent'
}

let selectedLabelClasses = ref([])

</script>