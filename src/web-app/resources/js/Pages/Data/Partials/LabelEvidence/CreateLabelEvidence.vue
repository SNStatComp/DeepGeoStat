<template>
    <modal>
        <template #title>
            Create Label Evidence
        </template>

        <template #content>
            <form @submit.prevent="createLabelEvidence" class="space-y-6">
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="label_evidence_dataset" value="Label Evidence Dataset*"/>

                    <!-- <v-autocomplete
                        id="dataset"
                        v-model="form.dataset"
                        :options="datasetOptions"
                        placeholder="Select dataset"
                        multiple
                    /> -->

                    <jet-select
                        id="label_evidence_dataset"
                        v-model="form.dataset"
                        class="w-full"
                    >
                        <option v-for="dataset in teamDatasets" :key="dataset.id" :value="dataset.id">{{ dataset.title }}</option>
                    </jet-select>

                    <jet-input-error :message="form.errors.dataset" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="label_evidence_title" value="Label Evidence Title*"/>
                    <jet-input id="label_evidence_title" type="text" class="block w-full mt-1" v-model="form.title" />
                    <jet-input-error :message="form.errors.title" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="label_evidence_description" value="Label Evidence Description"/>
                    <jet-textarea id="label_evidence_description" type="text" class="block w-full mt-1" v-model="form.description" />
                    <jet-input-error :message="form.errors.description" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="label_evidence_type" value="Label Evidence Type*"/>

                    <jet-select
                        id="label_evidence_type"
                        v-model.number="form.type"
                        class="w-full"
                    >
                        <option value="2">File</option>
                        <option value="1">Register</option>
                        <option value="0">Annotation Campaign</option>
                    </jet-select>

                    <jet-input-error :message="form.errors.type" class="mt-2"/>
                </div>

                <div v-show="form.type === 1">
                    <jet-label for="label_evidence_register" value="Label Evidence Register*"/>

                    <jet-select
                        id="label_evidence_register"
                        v-model.number="form.register"
                        class="w-full"
                        @change="setupRegisterLabelClasses"
                    >
                        <option v-for="register in availableRegisters" :key="register.id" :value="register.id">{{ register.title }}</option>
                    </jet-select>

                    <jet-input-error :message="form.errors.register" class="mt-2"/>

                    <div v-show="form.register">
                        <p>
                            Connect your project's labels to the register's labels.
                        </p>

                        <ul class="space-y-2">
                            <li v-for="(labelClass, i) in form.register_label_classes" :key="i" class="grid grid-cols-2 gap-2">
                                <div class="flex items-center">
                                    <jet-label :for="`label_evidence_register_label_class_${ labelClass.register_label_class }`" :value="availableRegisters.find((register) => register.id === form.register).labelClasses.find((registerLabelClass) => registerLabelClass.id === labelClass.register_label_class).title"/>
                                </div>

                                <jet-select
                                    :id="`label_evidence_register_label_class_${ labelClass.register_label_class }`"
                                    class="w-full"
                                    v-model.number="labelClass.team_label_class"
                                >
                                    <option v-for="teamLabelClass in teamLabelClasses" :key="teamLabelClass.id" :value="teamLabelClass.id">
                                        {{ teamLabelClass.title }}
                                    </option>
                                </jet-select>
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-show="form.type === 2" class="col-span-6 sm:col-span-4">
                    <jet-label for="label_evidence_register_file" value="Label Evidence Register File*"/>
                    <div>
                        <p>
                            Upload a file with the columns: grid name, label and mask_index if your dataset is masked.
                            Enter the labels found in your CSV to identify them with your project's labels.
                        </p>

                        <ul>
                            <li v-for="labelClass in form.register_label_class_identifiers" :key="labelClass.team_label_class" class="grid grid-cols-2 gap-2">
                                <label :for="`label_identifier_${ labelClass.team_label_class }`" class="flex items-center space-x-2">
                                    <div class="h-4 w-4 rounded-full" :style="`background-color: ${ teamLabelClasses.find((teamLabelClass) => teamLabelClass.id === labelClass.team_label_class).color };`"></div>
                                    <span >{{ teamLabelClasses.find((teamLabelClass) => teamLabelClass.id === labelClass.team_label_class).title }}</span>
                                </label>

                                <jet-input :id="`label_identifier_${ labelClass.team_label_class }`" type="text" v-model="labelClass.identifier" class="block w-full mt-1" />
                            </li>
                        </ul>
                    </div>

                    <jet-input id="label_evidence_register_file" type="file" class="block w-full mt-1" @input="form.register_file = $event.target.files[0]" />
                    <jet-input-error :message="form.errors.register_file" class="mt-2"/>
                </div>
            </form>
        </template>

        <template #footer>
            <jet-secondary-button @click="$emit('close')">Close</jet-secondary-button>

            <jet-button class="ml-3" @click="createLabelEvidence" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Create Label Evidence</jet-button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import Modal from '@/Jetstream/DialogModal'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import JetLabel from '@/Jetstream/Label'
import JetInput from '@/Jetstream/Input'
import JetTextarea from '@/Jetstream/Textarea'
import JetSelect from '@/Jetstream/Select'
import VAutocomplete from '@/Components/Input/Autocomplete'
import JetInputError from '@/Jetstream/InputError'

export default defineComponent({
    components: {
        Modal,
        JetButton,
        JetSecondaryButton,
        JetLabel,
        JetInput,
        JetTextarea,
        JetSelect,
        VAutocomplete,
        JetInputError,
    },  

    props: {
        teamDatasets: Array,
        teamLabelClasses: Array,
        availableRegisters: Array,
    },

    created() {
        this.teamLabelClasses.forEach((labelClass) => {
            this.form.register_label_class_identifiers.push({
                team_label_class: labelClass.id,
                identifier: labelClass.identifier,
            })
        })
    },

    data() {
        return {
            form: this.$inertia.form({
                dataset: null,
                title: '',
                description: '',
                type: null,
                register: null,
                register_label_classes: [],
                register_file: null,
                register_label_class_identifiers: [],
            }),
        }
    },

    computed: {
        datasetOptions() {
            return this.teamDatasets.map((dataset) => {
                return {
                    'value': dataset['id'],
                    'label': dataset['title'],
                }
            })
        },
    },

    methods: {
        setupRegisterLabelClasses() {
            let labelClasses = this.availableRegisters.find((register) => register.id === this.form.register).labelClasses.map((labelClass) => {
                return {
                    register_label_class: labelClass.id,
                    team_label_class: null,
                }
            })

            this.form.register_label_classes = labelClasses
        },

        createLabelEvidence() {
            this.form.transform((data) => {
                // Annotation Campaign
                if (this.form.type == 0) {
                    return _.pick(data, ['dataset', 'title', 'description', 'type'])
                // Register
                } else if (this.form.type == 1) {
                    // Register
                    if (this.registerType == 0) {
                        return _.pick(data, ['dataset', 'title', 'description', 'type', 'register', 'register_label_classes'])
                    // File
                    } else if (this.registerType == 1) {
                        return _.pick(data, ['dataset', 'title', 'description', 'type', 'register_file', 'register_label_class_identifiers'])
                    }
                }                

                return data
            }).post(route('data.labelEvidence.store'), {
                errorBag: 'createLabelEvidence',
                onSuccess: () => {
                    this.$emit('close')

                    this.form.reset()
                },
            })
        }
    }
})
</script>
