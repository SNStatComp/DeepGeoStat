<template>
    <div>
        <div class="p-6 border border-gray-200">
            <div class="relative">
                <div class="grid grid-cols-4">
                    <section class="flex items-start">
                        <svg
                            class="w-8 h-8 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                            />
                        </svg>

                        <div class="ml-4">
                            <div class="flex items-center">
                                <Link
                                    :href="
                                        route('experiments.show', experiment)
                                    "
                                    class="text-lg text-gray-600 leading-7 font-semibold"
                                >
                                    {{ experiment.title }}
                                </Link>
                                <span
                                    class="ml-2 px-3 py-1 bg-blue-100 text-blue-900 rounded-full text-xs font-bold uppercase leading-none"
                                >
                                    {{ experiment.status }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-gray-500">
                                {{ experiment.description }}
                            </p>

                            <Link :href="route('experiments.show', experiment)">
                                <div
                                    class="mt-3 flex items-center text-sm font-semibold text-indigo-700"
                                >
                                    <div>View Experiment</div>

                                    <div class="ml-1 text-indigo-500">
                                        <svg
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            class="w-4 h-4"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </section>

                    <section class="border-l text-normal pl-6">
                        <div class="grid grid-cols-2" style="max-width: 180px;">
                            <p>Accuracy: </p><span class="text-right">{{ (experiment.statistics && 'accuracy' in experiment.statistics) ? experiment.statistics.accuracy + '%' : 'N/A' }}</span>
                            <p>Precision: </p><span class="text-right">{{ (experiment.statistics && 'precision' in experiment.statistics) ? experiment.statistics.precision + '%' : 'N/A' }}</span>
                            <p>Recall: </p><span class="text-right">{{ (experiment.statistics && 'recall' in experiment.statistics) ? experiment.statistics.recall + '%' : 'N/A' }}</span>
                            <p>F1-score: </p><span class="text-right">{{ (experiment.statistics && 'f1_score' in experiment.statistics) ? experiment.statistics.f1_score + '%' : 'N/A' }}</span>
                        </div>
                    </section>

                    <section
                        v-if="this.experiment.learning_curve_simple"
                        class="col-span-2 border-l pl-3"
                    >
                        <div class="max-h-24" ref="learning_curve_simple"></div>
                    </section>
                </div>

                <button
                    class="absolute top-0 right-0 text-red-500 ml-4"
                    @click="confirmDelete = true"
                >
                    <svg
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        class="w-8 h-8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <confirmation-modal
            :show="confirmDelete"
            @close="confirmDelete = false"
        >
            <template #title> Delete Experiment </template>

            <template #content>
                Are you sure you want to delete this experiment? Once a
                experiment is deleted, all of its resources and data will be
                permanently deleted.
            </template>

            <template #footer>
                <secondary-button @click="confirmDelete = false">
                    Cancel
                </secondary-button>

                <danger-button
                    class="ml-3"
                    @click="deleteData"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Delete Experiment
                </danger-button>
            </template>
        </confirmation-modal>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import { Link } from "@inertiajs/inertia-vue3";
import SecondaryButton from "@/Jetstream/SecondaryButton";
import DangerButton from "@/Jetstream/DangerButton";
import ConfirmationModal from "@/Jetstream/ConfirmationModal";
import Plotly from "plotly.js-dist-min";
import PlotlyConvert from "@/Mixins/PlotlyConvert";

export default defineComponent({
    components: {
        Link,
        SecondaryButton,
        DangerButton,
        ConfirmationModal,
        Plotly,
    },

    mixins: [PlotlyConvert],

    props: {
        experiment: Object,
    },

    data() {
        const icon1 = {
            width: 24,
            height: 24,
            path: "M12 0c-1.497 0-2.749.965-3.248 2.17a3.45 3.45 0 00-.238 1.416 3.459 3.459 0 00-1.168-.834 3.508 3.508 0 00-1.463-.256 3.513 3.513 0 00-2.367 1.02c-1.06 1.058-1.263 2.625-.764 3.83.179.432.47.82.82 1.154a3.49 3.49 0 00-1.402.252C.965 9.251 0 10.502 0 12c0 1.497.965 2.749 2.17 3.248.437.181.924.25 1.414.236-.357.338-.65.732-.832 1.17-.499 1.205-.295 2.772.764 3.83 1.058 1.06 2.625 1.263 3.83.764.437-.181.83-.476 1.168-.832-.014.49.057.977.238 1.414C9.251 23.035 10.502 24 12 24c1.497 0 2.749-.965 3.248-2.17a3.45 3.45 0 00.238-1.416c.338.356.73.653 1.168.834 1.205.499 2.772.295 3.83-.764 1.06-1.058 1.263-2.625.764-3.83a3.459 3.459 0 00-.834-1.168 3.45 3.45 0 001.416-.238C23.035 14.749 24 13.498 24 12c0-1.497-.965-2.749-2.17-3.248a3.455 3.455 0 00-1.414-.236c.357-.338.65-.732.832-1.17.499-1.205.295-2.772-.764-3.83a3.513 3.513 0 00-2.367-1.02 3.508 3.508 0 00-1.463.256c-.437.181-.83.475-1.168.832a3.45 3.45 0 00-.238-1.414C14.749.965 13.498 0 12 0zm-.041 1.613a1.902 1.902 0 011.387 3.246v3.893L16.098 6A1.902 1.902 0 1118 7.902l-2.752 2.752h3.893a1.902 1.902 0 110 2.692h-3.893L18 16.098A1.902 1.902 0 1116.098 18l-2.752-2.752v3.893a1.902 1.902 0 11-2.692 0v-3.893L7.902 18A1.902 1.902 0 116 16.098l2.752-2.752H4.859a1.902 1.902 0 110-2.692h3.893L6 7.902A1.902 1.902 0 117.902 6l2.752 2.752V4.859a1.902 1.902 0 011.305-3.246z",
        };
        return {
            confirmDelete: false,
            form: this.$inertia.form(),

            layout: {
                paper_bgcolor: "rgba(0, 0, 0, 0)",
                plot_bgcolor: "rgba(0, 0, 0, 0)",
                showlegend: false,
                margin: {
                    l: 0,
                    r: 70,
                    b: 0,
                    t: 0,
                },
                xaxis: {
                    showticklabels: false,
                    fixedrange: true,
                    showgrid: false,
                    zeroline: false,
                    showline: false,
                },
                yaxis: {
                    showticklabels: false,
                    fixedrange: true,
                    showgrid: false,
                    zeroline: false,
                    showline: false,
                },
            },
            config: {
                displaylogo: false,
                displayModeBar: false,
            },
        };
    },

    mounted() {
        if (this.experiment.learning_curve_simple) {
            this.createLearningCurve();
        }
    },

    methods: {
        deleteData() {
            this.form.delete(route("experiments.destroy", this.experiment));
        },

        createLearningCurve() {
            const data = this.convertDataForScatter(
                this.experiment.learning_curve_simple
            );

            Plotly.newPlot(
                this.$refs.learning_curve_simple,
                data,
                this.layout,
                this.config
            );
        },
    },
});
</script>
