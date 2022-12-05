<template>
    <app-layout title="Projects">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Projects
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <div class="pt-4 pb-2">
                            <div class="flex justify-between items-center">
                                <h1 class="text-2xl col-span-12">Projects</h1>

                                <Link :href="route('teams.create')">
                                    <jet-button>New Project</jet-button>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <ul class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2">
                        <li v-for="team in teams" :key="team.id">
                            <div class="p-6 border border-gray-200">
                                <div class="flex items-center relative">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>

                                    <div class="ml-4 flex items-center">
                                        <Link class="text-lg text-gray-600 leading-7 font-semibold mr-2" :href="route('teams.show', team)">
                                            {{ team.name }}
                                        </Link>

                                        <span v-if="$page.props.user.current_team_id === team.id"
                                              class="px-3 py-1 text-sm font-medium text-white bg-green-600 rounded-full"
                                        >
                                            Selected
                                        </span>
                                        <Link v-else class="px-3 py-1 text-sm font-medium text-white bg-black rounded-full"
                                              :href="route('current-team.update')"
                                              method="PUT"
                                              :data="{
                                                  'team_id': team.id,
                                              }"
                                        >
                                            Select
                                        </Link>
                                    </div>
                                </div>

                                <div class="ml-12">
                                    <div class="mt-2 text-sm text-gray-500">
                                        {{ team.description }}
                                    </div>

                                    <Link :href="route('teams.show', team)">
                                        <div class="mt-3 flex items-center text-sm font-semibold text-indigo-700">
                                            <div>View Project</div>

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
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from "@inertiajs/inertia-vue3"
import JetButton from '@/Jetstream/Button.vue'

export default defineComponent({
    components: {
        AppLayout,
        Link,
        JetButton,
    },

    props: [
        'teams',
    ],
})
</script>
