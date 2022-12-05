<template>
    <modal>
        <template #title>
            Create Annotation Campaign
        </template>

        <template #content>
            <div class="space-y-6">
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="annotation_datasets" value="Annotation Campaign Dataset(s)*"/>

                    <select id="annotation_datasets" v-model.number="form.datasets" class="w-full" multiple>
                        <option v-for="dataset in datasets" :key="dataset.id" :value="dataset.id">{{ dataset.title }}</option>
                    </select>
<!--                    <jet-select id="annotation_datasets" class="block w-full mt-1" v-model="form.datasets" multiple>-->
<!--                        <option v-for="dataset in datasets" :value="dataset.id">{{ dataset.title }}</option>-->
<!--                    </jet-select>-->
                    <jet-input-error :message="form.errors.datasets" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="annotation_title" value="Annotation Campaign Title*"/>
                    <jet-input id="annotation_title" type="text" class="block w-full mt-1" v-model="form.title" />
                    <jet-input-error :message="form.errors.title" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="annotation_description" value="Annotation Campaign Description"/>
                    <jet-textarea id="annotation_description" type="text" class="block w-full mt-1" v-model="form.description" />
                    <jet-input-error :message="form.errors.description" class="mt-2"/>
                </div>
            </div>
        </template>

        <template #footer>
            <jet-secondary-button @click="$emit('close')">Close</jet-secondary-button>

            <jet-button class="ml-3" @click="createAnnotationCampaign" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Create Annotation Campaign</jet-button>
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
            }),
        }
    },

    methods: {
        createAnnotationCampaign() {
            this.form.post(route('data.annotations.store'), {
                errorBag: 'createAnnotationCampaign',
                onSuccess: () => {
                  this.$emit('close');

                  this.form.reset();
                },
            });
        }
    },
})
</script>
