<template>
    <div>
        <ShSelect v-model="selectedValue" @update:model-value="onValueChange">
            <SelectTrigger class="w-full">
                <SelectValue :placeholder="placeholder" />
            </SelectTrigger>
            <SelectContent class="bg-black text-white">
                <SelectGroup>
                    <SelectLabel v-if="label" class="text-sm font-semibold">{{ label }}</SelectLabel>
                    <SelectItem v-for="item in options" :key="item[valueKey]" :value="item[valueKey]">
                        {{ item[displayKey] }}
                    </SelectItem>
                </SelectGroup>
            </SelectContent>
        </ShSelect>
    </div>
</template>



<script>

// <Select
//   v-model="color"
//   :options="colors"
//   label="Pick a color"
//   placeholder="Choose…"
//   @change="onChanged"
// />
//   methods: {
//     onChanged(v) {
//       console.log('Changed to:', v);
//     },
//   },

import { Select as ShSelect, SelectLabel, SelectGroup, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@shadcn-vue-components/select';

export default {
    name: 'Select',
    components: {
        ShSelect, SelectContent, SelectItem, SelectTrigger, SelectValue, SelectGroup, SelectLabel
    },
    props: {
        placeholder: {
            type: String,
            default: 'Select an option',
        },
        label: {
            type: String,
            default: null,
        },
        options: {
            type: Array,
            default: () => [],
        },
        modelValue: {
            type: [String, Number, Object],
            default: null,
        },
        valueKey: {
            type: String,
        },
        displayKey: {
            type: String,
        },
    },
    data() {
        return {
            selectedValue: this.modelValue
        };
    },
    created() {
        console.log(this.selectedValue);
    },
    watch: {
        modelValue(newValue) {
            this.selectedValue = newValue;
        }
    },
    methods: {
        onValueChange(value) {
            this.$emit('update:modelValue', value);
            this.$emit('change', value);
        }
    },
};
</script>