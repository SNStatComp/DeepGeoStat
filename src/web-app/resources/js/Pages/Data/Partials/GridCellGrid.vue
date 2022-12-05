<template>
    <section id="gridCells">
        <section v-if="label_classes" class="flex items-center space-x-6 mb-8">
            <JetSecondaryButton
                v-if="!filters"
                v-for="label_class in label_classes"
                :class="{
                'bg-gray-200': label_class.id === selectedLabelClass
            }"
                @click="toggleSelectedLabelClass(label_class.id)"
            >
                <div class="flex items-center">
                    <div
                        class="h-4 w-4 rounded-full mr-2"
                        :style="`background-color: ${ label_class.color };`"
                    ></div>
                    <span>{{ label_class.name }}</span>
                    <span class="text-xs ml-1 text-gray-400 italic lowercase"
                          v-text="(label_class.id ===default_label_class?.id) ? '(default)' : ''"></span>
                </div>
            </JetSecondaryButton>

            <JetSecondaryButton
                v-else
                v-for="label_class in label_classes"
                :class="{
                'bg-gray-200': filteredLabelClasses.includes(label_class.id)
            }"
                @click="toggleFilterLabelClass(label_class.id)"
            >
                <div class="flex items-center">
                    <div
                        class="h-4 w-4 rounded-full mr-2"
                        :style="`background-color: ${ label_class.color };`"
                    ></div>
                    <span>{{ label_class.name }}</span>
                    <span class="text-xs ml-1 text-gray-400 italic lowercase"
                          v-text="(label_class.id ===default_label_class?.id) ? '(default)' : ''"></span>
                </div>
            </JetSecondaryButton>
        </section>

        <section
            class="grid mb-6"
            :class="{
            'grid-cols-3 gap-4': datasets.length <= 1,
            'grid-cols-2 gap-3': datasets.length >= 2
        }"
        >
            <div v-for="grid_cell in grid_cells.data" :key="grid_cell.id">
                <div
                    class="relative w-full h-full rounded overflow-hidden p-1"
                    :class="{
                        'cursor-pointer': editable && selectedLabelClass
                    }"
                    :style="getLabelClassBorder(grid_cell)"
                    @click="setLabelClass(grid_cell)"
                >
                    <div class="relative rounded overflow-hidden">
                        <div
                            class="absolute h-full w-full bg-black/[0.6] text-gray-200 opacity-0 hover:opacity-100 transition-opacity ease-in-out duration-300">
                            <div class="p-4">
                                <h1 class="text-white font-semibold">{{ grid_cell.name }}</h1>

                                <div v-if="getLabelEvidence(grid_cell)" class="mt-2 text-sm text-gray-300">
                                    <p v-if="getLabelClass(grid_cell)">
                                        <span class="font-bold mr-1">Label:</span>
                                        <span class="inline-block">{{ getLabelClass(grid_cell).name }}</span>
                                    </p>
                                    <p v-if="getLabelEvidence(grid_cell).confidence" class="flex">
                                        <span class="font-bold mr-1">Confidence:</span>
                                        <span>
                                            <span class="block font-medium">{{
                                                    percentage(getLabelEvidence(grid_cell).confidence)
                                                }}</span>
                                            <span v-text="getLabelEvidence(grid_cell).confidence_reason"/>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="grid"
                            :style="`grid-template-columns: repeat(${ datasets.length }, minmax(0, 1fr));`"
                        >
                            <img v-for="dataset in datasets"
                                 :src="`${ dataset.url }&BBOX=${ grid_cell.bbox }`"
                                 :alt="`Grid cell ${ grid_cell.name}`"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex items-start justify-between space-x-6">
            <div class="flex-none w-28">
                <Link
                    :href="grid_cells.meta.links[0].url"
                    v-show="grid_cells.meta.current_page !== 1"
                    preserve-scroll
                >
                    <JetSecondaryButton>Previous</JetSecondaryButton>
                </Link>
            </div>


            <div class="grow space-x-2">
                <Link
                    v-for="link in grid_cells.meta.links.slice(1, grid_cells.meta.links.length - 1)"
                    :href="link.url"
                    preserve-scroll
                >
                    <JetSecondaryButton
                        v-text="getLocaleString(link.label)"
                        :class="{ 'bg-gray-100 font-bold': link.active }"
                    ></JetSecondaryButton>
                </Link>
            </div>

            <div class="flex-none w-28 flex justify-end">
                <Link
                    :href="grid_cells.meta.links[grid_cells.meta.links.length - 1].url"
                    v-show="grid_cells.meta.current_page !== grid_cells.meta.last_page"
                    preserve-scroll
                >
                    <JetSecondaryButton>Next</JetSecondaryButton>
                </Link>
            </div>
        </section>
    </section>
