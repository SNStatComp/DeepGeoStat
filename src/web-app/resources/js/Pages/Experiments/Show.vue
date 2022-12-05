<template>
    <app-layout :title="`Experiment ${experiment.title}`">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Experiment: {{ experiment.title }}
                </h2>

                <div class="flex items-center space-x-4">
                    <a :href="route('experiments.download', this.experiment)"
                        target="_blank"
                    >
                        <jet-button>
                            <svg
                                class="mr-2 h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                />
                            </svg>
                            Download
                        </jet-button>
                    </a>

                    <jet-button @click="openSettings = true">
                        <svg
                            class="mr-2 h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        Settings
                    </jet-button>

                    <jet-button
                        @click="openPredictionsOverview = true"
                        v-if="this.experiment.status === 2"
                        style="height: 34px"
                    >
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        Predictions
                    </jet-button>


                    <jet-button
                        v-if="this.experiment.status === 1"
                        style="height: 34px"
                        @click="stopExperiment"
                    >
                        <svg
                            class="mr-2 h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"
                            />
                        </svg>
                        Stop
                    </jet-button>
                    <jet-button v-else @click="startExperiment">
                        <svg
                            class="mr-2 h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        Start
                    </jet-button>
                </div>
            </div>
        </template>

        <div class="py-12" v-show="learning_curve_detailed || confusion_matrix">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <div ref="learning_curve_detail"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <!-- <div>
                                    <div ref="cm_train"></div>
                                </div>
                                <div>
                                    <div ref="cm_val"></div>
                                </div> -->
                                <div>
                                    <div ref="cm_test"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <edit-experiment
            :show="openSettings"
            @close="openSettings = false"
            :experiment="experiment"
        />

        <predictions-overview
            :show="openPredictionsOverview"
            @close="openPredictionsOverview = false"
            :available-datasets="availableDatasets"
            :available-filters="availableFilters"
            :experiment="experiment"
        />
    </app-layout>
</template>

<script>
import { defineComponent } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Plotly from "plotly.js-dist-min";
import JetButton from "@/Jetstream/Button";
import EditExperiment from "@/Pages/Experiments/Partials/EditExperiment";
import PredictionsOverview from "@/Pages/Experiments/Partials/PredictionsOverview";
import PlotlyConvert from "@/Mixins/PlotlyConvert";

