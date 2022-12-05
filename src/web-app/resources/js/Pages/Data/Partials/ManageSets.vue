<template>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <div class="pt-4 pb-2">
                <slot name="title" />
            </div>
        </div>

        <ul class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2">
            <slot name="data" />
        </ul>
    </div>
</template>

<script>
import {defineComponent} from 'vue'

export default defineComponent({})
</script>
