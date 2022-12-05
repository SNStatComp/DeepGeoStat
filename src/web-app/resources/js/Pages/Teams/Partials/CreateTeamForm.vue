<template>
    <jet-form-section @submitted="createTeam">
        <template #title>
            Project Details
        </template>

        <template #description>
            Create a new project to collaborate with others.
        </template>

        <template #form>
            <div class="col-span-6">
                <jet-label value="Project Owner"/>

                <div class="flex items-center mt-2">
                    <img class="object-cover w-12 h-12 rounded-full" :src="$page.props.user.profile_photo_url"
                         :alt="$page.props.user.name">

                    <div class="ml-4 leading-tight">
                        <div>{{ $page.props.user.name }}</div>
                        <div class="text-sm text-gray-700">{{ $page.props.user.email }}</div>
                    </div>
                </div>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <jet-label for="name" value="Project Name"/>
                <jet-input id="name" type="text" class="block w-full mt-1" v-model="form.name" autofocus/>
                <jet-input-error :message="form.errors.name" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <jet-label for="description" value="Project Description"/>
                <jet-textarea id="description" type="text" class="block w-full mt-1" v-model="form.description"/>
                <jet-input-error :message="form.errors.description" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <jet-label for="detection_type" value="Detection Type"/>
                <jet-select id="detection_type" class="block w-full mt-1" v-model.number="form.detection_type">
                    <option value="1">Classification</option>
                    <option value="2">Change Detection</option>
                </jet-select>
                <jet-input-error :message="form.errors.detection_type" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <jet-label value="Project Label Classes"/>
                <ul class="space-y-2">
                    <li v-for="(label_class, i) in this.form.label_classes">
                        <div class="grid gap-2" style="grid-template-columns: 1fr minmax(auto, 20%) minmax(auto, 10%);">
                            <jet-input :id="`label-${i}-name`" type="text" class="block w-full mt-1"
                                       v-model="label_class.name"/>

                            <label :for="`label-${i}-color`"
                                   class="mt-1 w-full border border-gray-300 rounded-md shadow-sm px-2 py-1 cursor-pointer"
                                   :style="`background-color: ${label_class.color};`">
                                <jet-input :id="`label-${i}-color`" type="color" class="invisible"
                                           v-model="label_class.color"/>
                            </label>

                            <div class="flex items-center justify-end">
                                <button type="button" class="text-red-500" @click="removeLabelClass(i)">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <jet-input-error :message="form.errors[`label_classes.${i}.name`]" class="mt-2"/>
                        <jet-input-error :message="form.errors[`label_classes.${i}.color`]" class="mt-2"/>
                    </li>

                    <jet-secondary-button @click="addLabelClass">Add Row</jet-secondary-button>
                </ul>
                <jet-input-error :message="form.errors.label_classes" class="mt-2"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <jet-label for="label_class_default" value="Project Default Label Class"/>
                <jet-select id="label_class_default" class="block w-full mt-1" v-model.number="form.default_label_class">
                    <option value="">None</option>
                    <option v-for="(label_class, i) in this.form.label_classes" :value="i">{{
                            label_class.name
                        }}
                    </option>
                </jet-select>
                <jet-input-error :message="form.errors.default_label_class" class="mt-2"/>
            </div>
        </template>

        <template #actions>
            <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Create
            </jet-button>
        </template>
    </jet-form-section>
</template>

<script>
import {defineComponent} from 'vue'
import JetButton from '@/Jetstream/Button.vue'
import JetSecondaryButton from "@/Jetstream/SecondaryButton"
import JetFormSection from '@/Jetstream/FormSection.vue'
import JetInput from '@/Jetstream/Input.vue'
import JetTextarea from '@/Jetstream/Textarea'
import JetSelect from '@/Jetstream/Select'
import JetInputError from '@/Jetstream/InputError.vue'
import JetLabel from '@/Jetstream/Label.vue'
import LabelClass from "@/Mixins/LabelClass"

export default defineComponent({
    components: {
        JetButton,
        JetSecondaryButton,
        JetFormSection,
        JetInput,
        JetTextarea,
        JetSelect,
        JetInputError,
        JetLabel,
    },

    mixins: [
        LabelClass,
    ],

    data() {
        return {
            form: this.$inertia.form({
                name: '',
                description: '',
                detection_type: '',
                label_classes: [],
                default_label_class: null,
            }),

        }
    },

    mounted() {
        this.addLabelClass();
        this.addLabelClass();
    },

    methods: {
        createTeam() {
            this.form.post(route('teams.store'), {
                errorBag: 'createTeam',
                preserveScroll: true
            });
        },

        addLabelClass() {
            this.add(this.form.label_classes);
        },

        removeLabelClass(i) {
            this.remove(this.form.label_classes, i);
        }
    },
})
</script>
