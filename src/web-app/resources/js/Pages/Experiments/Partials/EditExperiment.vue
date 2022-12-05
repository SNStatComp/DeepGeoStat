<template>
    <modal>
        <template #title>
            Experiment Settings
        </template>

        <template #content>
            <div class="space-y-6">
<!--                <div class="col-span-6 sm:col-span-4">-->
<!--                    <jet-label for="experiment_train_data" value="Experiment Train Data*"/>-->

<!--                    <jet-select id="experiment_train_data" class="block w-full mt-1" v-model="form.train_data">-->
<!--                        <option v-for="train_data in experimentData" :value="train_data.id" :key="train_data.id">{{ train_data.name }}</option>-->
<!--                    </jet-select>-->
<!--                    <jet-input-error :message="form.errors.train_data" class="mt-2"/>-->
<!--                </div>-->

<!--                <div class="col-span-6 sm:col-span-4">-->
<!--                    <jet-label for="experiment_test_data" value="Experiment Test Data"/>-->

<!--                    <jet-select id="experiment_test_data" class="block w-full mt-1" v-model="form.test_data">-->
<!--                        <option value="">20% of Train Data</option>-->
<!--                        <option v-for="test_data in experimentData" :value="test_data.id" :key="test_data.id">{{ test_data.name }}</option>-->
<!--                    </jet-select>-->
<!--                    <jet-input-error :message="form.errors.test_data" class="mt-2"/>-->
<!--                </div>-->

<!--                <div class="col-span-6 sm:col-span-4">-->
<!--                    <jet-label for="experiment_title" value="Experiment Title*"/>-->
<!--                    <jet-input id="experiment_title" type="text" class="block w-full mt-1 cursor-not-allowed disabled:text-gray-500" v-model="form.name" disabled />-->
<!--                    <jet-input-error :message="form.errors.name" class="mt-2"/>-->
<!--                </div>-->

<!--                <div class="col-span-6 sm:col-span-4">-->
<!--                    <jet-label for="experiment_description" value="Experiment Description"/>-->
<!--                    <jet-textarea id="experiment_description" type="text" class="block w-full mt-1 cursor-not-allowed disabled:text-gray-500" v-model="form.description" disabled />-->
<!--                    <jet-input-error :message="form.errors.description" class="mt-2"/>-->
<!--                </div>-->

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="experiment_model" value="Experiment Model*"/>

                    <jet-select id="experiment_model" class="block w-full mt-1 cursor-not-allowed" v-model="form.model" disabled>
                        <option value="resnet50">ResNet50</option>
                        <option value="vgg16">VGG16</option>
                        <option value="inceptionv3">InceptionV3</option>
                    </jet-select>
                    <jet-input-error :message="form.errors.model" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="experiment_epochs" value="Experiment Epochs*"/>
                    <jet-input id="experiment_epochs" type="number" class="block w-full mt-1 cursor-not-allowed disabled:text-gray-500" v-model.number="form.epochs" disabled />
                    <jet-input-error :message="form.errors.epochs" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="experiment_batch_size" value="Experiment Batch Size*"/>
                    <jet-input id="experiment_batch_size" type="number" class="block w-full mt-1 cursor-not-allowed disabled:text-gray-500" v-model.number="form.batch_size" disabled />
                    <jet-input-error :message="form.errors.batch_size" class="mt-2"/>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="experiment_learning_rate" value="Experiment Learning Rate*"/>
                    <jet-input id="experiment_learning_rate" type="number" class="block w-full mt-1 cursor-not-allowed disabled:text-gray-500" v-model.number="form.learning_rate" disabled />
                    <jet-input-error :message="form.errors.learning_rate" class="mt-2"/>
                </div>
            </div>
        </template>

        <template #footer>
            <jet-secondary-button @click="$emit('close')">Close</jet-secondary-button>

<!--            <jet-button class="ml-3" @click="editExperiment" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Edit Experiment</jet-button>-->
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import Modal from '@/Jetstream/DialogModal'
import JetLabel from '@/Jetstream/Label'
import JetInput from '@/Jetstream/Input'
import JetTextarea from '@/Jetstream/Textarea'
import JetSelect from '@/Jetstream/Select'
import JetInputError from '@/Jetstream/InputError'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'

export default defineComponent({
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

    props: {
        experiment: Object,
    },

    data() {
        return {
            form: this.$inertia.form({
                test_data: null,
                train_data: null,
                name: this.experiment.name,
                description: this.experiment.description,
                model: this.experiment.options.model,
                epochs: this.experiment.options.epochs,
                batch_size: this.experiment.options.batch_size,
                learning_rate: this.experiment.options.learning_rate,
            }),
        }
    },

    methods: {
        editExperiment() {
            // this.form.post(route('experiments.store'), {
            //     errorBag: 'createExperiment',
            // })
        },
    }
})
</script>
>
