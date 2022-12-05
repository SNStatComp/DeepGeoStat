<template>
    <div class="p-6 border border-gray-200">
        <div class="flex items-center relative">
            <slot name="svg"/>

            <div class="ml-4 text-lg text-gray-600 leading-7 font-semibold">
                <Link :href="url">
                    <slot name="title" />
                </Link>
            </div>

            <button class="absolute top-0 right-0 text-red-500 ml-4" @click="confirmDelete = true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
        </div>

        <div class="ml-12">
            <div class="mt-2 text-sm text-gray-500">
                <slot name="description" />
            </div>

            <Link :href="url">
                <div class="mt-3 flex items-center text-sm font-semibold text-indigo-700">
                    <div>View {{ name }}</div>

                    <div class="ml-1 text-indigo-500">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                            <path fill-rule="evenodd"
                                  d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                  clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </Link>
        </div>
    </div>

    <confirmation-modal :show="confirmDelete" @close="confirmDelete = false">
        <template #title>
            Delete {{ name }}
        </template>

        <template #content>
            Are you sure you want to delete this {{ name.toLowerCase() }}? Once a {{ name.toLowerCase() }} is deleted, all of its resources and data will be permanently deleted.
        </template>

        <template #footer>
            <secondary-button @click="confirmDelete = false">
                Cancel
            </secondary-button>

            <danger-button class="ml-3" @click="deleteData" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Delete {{ name }}
            </danger-button>
        </template>
    </confirmation-modal>
</template>

<script>
import {defineComponent} from 'vue'
import { Link } from "@inertiajs/inertia-vue3";
import SecondaryButton from '@/Jetstream/SecondaryButton'
import DangerButton from '@/Jetstream/DangerButton'
import ConfirmationModal from '@/Jetstream/ConfirmationModal'

export default defineComponent({
    components: {
        Link,
        SecondaryButton,
        DangerButton,
        ConfirmationModal,
    },

    emits: [
        'delete',
    ],

    props: {
        name: String,
        url: String,
        deleteUrl: String,
    },

    data() {
        return {
            confirmDelete: false,
            form: this.$inertia.form(),
        }
    },

    methods: {
        deleteData() {
            this.form.delete(this.deleteUrl);
        }
    }
})
</script>