</template>

<script>
import {defineComponent} from "vue";
import {Link} from "@inertiajs/inertia-vue3"
import JetSecondaryButton from '@/Jetstream/SecondaryButton';

export default defineComponent({
    components: {
        Link,
        JetSecondaryButton,
    },

    props: {
        filters: {
            required: false,
            type: Object,
        },
        editable: {
            required: false,
            type: Boolean,
        },
        default_label_class: {
            required: false,
            type: Object,
        },
        label_classes: {
            required: false,
            type: Array,
        },
        datasets: {
            required: true,
            type: Array,
        },
        grid_cells: {
            required: true,
            type: Object,
        },
        label_evidence: {
            required: false,
            type: Array,
        }
    },

    mounted() {
        window.addEventListener('keydown', event => {
            if (event.ctrlKey && event.key === 's') {
                event.preventDefault();

                this.save();
            }
        });

        if (this.filters) {
            this.filteredLabelClasses = this.filters.label_classes.split(',').map((label_class) => {
                return parseInt(label_class);
            });
        }
    },

    emits: [
        'update',
        'changeFilters',
    ],

    data() {
        return {
            selectedLabelClass: null,
            filteredLabelClasses: [],
        }
    },

    computed: {
        labelEvidenceFormData() {
            let label_evidence = [];
            this.label_evidence.forEach(evidence => {
                label_evidence.push({
                    grid_cell_id: evidence.grid_cell_id,
                    label_class_id: evidence.label_class_id,
                })
            });

            return label_evidence;
        }
    },

    methods: {
        percentage(value) {
            if (value <= 1) {
                return `${(value * 100).toFixed(0)}%`;
            }

            return value;
        },


        save() {
            if (this.labelEvidenceFormData.length) {
                this.$emit('update', this.labelEvidenceFormData);
            }
        },

        toggleSelectedLabelClass(id) {
            if (!this.editable) {
                return;
            }

            if (this.selectedLabelClass === id) {
                this.selectedLabelClass = null;
            } else {
                this.selectedLabelClass = id;
            }
        },

        toggleFilterLabelClass(id) {
            if (!this.filters) {
                return;
            }

            if (this.filteredLabelClasses.includes(id)) {
                if (this.filteredLabelClasses.length === 1) {
                    return;
                }

                this.filteredLabelClasses = this.filteredLabelClasses.filter(e => e !== id);
            } else {
                this.filteredLabelClasses.push(id);
            }

            this.$emit('changeFilters', {
                label_classes: this.filteredLabelClasses.join(','),
            })
        },

        setLabelClass(grid_cell) {
            if (!this.editable || !this.selectedLabelClass) {
                return;
            }

            let label_evidence = this.getLabelEvidence(grid_cell);
            if (label_evidence) {
                label_evidence.label_class_id = this.selectedLabelClass;
            } else {
                this.label_evidence.push({
                    grid_cell_id: grid_cell.id,
                    label_class_id: this.selectedLabelClass,
                });
            }

            this.save();
        },

        getLabelEvidence(grid_cell) {
            if (!this.label_evidence) {
                return;
            }

            let label_evidence = this.label_evidence.findIndex(evidence => evidence.grid_cell_id === grid_cell.id);
            if (label_evidence !== -1) {
                return this.label_evidence[label_evidence];
            }

            return null;
        },

        getLabelClass(grid_cell) {
            if (!this.label_evidence) {
                return;
            }

            let label_evidence = this.getLabelEvidence(grid_cell);
            if (label_evidence) {
                let label_class = this.label_classes.findIndex(label => label.id === label_evidence['label_class_id']);
                if (label_class !== -1) {
                    label_class = this.label_classes[label_class];

                    return label_class;
                }
            }

            return null;
        },

        getLabelClassBorder(grid_cell) {
            // Get label class associated with label class.
            let label_class = this.getLabelClass(grid_cell);

            // Default color is transparent so the user cannot see anything.
            let color = "transparent";
            if (label_class) {
                // If the grid cell has label evidence show the label class.
                color = label_class.color;
            } else if (this.default_label_class) {
                // If there is no label evidence for the grid cell check if there is a default label class.
                // If there is a default label class show the default label class.
                color = this.default_label_class.color;
            }

            return `background-color: ${color};`;
        },

        getLocaleString(string) {
            if (!!string && !isNaN(+string.replace(/\s|\$/g, ''))) {
                return Number(string).toLocaleString();
            }

            return string;
        }
    },
})
</script>