export default defineComponent({
    components: {
        AppLayout,
        Plotly,
        EditExperiment,
        PredictionsOverview,
        JetButton,
    },

    mixins: [PlotlyConvert],

    props: [
        "availableDatasets",
        "availableFilters",
        "experiment",
        "learning_curve_detailed",
        "confusion_matrix",
    ],

    data() {
        const icon1 = {
            width: 24,
            height: 24,
            path: "M12 0c-1.497 0-2.749.965-3.248 2.17a3.45 3.45 0 00-.238 1.416 3.459 3.459 0 00-1.168-.834 3.508 3.508 0 00-1.463-.256 3.513 3.513 0 00-2.367 1.02c-1.06 1.058-1.263 2.625-.764 3.83.179.432.47.82.82 1.154a3.49 3.49 0 00-1.402.252C.965 9.251 0 10.502 0 12c0 1.497.965 2.749 2.17 3.248.437.181.924.25 1.414.236-.357.338-.65.732-.832 1.17-.499 1.205-.295 2.772.764 3.83 1.058 1.06 2.625 1.263 3.83.764.437-.181.83-.476 1.168-.832-.014.49.057.977.238 1.414C9.251 23.035 10.502 24 12 24c1.497 0 2.749-.965 3.248-2.17a3.45 3.45 0 00.238-1.416c.338.356.73.653 1.168.834 1.205.499 2.772.295 3.83-.764 1.06-1.058 1.263-2.625.764-3.83a3.459 3.459 0 00-.834-1.168 3.45 3.45 0 001.416-.238C23.035 14.749 24 13.498 24 12c0-1.497-.965-2.749-2.17-3.248a3.455 3.455 0 00-1.414-.236c.357-.338.65-.732.832-1.17.499-1.205.295-2.772-.764-3.83a3.513 3.513 0 00-2.367-1.02 3.508 3.508 0 00-1.463.256c-.437.181-.83.475-1.168.832a3.45 3.45 0 00-.238-1.414C14.749.965 13.498 0 12 0zm-.041 1.613a1.902 1.902 0 011.387 3.246v3.893L16.098 6A1.902 1.902 0 1118 7.902l-2.752 2.752h3.893a1.902 1.902 0 110 2.692h-3.893L18 16.098A1.902 1.902 0 1116.098 18l-2.752-2.752v3.893a1.902 1.902 0 11-2.692 0v-3.893L7.902 18A1.902 1.902 0 116 16.098l2.752-2.752H4.859a1.902 1.902 0 110-2.692h3.893L6 7.902A1.902 1.902 0 117.902 6l2.752 2.752V4.859a1.902 1.902 0 011.305-3.246z",
        };
        return {
            openSettings: false,
            openPredictionsOverview: false,
            form: this.$inertia.form({
                name: null,
                description: null,
                model: null,
                epochs: null,
                batch_size: null,
                learning_rate: null,
            }),
            layout: {
                title: {
                    text: "Learning Curve",
                    font: {
                        family: "Nunito",
                        size: 20,
                        color: "black",
                    },
                },
                uirevision: "true",
                margin: {
                    r: 0,
                },
                modebar: {
                    orientation: "v",
                    bgcolor: "rgba(255 ,255 ,255 ,0.7)",
                },
                xaxis: {
                    title: "Epoch",
                },
                yaxis: {
                    title: "Value",
                },
            },
            config: {
                displaylogo: false,
                modeBarButtonsToAdd: [
                    {
                        name: "Download plot as SVG",
                        icon: icon1,
                        click: function (gd) {
                            Plotly.downloadImage(gd, {
                                format: "svg",
                            });
                        },
                    },
                ],
            },
        };
    },

    mounted() {
        if (this.confusion_matrix) {
            // if (this.confusion_matrix.matrices.train) {
            //     this.createConfusionMatrix(
            //         this.$refs.cm_train,
            //         this.confusion_matrix.matrices.train
            //     );
            // }

            // if (this.confusion_matrix.matrices.val) {
            //     this.createConfusionMatrix(
            //         this.$refs.cm_val,
            //         this.confusion_matrix.matrices.val
            //     );
            // }

            if (this.confusion_matrix.matrices.test) {
                this.createConfusionMatrix(
                    this.$refs.cm_test,
                    this.confusion_matrix.matrices.test
                );
            }
        }

        if (this.learning_curve_detailed) {
            this.createLearningCurve();
        }

        if (this.experiment.status === 1) {
            setInterval(() => {
                console.log("Reload!");

                this.$inertia.reload();
                if (this.confusion_matrix) {
                    this.updateConfusionMatrixes();
                }
                if (this.learning_curve_detailed) {
                    this.updateLearningCurve();
                }
            }, 10000);
        }
    },

    updated() {
        if (this.experiment.status === 1) {
            setInterval(() => {
                console.log("Reload2!");

                this.$inertia.reload();
                if (this.confusion_matrix) {
                    this.updateConfusionMatrixes();
                }
                if (this.learning_curve_detailed) {
                    this.updateLearningCurve();
                }
            }, 10000);
        }
    },

    methods: {
        startExperiment() {
            this.$inertia.post(route("experiments.start", this.experiment));
        },

        stopExperiment() {
            this.$inertia.delete(route("experiments.stop", this.experiment));
        },

        createConfusionMatrix(ref, data) {
            const icon1 = {
                width: 24,
                height: 24,
                path: "M12 0c-1.497 0-2.749.965-3.248 2.17a3.45 3.45 0 00-.238 1.416 3.459 3.459 0 00-1.168-.834 3.508 3.508 0 00-1.463-.256 3.513 3.513 0 00-2.367 1.02c-1.06 1.058-1.263 2.625-.764 3.83.179.432.47.82.82 1.154a3.49 3.49 0 00-1.402.252C.965 9.251 0 10.502 0 12c0 1.497.965 2.749 2.17 3.248.437.181.924.25 1.414.236-.357.338-.65.732-.832 1.17-.499 1.205-.295 2.772.764 3.83 1.058 1.06 2.625 1.263 3.83.764.437-.181.83-.476 1.168-.832-.014.49.057.977.238 1.414C9.251 23.035 10.502 24 12 24c1.497 0 2.749-.965 3.248-2.17a3.45 3.45 0 00.238-1.416c.338.356.73.653 1.168.834 1.205.499 2.772.295 3.83-.764 1.06-1.058 1.263-2.625.764-3.83a3.459 3.459 0 00-.834-1.168 3.45 3.45 0 001.416-.238C23.035 14.749 24 13.498 24 12c0-1.497-.965-2.749-2.17-3.248a3.455 3.455 0 00-1.414-.236c.357-.338.65-.732.832-1.17.499-1.205.295-2.772-.764-3.83a3.513 3.513 0 00-2.367-1.02 3.508 3.508 0 00-1.463.256c-.437.181-.83.475-1.168.832a3.45 3.45 0 00-.238-1.414C14.749.965 13.498 0 12 0zm-.041 1.613a1.902 1.902 0 011.387 3.246v3.893L16.098 6A1.902 1.902 0 1118 7.902l-2.752 2.752h3.893a1.902 1.902 0 110 2.692h-3.893L18 16.098A1.902 1.902 0 1116.098 18l-2.752-2.752v3.893a1.902 1.902 0 11-2.692 0v-3.893L7.902 18A1.902 1.902 0 116 16.098l2.752-2.752H4.859a1.902 1.902 0 110-2.692h3.893L6 7.902A1.902 1.902 0 117.902 6l2.752 2.752V4.859a1.902 1.902 0 011.305-3.246z",
            };

            // if (ref === this.$refs.cm_train) {
            //     var cm_title = "Training Confusion Matrix";
            // } else if (ref === this.$refs.cm_val) {
            //     var cm_title = "Validation Confusion Matrix";
            // } else 
            
            // if (ref === this.$refs.cm_test) {
                var cm_title = "Test Confusion Matrix";
            // }

            Plotly.newPlot(ref, {
                data: [
                    {
                        x: this.confusion_matrix.labels,
                        y: this.confusion_matrix.labels,
                        z: data,
                        type: "heatmap",
                        texttemplate: "%{z}",
                        colorscale: [
                            ["0.0", "rgb(247,251,255)"],
                            ["0.125", "rgb(222,235,247)"],
                            ["0.25", "rgb(198,219,239)"],
                            ["0.375", "rgb(158,202,225)"],
                            ["0.5", "rgb(107,174,214)"],
                            ["0.625", "rgb(66,146,198)"],
                            ["0.75", "rgb(33,113,181)"],
                            ["0.875", "rgb(8,81,156)"],
                            ["1.0", "rgb(8,48,107)"],
                        ],
                        showscale: false,
                    },
                ],
                layout: {
                    modebar: {
                        orientation: "v",
                        bgcolor: "rgba(255 ,255 ,255 ,0.7)",
                    },
                    title: {
                        text: cm_title,
                        font: {
                            family: "Nunito",
                            size: 20,
                            color: "black",
                        },
                    },
                },
                config: {
                    modeBarButtonsToRemove: [
                        "lasso2d",
                        "autoScale2d",
                        "zoomIn2d",
                        "zoomOut2d",
                    ],
                    displaylogo: false,
                    modeBarButtonsToAdd: [
                        {
                            name: "Download plot as SVG",
                            icon: icon1,
                            click: function (gd) {
                                Plotly.downloadImage(gd, {
                                    format: "svg",
                                });
                            },
                        },
                    ],
                },
            });
        },

        updateConfusionMatrixes() {
            if (this.confusion_matrix.train) {
                Plotly.restyle(this.$refs.cm_train, "z", [
                    this.confusion_matrix.matrices.train,
                ]);
            }

            if (this.confusion_matrix.val) {
                Plotly.restyle(this.$refs.cm_val, "z", [
                    this.confusion_matrix.matrices.val,
                ]);
            }

            if (this.confusion_matrix.test) {
                Plotly.restyle(this.$refs.cm_test, "z", [
                    this.confusion_matrix.matrices.test,
                ]);
            }
        },

        createLearningCurve() {
            const data = this.convertDataForScatter(
                this.learning_curve_detailed
            );

            Plotly.newPlot(
                this.$refs.learning_curve_detail,
                data,
                this.layout,
                this.config
            );
        },

        updateLearningCurve() {
            const data = this.convertDataForScatter(
                this.learning_curve_detailed
            );
            Plotly.react(
                this.$refs.learning_curve_detail,
                data,
                this.layout,
                this.config
            );
        },
    },
});
</script>
