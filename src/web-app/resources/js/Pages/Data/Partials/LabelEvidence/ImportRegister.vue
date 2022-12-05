<template>
    <modal>
        <template #title>
            Import Register
        </template>

        <template #content>
            <div class="space-y-6">
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="register_datasets" value="Register Dataset(s)*"/>

                    <select id="register_datasets" v-model.number="form.datasets" class="w-full" multiple>
                        <option v-for="dataset in datasets" :key="dataset.id" :value="dataset.id">{{ dataset.title }}</option>
                    </select>
<!--                    <jet-select id="annotation_datasets" class="block w-full mt-1" v-model="form.datasets" multiple>-->
<!--                        <option v-for="dataset in datasets" :value="dataset.id">{{ dataset.title }}</option>-->
<!--                    </jet-select>-->
                    <jet-input-error :message="form.errors.datasets" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="register_title" value="Register Title*"/>
                    <jet-input id="register_title" type="text" class="block w-full mt-1" v-model="form.title" />
                    <jet-input-error :message="form.errors.title" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="register_description" value="Register Description"/>
                    <jet-textarea id="register_description" type="text" class="block w-full mt-1" v-model="form.description" />
                    <jet-input-error :message="form.errors.description" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="register_file" value="Register File*"/>
                    <div>
                        <p>

                        </p>

                        <ul>
                            <li v-for="(label_class, i) in form.label_identifiers" :key="label_class.id" class="grid grid-cols-2 gap-2">
                                <label :for="`label_identifier_${ i }`" class="flex items-center space-x-2">
                                    <div class="h-4 w-4 rounded-full" :style="`background-color: ${labelClasses[i].color};`"></div>
                                    <span >{{ labelClasses[i].name }}</span>
                                </label>

                                <jet-input :id="`label_identifier_${ i }`" type="text" class="block w-full mt-1" v-model="label_class.identifier" />
                            </li>
                        </ul>
                    </div>

                    <jet-input id="register_file" type="file" class="block w-full mt-1" @input="form.file = $event.target.files[0]" />
                    <jet-input-error :message="form.errors.file" class="mt-2"/>
                </div>
            </div>

        </template>

        <template #footer>
            <jet-secondary-button @click="$emit('close')">Close</jet-secondary-button>

            <jet-button class="ml-3" @click="createAnnotationCampaign" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Import Register</jet-button>
        </template>
    </modal>
</template>

<script>
import {defineComponent} from 'vue'
import Modal from '@/Jetstream/DialogModal'
import JetLabel from '@/Jetstream/Label'
import JetInput from '@/Jetstream/Input'
import JetTextarea from '@/Jetstream/Textarea'
import JetSelect from '@/Jetstream/Select'
import JetInputError from '@/Jetstream/InputError'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'

export default defineComponent({
    props: {
        labelClasses: Array,
        datasets: Array,
    },

    components: {
        Modal,
        JetLabel,
        JetInput,
        JetTextarea,
        JetSelect,
        JetInputError,
        JetButton,
        JetSecondaryButton,
    },

    data() {
        return {
            form: this.$inertia.form({
                datasets: [],
                title: '',
                description: '',
                label_identifiers: [],
                file: null,
            }),
        }
    },

    created() {
        this.labelClasses.forEach((label_class) => {
            this.form.label_identifiers.push({
                label_class_id: label_class.id,
                identifier: label_class.identifier,
            });
        });
    },

    methods: {
        createAnnotationCampaign() {
            this.form
                .transform((data) => ({
                    ...data,
                    register_label_identifiers: [],
                })).post(route('data.registers.store'), {
                    errorBag: 'importRegister',
                    onSuccess: () => {
                        this.$emit('close');

                        this.form.reset();
                    },
                });
        }
    },
})
</script>
