<template>
    <jet-form-section @submitted="updateTeamName">
        <template #title>
            Project Name
        </template>

        <template #description>
            The project's name and owner information.
        </template>

        <template #form>
            <!-- Team Owner Information -->
            <div class="col-span-6">
                <jet-label value="Project Owner" />

                <div class="flex items-center mt-2">
                    <img class="w-12 h-12 rounded-full object-cover" :src="team.owner.profile_photo_url" :alt="team.owner.name">

                    <div class="ml-4 leading-tight">
                        <div>{{ team.owner.name }}</div>
                        <div class="text-gray-700 text-sm">{{ team.owner.email }}</div>
                    </div>
                </div>
            </div>

            <!-- Team Name -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="name" value="Project Name" />

                <jet-input id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            :disabled="! permissions.canUpdateTeam" />

                <jet-input-error :message="form.errors.name" class="mt-2" />
            </div>

            <!-- Team Description -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="description" value="Project Description" />

                <jet-textarea id="description"
                           class="mt-1 block w-full"
                           v-model="form.description"
                           :disabled="! permissions.canUpdateTeam"
                />

                <jet-input-error :message="form.errors.description" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <jet-label for="detection_type" value="Detection Type"/>
                <jet-select id="detection_type" class="block w-full mt-1" :value="team.detection_type" disabled>
                    <option value="1">Classification</option>
                    <option value="2">Change Detection</option>
                </jet-select>
                <jet-input-error :message="form.errors.detection_type" class="mt-2"/>
            </div>

            <!-- Team Label Classes -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label value="Project Label Classes"/>
                <ul class="space-y-2">
                    <li v-for="(label_class, i) in this.form.label_classes" :key="i">
                        <div class="grid gap-2" style="grid-template-columns: 1fr minmax(auto, 20%) minmax(auto, 10%);">
                            <jet-input
                                :id="`label-${i}-title`"
                                type="text"
                                class="block w-full mt-1"
                                v-model="label_class.title"
                                :disabled="! permissions.canUpdateTeam"
                            />

                            <label :for="`label-${i}-color`"
                                   class="mt-1 w-full border border-gray-300 rounded-md shadow-sm px-2 py-1 cursor-pointer"
                                   :style="`background-color: ${label_class.color};`">
                                <jet-input
                                    :id="`label-${i}-color`"
                                    type="color"
                                    class="invisible"
                                    v-model="label_class.color"
                                    :disabled="! permissions.canUpdateTeam"
                                />
                            </label>

                            <div class="flex items-center justify-end">
                                <button type="button" class="text-red-500" @click="removeLabelClass(i)" :disabled="! permissions.canUpdateTeam">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <jet-input-error :message="form.errors[`label_classes.${i}.title`]" class="mt-2"/>
                        <jet-input-error :message="form.errors[`label_classes.${i}.color`]" class="mt-2"/>
                    </li>

                    <jet-secondary-button @click="addLabelClass" :disabled="! permissions.canUpdateTeam">Add Row</jet-secondary-button>
                </ul>
                <jet-input-error :message="form.errors.label_classes" class="mt-2"/>
            </div>

            <!-- Team Default Label Class -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="label_class_default" value="Project Default Label Class"/>
                <jet-select id="label_class_default" class="block w-full mt-1" v-model.number="form.default_label_class" :disabled="! permissions.canUpdateTeam">
                    <option value="">None</option>
                    <option v-for="(label_class, i) in this.form.label_classes" :key="i" :value="i">{{
                            label_class.title
                        }}
                    </option>
                </jet-select>
                <jet-input-error :message="form.errors.default_label_class" class="mt-2"/>
            </div>
        </template>

        <template #actions v-if="permissions.canUpdateTeam">
            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </jet-action-message>

            <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </jet-button>
        </template>
    </jet-form-section>
</template>

<script>
    import { defineComponent } from 'vue'
    import JetActionMessage from '@/Jetstream/ActionMessage'
    import JetButton from '@/Jetstream/Button'
    import JetSecondaryButton from "@/Jetstream/SecondaryButton";
    import JetFormSection from '@/Jetstream/FormSection'
    import JetInput from '@/Jetstream/Input'
    import JetTextarea from '@/Jetstream/Textarea'
    import JetSelect from '@/Jetstream/Select'
    import JetInputError from '@/Jetstream/InputError'
    import JetLabel from '@/Jetstream/Label'
    import LabelClass from "@/Mixins/LabelClass"

    export default defineComponent({
        components: {
            JetActionMessage,
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

        props: ['team', 'permissions'],

        data() {
            return {
                form: this.$inertia.form({
                    name: this.team.name,
                    description: this.team.description,
                    label_classes: this.team.label_classes,
                    default_label_class: null,
                })
            }
        },

        created() {
            if (this.team.default_label_class) {
                let index = this.form.label_classes.findIndex((label_class) => {
                    return label_class.id === this.team.default_label_class.id;
                });

                this.form.default_label_class = (index >= 0) ? index : null;
            }
        },

        methods: {
            updateTeamName() {
                this.form.put(route('teams.update', this.team), {
                    errorBag: 'updateTeamName',
                    preserveScroll: true
                });
            },

            addLabelClass() {
                this.add(this.form.label_classes);
            },

            removeLabelClass(i) {
                let default_label_class = this.form.label_classes[this.form.default_label_class];

                this.remove(this.form.label_classes, i);

                let $index = this.form.label_classes.indexOf(default_label_class);
                this.form.default_label_class = ($index >= 0) ? $index : null;
            }
        },
    })
</script>
