<template>
    <Combobox 
        @update:modelValue="value => emit('update:modelValue', value)"
        :model-value="props.modelValue"
        :multiple="props.multiple"
    >
      <div class="relative mt-1">
        <div
          class="relative w-full cursor-default overflow-hidden border border-gray-300 rounded-md bg-white text-left shadow-sm "
        >
          <ComboboxButton
            as="div"
          >
            <span
              class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
            >
              <ChevronDownIcon
                  class="h-5 w-5 text-gray-400"
                  aria-hidden=true
              />
            </span>

            <div v-if="Array.isArray(props.modelValue)" class="flex flex-wrap pl-3 py-2 pr-10 leading-relaxed">
                <p v-for="(option, i) in label" :key="i" class="mr-2">{{ option }}<span v-if="i !== (label.length - 1)">,</span></p>
                
                <ComboboxInput
                    class="p-0 border-none text-gray-900 focus:ring-0"
                    @change="query = $event.target.value"
                />
            </div>
            <ComboboxInput
                v-else
                class="border-none py-2 pl-3 pr-10 leading-5 text-gray-900 focus:ring-0"
                :displayValue="(displayOption) => props.options.find(option => option.value === displayOption)?.label"
                @change="query = $event.target.value"
            />
          </ComboboxButton>
        </div>
        <TransitionRoot
          leave="transition ease-in duration-100"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
          @after-leave="query = ''"
        >
          <ComboboxOptions
            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
          >
            <div
              v-if="filteredOptions.length === 0 && query !== ''"
              class="relative cursor-default select-none py-2 px-4 text-gray-700"
            >
              Nothing found.
            </div>

            <ComboboxOption
              v-slot="{ selected, active }"
              v-for="option in filteredOptions"
              :key="option.label"
              :value="option.value"
              :disabled="option.disabled"
              as="template"
            >
              <li
                class="relative cursor-default select-none py-2 pl-10 pr-4"
                :class="{
                  'bg-indigo-100 text-indigo-900': active,
                  'text-gray-900': !active,
                }"
              >
                <span
                  class="block truncate"
                  :class="{ 'font-medium': selected, 'font-normal': !selected }"
                >
                  {{ option.label }}
                </span>
                <span
                  v-if="selected"
                  class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-600"
                >
                  <CheckIcon class="h-5 w-5" aria-hidden="true" />
                </span>
              </li>
            </ComboboxOption>
          </ComboboxOptions>
        </TransitionRoot>
      </div>
    </Combobox>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
  TransitionRoot,
} from '@headlessui/vue'
import { CheckIcon, ChevronDownIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
    modelValue: [String, Number, Array],
    options: Array,
    placeholder: {
        String,
        default: 'Select option(s)',
    },
    multiple: Boolean,
})

const emit = defineEmits([
    'update:modelValue',
])

const query = ref('')
const filteredOptions = computed(() =>
  query.value === ''
    ? props.options
    : props.options.filter((option) =>
        option.label
          .toLowerCase()
          .replace(/\s+/g, '')
          .includes(query.value.toLowerCase().replace(/\s+/g, ''))
      )
)

const label = computed(() => {
    return props.options.filter((option) => {
        return props.modelValue.includes(option.value)
    }).map(option => option.label)
})

</script>